<?php
namespace Kyklydse\ComposerGraph;


use Composer\DependencyResolver\Pool;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Kyklydse\ComposerGraph\Dumper\GraphvizDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class GraphCommand extends Command
{
    protected function configure()
    {
        $this->setName('graph');
        $this->addArgument('file', InputArgument::OPTIONAL, 'Composer file to parse', 'composer.json');
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file pattern', 'graph.%t');
        $this->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Output type', 'svg');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $outputPattern = $input->getOption('output');
        if (false === strpos($outputPattern, '%t')) {
            $outputPattern .= '.%t';
        }
        $io = new ConsoleIO($input, $output, $this->getHelperSet());
        $composer = Factory::create($io, $file);
        $pool = new Pool($composer->getPackage()->getMinimumStability(), $composer->getPackage()->getStabilityFlags());
        foreach ($composer->getRepositoryManager()->getRepositories() as $repo) {
            $pool->addRepository($repo);
        }

        $graph = new Graph($pool, $composer->getPackage());

        $node = $graph->getRootNode();

        $dumper = new GraphvizDumper();

        $filename = strtr($outputPattern, array('%t' => $input->getOption('type')));
        $dotData = $dumper->dump($node);
        $process = new Process(
            sprintf('circo -T%s -o%s', $input->getOption('type'), $filename),
            null,
            null,
            $dotData
        );
        $process->run();
    }
}
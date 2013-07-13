<?php
namespace Kyklydse\ComposerGraph;


use Composer\DependencyResolver\Pool;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Kyklydse\ComposerGraph\Dumper\GraphvizDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Process\Process;

class GraphCommand extends Command
{
    public function __construct()
    {
        parent::__construct('graph');
    }

    protected function configure()
    {
        $this->addArgument('file', InputArgument::OPTIONAL, 'Composer file to parse', 'composer.json');
        $this->addArgument('output', InputArgument::OPTIONAL, 'Output file pattern', 'graph.$i.svg');
    }

    public function execute(Input $input, Output $output)
    {
        $file = $input->getArgument('file');
        $outputPattern = $input->getArgument('output');
        if (false === strpos($outputPattern, '$i')) {
            $outputPattern .= '.$i';
        }
        $io = new ConsoleIO($input, $output, $this->getHelperSet());
        $composer = Factory::create($io, $file);
        $pool = new Pool($composer->getPackage()->getMinimumStability(), $composer->getPackage()->getStabilityFlags());
        foreach ($composer->getRepositoryManager()->getRepositories() as $repo) {
            $pool->addRepository($repo);
        }

        $graph = new Graph($pool, $composer->getPackage());

        $node = $graph->getRootNode();
        $maps = $graph->getChoiceMaps();

        $dumper = new GraphvizDumper();

        foreach ($maps as $i => $map) {
            $filename = str_replace('$i', $i, $outputPattern);
            $dotData = $dumper->dump($node, $map);
            $process = new Process('dot -Tsvg -o'.$filename, null, null, $dotData);
            $process->run();
        }
    }
}
<?php
namespace Kyklydse\ComposerGraph;


use Composer\DependencyResolver\Pool;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Kyklydse\ComposerGraph\Dumper\ConsoleDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\Output;

class GraphCommand extends Command
{
    public function __construct()
    {
        parent::__construct('graph');
    }

    protected function configure()
    {
        $this->addArgument('file', InputArgument::OPTIONAL, 'Composer file to parse', 'composer.json');
    }

    public function execute(Input $input, Output $output)
    {
        $file = $input->getArgument('file');
        $io = new ConsoleIO($input, $output, $this->getHelperSet());
        $composer = Factory::create($io, $file);
        $pool = new Pool($composer->getPackage()->getMinimumStability(), $composer->getPackage()->getStabilityFlags());
        foreach ($composer->getRepositoryManager()->getRepositories() as $repo) {
            $pool->addRepository($repo);
        }

        $graph = new Graph($pool);

        $node = $graph->getNode($composer->getPackage());

        $dumper = new ConsoleDumper($output);
        $dumper->dump($node);
    }
}
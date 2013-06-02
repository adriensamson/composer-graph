<?php

namespace Kyklydse\ComposerGraph\Dumper;

use Kyklydse\ComposerGraph\Node;
use Symfony\Component\Console\Output\Output;

class GraphvizDumper
{
    protected $output;
    protected $statements;
    protected $viewed;

    public function __construct(Output $output)
    {
        $this->output = $output;
    }

    public function dump(Node $node)
    {
        $this->viewed = array();
        $this->output->writeln('digraph {');
        $this->doDump($node, 0);
        $this->output->writeln('}');
    }

    protected function doDump(Node $node)
    {
        if (isset($this->viewed[(string) $node])) {
            return;
        }
        $this->viewed[(string) $node] = true;

        foreach ($node->getRequires() as $name => $choices) {
            $this->output->writeln(sprintf('"%s" -> "%s";', $node, $name));
            if (isset($this->viewed[(string) $name])) {
                continue;
            }
            $this->viewed[(string) $name] = true;
            foreach ($choices as $choice) {
                $this->output->writeln(sprintf('"%s" -> "%s" [style=dashed];', $name, $choice));
                $this->doDump($choice);
            }
        }
    }
}
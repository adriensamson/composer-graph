<?php

namespace Kyklydse\ComposerGraph\Dumper;

use Kyklydse\ComposerGraph\Node;
use Symfony\Component\Console\Output\Output;

class ConsoleDumper
{
    protected $output;
    protected $viewed;

    public function __construct(Output $output)
    {
        $this->output = $output;
    }

    public function dump(Node $node)
    {
        $this->viewed = array();
        $this->doDump($node, 0);
    }

    protected function doDump(Node $node, $level)
    {
        $prefix = str_repeat(' ', $level*2);
        $this->output->writeln($prefix . $node->getPackage());
        if (isset($this->viewed[(string) $node->getPackage()])) {
            $this->output->writeln($prefix.'  [Recursion]');
            return;
        }
        $this->viewed[(string) $node->getPackage()] = true;

        foreach ($node->getRequires() as $name => $choices) {
            $this->output->writeln($prefix.'* '.$name);
            foreach ($choices as $choice) {
                $this->doDump($choice, $level+1);
            }
        }
    }
}
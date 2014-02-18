<?php

namespace Kyklydse\ComposerGraph\Dumper;

use Kyklydse\ComposerGraph\ChoiceMap;
use Kyklydse\ComposerGraph\Node;
use Symfony\Component\Console\Output\Output;

class GraphvizDumper
{
    protected $output;
    protected $statements;
    protected $viewed;

    public function dump(Node $node, ChoiceMap $map = null)
    {
        $this->output = '';
        $this->viewed = array();
        $this->output .= "digraph {\n";
        $this->doDump($node, $map);
        $this->output .= "}\n";
        return $this->output;
    }

    protected function doDump(Node $node, ChoiceMap $map = null)
    {
        if (isset($this->viewed[(string) $node])) {
            return;
        }
        $this->viewed[(string) $node] = true;

        foreach ($node->getRequires() as $name => $choices) {
            if (isset($this->viewed[(string) $name])) {
                continue;
            }
            $this->viewed[(string) $name] = true;
            if (null === $map) {
                $this->output .= sprintf('"%s" [shape=diamond]', $name) . "\n";
                $this->output .= sprintf('"%s" -> "%s";', $node, $name) . "\n";
                foreach ($choices as $choice) {
                    $this->output .= sprintf('"%s" -> "%s" [style=dashed];', $name, $choice) . "\n";
                    $this->doDump($choice);
                }
            } else {
                $choice = $map->getChoice(explode(' ', $name)[0]);
                if (null !== $choice) {
                    $this->output .= sprintf('"%s" -> "%s";', $node, $choice) . "\n";
                    $this->doDump($choice, $map);
                }
            }
        }
    }

    protected function doDumpConfilcts($conflicts)
    {
        foreach ($conflicts as $conflict) {
            $this->output .= sprintf('"%s" -> "%s" [color=red,dir=both];', $conflict[0], $conflict[1]) . "\n";
        }
    }
}
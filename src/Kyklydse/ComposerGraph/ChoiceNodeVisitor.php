<?php

namespace Kyklydse\ComposerGraph;

class ChoiceNodeVisitor
{
    protected $maps;

    public function getMaps(Node $node, ChoiceMap $choiceMap)
    {
        $this->maps = array($choiceMap);
        $this->visitNode($node, $choiceMap, array());

        return $this->maps;
    }

    protected function visitNode(Node $node, ChoiceMap $choiceMap, $visited)
    {
        if (isset($visited[(string) $node])) {
            return;
        }
        $visited[(string) $node] = true;
        $this->visitRequires($node->getRequires(), $choiceMap, $visited);


    }

    protected function visitRequires($requires, ChoiceMap $choiceMap, $visited)
    {
        if (0 === count($requires)) {
            return;
        }
        reset($requires);
        $targetName = key($requires);
        $choices = array_shift($requires);
        $nextMap = null;
        foreach ($choices as $choice) {
            if ($choiceMap->getChoice(explode(' ', $targetName)[0])) {
                continue;
            }
            if ($nextMap !== null) {
                $this->maps[] = $choiceMap;
            }
            $nextMap = clone $choiceMap;
            if ($choiceMap->choose($choice)) {
                $this->visitRequires($requires, $choiceMap, $visited);
                $this->visitNode($choice, $choiceMap, $visited);
            }
            $choiceMap = $nextMap;
            //break;
        }
    }
}

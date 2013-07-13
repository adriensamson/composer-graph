<?php

namespace Kyklydse\ComposerGraph;

class ChoiceMap
{
    /**
     * @var Node[][] $possibleChoices[targetName][]
     */
    protected $possibleChoices;

    /**
     * @var Node[] $fixedChoices[targetName]
     */
    protected $fixedChoices = array();

    protected $conflicts = array();

    public function __construct($possibleChoices)
    {
        $this->possibleChoices = $possibleChoices;
    }

    public function choose($node)
    {
        if (in_array($node, $this->fixedChoices)) {
            return true;
        }
        foreach ($this->possibleChoices as $targetName => $choices) {
            if (in_array($node, $choices)) {
                if (isset($this->fixedChoices[$targetName])) {
                    $this->conflicts[] = array($this->fixedChoices[$targetName], $node);
                }
                $this->fixedChoices[$targetName] = $node;
            }
        }
        return 0 === count($this->conflicts);
    }

    public function getChoice($targetName)
    {
        return isset($this->fixedChoices[$targetName]) ? $this->fixedChoices[$targetName] : null;
    }

    public function getConflicts()
    {
        return $this->conflicts;
    }
}

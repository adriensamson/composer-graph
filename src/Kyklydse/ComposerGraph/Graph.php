<?php

namespace Kyklydse\ComposerGraph;

use Composer\DependencyResolver\Pool;
use Composer\Package\Link;
use Composer\Package\PackageInterface;

class Graph
{
    protected $pool;

    /**
     * @var Node[][][] $choices[targetName][constraint][]
     */
    protected $choices = array();

    /**
     * @var Node[][] $nodes[packageName][packageVersion]
     */
    protected $nodes = array();

    protected $rootNode;

    public function __construct(Pool $pool, PackageInterface $package)
    {
        $this->pool = $pool;
        $this->rootNode = new Node($package);
        $this->loadRequires($this->rootNode);
    }

    public function getRootNode()
    {
        return $this->rootNode;
    }

    public function getChoiceMaps()
    {
        $possibleChoices = array();
        foreach ($this->choices as $targetName => $arr) {
            $possibleChoices[$targetName] = array();
            foreach($arr as $constraint => $choices) {
                $possibleChoices[$targetName] += $choices;
            }
        }
        $choiceMap = new ChoiceMap($possibleChoices);
        $visitor = new ChoiceNodeVisitor();

        return $visitor->getMaps($this->rootNode, $choiceMap);
    }

    protected function loadRequires(Node $node)
    {
        foreach ($node->getPackage()->getRequires() as $link) {
            /* @var $link \Composer\Package\Link */
            if ($link->getTarget() === 'php' || preg_match('/^ext-[a-z]+$/', $link->getTarget())) {
                continue;
            }
            $choices = $this->getChoices($link);
            $node->addRequire($link, $choices);
        }
    }

    protected function getChoices(Link $link)
    {
        if (isset($this->choices[$link->getTarget()][$link->getPrettyConstraint()])) {
            return $this->choices[$link->getTarget()][$link->getPrettyConstraint()];
        }

        $provides = $this->pool->whatProvides($link->getTarget(), $link->getConstraint());
        $choices  = array();

        foreach ($provides as $provide) {
            $node = $this->getNode($provide);
            if (!in_array($node, $choices)) {
                $choices[] = $node;
            }
        }
        $this->choices[$link->getTarget()][$link->getPrettyConstraint()] = $choices;
        array_walk($choices, array($this, 'loadRequires'));
        return $choices;
    }

    protected function getNode(PackageInterface $package)
    {
        if (isset($this->nodes[$package->getName()][$package->getVersion()])) {
            return $this->nodes[$package->getName()][$package->getVersion()];
        }

        if (isset($this->nodes[$package->getName()])) {
            foreach ($this->nodes[$package->getName()] as $node) {
                if ($node->match($package)) {
                    $node->addVersion($package->getVersion());
                    $this->nodes[$package->getName()][$package->getVersion()] = $node;

                    return $node;
                }
            }
        }

        $node = new Node($package);
        $this->nodes[$package->getName()][$package->getVersion()] = $node;

        return $node;
    }
}

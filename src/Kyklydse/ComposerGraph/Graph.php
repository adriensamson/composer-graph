<?php

namespace Kyklydse\ComposerGraph;

use Composer\DependencyResolver\Pool;
use Composer\Package\PackageInterface;

class Graph
{
    protected $pool;
    protected $nodes = array();

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function getNode(PackageInterface $package)
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

        $this->nodes[$package->getName()][$package->getVersion()] = $node = new Node($package);
        $this->loadRequires($node);
        return $node;
    }

    protected function loadRequires(Node $node)
    {
        foreach ($node->getPackage()->getRequires() as $link) {
            /* @var $link \Composer\Package\Link */
            $choices = $this->pool->whatProvides($link->getTarget(), $link->getConstraint());
            $nodeChoices = array_unique(array_map(array($this, 'getNode'), $choices));
            $node->addRequire($link, $nodeChoices);
        }
    }
}
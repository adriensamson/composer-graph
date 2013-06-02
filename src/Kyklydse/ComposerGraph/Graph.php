<?php

namespace Kyklydse\ComposerGraph;

use Composer\DependencyResolver\Pool;
use Composer\Package\Link;
use Composer\Package\PackageInterface;

class Graph
{
    protected $pool;
    protected $choices = array();

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function getRootNode(PackageInterface $package)
    {
        $node = new Node($package);
        $this->loadRequires($node);
        return $node;
    }

    protected function loadRequires(Node $node)
    {
        foreach ($node->getPackage()->getRequires() as $link) {
            /* @var $link \Composer\Package\Link */
            $choices = $this->getChoices($link);
            $node->addRequire($link, $choices);
        }
    }

    protected function getChoices(Link $link)
    {
        if (isset($this->choices[$this->getLinkId($link)])) {
            return $this->choices[$this->getLinkId($link)];
        }

        $provides = $this->pool->whatProvides($link->getTarget(), $link->getConstraint());
        $choices  = array();

        foreach ($provides as $provide) {
            $matched = false;
            foreach ($choices as $choice) {
                if ($choice->match($provide)) {
                    $choice->addVersion($provide->getVersion());
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                $choices[] = new Node($provide);
            }
        }
        $this->choices[$this->getLinkId($link)] = $choices;
        array_walk($choices, array($this, 'loadRequires'));
        return $choices;
    }

    protected function getLinkId(Link $link)
    {
        return sprintf('%s (%s)', $link->getTarget(), $link->getPrettyConstraint());
    }
}
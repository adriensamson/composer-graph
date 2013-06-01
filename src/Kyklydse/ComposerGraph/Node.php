<?php

namespace Kyklydse\ComposerGraph;

use Composer\Package\Link;
use Composer\Package\PackageInterface;

class Node
{
    protected $package;
    protected $versions;
    protected $requires;

    public function __construct(PackageInterface $package)
    {
        $this->package = $package;
        $this->versions = array($package->getVersion());
        $this->requires = array();
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function getRequires()
    {
        return $this->requires;
    }

    public function addRequire(Link $link, $choices)
    {
        $this->requires[sprintf('%s (%s)', $link->getTarget(), $link->getPrettyConstraint())] = $choices;
    }

    public function addVersion($version)
    {
        $this->versions[] = $version;
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->package->getName(), implode(', ', $this->versions));
    }

    public function match(PackageInterface $package)
    {
        if ($package->getName() !== $this->package->getName()) {
            return false;
        }
        $requires = array_map(function(Link $link) {return sprintf('%s (%s)', $link->getTarget(), $link->getPrettyConstraint());}, $package->getRequires());
        return array_diff($requires, array_keys($this->requires)) == array() && array_diff(array_keys($this->requires), $requires) == array();
    }
}

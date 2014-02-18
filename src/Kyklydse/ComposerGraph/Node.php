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
        if (count($this->versions) > 4) {
            $versions = sprintf('%s, ..., %s', $this->versions[0], end($this->versions));
        } else {
            $versions = implode(', ', $this->versions);
        }
        return sprintf('%s (%s)', $this->package->getName(), $versions);
    }

    public function match(PackageInterface $package)
    {
        if ($package->getName() !== $this->package->getName()) {
            return false;
        }
        $itsRequires = array_map(function(Link $link) {return sprintf('%s (%s)', $link->getTarget(), $link->getPrettyConstraint());}, $package->getRequires());
        $myRequires = array_map(function(Link $link) {return sprintf('%s (%s)', $link->getTarget(), $link->getPrettyConstraint());}, $this->package->getRequires());

        return array_diff($myRequires, $itsRequires) == array() && array_diff($itsRequires, $myRequires) == array();
    }
}

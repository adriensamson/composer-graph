<?php

namespace Kyklydse\ComposerGraph;

use Composer\Package\PackageInterface;

class Node
{
    protected $package;
    protected $requires;

    public function __construct(PackageInterface $package)
    {
        $this->package = $package;
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

    public function addRequire($name, $choices)
    {
        $this->requires[$name] = $choices;
    }
}

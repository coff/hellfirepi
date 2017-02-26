<?php

namespace Coff\Hellfire\Application;

use Pimple\Container;
use Symfony\Component\Console\Application;

class HellfireApplication extends Application
{
    protected $container;

    public function setContainer(Container $container) {
        $this->container = $container;

        return $this;
    }

    public function getContainer() {
        return $this->container;
    }
}

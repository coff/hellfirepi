<?php

namespace Coff\Hellfire\Server;

use Pimple\Container;

/**
 * Abstract Server class
 *
 *
 */
abstract class Server extends \Coff\OneWire\Server\Server
{
    /**
     * @var Container $container;
     */
    protected $container;

    /**
     * Sets service container object
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container) {
        $this->container = $container;

        return $this;
    }

    /**
     * Returns service container object
     * @return Container
     */
    public function getContainer() {
        return $this->container;
    }

}

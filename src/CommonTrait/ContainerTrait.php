<?php

namespace Coff\Hellfire\CommonTrait;

use Pimple\Container;

trait ContainerTrait
{
    /** @var Container */
    protected $container;

    /**
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container) {
        $this->container = $container;

        return $this;
    }

    /**
     * @return Container
     */
    public function getContainer() {
        return $this->container;
    }
}

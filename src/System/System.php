<?php

namespace Coff\Hellfire\System;

use Coff\DataSource\DataSourceInterface;
use Coff\Hellfire\CommonTrait\ContainerTrait;
use Coff\Hellfire\CommonTrait\EventDispatcherTrait;
use Pimple\Container;

abstract class System implements SystemInterface, DataSourceInterface
{
    use EventDispatcherTrait;
    use ContainerTrait;

    protected $state;

    /**
     * @return $this
     */
    public function init() {

        return $this;
    }

    public function getState() {
        return $this->state;
    }

    /**
     * @param $state
     * @return $this
     */
    public function setState($state) {
        $this->state = $state;

        return $this;
    }

    public function update() {

        return $this;
    }

    public function getValue()
    {
        return $this->getState();
    }

    public function getStamp()
    {
        return time();
    }

}

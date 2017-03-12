<?php

namespace Coff\Hellfire\System;

use Coff\DataSource\DataSourceInterface;
use Coff\Hellfire\CommonTrait\ContainerTrait;
use Coff\Hellfire\CommonTrait\EventDispatcherTrait;
use Psr\Log\LoggerAwareTrait;

abstract class System implements SystemInterface, DataSourceInterface
{
    use EventDispatcherTrait;
    use ContainerTrait;
    use LoggerAwareTrait;

    protected $state;

    /**
     * @return $this
     */
    public function init() {
        $this->logger->info('System ' . (new \ReflectionClass($this))->getShortName() . ' initialized');
        return $this;
    }

    public function getState() {
        return $this->state;
    }

    public function isState($state) {
        return $this->state === $state;
    }

    /**
     * @param $state
     * @return $this
     */
    public function setState($state) {
        $this->logger->info('System ' . (new \ReflectionClass($this))->getShortName() . ' state changes from ' . $this->state . ' to ' . $state);
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

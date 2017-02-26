<?php

namespace Coff\Hellfire\System;

use Coff\Hellfire\Relay\Relay;

trait PumpTrait
{
    /**
     * @var Relay
     */
    protected $pump;

    /**
     * Sets pump that sends boiler's heat to buffer tank.
     * @param Relay $pump
     * @return $this
     */
    public function setPump(Relay $pump) {
        $this->pump = $pump;

        return $this;
    }

    /**
     * @return Relay
     */
    public function getPump()
    {
        return $this->pump;
    }
}

<?php

namespace Coff\Hellfire\ComponentArray;

use Coff\Hellfire\Relay\Relay;

/**
 * RelayArray
 *
 * Control over whole array of relays.
 */
class RelayArray extends DataSourceArray
{
    /**
     * Turns all relays off
     */
    public function off() {

        /** @var Relay $relay */
        foreach ($this->components as $relay) {
            $relay->off();
        }
    }

    /**
     * Turns all relays on
     */
    public function on() {

        /** @var Relay $relay */
        foreach ($this->components as $relay) {
            $relay->on();
        }
    }


}

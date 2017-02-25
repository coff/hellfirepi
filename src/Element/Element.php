<?php

namespace Coff\Hellfire\Heater;

abstract class Element implements ElementInterface
{

    protected $state;

    public function getState() {
        return $this->state;
    }

    public function setState($state) {
        $this->state = $state;

        return $this;
    }

}

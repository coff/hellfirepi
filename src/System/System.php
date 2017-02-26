<?php

namespace Coff\Hellfire\System;

abstract class System implements SystemInterface
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

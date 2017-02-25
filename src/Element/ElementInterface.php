<?php

namespace Coff\Hellfire\Heater;

interface ElementInterface
{
    public function getState();

    public function setState($state);

    public function process();

}

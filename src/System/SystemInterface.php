<?php

namespace Coff\Hellfire\System;

interface SystemInterface
{
    public function getState();

    public function setState($state);
}

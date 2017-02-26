<?php

namespace Coff\Hellfire\System;

class HeaterSystem extends System
{
    use PumpTrait;
    use SensorArrayTrait;

    const
        SENSOR_HIGH = 'high',
        SENSOR_LOW = 'low';

    public function process()
    {
        // TODO: Implement process() method.
    }
}

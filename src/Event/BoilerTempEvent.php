<?php

namespace Coff\Hellfire\Event;

use Coff\Hellfire\ComponentArray\BoilerSensorArray;

class BoilerTempEvent extends Event
{
    const
        ON_RAISE        = 'boiler_temp.raise',
        ON_DROP         = 'boiler_temp.drop',
        ON_TOO_HIGH     = 'boiler_temp.too_high',
        ON_TARGET       = 'boiler_temp.target',
        ON_TOO_LOW      = 'boiler_temp.too_low',
        ON_RANGE_UP     = 'boiler_temp.range_up',
        ON_RANGE_DOWN   = 'boiler_temp.range_down';

    protected $boilerSensorArray;

    public function __construct(BoilerSensorArray $array)
    {
        $this->boilerSensorArray = $array;
    }

    /**
     * @return BoilerSensorArray
     */
    public function getBoilerSensorArray()
    {
        return $this->boilerSensorArray;
    }
}

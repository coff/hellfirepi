<?php

namespace Coff\Hellfire\System;

use Coff\Hellfire\ComponentArray\DataSourceArray;

trait SensorArrayTrait
{
    /**
     * @var DataSourceArray
     */
    protected $sensorArray;

    /**
     * Sets sensor array with HI and LO sensor for boiler
     *
     * @param DataSourceArray $array
     * @return $this
     */
    public function setSensorArray(DataSourceArray $array) {
        $this->sensorArray = $array;

        return $this;
    }

    /**
     * @return DataSourceArray
     */
    public function getSensorArray()
    {
        return $this->sensorArray;
    }
}

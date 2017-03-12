<?php

namespace Coff\Hellfire\ComponentArray;

/**
 * BoilerSensorArray
 *
 * An object for boiler sensor array operations
 */
class BoilerSensorArray extends SensorArray
{
    const
        SENSOR_LOW  =   'low',
        SENSOR_HIGH =   'high';

    const
        RANGE_COLD = 0,
        RANGE_LOW = 1,
        RANGE_NORMAL = 2,
        RANGE_HIGH = 3,
        RANGE_CRITICAL  = 4;



    public function update()
    {
        $this->lastReadings = $this->readings;

        $ret = parent::update();

        $currentTemp = $this->getReading(self::SENSOR_HIGH);

        $this->lastRange = $this->range;

        switch (true) {
            case ($currentTemp <= 30):
                $this->range = self::RANGE_COLD;
                break;
            case ($currentTemp <= 65):
                $this->range = self::RANGE_LOW;
                break;
            case ($currentTemp <= 90):
                $this->range = self::RANGE_NORMAL;
                break;
            case ($currentTemp <= 96):
                $this->range = self::RANGE_HIGH;
                break;
            case ($currentTemp > 96):
                $this->range = self::RANGE_CRITICAL;
                break;
        }

        return $ret;
    }








}

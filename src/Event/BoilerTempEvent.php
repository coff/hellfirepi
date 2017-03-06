<?php

namespace Coff\Hellfire\Event;

class BoilerTempEvent extends Event
{
    const
        ON_TOO_HIGH = 'boiler_temp.too_high',
        ON_TOO_LOW  = 'boiler_temp.too_low';

    const
        RANGE_COLD = 0,
        RANGE_LOW = 1,
        RANGE_NORMAL = 2,
        RANGE_HIGH = 3,
        RANGE_CRITICAL  = 4;

    protected $percentTarget;

    protected $division;

    protected $range;

    protected $lastTemp;

    public function __construct($currentTemp, $lastTemp, $targetTemp)
    {
        $this->percentTarget = $currentTemp / $targetTemp * 100;

        switch (true) {
            case ($currentTemp <= 50):
                $this->range = self::RANGE_COLD;
                break;
            case ($currentTemp <= 70):
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

    }

    public function getLastTemp() {
        return $this->lastTemp;
    }

    public function getRange() {
        return $this->range;
    }

    public function getPercentTarget() {
        return $this->percentTarget;
    }
}

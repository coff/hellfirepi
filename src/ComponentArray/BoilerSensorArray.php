<?php

namespace Coff\Hellfire\ComponentArray;

use Coff\Hellfire\Exception\HellfireException;

/**
 * BoilerSensorArray
 *
 * An object for boiler sensor array operations
 */
class BoilerSensorArray extends DataSourceArray
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

    /** @var int */
    protected $range;

    protected $lastRange;

    protected $lastReadings = [];

    protected $targets = [];

    protected $targetsHysteresis = [];


    public function update()
    {
        $this->lastReadings = $this->readings;

        $ret = parent::update();

        $currentTemp = $this[self::SENSOR_HIGH];

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

    /**
     * @return int
     */
    public function getRange() {
        return $this->range;
    }

    public function getLastRange() {
        return $this->lastRange;
    }

    public function isRangeChanged() {
        return $this->range !== $this->lastRange;
    }

    /**
     * Returns previous readings
     * @param $sensorId
     * @return mixed
     */
    public function getLastReading($sensorId) {
        return $this->lastReadings[$sensorId];
    }

    /**
     * Checks whether current sensor reading is higher than the last one
     * @param $sensorId
     * @param int $precision optional parameter to round values before comparing,
     *              defaults to 0 so rounds to full celsius degrees
     * @return bool
     */
    public function isRising($sensorId, $precision=0) {
        if (round($this->readings[$sensorId],$precision) > round($this->lastReadings[$sensorId], $precision)) {
            return true;
        }
        return false;
    }

    /**
     * Checks whether current sensor reading is lower than the last one
     * @param $sensorId
     * @param int $precision optional parameter to round values before comparing,
     *              defaults to 0 so rounds to full celsius degrees
     * @return bool
     */
    public function isDropping($sensorId, $precision=0) {
        if (round($this->readings[$sensorId],$precision) > round($this->lastReadings[$sensorId], $precision)) {
            return true;
        }
        return false;
    }

    /**
     * Sets target temperatures per sensor
     * @param $sensorId
     * @param $readingValue
     * @param $hysteresis
     * @return $this
     */
    public function setTargets($sensorId, $readingValue, $hysteresis = 0.5) {
        $this->targets[$sensorId] = $readingValue;
        $this->targetsHysteresis[$sensorId] = $hysteresis;

        return $this;
    }

    /**
     * Checks whether sensor's temp. is below target
     * @param $sensorId
     * @return bool
     * @throws HellfireException
     */
    public function isReadingBelowTarget($sensorId) {
        if (false === isset($this->targets[$sensorId])) {
            throw new HellfireException('Target reading not specified!');
        }

        if ($this->readings[$sensorId] < $this->targets[$sensorId] - $this->targetsHysteresis[$sensorId]) {
            return true;
        }

        return false;
    }


    /**
     * Checks whether sensor's temp. is above target
     * @param $sensorId
     * @return bool
     * @throws HellfireException
     */
    public function isReadingAboveTarget($sensorId) {
        if (false === isset($this->targets[$sensorId])) {
            throw new HellfireException('Target reading not specified!');
        }

        if ($this->readings[$sensorId] > $this->targets[$sensorId] + $this->targetsHysteresis[$sensorId]) {
            return true;
        }

        return false;
    }
}

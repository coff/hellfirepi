<?php

namespace Coff\Hellfire\ComponentArray;

use Coff\Hellfire\Exception\HellfireException;

class SensorArray extends DataSourceArray
{

    protected $lastReadings = [];

    protected $targets = [];

    protected $targetsHysteresis = [];

    /** @var int */
    protected $range;

    protected $lastRange;

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

    public function getTargetPercent($sensorId, $hysteresis = 0) {

        $aboveRoomTemp = $this->readings[$sensorId] - 20;
        $targetAboveRoomTemp =  $this->targets[$sensorId] - 20 + $hysteresis;

        return $aboveRoomTemp / $targetAboveRoomTemp * 100;
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
     * Returns previous readings
     * @param $sensorId
     * @return mixed
     */
    public function getLastReading($sensorId) {
        return $this->lastReadings[$sensorId];
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
}

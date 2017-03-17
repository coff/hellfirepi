<?php

namespace Coff\Hellfire\ComponentArray;

use Coff\Hellfire\ComponentArray\DataSourceArray;

class BufferSensorArray extends DataSourceArray
{
    const
        SENSOR_LOW  =   'low',
        SENSOR_HIGH =   'high';

    const
        WATER_SPEC_HEAT = 4189.9,
        JOULES_IN_KW = 3600000;


    /** @var int buffer capacity in litres */
    protected $capacity;

    /** @var double power capacity in KWh */
    protected $powerCapacity;

    /** @var int power fill in KWh */
    protected $powerFill;

    /** @var int previous power fill in KWh */
    protected $lastPowerFill;

    /** @var int max and min temp for buffer tank */
    protected $minTemp=25, $maxTemp=85;

    /**
     * Sets tank's capacity in litres. Mostly for power capacity calculations.
     * @param $litres
     * @return $this
     */
    public function setCapacity($litres) {
        $this->capacity = $litres;

        return $this;
    }

    /**
     * Sets tank's maximum working temperature. Mostly for power capacity calculations.
     * @param $temp
     * @return $this
     */
    public function setMaxTemp($temp) {
        $this->maxTemp = $temp;

        return $this;
    }

    /**
     * Sets tank's nominal temperature. Mostly for power capacity calculations.
     * @param $temp
     * @return $this
     */
    public function setMinTemp($temp) {
        $this->minTemp = $temp;

        return $this;
    }

    /**
     * Returns available power capacity considering capacity and working temperatures
     * range [KWh]
     */
    public function getPowerCapacity() {
        return $this->powerCapacity;
    }


    /**
     * Returns amount of heat collected in buffer [KWh]
     */
    public function getPowerFill() {
        return $this->powerFill;
    }

    public function getLastPowerFill() {
        return $this->lastPowerFill;
    }

    public function isPowerFillRising() {
        return $this->powerFill > $this->lastPowerFill;
    }

    public function isPowerFillDropping() {
        return $this->powerFill < $this->lastPowerFill;
    }

    public function getPowerFillPercent() {

        if ($this->getPowerCapacity() == 0) {
            return 0;
        }

        return $this->getPowerFill() / $this->getPowerCapacity() * 100;
    }

    public function update()
    {
        $ret = parent::update();

        $maxTempDelta = $this->maxTemp - $this->minTemp;

        /**
         * assuming kg = litres for water
         */
        $this->powerCapacity = $this->capacity * $maxTempDelta * self::WATER_SPEC_HEAT / self::JOULES_IN_KW;

        $this->lastPowerFill = $this->powerFill;
        /**
         * This doesn't seems to be too accurate... but let's leave it like that
         * for now.
         */
        $tempDelta = $this->getAverage() - $this->minTemp;

        if ($tempDelta < 0) {
            $this->powerFill = 0;
        } else {
            $this->powerFill = $this->capacity * $tempDelta * self::WATER_SPEC_HEAT / self::JOULES_IN_KW;
        }

        return $ret;
    }
}

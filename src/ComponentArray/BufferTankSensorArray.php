<?php

namespace Hellfire\SensorArray;

use Coff\Hellfire\ComponentArray\DataSourceArray;

class BufferTankSensorArray extends DataSourceArray
{
    const
        WATER_HEAT = 4189.9; //

    protected $bufferCapacity;
    protected $minTemp, $maxTemp;

    /**
     * Sets tank's capacity in litres. Mostly for power capacity calculations.
     * @param $litres
     * @return $this
     */
    public function setBufferCapacity($litres) {
        $this->bufferCapacity = $litres;

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
     * range.
     */
    public function getPowerCapacity() {

    }


    /**
     * Returns available capacity in [KW]
     */
    public function getFreePowerCapacity() {

    }
}

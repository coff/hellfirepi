<?php

namespace Hellfire\Sensor;

use Hellfire\Conversion\TemperatureConversion;
use Hellfire\Enum\TemperatureUnit;

class TemperatureSensor extends Sensor
{
    protected $device;
    protected $resource;


    /**
     * TemperatureSensor constructor.
     *
     * @param $description
     * @param null $device
     * @param TemperatureUnit|null $measureUnit units to display temperature in
     * @param string $measureSuffix
     */
    public function __construct($description, $device=null, TemperatureUnit $measureUnit=null, $measureSuffix='°C') {
        $this->measureUnit = $measureUnit;
        $this->measureSuffix = $measureSuffix;
        $this->device = $device;
        $this->description = $description;

        $this->init();
    }

    public function init() {
        $this->resource = fopen($this->device, 'r');

        return $this;
    }

    public function setDevice($device) {
        $this->device = $device;

        return $this;
    }

    public function update() {
        fseek($this->resource, 0);
        $crcLine = trim(fgets($this->resource));
        $valueLine = trim(fgets($this->resource));
        if (substr($crcLine,-1) == 'S') { // YE(S)
            $this->value = (double)explode('=', substr($valueLine,-8))[1] / 1000;
        }

        return $this;
    }

    public function render() {
        $t = new TemperatureConversion($this->getValue(), new TemperatureUnit(TemperatureUnit::CELSIUS));
        return (string)round($t->to($this->measureUnit),1) . $this->getMeasureSuffix();
    }

}

<?php


class TemperatureSensor extends Sensor
{
    protected $device;
    protected $resource;


    public function __construct($description, $device=null, $measureUnit='celsius', $measureSuffix='C') {
        $this->measureUnit = $measureUnit;
        $this->measureSuffix = $measureSuffix;
        $this->device = $device;
        $this->description = $description;

        $this->init();
    }

    public function init() {
        $this->resource = fopen($this->device, 'r');

    }

    public function setDevice($device) {
        $this->device = $device;
    }

    public function update() {
        $crcLine = fgets($this->resource);
        $valueLine = fgets($this->resource);

        if (substr($crcLine,-1) == 'S') { // YE(S)
            $this->value = (double)explode('=', substr($valueLine,-8))[1] / 1000;
        }
    }

    public function render() {
        return (string)round($this->getValue(),1) . $this->getMeasureSuffix();
    }

}

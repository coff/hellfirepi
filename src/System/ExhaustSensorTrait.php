<?php

namespace Coff\Hellfire\System;

use Coff\Hellfire\Sensor\ExhaustSensor;

trait ExhaustSensorTrait
{
    /**
     * @var ExhaustSensor
     */
    protected $exhaust;

    /**
     * Sets exhaust gases thermocouple sensor
     *
     * @param ExhaustSensor $sensor
     * @return $this
     */
    public function setExhaustSensor(ExhaustSensor $sensor) {
        $this->exhaust = $sensor;

        return $this;
    }

    /**
     * @return ExhaustSensor
     */
    public function getExhaustSensor() {
        return $this->exhaust;
    }
}

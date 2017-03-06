<?php

namespace Coff\Hellfire\System;

use Coff\DataSource\DataSource;

trait ExhaustSensorTrait
{
    /**
     * @var DataSource
     */
    protected $exhaust;

    /**
     * Sets exhaust gases thermocouple sensor
     *
     * @param DataSource $sensor
     * @return $this
     */
    public function setExhaustSensor(DataSource $sensor) {
        $this->exhaust = $sensor;

        return $this;
    }

    /**
     * @return DataSource
     */
    public function getExhaustSensor() {
        return $this->exhaust;
    }
}

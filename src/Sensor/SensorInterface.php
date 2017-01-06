<?php

namespace Hellfire\Sensor;

interface SensorInterface
{
    public function init();

    public function update();

    public function getValue();

    public function render();

    public function getMeasureUnit();

    public function getMeasureSuffix();

    public function setDescription($description);

    public function getDescription();
}

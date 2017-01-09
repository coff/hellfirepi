<?php

namespace Hellfire\Sensor;

interface SensorInterface
{
    public function init();

    /**
     * Updates sensor reading
     * @return $this
     */
    public function update();

    /**
     * Returns updated value
     * @return $this
     */
    public function getValue();

    /**
     * Renders values in proper units with measuring suffix.
     * @return $this
     */
    public function render();

    public function getMeasureUnit();

    public function getMeasureSuffix();

    public function setDescription($description);

    public function getDescription();
}

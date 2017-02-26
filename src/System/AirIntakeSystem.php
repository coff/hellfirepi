<?php

namespace Coff\Hellfire\System;

use Coff\DataSource\DataSourceInterface;
use Hellfire\Servo\AnalogServo;

class AirIntakeSystem extends System implements DataSourceInterface
{
    const
        STATE_OPEN           = 1,
        STATE_EXHAUST_DRIVEN = 2,
        STATE_MEDIUM_DRIVEN  = 3,
        STATE_CLOSED         = 0;

    protected $position;
    protected $stepValue=1;
    protected $servo;

    public function init() {
        $this->open();
    }

    public function process()
    {
        /** processed from BoilerSystem */
    }

    /**
     * Performs one step down
     */
    public function stepDown() {
        $this->position -= $this->stepValue;
    }

    /**
     * Performs one step up
     */
    public function stepUp() {
        $this->position += $this->stepValue;
    }

    /**
     * Fully closes air intake valve
     */
    public function close() {
        $this->position = 0;
    }

    /**
     * Fully opens air intake valve
     */
    public function open() {
        $this->position = 100;
    }

    public function update() {

    }

    public function getPosition() {
        return $this->position;
    }

    public function getValue()
    {
        return $this->position;
    }

    public function getStamp()
    {

    }

    public function setServo(AnalogServo $servo) {
        $this->servo = $servo;

        return $this;
    }
}

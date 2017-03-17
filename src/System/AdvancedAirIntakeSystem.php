<?php

namespace Coff\Hellfire\System;

use Coff\Hellfire\ComponentArray\BoilerSensorArray;
use Coff\Hellfire\Event\BoilerTempEvent;
use Coff\Hellfire\Event\ExhaustTempEvent;
use Coff\Hellfire\Sensor\ExhaustSensor;

/**
 * AdvancedAirIntakeSystem
 *
 *
 */
class AdvancedAirIntakeSystem extends AirIntakeSystem
{
    public function init()
    {
        $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_TOO_HIGH,
            [$this, 'onBoilerTooHigh']);
        $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_TOO_LOW,
            [$this, 'onBoilerTooLow']);
        $this->getEventDispatcher()->addListener(ExhaustTempEvent::ON_TOO_HIGH,
            [$this, 'onExhaustTooHigh']);
        $this->getEventDispatcher()->addListener(ExhaustTempEvent::ON_TOO_LOW,
            [$this, 'onExhaustTooLow']);

        return parent::init();
    }



    public function onBoilerTooHigh(BoilerTempEvent $event) {
        /** @var BoilerSensorArray $boilerSensorArr */
        $boilerSensorArr = $this->getContainer()['system:boiler']->getSensorArray();

        switch ($this->getState()) {
            case self::STATE_TEMP_DRIVEN:
                $this->adjustShutterDown($boilerSensorArr->getRange());
                break;

            case self::STATE_CLOSED:
                break;

            case self::STATE_OPEN:
                break;
        }
    }

    public function onBoilerTooLow(BoilerTempEvent $event) {
        /** @var BoilerSensorArray $boilerSensorArr */
        $boilerSensorArr = $this->getContainer()['system:boiler']->getSensorArray();

        switch ($this->getState()) {
            case self::STATE_TEMP_DRIVEN:
                if ($this->exhaust->getRange() < ExhaustSensor::RANGE_HIGH) {
                    $this->adjustShutterUp($boilerSensorArr->getRange());
                }
                break;

            case self::STATE_CLOSED:
                break;

            case self::STATE_OPEN:
                break;
        }
    }

    public function onExhaustTooHigh(ExhaustTempEvent $event) {
        switch ($this->getState()) {
            case self::STATE_TEMP_DRIVEN:
                $this->adjustShutterDown($this->exhaust->getRange());
                break;

            case self::STATE_CLOSED:
                $this->close();
                break;

            case self::STATE_OPEN:
                $this->close();
                break;
        }
    }

    public function onExhaustTooLow(ExhaustTempEvent $event) {
        /** @var BoilerSensorArray $boilerSensorArr */
        $boilerSensorArr = $this->getContainer()['system:boiler']->getSensorArray();
        switch ($this->getState()) {
            case self::STATE_TEMP_DRIVEN:
                if (false === $boilerSensorArr->isReadingAboveTarget(BoilerSensorArray::SENSOR_HIGH)) {
                    $this->adjustShutterUp($this->exhaust->getRange());
                }
                break;

            case self::STATE_CLOSED:
                // stays closed
                break;

            case self::STATE_OPEN:
                // stays open
                break;
        }
    }

    protected function adjustShutterDown($tempRange) {
        switch ($tempRange) {
            case ExhaustSensor::RANGE_CRITICAL:
                /** there's only one thing we can do */
                $this->close();
                break;
            case ExhaustSensor::RANGE_HIGH:
                /** check if arm isn't too high */
                if ($this->servo->getRelative() > 0.1) {
                    $this->servo->setRelative(0.1);
                } else {
                    $this->stepDown();
                }

                break;
            case ExhaustSensor::RANGE_NORMAL:
                /** just step down, no risk no fun */
                $this->stepDown();
                break;

        }
    }

    protected function adjustShutterUp($tempRange) {
        switch ($tempRange) {
            case ExhaustSensor::RANGE_COLD:
                $this->open();
                break;

            case ExhaustSensor::RANGE_LOW:
                /** no need to be careful now */
                $this->stepUp(10);
                break;

            case ExhaustSensor::RANGE_NORMAL:
                $this->stepUp();
                break;
        }
    }

}

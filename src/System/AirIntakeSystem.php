<?php

namespace Coff\Hellfire\System;

use Coff\Hellfire\ComponentArray\BoilerSensorArray;
use Coff\Hellfire\Event\BoilerTempEvent;
use Coff\Hellfire\Event\CyclicEvent;
use Coff\Hellfire\Event\ExhaustTempEvent;
use Coff\Hellfire\Servo\AnalogServo;
use Hellfire\Sensor\ExhaustSensor;

/**
 * AirIntakeSystem
 *
 *
 */
class AirIntakeSystem extends System
{
    use SensorArrayTrait;
    use ExhaustSensorTrait;

    const
        STATE_OPEN           = 1,
        STATE_TEMP_DRIVEN    = 2,
        STATE_CLOSED         = 0;

    protected $targetExhaustTemp=170;
    protected $targetExhaustTempHysteresis=5;

    protected $exhaustTemp;
    protected $lastExhaustTemp;

    /**
     * @var BoilerSystem
     */
    protected $boiler;

    /**
     * @var AnalogServo
     */
    protected $servo;

    public function init()
    {
        $this->getEventDispatcher()->addListener(CyclicEvent::EVERY_SECOND,
            [$this, 'everySecond']);
        $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_TOO_HIGH,
            [$this, 'onBoilerTooHigh']);
        $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_TOO_LOW,
            [$this, 'onBoilerTooLow']);
        $this->getEventDispatcher()->addListener(ExhaustTempEvent::ON_TOO_HIGH,
            [$this, 'onExhaustTooHigh']);
        $this->getEventDispatcher()->addListener(ExhaustTempEvent::ON_TOO_LOW,
            [$this, 'onExhaustTooLow']);

        $this->open();

        $this->setState(self::STATE_TEMP_DRIVEN);

        return parent::init();
    }

    /**
     * @param CyclicEvent $event
     */
    public function everySecond(CyclicEvent $event) {
        $this->exhaust->update();
        $this->lastExhaustTemp = $this->exhaustTemp;
        $this->exhaustTemp = $this->exhaust->getValue();

        if ($this->exhaustTemp > $this->targetExhaustTemp + $this->targetExhaustTempHysteresis) {
            $this->getEventDispatcher()
                ->dispatch(ExhaustTempEvent::ON_TOO_HIGH, new ExhaustTempEvent());
        } elseif ($this->exhaustTemp < $this->targetExhaustTemp - $this->targetExhaustTempHysteresis) {
            $this->getEventDispatcher()
                ->dispatch(ExhaustTempEvent::ON_TOO_LOW, new ExhaustTempEvent());
        }

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
                if ($boilerSensorArr->getRange() < BoilerSensorArray::RANGE_HIGH) {
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

    public function setState($state)
    {
        switch ($state) {
            case self::STATE_TEMP_DRIVEN:

                break;

            case self::STATE_CLOSED:
                $this->close();
                break;

            case self::STATE_OPEN:
                $this->open();
                break;
        }
        return parent::setState($state);
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

    /**
     * Performs one step down
     *
     * @param int $rel
     */
    public function stepDown($rel=1) {
        $this->servo
            ->stepDown($rel)
            ->send();
    }

    /**
     * Performs one step up by doing two steps up and one step down.
     *
     * It's due to boiler's air intake shutter's weight servo makes annoying
     * noises when stopped after pulling shutter upward.
     *
     * @param int $rel
     */
    public function stepUp($rel=1) {
        $this->servo
            ->stepUp(3)
            ->send();

        /**
         * We don't step down when at max because we'd never came to max so
         * servo's arm would go crazy trying.
         */
        if (false == $this->servo->isMax()) {
            usleep(50000);

            $this->servo
                ->stepDown($rel+1)
                ->send();
        }
    }

    /**
     * Fully closes air intake valve
     */
    public function close() {
        $this->servo
            ->setRelative(0)
            ->send();
    }

    /**
     * Fully opens air intake valve
     */
    public function open() {
        $this->servo
            ->setRelative(1)
            ->send();
    }

    public function setServo(AnalogServo $servo) {
        $this->servo = $servo;

        return $this;
    }

    public function getServo() {
        return $this->servo;
    }

}

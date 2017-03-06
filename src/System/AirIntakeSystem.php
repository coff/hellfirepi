<?php

namespace Coff\Hellfire\System;

use Coff\Hellfire\Event\BoilerTempEvent;
use Coff\Hellfire\Event\CyclicEvent;
use Coff\Hellfire\Event\ExhaustTempEvent;
use Coff\Hellfire\Servo\AnalogServo;

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
        STATE_EXHAUST_DRIVEN = 2,
        STATE_MEDIUM_DRIVEN  = 3, // usually water ;)
        STATE_CLOSED         = 0;

    protected $targetMediumTemp=85;
    protected $mediumDrivenStateTemp=87;
    protected $targetExhaustTemp=170;
    protected $targetMediumTempHysteresis=1.5;

    protected $mediumTemp;
    protected $lastMediumTemp;
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
        $this->open();

        $this->setState(self::STATE_EXHAUST_DRIVEN);

        return parent::init();
    }

    public function everySecond(CyclicEvent $event) {
        $this->update();

        if ($this->mediumTemp > $this->targetMediumTemp + $this->targetMediumTempHysteresis) {
            $this->getEventDispatcher()->dispatch(BoilerTempEvent::ON_TOO_HIGH,
                new ExhaustTempEvent($this->exhaustTemp, $this->lastExhaustTemp, $this->targetExhaustTemp));
        } elseif ($this->mediumTemp < $this->targetMediumTemp - $this->targetMediumTempHysteresis) {
            $this->getEventDispatcher()->dispatch(BoilerTempEvent::ON_TOO_LOW,
                new ExhaustTempEvent($this->exhaustTemp, $this->lastExhaustTemp, $this->targetExhaustTemp));
        }

    }

    public function everyMinute(CyclicEvent $event) {
        $this->update();

        if ($this->mediumTemp > $this->targetMediumTemp + $this->targetMediumTempHysteresis) {
            $this->getEventDispatcher()->dispatch(BoilerTempEvent::ON_TOO_HIGH,
                new BoilerTempEvent($this->mediumTemp, $this->lastMediumTemp, $this->targetMediumTemp));
        } elseif ($this->mediumTemp < $this->targetMediumTemp - $this->targetMediumTempHysteresis) {
            $this->getEventDispatcher()->dispatch(BoilerTempEvent::ON_TOO_LOW,
                new BoilerTempEvent($this->mediumTemp, $this->lastMediumTemp, $this->targetMediumTemp));
        }

    }

    public function update() {
        $this->exhaust->update();
        $this->lastExhaustTemp = $this->exhaustTemp;
        $this->exhaustTemp = $this->exhaust->getValue();
        $this->lastMediumTemp = $this->mediumTemp;
        $this->mediumTemp = $this->sensorArray[BoilerSystem::SENSOR_HIGH]->getValue();

    }

    public function onBoilerTooHigh(BoilerTempEvent $event) {
        switch ($event->getRange()) {
            case BoilerTempEvent::RANGE_CRITICAL:
                /** there's only one thing we can do */
                $this->close();
                break;
            case BoilerTempEvent::RANGE_HIGH:
                /** check if arm isn't too high */
                if ($this->servo->getRelative() > 0.1) {
                    $this->servo->setRelative(0.1);
                } else {
                    $this->stepDown();
                }
                break;
            case BoilerTempEvent::RANGE_NORMAL:
                /** just step down, no risk no fun */
                $this->stepDown();
                break;

        }
    }

    public function onBoilerTooLow(BoilerTempEvent $event) {
        switch ($event->getRange()) {
            case BoilerTempEvent::RANGE_COLD:
                $this->open();
                break;

            case BoilerTempEvent::RANGE_LOW:
                /** no need to be careful now */
                $this->stepUp(10);
                break;

            case BoilerTempEvent::RANGE_NORMAL:
                $this->stepUp();
        }
    }

    public function onExhaustTooHigh(ExhaustTempEvent $event) {
        switch ($event->getRange()) {
            case ExhaustTempEvent::RANGE_CRITICAL:
                $this->close();
                break;
            case ExhaustTempEvent::RANGE_HIGH:
                /** check if arm isn't too high */
                if ($this->servo->getRelative() > 0.1) {
                    $this->servo->setRelative(0.1);
                } else {
                    $this->stepDown();
                }
                break;

            case ExhaustTempEvent::RANGE_NORMAL:
                /** just step down, no risk no fun */
                $this->stepDown();
                break;
        }
    }

    public function onExhaustTooLow(ExhaustTempEvent $event) {
        switch ($event->getRange()) {
            case ExhaustTempEvent::RANGE_COLD:
                $this->open();
                break;

            case ExhaustTempEvent::RANGE_LOW:
                /** no need to be careful now */
                $this->stepUp(10);
                break;

            case ExhaustTempEvent::RANGE_NORMAL:
                $this->stepUp();
        }
    }

    public function setState($state)
    {
        switch ($state) {
            case self::STATE_MEDIUM_DRIVEN:
                /** in this case switch to minute cycle */
                $this->getEventDispatcher()->removeListener(CyclicEvent::EVERY_SECOND, [$this, 'everySecond']);
                $this->getEventDispatcher()->addListener(CyclicEvent::EVERY_MINUTE, [$this, ['everyMinute']]);
                $this->installTempEventListeners();
                break;

            case self::STATE_EXHAUST_DRIVEN:
                /** this can change quickly so be careful */
                $this->getEventDispatcher()->removeListener(CyclicEvent::EVERY_MINUTE, [$this, 'everyMinute']);
                $this->getEventDispatcher()->addListener(CyclicEvent::EVERY_SECOND, [$this, ['everySecond']]);
                $this->installTempEventListeners();
                break;

            case self::STATE_CLOSED:
                $this->removeTempEventListeners();
                $this->getEventDispatcher()->removeListener(CyclicEvent::EVERY_SECOND, [$this, 'everySecond']);
                $this->getEventDispatcher()->removeListener(CyclicEvent::EVERY_MINUTE, [$this, 'everyMinute']);

                $this->close();
                break;

            case self::STATE_OPEN:
                $this->removeTempEventListeners();
                $this->getEventDispatcher()->removeListener(CyclicEvent::EVERY_SECOND, [$this, 'everySecond']);
                $this->getEventDispatcher()->removeListener(CyclicEvent::EVERY_MINUTE, [$this, 'everyMinute']);

                $this->open();
                break;


        }

        return parent::setState($state);
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

    protected function installTempEventListeners() {
        $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_TOO_HIGH,
            [$this, 'onBoilerTooHigh']);
        $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_TOO_LOW,
            [$this, 'onBoilerTooLow']);
        $this->getEventDispatcher()->addListener(ExhaustTempEvent::ON_TOO_HIGH,
            [$this, 'onExhaustTooHigh']);
        $this->getEventDispatcher()->addListener(ExhaustTempEvent::ON_TOO_LOW,
            [$this, 'onExhaustTooLow']);
    }

    protected function removeTempEventListeners() {
        $this->getEventDispatcher()->removeListener(BoilerTempEvent::ON_TOO_HIGH,
            [$this, 'onBoilerTooHigh']);
        $this->getEventDispatcher()->removeListener(BoilerTempEvent::ON_TOO_LOW,
            [$this, 'onBoilerTooLow']);
        $this->getEventDispatcher()->removeListener(ExhaustTempEvent::ON_TOO_HIGH,
            [$this, 'onExhaustTooHigh']);
        $this->getEventDispatcher()->removeListener(ExhaustTempEvent::ON_TOO_LOW,
            [$this, 'onExhaustTooLow']);
    }
}

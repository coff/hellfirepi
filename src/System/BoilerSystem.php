<?php

namespace Coff\Hellfire\System;

use Coff\Hellfire\ComponentArray\BoilerSensorArray;
use Coff\Hellfire\Event\BoilerTempEvent;
use Coff\Hellfire\Event\CyclicEvent;

class BoilerSystem extends System
{
    use PumpTrait;
    use SensorArrayTrait;

    const
        STATE_COLD          = 0,
        STATE_STARTING      = 1,
        STATE_BURNING       = 2,
        STATE_COOLING       = 3,
        STATE_OVERHEAT      = 4;

    /** @var  AirIntakeSystem */
    protected $intake;

    protected $startStamp;

    /**
     * @var BoilerSensorArray
     */
    protected $sensorArray;

    public function init()
    {
        $this->intake = $this->getContainer()['system:intake'];

        $this->getEventDispatcher()->addListener(CyclicEvent::EVERY_MINUTE, [$this, ['everyMinute']]);
      //  $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_TOO_HIGH, [$this, ['onOverheat']]);
      //  $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_TARGET, [$this, ['onTempTarget']]);
     //   $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_TOO_HIGH, [$this, ['onTempLow']]);
        //$this->getEventDispatcher()->addListener(BoilerTempEvent::ON_RAISE, [$this, ['onTempRaise']]);
       // $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_DROP, [$this, ['onTempDrop']]);
        $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_RANGE_UP, [$this, ['onTempRangeUp']]);
        $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_RANGE_DOWN, [$this, ['onTempRangeUp']]);
        return parent::init();
    }


    public function everyMinute(CyclicEvent $event) {
        $this->sensorArray->update();

        if ($this->sensorArray->isRising(BoilerSensorArray::SENSOR_HIGH)) {
            $this->getEventDispatcher()
                ->dispatch(BoilerTempEvent::ON_RAISE, new BoilerTempEvent($this->sensorArray));

            if ($this->sensorArray->isRangeChanged()) {
                $this->getEventDispatcher()
                    ->dispatch(BoilerTempEvent::ON_RANGE_UP, new BoilerTempEvent($this->sensorArray));
            }
        }

        if ($this->sensorArray->isDropping(BoilerSensorArray::SENSOR_HIGH)) {
            $this->getEventDispatcher()
                ->dispatch(BoilerTempEvent::ON_DROP, new BoilerTempEvent($this->sensorArray));

            if ($this->sensorArray->isRangeChanged()) {
                $this->getEventDispatcher()
                    ->dispatch(BoilerTempEvent::ON_RANGE_DOWN, new BoilerTempEvent($this->sensorArray));
            }
        }

        if ($this->sensorArray->isReadingAboveTarget(BoilerSensorArray::SENSOR_HIGH)) {
            $this->getEventDispatcher()
                ->dispatch(BoilerTempEvent::ON_TOO_HIGH, new BoilerTempEvent($this->sensorArray));
        } elseif ($this->sensorArray->isReadingBelowTarget(BoilerSensorArray::SENSOR_HIGH)) {
            $this->getEventDispatcher()
                ->dispatch(BoilerTempEvent::ON_TOO_LOW, new BoilerTempEvent($this->sensorArray));
        } else {
            $this->getEventDispatcher()
                ->dispatch(BoilerTempEvent::ON_TARGET, new BoilerTempEvent($this->sensorArray));

        }
    }


    public function onTempRangeUp(BoilerTempEvent $event) {
        switch ($this->sensorArray->getRange()) {
            case BoilerSensorArray::RANGE_LOW:
                /* STATE_STARTING is only initiated manually */
                $this->setStartStamp(time());
                break;
            case BoilerSensorArray::RANGE_NORMAL:
                $this->setState(BoilerSystem::STATE_BURNING);
                break;
            case BoilerSensorArray::RANGE_HIGH:
                // no break
            case BoilerSensorArray::RANGE_CRITICAL:
                $this->setState(BoilerSystem::STATE_OVERHEAT);
                break;
        }

        /* so far anything that happens here causes pump to be on */
        $this->pump->on();
    }

    /**
     * @param BoilerTempEvent $event
     */
    public function onTempRangeDown(BoilerTempEvent $event) {
        switch ($this->sensorArray->getRange()) {
            case BoilerSensorArray::RANGE_NORMAL:
                $this->setState(BoilerSystem::STATE_BURNING);
                break;
            case BoilerSensorArray::RANGE_LOW:
                if (time() - $this->getStartStamp() > 5 * 60 * 60) { // 5 hours
                    /* we're assuming it only, may also be just a fuel stuck in chamber */
                    $this->setState(BoilerSystem::STATE_COOLING);
                }
                break;
            case BoilerSensorArray::RANGE_COLD:
                $this->setState(BoilerSystem::STATE_COLD);
                $this->pump->off();
                break;

        }
    }

    /**
     * @param mixed $startStamp
     * @return $this
     */
    public function setStartStamp($startStamp)
    {
        $this->startStamp = $startStamp;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartStamp()
    {
        return $this->startStamp;
    }

}

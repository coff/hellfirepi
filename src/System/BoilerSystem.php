<?php

namespace Coff\Hellfire\System;

use Casadatos\Component\Dashboard\ConsoleDashboard;
use Casadatos\Component\Dashboard\Gauge\ValueGauge;
use Coff\Hellfire\ComponentArray\BoilerSensorArray;
use Coff\Hellfire\Event\BoilerTempEvent;
use Coff\Hellfire\Event\CyclicEvent;

class BoilerSystem extends System
{
    use PumpTrait;
    use SensorArrayTrait;
    use DashboardTrait;

    const
        STATE_COLD          = 'cold',
        STATE_STARTING      = 'starting',
        STATE_BURNING       = 'burning',
        STATE_COOLING       = 'cooling',
        STATE_OVERHEAT      = 'overheat';


    protected $startStamp;

    /**
     * @var BoilerSensorArray
     */
    protected $sensorArray;

    public function init()
    {

        $this->getEventDispatcher()->addListener(CyclicEvent::EVERY_MINUTE, [$this, 'everyMinute']);
        $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_RANGE_UP, [$this, 'onTempRangeUp']);
        $this->getEventDispatcher()->addListener(BoilerTempEvent::ON_RANGE_DOWN, [$this, 'onTempRangeDown']);

        $this->getDashboard()
            ->add('BoilP', new ValueGauge(5))
            ->add('BoilHi', new ValueGauge(6), null, ConsoleDashboard::COL_FG_LIGHTRED)
            ->add('BoilLo', new ValueGauge(6), null, ConsoleDashboard::COL_FG_LIGHTBLUE)
            ->add('BoilrState', new ValueGauge(10))
            ;
        return parent::init();
    }

    public function update() {
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

        $this->getDashboard()
            ->update('BoilP', $this->pump->isOn() ? 'ON' : 'OFF')
            ->update('BoilHi', sprintf("%.1f", $this->sensorArray->getReading(BoilerSensorArray::SENSOR_HIGH)))
            ->update('BoilLo', sprintf("%.1f", $this->sensorArray->getReading(BoilerSensorArray::SENSOR_LOW)))
            ->update('BoilrState', $this->getState())
        ;
    }


    public function everyMinute(CyclicEvent $event) {
        $this->update();
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

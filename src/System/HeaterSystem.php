<?php

namespace Coff\Hellfire\System;

use Casadatos\Component\Dashboard\ConsoleDashboard;
use Casadatos\Component\Dashboard\Gauge\ValueGauge;
use Coff\Hellfire\Buzzer;
use Coff\Hellfire\BuzzerNotes;
use Coff\Hellfire\ComponentArray\HeaterSensorArray;
use Coff\Hellfire\Event\BufferEvent;
use Coff\Hellfire\Event\CyclicEvent;
use Coff\Hellfire\Event\Event;
use Coff\Hellfire\Event\HeaterEvent;
use Coff\Hellfire\Event\RoomTempEvent;
use Coff\Hellfire\StateEnum\BufferStateEnum;
use Coff\Hellfire\StateEnum\HeaterStateEnum;
use Coff\OneWire\Sensor\SensorInterface;
use Coff\SMF\Assertion\AlwaysFalseAssertion;

class HeaterSystem extends System
{
    use PumpTrait;
    use SensorArrayTrait;
    use DashboardTrait;

    /**
     * @var SensorInterface
     */
    protected $roomTempSensor;

    protected $roomTemp;
    protected $targetRoomTemp = 20.5;
    protected $roomTempHysteresis = 0.5;

    public function init() {

        // standard on-off behavior
        $this
            ->allowTransition(HeaterStateEnum::OFF(), HeaterStateEnum::ON())
            ->allowTransition(HeaterStateEnum::ON(), HeaterStateEnum::OFF())

            ->allowTransition(HeaterStateEnum::OFF(), HeaterStateEnum::ACTIVE())
            ->allowTransition(HeaterStateEnum::ACTIVE(), HeaterStateEnum::OFF())

            ->allowTransition(HeaterStateEnum::OFF(), HeaterStateEnum::EXTON(), new AlwaysFalseAssertion())
            ->allowTransition(HeaterStateEnum::EXTON(), HeaterStateEnum::OFF(), new AlwaysFalseAssertion())

            ;

        $this->getDashboard()
            ->add('HeatP', new ValueGauge(5))
            ->add('HeatHi', new ValueGauge(6), null, ConsoleDashboard::COL_FG_LIGHTRED)
            ->add('HeatLo', new ValueGauge(6), null, ConsoleDashboard::COL_FG_LIGHTBLUE)
            ->add('RoomT', new ValueGauge(5))
            ->add('HeatgState', new ValueGauge(10), null, ConsoleDashboard::COL_FG_WHITE)
            ;

        return parent::init();
    }

    public function assertOffToActive() {

        /** @var BufferSystem $buffer */
        $buffer = $this->getContainer()['system:buffer'];
        return $buffer->isMachineState(BufferStateEnum::NOTEMPTY()) ? true : false;
    }

    public function assertActiveToOff() {
        /** @var BufferSystem $buffer */
        $buffer = $this->getContainer()['system:buffer'];
        return $buffer->isMachineState(BufferStateEnum::NOTEMPTY()) ? false : true;
    }

    public function assertOffToOn() {
        /** @var BufferSystem $buffer */
        $buffer = $this->getContainer()['system:buffer'];
        return $buffer->isMachineState(BufferStateEnum::FULL()) ? true : false;
    }

    public function assertOnToOff() {
        /** @var BufferSystem $buffer */
        $buffer = $this->getContainer()['system:buffer'];
        return $buffer->isMachineState(BufferStateEnum::FULL()) ? false : true;
    }

    /**
     * Enables pump and switches system to EXTON state
     */
    public function on()
    {
        $this->getPump()->on();

        // if state any other than off then switch it off first
        if (!$this->isMachineState(HeaterStateEnum::OFF())) {
            $this->setMachineState(HeaterStateEnum::OFF());
        }

        $this->setMachineState(HeaterStateEnum::EXTON());
    }

    /**
     * Disables pump and switches system to OFF state
     */
    public function off()
    {
        $this->getPump()->off();
        $this->setMachineState(HeaterStateEnum::OFF());
    }

    public static function getSubscribedEvents()
    {
        $events = parent::getSubscribedEvents();

        $events[CyclicEvent::EVERY_MINUTE] = 'everyMinute';

        return $events;
    }

    public function update() {
        $this->sensorArray->update();
        $this->roomTempSensor->update();
        $this->roomTemp = $this->roomTempSensor->getValue();

        $this->run();

        if ($this->isMachineState(HeaterStateEnum::ACTIVE())) {
            /*
             * Active state room temp control here
             */

            if ($this->roomTemp > $this->targetRoomTemp + $this->roomTempHysteresis) {
                $this->getPump()->on();
            } elseif ($this->roomTemp < $this->targetRoomTemp - $this->roomTempHysteresis) {
                $this->getPump()->off();
            }
        }

        $this->getDashboard()
            ->update('HeatP', $this->pump->isOn() ? 'ON' : 'OFF')
            ->update('HeatHi', sprintf("%.1f", $this->sensorArray->getReading(HeaterSensorArray::SENSOR_HIGH)))
            ->update('HeatLo', sprintf("%.1f", $this->sensorArray->getReading(HeaterSensorArray::SENSOR_LOW)))
            ->update('RoomT', sprintf("%.1f", $this->roomTemp))
            ->update('HeatgState', $this->getState())
        ;
    }

    public function everyMinute(CyclicEvent $event)
    {
        $this->update();
    }

    /**
     * Sets room temp. sensor
     *
     * @param SensorInterface $sensor
     *
     * @return $this
     */
    public function setRoomTempSensor(SensorInterface $sensor) {
        $this->roomTempSensor = $sensor;

        return $this;
    }

    /**
     * Sets target room temperature and temp. hysteresis
     *
     * @param $temp
     * @param $hysteresis
     * @return $this
     */
    public function setTargetRoomTemp($temp, $hysteresis = 0.5) {
        $this->targetRoomTemp = $temp;
        $this->roomTempHysteresis = $hysteresis;

        return $this;
    }

}

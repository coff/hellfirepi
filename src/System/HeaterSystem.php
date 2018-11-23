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
use Coff\OneWire\Sensor\SensorInterface;

class HeaterSystem extends System
{
    use PumpTrait;
    use SensorArrayTrait;
    use DashboardTrait;

    const
        STATE_ACTIVE        = 'active',
        STATE_OVERHEAT      = 'overheat',
        STATE_OFF           = 'off';

    /**
     * @var SensorInterface
     */
    protected $roomTempSensor;

    protected $roomTemp;
    protected $targetRoomTemp = 20.5;
    protected $roomTempHysteresis = 0.5;

    public function init() {
        $eventDispatcher = $this->getEventDispatcher();

        $eventDispatcher->addListener(CyclicEvent::EVERY_MINUTE,
            [$this, 'everyMinute']);
        $eventDispatcher->addListener(RoomTempEvent::ON_TOO_HIGH,
            [$this, 'onRoomTempTooHigh']);
        $eventDispatcher->addListener(RoomTempEvent::ON_TOO_LOW,
            [$this, 'onRoomTempTooLow']);
        $eventDispatcher->addListener(BufferEvent::ON_FILLING_FULL,
            [$this, 'onBufferFillingFull']);
        $eventDispatcher->addListener(BufferEvent::ON_DROPPING_NOT_FULL,
            [$this, 'onBufferDroppingNotFull']);
        $eventDispatcher->addListener(BufferEvent::ON_FILLING_NOT_EMPTY,
            [$this, 'onBufferFillingNotEmpty']);
        $eventDispatcher->addListener(BufferEvent::ON_DROPPING_EMPTY,
            [$this, 'onBufferDroppingEmpty']);

        $this->getDashboard()
            ->add('HeatP', new ValueGauge(5))
            ->add('HeatHi', new ValueGauge(6), null, ConsoleDashboard::COL_FG_LIGHTRED)
            ->add('HeatLo', new ValueGauge(6), null, ConsoleDashboard::COL_FG_LIGHTBLUE)
            ->add('RoomT', new ValueGauge(5))
            ->add('HeatgState', new ValueGauge(10), null, ConsoleDashboard::COL_FG_WHITE)
            ;

        return parent::init();
    }

    public function update() {
        $this->sensorArray->update();
        $this->roomTempSensor->update();
        $this->roomTemp = $this->roomTempSensor->getValue();

        if ($this->roomTemp < $this->targetRoomTemp - $this->roomTempHysteresis) {
            $this->getEventDispatcher()->dispatch(RoomTempEvent::ON_TOO_LOW, new RoomTempEvent());
        } elseif ($this->roomTemp >  $this->targetRoomTemp + $this->roomTempHysteresis) {
            $this->getEventDispatcher()->dispatch(RoomTempEvent::ON_TOO_HIGH, new RoomTempEvent());
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

    public function onRoomTempTooLow(RoomTempEvent $event) {
      //  if ($this->isState(self::STATE_ACTIVE)) {
            $this->enablePump();
      //  }
    }

    public function onRoomTempTooHigh(RoomTempEvent $event) {

        /** we can't disable that pump in such state */
        if ($this->isState(self::STATE_OVERHEAT)) {
            return;
        }

        $this->disablePump();
    }

    public function onBufferFillingFull(Event $event) {
        $this->enablePump();
        $this->setState(self::STATE_OVERHEAT);
    }

    public function onBufferDroppingEmpty(Event $event) {
        $this->setState(self::STATE_OFF);
        $this->disablePump();
    }

    public function onBufferFillingNotEmpty(Event $event) {
        $this->setState(self::STATE_ACTIVE);
    }

    public function onBufferDroppingNotFull(Event $event) {
        $this->setState(self::STATE_ACTIVE);
    }

    /**
     * Enables heater system pump and dispatches proper event
     */
    public function enablePump() {
        if ($this->pump->isOff()) {
            $this->getEventDispatcher()->dispatch(HeaterEvent::PUMP_ENABLED, new HeaterEvent());
        }

        $this->pump->on();
    }

    /**
     * Disables heater system pump and dispatches proper event
     */
    public function disablePump() {
        if ($this->pump->isOn()) {
            $this->getEventDispatcher()->dispatch(HeaterEvent::PUMP_DISABLED, new HeaterEvent());
        }
        $this->pump->off();

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

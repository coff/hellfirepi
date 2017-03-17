<?php

namespace Coff\Hellfire\System;

use Casadatos\Component\Dashboard\ConsoleDashboard;
use Casadatos\Component\Dashboard\Gauge\PercentGauge;
use Casadatos\Component\Dashboard\Gauge\ValueGauge;
use Coff\Hellfire\Event\BufferEvent;
use Coff\Hellfire\Event\CyclicEvent;
use Coff\Hellfire\ComponentArray\BufferSensorArray;

class BufferSystem extends System
{
    use SensorArrayTrait;
    use DashboardTrait;

    const
        STATE_EMPTY          = 'empty',
        STATE_NEAR_EMPTY     = 'near_empty',
        STATE_NEAR_FULL      = 'near_full',
        STATE_FULL           = 'full';

    public function init() {
        $this->getEventDispatcher()->addListener(CyclicEvent::EVERY_3_SECOND, [$this, 'every3rdSecond']);
        $this->getEventDispatcher()->addListener(CyclicEvent::EVERY_2_MINUTE, [$this, 'every2ndMinute']);

        $this->getDashboard()
            ->add('Fill', new PercentGauge(4))
            ->add('KWh', new ValueGauge(3))
            ->add('BuffHi', new ValueGauge(6), null, ConsoleDashboard::COL_FG_LIGHTRED)
            ->add('BuffLo', new ValueGauge(6), null, ConsoleDashboard::COL_FG_LIGHTBLUE)
            ;
        return parent::init();
    }

    public function update() {
        /** @var BufferSensorArray $sensorArray */
        $sensorArray = $this->getSensorArray();

        $sensorArray->update();

        $fill = $sensorArray->getPowerFillPercent();

        if ($sensorArray->isPowerFillRising()) {
            switch (true) {
                case $fill > 80:
                    $eventType = BufferEvent::ON_FILLING_FULL;
                    break;
                case $fill > 70:
                    $eventType = BufferEvent::ON_FILLING_NEAR_FULL;
                    break;
                case $fill > 0:
                    $eventType = BufferEvent::ON_FILLING_NOT_EMPTY;
                    break;
            }
            if (isset($eventType)) {
                $this->getEventDispatcher()->dispatch($eventType, new BufferEvent());
            }

        } elseif ($sensorArray->isPowerFillDropping()) {
            switch (true) {
                case $fill < 1:
                    $eventType = BufferEvent::ON_DROPPING_EMPTY;
                    break;
                case $fill < 20:
                    $eventType = BufferEvent::ON_DROPPING_NEAR_EMPTY;
                    break;
                case $fill < 95:
                    $eventType = BufferEvent::ON_DROPPING_NOT_FULL;
                    break;

            }
            if (isset($eventType)) {
                $this->getEventDispatcher()->dispatch($eventType, new BufferEvent());
            }
        }

    }

    public function every3rdSecond(CyclicEvent $event) {
        /** @var BufferSensorArray $sensorArray */
        $sensorArray = $this->getSensorArray();

        $this->getDashboard()
            ->update('Fill', sprintf("%d", $sensorArray->getPowerFillPercent()))
            ->update('KWh', sprintf("%d", $sensorArray->getPowerFill()))
            ->update('BuffHi', sprintf("%.1f", $sensorArray->getReading(BufferSensorArray::SENSOR_HIGH)))
            ->update('BuffLo', sprintf("%.1f", $sensorArray->getReading(BufferSensorArray::SENSOR_LOW)))
        ;

    }

    public function every2ndMinute(CyclicEvent $event) {
        $this->update();
    }
}

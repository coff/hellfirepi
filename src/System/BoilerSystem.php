<?php

namespace Coff\Hellfire\System;

use Casadatos\Component\Dashboard\ConsoleDashboard;
use Casadatos\Component\Dashboard\Gauge\ValueGauge;
use Coff\Hellfire\ComponentArray\BoilerSensorArray;
use Coff\Hellfire\Event\CyclicEvent;
use Coff\Hellfire\StateEnum\BoilerStateEnum;

class BoilerSystem extends System
{

    /**
     * @var BoilerSensorArray
     */
    protected $sensorArray;

    public function init()
    {
        $this->setInitState(BoilerStateEnum::COLD());

        // normal states transitions cycle
        $this
            ->allowTransition(BoilerStateEnum::COLD(), BoilerStateEnum::WARMUP())
            ->allowTransition(BoilerStateEnum::WARMUP(), BoilerStateEnum::WORKING())
            ->allowTransition(BoilerStateEnum::WORKING(), BoilerStateEnum::COOLING())
            ->allowTransition(BoilerStateEnum::COOLING(), BoilerStateEnum::COLD())
            ;

        // extraordinary states transitions cycle
        $this
            ->allowTransition(BoilerStateEnum::WARMUP(), BoilerStateEnum::COLD())
            ->allowTransition(BoilerStateEnum::WORKING(), BoilerStateEnum::OVERHEATING())
            ->allowTransition(BoilerStateEnum::OVERHEATING(), BoilerStateEnum::WORKING())
            ;


        $this->getDashboard()
            ->add('BoilP', new ValueGauge(5))
            ->add('BoilHi', new ValueGauge(6), null, ConsoleDashboard::COL_FG_LIGHTRED)
            ->add('BoilLo', new ValueGauge(6), null, ConsoleDashboard::COL_FG_LIGHTBLUE)
            ->add('BoilrState', new ValueGauge(10), null, ConsoleDashboard::COL_FG_WHITE)
            ;
        return parent::init();
    }

    public function assertColdToWarmup()
    {
        $reading = $this->sensorArray->getReading(BoilerSensorArray::SENSOR_HIGH);
        return $reading > 40 ? true :  false;
    }

    public function assertWarmupToWorking()
    {
        $reading = $this->sensorArray->getReading(BoilerSensorArray::SENSOR_HIGH);
        return $reading > 70 ? true :  false;
    }

    public function assertWarmupToCold()
    {
        $reading = $this->sensorArray->getReading(BoilerSensorArray::SENSOR_HIGH);
        return $reading < 35 ? true :  false;
    }

    public function assertWorkingToCooling()
    {
        $reading = $this->sensorArray->getReading(BoilerSensorArray::SENSOR_HIGH);
        return $reading < 65 ? true :  false;
    }

    public function assertCoolingToCold()
    {
        $reading = $this->sensorArray->getReading(BoilerSensorArray::SENSOR_HIGH);
        return $reading < 27 ? true :  false;
    }

    public function assertWorkingToOverheating()
    {
        $reading = $this->sensorArray->getReading(BoilerSensorArray::SENSOR_HIGH);
        return $reading > 92 ? true :  false;
    }

    public function assertOverheatingToWorking()
    {
        $reading = $this->sensorArray->getReading(BoilerSensorArray::SENSOR_HIGH);
        return $reading < 89 ? true :  false;
    }

    /**
     * Implements EventSubscriberInterface
     * @return array
     */
    public static function getSubscribedEvents()
    {
        $events = parent::getSubscribedEvents();

        $events[CyclicEvent::EVERY_3_SECOND] = 'every3rdSecond';
        $events[CyclicEvent::EVERY_3_SECOND] = 'everyMinute';

        $events['BoilerSystem.transitionColdToWarmup'] = 'transitionColdToWarmup';

        return $events;
    }

    public function transitionColdToWarmup() {

    }

    public function update() {
        $this->sensorArray->update();
    }

    public function every3rdSecond(CyclicEvent $event) {
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

}

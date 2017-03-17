<?php

namespace Coff\Hellfire\System;

use Casadatos\Component\Dashboard\ConsoleDashboard;
use Casadatos\Component\Dashboard\Gauge\PercentGauge;
use Casadatos\Component\Dashboard\Gauge\ValueGauge;
use Coff\DataSource\Exception\DataSourceException;
use Coff\Hellfire\Event\CyclicEvent;
use Coff\Hellfire\Event\ExhaustTempEvent;
use Coff\Hellfire\Servo\AnalogServo;

abstract class AirIntakeSystem extends System
{
    use SensorArrayTrait;
    use DashboardTrait;
    use ExhaustSensorTrait;

    const
        STATE_OPEN           = 'open',
        STATE_TEMP_DRIVEN    = 'tmp_driven',
        STATE_CLOSED         = 'closed';


    protected $targetExhaustTemp=170;
    protected $targetExhaustTempHysteresis=5;

    protected $exhaustTemp;
    protected $lastExhaustTemp;
    protected $thermocoupleBroken=false;

    /** @var AnalogServo */
    protected $servo;

    public function init()
    {
        $this->getEventDispatcher()->addListener(CyclicEvent::EVERY_3_SECOND,
            [$this, 'every3s']);

        $this->getDashboard()
            ->add('Intk', new PercentGauge(7))
            ->add('Exhst', new ValueGauge(5))
            ->add('IntkState', new ValueGauge(10), null, ConsoleDashboard::COL_FG_WHITE)
        ;

        $this->open();

        $this->setState(self::STATE_TEMP_DRIVEN);

        return parent::init(); // TODO: Change the autogenerated stub
    }

    /**
     * @param CyclicEvent $event
     */
    public function every3s(CyclicEvent $event) {
        $this->update();
    }

    public function update()
    {
        if ($this->thermocoupleBroken === false) {
            try {
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
            } catch (DataSourceException $e) {
                $this->thermocoupleBroken = true;
                /* @todo some logging aggregation needed! */

                /* Thermocouple is open? Fuck thermocouple! */
                $this->logger->alert($e->getMessage());
            }
        }

        $this->getDashboard()
            ->update('Intk', sprintf("%.2f", $this->getServo()->getRelative() * 100))
            ->update('Exhst', sprintf("%d", $this->exhaustTemp))
            ->update('IntkState', $this->getState())
        ;
    }

    /**
     * Fully closes air intake valve
     */
    public function close() {
        $this->logger->info('Shutter closed');
        $this->servo
            ->setRelative(0)
            ->send();
    }

    /**
     * Fully opens air intake valve
     */
    public function open() {
        $this->logger->info('Shutter opened');
        $this->servo
            ->setRelative(1)
            ->send();
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

    public function setServo(AnalogServo $servo) {
        $this->servo = $servo;

        return $this;
    }

    public function getServo() {
        return $this->servo;
    }
}

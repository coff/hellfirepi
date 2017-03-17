<?php

namespace Coff\Hellfire\System;

use Coff\Hellfire\ComponentArray\BoilerSensorArray;
use Coff\Hellfire\Event\BoilerTempEvent;
use Coff\Hellfire\Event\CyclicEvent;

class FailoverAirIntakeSystem extends AirIntakeSystem
{
    public function init()
    {
        $this->getEventDispatcher()->addListener(CyclicEvent::EVERY_30_SECOND,
            [$this, 'every30s']);

        return parent::init();
    }

    /**
     * @param CyclicEvent $event
     */
    public function every30s(CyclicEvent $event) {
        $this->moveTrigonometrically();
    }

    protected function moveTrigonometrically() {

        /** @var BoilerSensorArray $boilerSensors */
        $boilerSensors = $this->getContainer()['data-sources:boiler'];

        $percent = $boilerSensors->getTargetPercent(BoilerSensorArray::SENSOR_HIGH, 3);

        /* we make sure value is not out of these bounds */
        if ($percent > 100) {
            $percent = 100;
        }

        if ($percent < 0) {
            $percent = 0;
        }

        /* pi() / 3 gives first 60 degrees where secans (1/cos) gives
            values 1 to 2 */
        $radians = $percent / 100 * pi() / 3;

        /*
         * - 1/cos gives values 1-2 so we reduce it by 1 to get 0-1
         * - then result is substracted from 1 since we need 1 as fully open
         *   and 0 as closed
         */
        $newPosition = 1 - (1 / cos($radians) - 1);

        $currentPosition = $this->servo->getRelative();

        /* when moving shutter up this makes some extra step up so that last
           move made by the shutter is downward - otherwise servo makes
           annoying noises for a long time after moving up */
        if ($newPosition > $currentPosition) {
            $this->servo
                ->setRelative($newPosition + 0.1)
                ->send();
            usleep(300000);
        }

        $this->servo
            ->setRelative($newPosition)
            ->send();


    }
}

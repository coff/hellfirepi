<?php


namespace Coff\Hellfire\Bootstrap;

use Coff\Hellfire\Servo\AnalogServo;
use Coff\Hellfire\System\AirIntakeSystem;

class AirIntakeSystemBootstrap extends Bootstrap
{
    public function init()
    {
        $this->container['system:intake'] = function($c) {

            $logger = $c['logger'];

            $servo = new AnalogServo(800,2350);

            $logger->info('Initializing servo...');

            $servo
                ->setPigpioClient($c['client:pigpio'])
                ->setGpio(22)
                ->setStepLength(20)
                ->init();

            $logger->info('Servo initialized.');

            $intake = new AirIntakeSystem();
            $intake->setLogger($logger);
            $intake
                ->setContainer($c)
                ->setServo($servo)
            ;

            return $intake;
        };
    }
}
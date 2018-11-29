<?php


namespace Coff\Hellfire\Bootstrap;


use Coff\Hellfire\System\AirIntakeSystem;

class HellfireServerCommandBootstrap extends Bootstrap
{
    public function init()
    {
        (new LoggerBootstrap($this->container))->init();


        (new AirIntakeSystemBootstrap($this->container))->init();

        /** @var AirIntakeSystem $intake */
        $intake = $this->container['system:intake'];
        $intake->setSensorArray($this->c)

        intake->init();
    }
}
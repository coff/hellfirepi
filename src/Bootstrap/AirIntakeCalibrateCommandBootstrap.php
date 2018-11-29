<?php


namespace Coff\Hellfire\Bootstrap;


class AirIntakeCalibrateCommandBootstrap extends Bootstrap
{
    public function init()
    {
        (new LoggerBootstrap($this->container))->init();
        (new AirIntakeSystemBootstrap($this->container))->init();
    }
}
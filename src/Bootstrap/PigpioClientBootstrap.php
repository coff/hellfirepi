<?php


namespace Coff\Hellfire\Bootstrap;


use Volantus\Pigpio\Client;
use Volantus\Pigpio\Network\Socket;

class PigpioClientBootstrap extends Bootstrap
{
    public function init()
    {
        $this->container['client:pigpio'] = function($c) {
            return new Client(new Socket('127.0.0.1', 8888));
        };
    }
}
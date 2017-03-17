<?php

namespace Coff\Hellfire\Event;

use Coff\Hellfire\CommonTrait\ContainerTrait;

class Event extends \Symfony\Component\EventDispatcher\Event
{
    static private $container;

    public static function setContainer($container) {
        self::$container = $container;
    }

    public function getContainer() {
        return self::$container;
    }

    public function onDispatch($eventName) {

        return 0;
    }
}

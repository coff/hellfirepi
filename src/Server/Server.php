<?php

namespace Coff\Hellfire\Server;

use Coff\Hellfire\CommonTrait\ContainerTrait;
use Coff\Hellfire\CommonTrait\EventDispatcherTrait;

/**
 * Abstract Server class
 *
 *
 */
abstract class Server extends \Coff\OneWire\Server\Server
{
    use ContainerTrait;
    use EventDispatcherTrait;
}

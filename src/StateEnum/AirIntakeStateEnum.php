<?php


namespace Coff\Hellfire\StateEnum;

use Coff\SMF\StateEnum;

class AirIntakeStateEnum extends StateEnum
{
    const   __default   = self::CLOSED,
            CLOSED      = 'closed',
            OPENED      = 'opened',
            ACTIVE      = 'active';
}
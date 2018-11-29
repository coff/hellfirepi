<?php


namespace Coff\Hellfire\StateEnum;

use Coff\SMF\StateEnum;

class HeaterStateEnum extends StateEnum
{
    const   __default     = self::OFF,
            OFF           = 'off',
            ACTIVE        = 'active',
            EXTON         = 'exton', // externally on
            ON            = 'on';
}
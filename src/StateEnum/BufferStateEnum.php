<?php


namespace Coff\Hellfire\StateEnum;


use Coff\SMF\StateEnum;

class BufferStateEnum extends StateEnum
{
    const   __default     = self::EMPTY,
            EMPTY         = 'empty',
            NOTEMPTY      = 'notempty',
            FULL          = 'full';
}
<?php

namespace Coff\Hellfire\StateEnum;

use Coff\SMF\StateEnum;

class BoilerStateEnum extends StateEnum
{
    const   __default       =   self::COLD,
            COLD            =   'cold',
            WARMUP          =   'warmup',
            WARMUPFAILED    =   'warmupfailed',
            WORKING         =   'working',
            OVERHEATING     =   'overheating',
            COOLING         =   'cooling';

}
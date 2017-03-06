<?php

namespace Coff\Hellfire\Event;

class RoomTempEvent extends Event
{
    const
        ON_TOO_HIGH = 'boiler_temp.too_high',
        ON_TOO_LOW  = 'boiler_temp.too_low';
}


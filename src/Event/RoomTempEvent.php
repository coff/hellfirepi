<?php

namespace Coff\Hellfire\Event;

class RoomTempEvent extends Event
{
    const
        ON_TOO_HIGH = 'room_temp.too_high',
        ON_TOO_LOW  = 'room_temp.too_low';
}


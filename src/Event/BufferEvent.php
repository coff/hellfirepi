<?php

namespace Coff\Hellfire\Event;

class BufferEvent extends Event
{
    const
        /* emptying events */
        ON_DROPPING_EMPTY        = 'buffer.dropping_empty',
        ON_DROPPING_NEAR_EMPTY   = 'buffer.dropping_near_empty',
        ON_DROPPING_NOT_FULL     = 'buffer.dropping_not_full',

        /* filling events */
        ON_FILLING_NOT_EMPTY    = 'buffer.filling_not_empty',
        ON_FILLING_NEAR_FULL    = 'buffer.filling_near_full',
        ON_FILLING_FULL         = 'buffer.filling_full';


}

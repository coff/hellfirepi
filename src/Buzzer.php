<?php

namespace Coff\Hellfire;

use PiPHP\GPIO\Pin\OutputPin;

class Buzzer
{
    const
        EMITS_ON_HIGH = true,
        EMITS_ON_LOW = false,

        PLAY_IN_BG = true,
        PLAY_IN_FG = false;

    /** @var  OutputPin */
    protected $pin;

    protected $playState, $silentState;

    /** @var  BuzzerNotes */
    protected $buzzerNotes;

    public function __construct(OutputPin $pin, $playsOnHigh = self::EMITS_ON_HIGH)
    {
        $this->pin = $pin;

        if ($playsOnHigh === true) {
            $this->playState = 1;
            $this->silentState = 0;
        } else {
            $this->playState = 0;
            $this->silentState = 1;
        }

        $pin->setValue($this->silentState);
    }

    public function getPin() {
        return $this->pin;
    }

    public function setNotes(BuzzerNotes $notes) {
        $this->buzzerNotes = $notes;

        return $this;
    }

    public function play($inBackground = self::PLAY_IN_BG) {

        $forkSuccess = null;

        if ($inBackground) {
            $res = pcntl_fork();
            switch (true) {
                case $res > 0:
                    /* we're parent */
                    return;
                case $res === 0:
                    /* we're child */
                    $forkSuccess = true;
                    break;
                case $res < 0:
                    /* Error! Continue in foreground! */
                    break;
            }
        }

        $notes = $this->buzzerNotes->getNotes();
        foreach ($notes as $note) {
            list($freq, $uSecs) = $note;

            switch (true) {
                case $freq === true:
                    $this->pin->setValue($this->playState);
                    usleep($uSecs);
                    $this->pin->setValue($this->silentState);
                    break;

                case $freq === false:
                    $this->pin->setValue($this->silentState); // just in case
                    usleep($uSecs);
                    break;
/*
 * That's not too good. Any ideas?
                case $freq > 1:
                    $cycleLength = 1000000 / $freq;
                    $times = $uSecs / $cycleLength;

                    for ($i=0;$i<$times;$i++) {
                        $this->pin->setValue($this->playState);
                        usleep($cycleLength/2);
                        $this->pin->setValue($this->silentState);
                        usleep($cycleLength/2);
                    }
                    break;*/
            }
        }

        if ($inBackground && $forkSuccess) {
            /* We're child and our parent does not need us anymore so
                we commit suicide ;) */
            exit(0);
        }
    }
}

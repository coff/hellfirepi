<?php

namespace Coff\Hellfire;

class BuzzerNotes
{
    protected $notes;

    /* @var int $timing one tenth of a second by default */
    protected $timing=100000;

    public function setTiming($uSecs) {
        $this->timing = $uSecs;

        return $this;
    }


    /**
     * Adds notes as array with given or default timing or
     *
     * @param array $notes an array of notes i.e. [1,1,0,1,0,1,0,1,1]
     * @param null $timingUSecs
     * @return $this
     */
    public function add(array $notes, $timingUSecs = null) {

        if ($timingUSecs === null) {
            $timingUSecs = $this->timing;
        }

        foreach ($notes as $note) {
            $this->notes[] = array((bool)$note, $timingUSecs);
        }

        return $this;
    }

    public function on($uSecs) {
        $this->notes[] = array(true, $uSecs);

        return $this;
    }

    public function off($uSecs) {
        $this->notes[] = array(false, $uSecs);

        return $this;
    }

    public function getNotes() {
        return $this->notes;
    }
}

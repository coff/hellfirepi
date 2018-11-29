<?php


namespace Coff\Hellfire\ControlMap;

class AirIntakeControlMap extends ControlMap
{
    public function setExhaustTemp($temp)
    {
        $this->y = $temp;

        return $this;
    }

    public function setBoilerTemp($temp)
    {
        $this->x = $temp;

        return $this;
    }

    public function parseValue($value)
    {
        // we invert it for map storing convenience ;)
        return 100 - $value;
    }

}
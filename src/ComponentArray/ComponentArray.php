<?php

namespace Coff\Hellfire\ComponentArray;

class ComponentArray implements \ArrayAccess
{
    protected $components = [];

    public function offsetExists($offset)
    {
        return isset($this->components[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->components[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->components[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->components[$offset]);
    }


}

<?php

namespace Coff\Hellfire\ComponentArray;

class ComponentArray implements \ArrayAccess, \Iterator
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

    function rewind() {
        return reset($this->components);
    }

    function current() {
        return current($this->components);
    }

    function key() {
        return key($this->components);
    }

    function next() {
        return next($this->components);
    }

    function valid() {
        return key($this->components) !== null;
    }
}

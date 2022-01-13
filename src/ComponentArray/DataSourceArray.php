<?php

namespace Coff\Hellfire\ComponentArray;

use Coff\DataSource\DataSource;
use Coff\DataSource\DataSourceInterface;
use Coff\Hellfire\Exception\HellfireException;

class DataSourceArray extends ComponentArray
{
    protected $readings;

    /**
     * Prevents inserting value other than DataSource
     * @param mixed $offset
     * @param mixed $value
     * @throws HellfireException
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof DataSourceInterface) {
            throw new HellfireException('DataSource object expected!');
        }

        $this->components[$offset] = $value;
    }

    /**
     * Performs internal readings update.
     * data.
     */
    public function update() {

        /**
         * @var string $index
         * @var DataSource $component
         */
        foreach ($this->components as $index => $component) {
            $component->update();
            $this->readings[(string)$index] = $component->getValue();
        }

        return $this;
    }

    /**
     * Returns readings for all sensors in array
     *
     * @param string|int $sensorId
     * @return mixed
     */
    public function getReading($sensorId)
    {
        if (!isset($this->readings[$sensorId])) {
            throw new HellfireException('No reading for ' . $sensorId);
        }
        return $this->readings[$sensorId];
    }

    /**
     * Returns an average reading of all array's sensors.
     * @return float|int
     */
    public function getAverage() {

        return array_sum($this->readings) / count($this->components);
    }

    /**
     * Returns max value
     * @return mixed
     */
    public function getMax() {
        return max($this->readings);
    }

    /**
     * Returns min value
     * @return float|int
     */
    public function getMin() {
        return min($this->readings);
    }

    /**
     * Returns difference between max and min.
     * @return float|int
     */
    public function getGap() {
        return max($this->readings) - min($this->readings);
    }

    /**
     * Performs array_walk so calls callback for each array element
     * @param callable $callback
     */
    public function onEach(callable $callback) {
        array_walk($this->components, $callback);
    }

    /**
     * Returns components as array for easier array-like operations.
     * @return array
     */
    public function toArray() {
        return $this->components;
    }
}

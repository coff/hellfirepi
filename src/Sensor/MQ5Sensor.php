<?php

namespace Coff\Hellfire\Sensor;

class MQ5Sensor extends Sensor
{
    protected $rawReading;
    protected $spi;

    public function init()
    {
        /**
         * SPI library: https://github.com/frak/php_spi
         */
        /** @noinspection PhpUndefinedConstantInspection */
        /** @noinspection PhpUndefinedClassInspection */
        $this->spi = new \Spi(
            0, // bus number (always 0 on RPi)
            1, // chip select CS (0 or 1)
            array (
                'mode' => SPI_MODE_0,
                'bits' => 8,
                'speed' => 100000, // min. 10KHz
                'delay' => 100000, // don't know really if this is set properly
            )
        );
    }

    public function update()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $x = $this->spi->transfer(array(1,128,0));
        $this->rawReading = str_pad(decbin($x[0]),8,'0', STR_PAD_LEFT).'|'.str_pad(decbin($x[1]),8,'0', STR_PAD_LEFT).'|'.str_pad(decbin($x[2]),8,'0', STR_PAD_LEFT);

        $this->value = ($x[1] << 8) + $x[2];

        return $this;
    }

    public function render()
    {
        // TODO: Implement render() method.
    }
}

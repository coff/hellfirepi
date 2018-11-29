<?php

namespace Coff\Hellfire\DataSource;

use Coff\DataSource\DataSource;
use Coff\DataSource\Exception\DataSourceException;
use Volantus\Pigpio\Client;
use Volantus\Pigpio\SPI\SpiDevice;

/**
 * Max6675Sensor
 *
 * Hookup according to
 * https://www.raspberrypi.org/forums/viewtopic.php?f=91&t=145568&p=961403
 * is as follows:
 * Max6675 pin 1 GND to Pi pin 6 GND
 * Max6675 pin 2 VCC to Pi Pin 1 3V DC Power
 * Max6675 pin 3 DO to Pi pin 21, MISO SPI (GPIO 13)
 * Max6675 pin 4 CS to Pi pin 24, CE0 SPI (GPIO 10) (note: for SPI device 0) *
 * Max6675 pin 5 CLK to Pi pin 23, SCLK SPI (GPIO 14)
 *
 * Spi interface tutorial:
 * http://www.corelis.com/education/SPI_Tutorial.htm
 */
class Max6675DataSource extends DataSource
{
    /** @var SpiDevice */
    protected $spiDevice;

    /** @var Client */
    protected $client;

    /**
     * @param Client $client
     * @return $this
     */
    public function setPigpioClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Sets SPI device
     * @param SpiDevice $spiDevice
     * @return $this
     */
    public function setSpiDevice(SpiDevice $spiDevice)
    {
        $this->spiDevice = $spiDevice;

        return $this;
    }

    public function update()
    {
        $res = $this->spiDevice->crossTransfer([1,1]);

        /*
         * According to MAX6675 specs consequent bits contain
         * 15   - dummy bit always 0
         * 14-3 - bits containing temperature in celsius with 1/4 of a degree
         *        resolution, first most significant bit (MSB-LSB)
         * 2    - is high only if thermocouple is open (broken circuit?)
         * 1    - device ID?
         * 0    - three-state? don't know what it means
         *
         * Full specs here:
         * https://cdn-shop.adafruit.com/datasheets/MAX6675.pdf
         */

        if ($res[1] & 4) {
            throw new DataSourceException('Thermocouple is open!');
        }

        $this->value = (($res[0] << 8 | $res[1]) >> 3) * 0.25;

        return $this;
    }

}

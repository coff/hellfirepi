<?php

namespace Coff\Hellfire\Relay;

use Coff\DataSource\DataSourceInterface;
use Hellfire\Exception\HellfireException;
use PiPHP\GPIO\Pin\OutputPinInterface;
use PiPHP\GPIO\Pin\PinInterface;

class Relay implements DataSourceInterface
{
    const
        STATE_ON    = 1,
        STATE_OFF   = 0,

        NORMALLY_OFF = 'noff',
        NORMALLY_ON  = 'non';

    /**
     * @var OutputPinInterface
     */
    protected $pin;

    protected $type;

    protected $defaultState;

    protected $state;

    public function __construct($type = self::NORMALLY_OFF, $defaultState = self::STATE_OFF)
    {
        $this->type = $type;
        $this->defaultState = $defaultState;
    }

    public function setPin(OutputPinInterface $pin) {
        $this->pin = $pin;

        return $this;
    }

    public function getPin() {
        return $this->pin;
    }

    public function init() {
        if ($this->defaultState === self::STATE_OFF) {
            $this->off();
        } else {
            $this->on();
        }

        return $this;
    }

    public function on() {
        $this->state = self::STATE_ON;

        if ($this->type === self::NORMALLY_OFF) {
            $this->high();
        } else {
            $this->low();
        }

        return $this;
    }

    public function off() {
        $this->state = self::STATE_OFF;
        
        if ($this->type === self::NORMALLY_OFF) {
            $this->low();
        } else {
            $this->high();
        }

        return $this;
    }

    public function getState() {
        return $this->state;
    }

    public function getValue()
    {
        return $this->getState();
    }

    public function update()
    {
        /* this is just to allow Relay implement DataSourceInterface */

        return $this;
    }

    /**
     * Just returns current time() for Relay it doesn't matter.
     * @return int
     */
    public function getStamp()
    {
        return time(); // just returns current timestamp
    }

    protected function high() {
        if (!$this->pin instanceof OutputPinInterface) {
            throw new HellfireException('OutputPin required!');
        }

        $this->pin->setValue(PinInterface::VALUE_HIGH);
    }

    protected function low() {
        if (!$this->pin instanceof OutputPinInterface) {
            throw new HellfireException('OutputPin required!');
        }

        $this->pin->setValue(PinInterface::VALUE_LOW);
    }
}

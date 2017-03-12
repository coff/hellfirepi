<?php

namespace Coff\Hellfire\Relay;

use Coff\DataSource\DataSourceInterface;
use Coff\Hellfire\Exception\HellfireException;
use PiPHP\GPIO\Pin\OutputPinInterface;
use PiPHP\GPIO\Pin\PinInterface;

/**
 * Relay control class
 *
 *
 */
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

    /** @var  int */
    protected $stamp;

    public function __construct($type = self::NORMALLY_OFF, $defaultState = self::STATE_OFF)
    {
        $this->type = $type;
        $this->defaultState = $defaultState;
    }

    /**
     * Sets pin relay works on
     *
     * @param OutputPinInterface $pin
     * @return $this
     */
    public function setPin(OutputPinInterface $pin) {
        $this->pin = $pin;

        return $this;
    }

    /**
     * Returns pin relay works on
     *
     * @return OutputPinInterface
     */
    public function getPin() {
        return $this->pin;
    }

    /**
     * Initialization method (turns relay on or off depending on configuration)
     *
     * @return $this
     */
    public function init() {
        if ($this->defaultState === self::STATE_OFF) {
            $this->off();
        } else {
            $this->on();
        }

        return $this;
    }

    /**
     * Switches relay on
     *
     * @return $this
     */
    public function on() {
        $this->state = self::STATE_ON;

        if ($this->type === self::NORMALLY_OFF) {
            $this->high();
        } else {
            $this->low();
        }

        return $this;
    }

    /**
     * Switches relay off
     *
     * @return $this
     */
    public function off() {
        $this->state = self::STATE_OFF;
        
        if ($this->type === self::NORMALLY_OFF) {
            $this->low();
        } else {
            $this->high();
        }

        return $this;
    }

    /**
     * Returns state of the pin
     *
     * @return self::STATE_*
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Returns true if state is STATE_ON
     *
     * @return bool
     */
    public function isOn() {
        return $this->state === self::STATE_ON;
    }

    /**
     * Returns true if state is STATE_OFF
     *
     * @return bool
     */
    public function isOff() {
        return $this->state === self::STATE_OFF;
    }

    /**
     * Returns state of the pin
     *
     * @return self::STATE_*
     */
    public function getValue()
    {
        return $this->getState();
    }

    /**
     * Does nothing at the moment
     *
     * @return $this
     */
    public function update()
    {
        /* this is just to allow Relay implement DataSourceInterface */

        return $this;
    }

    /**
     * Returns time when state has changed
     *
     * @return int
     */
    public function getStamp()
    {
        return $this->stamp;
    }

    /**
     * Internal method for setting output pin state
     *
     * @throws HellfireException
     */
    protected function high() {
        if (!$this->pin instanceof OutputPinInterface) {
            throw new HellfireException('OutputPin required!');
        }

        $this->pin->setValue(PinInterface::VALUE_HIGH);
        $this->stamp = time();
    }


    /**
     * Internal method for setting output pin state
     *
     * @throws HellfireException
     */
    protected function low() {
        if (!$this->pin instanceof OutputPinInterface) {
            throw new HellfireException('OutputPin required!');
        }

        $this->pin->setValue(PinInterface::VALUE_LOW);
        $this->stamp = time();
    }
}

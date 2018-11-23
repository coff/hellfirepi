<?php

namespace Coff\Hellfire\Servo;

use Coff\DataSource\DataSourceInterface;
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\PWM\PwmSender;

/**
 * AnalogServo for analog servo control over ServoBlaster library
 */
class AnalogServo implements DataSourceInterface {

    const
        RANGE_MIN = 0,
        RANGE_MAX = 1;

    /**
     * @var int[] $signalRangeSig min,max value for servo in uSecs
     */
    protected $travelRangeSig;

    protected $signalTravel;

    protected $stepScaling=1;

    /**
     * @var int $gpio number servo works on
     */
    protected $gpio;

    protected $signalLength;

    /**
     * Step length expressed in signal length.
     *
     * @var
     */
    protected $stepLength=4;

    protected $sender;
    protected $client;

    /**
     * @param int $signalMin
     * @param int $signalMax
     * @internal param int $signalMin
     */
    public function __construct($signalMin=500, $signalMax=2500) {
        $this->setTravelRangeSig($signalMin, $signalMax);
    }

    public function init()
    {

        $this->sender = new PwmSender($this->client);

        $this->setRelative(0.5); // start with neutral position?

        return $this;
    }


    /**
     * @param Client $client
     * @return $this
     */
    public function setPigpioClient($client) {
        $this->client = $client;

        return $this;
    }

    /**
     * @param $gpio
     * @return $this
     */
    public function setGpio($gpio) {
        $this->gpio = $gpio;

        return $this;
    }

    public function setStepLength($signalValue) {
        $this->stepLength = $signalValue;

        return $this;
    }

    /**
     * @param float|int $rel
     * @return $this
     */
    public function stepUp($rel=1) {
        $this->signalLength += $rel * $this->stepLength;

        return $this;
    }

    /**
     * @param float|int $rel
     * @return $this
     */
    public function stepDown($rel=1) {
        $this->signalLength -= $rel * $this->stepLength;

        return $this;
    }

    public function isMax() {
        if ($this->signalLength == $this->travelRangeSig[self::RANGE_MAX]) {
            return true;
        }

        return false;
    }


    public function isMin() {
        if ($this->signalLength == $this->travelRangeSig[self::RANGE_MIN]) {
            return true;
        }

        return false;
    }

    /**
     * Sets signalLength by relative value 0 to 1
     * Examples:
     *  0.5 = 50%
     *  1   = 100%
     *
     * @param double $relative
     * @return $this
     */
    public function setRelative($relative) {
        $this->signalLength = $this->travelRangeSig[self::RANGE_MIN] + $relative * $this->signalTravel;

        return $this;
    }

    public function getRelative() {
        return ($this->signalLength - $this->travelRangeSig[self::RANGE_MIN]) / $this->signalTravel;
    }

    public function setStepScaling($scale) {
        $this->stepScaling = $scale;

        return $this;
    }

    public function setSignalLength($signalLength) {
        $this->signalLength = $signalLength;

        return $this;
    }

    public function getSignalLength() {
        return $this->signalLength;
    }

    /**
     * @param int $min
     * @param int $max
     * @return $this
     */
    public function setTravelRangeSig($min=500, $max=2500) {
        $this->travelRangeSig = [$min, $max];
        $this->signalTravel = $max-$min;

        return $this;
    }

    /**
     * Sends signal to device.
     */
    public function send() {
        $this->fixLength();

        /*
        if ($this->signalLength < $this->travelRangeSig[self::RANGE_MIN]) {
            throw new HellfireException('Servo signal out of range!');
        }

        if ($this->signalLength > $this->travelRangeSig[self::RANGE_MAX]) {
            throw new HellfireException('Servo signal out of range!');
        }*/

//        system('echo "m ' . $this->gpio . ' w s ' . $this->gpio . ' ' . $this->signalLength . '" > /dev/pigpio');
 //       exec('/usr/bin/pigs s ' . $this->gpio . ' ' . $this->signalLength);

  /*      $h = fopen("/dev/pigpio", "w");

        if ($h === false) {
            echo "Error opening pigpio! \n";
        }

        fprintf($h, "s " . $this->gpio . " ". $this->signalLength . "\n");
        fclose($h);*/

        $this->sender->setPulseWidth($this->gpio, $this->signalLength);

        return $this;
    }

    protected function fixLength() {
        if ($this->signalLength < $this->travelRangeSig[self::RANGE_MIN]) {
            $this->signalLength = $this->travelRangeSig[self::RANGE_MIN];
            return false;
        }

        if ($this->signalLength > $this->travelRangeSig[self::RANGE_MAX]) {
            $this->signalLength = $this->travelRangeSig[self::RANGE_MAX];
            return false;
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getSignalTravel()
    {
        return $this->signalTravel;
    }

    /**
     * This here is just to implement DataSourceInterface
     * @return int
     */
    public function getStamp()
    {
        return time();
    }

    public function getValue()
    {
        return $this->signalLength;
    }

    public function update()
    {
        $this->send();

        return $this;
    }
}

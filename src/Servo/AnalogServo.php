<?php

namespace Hellfire\Servo;

use Hellfire\Exception\HellfireException;

/**
 * AnalogServo for analog servo control over servoblaster library
 */
class AnalogServo {

    const
        RANGE_MIN = 0,
        RANGE_MAX = 1;

    /**
     * @var int[] $signalRangeSig min,max value for servo in uSecs
     */
    protected $travelRangeSig;

    protected $travelRangeDeg;

    protected $travelRangeRad;

    protected $armLength=1;

    protected $signalTravel;

    protected $linearTravel;

    protected $radialTravel;

    /**
     * @var int $gpio number servo works on
     */
    protected $gpio;

    protected $signalLength;
    protected $radialPos;
    protected $linearPos;

    /**
     *
     */
    public function __construct($travelRangeDeg=180, $signalMin=500, $signalMax=2500) {
        $this->setTravelRangeSig($signalMin, $signalMax);
        $this->setTravelRangeDeg(0, $travelRangeDeg);
        $rad = deg2rad($travelRangeDeg);
        $this->setTravelRangeRad(0, $rad);
    }

    public function setGpio($gpio) {
        $this->gpio = $gpio;

        return $this;
    }

    public function setTravelRangeSig($min=500, $max=2500) {
        $this->travelRangeSig = [$min, $max];
        $this->signalTravel = $max-$min;

        return $this;
    }

    public function setTravelRangeDeg($degMin, $degMax) {
        $this->travelRangeDeg = [$degMin, $degMax];

        return $this;
    }

    public function setTravelRangeRad($radMin, $radMax) {
        $this->travelRangeRad = [$radMin, $radMax];

        return $this;
    }

    public function setArmLength($armLength) {
        $this->armLength = $armLength;

        $travelRads = $this->travelRangeRad[self::RANGE_MAX] - $this->travelRangeRad[self::RANGE_MIN];

        $this->linearTravel = 2 * $armLength * sin($travelRads / 2);
        $this->radialTravel = $travelRads * $armLength;

        return $this;
    }

    public function linearMoveTo($linearPos) {
        $relPos = $linearPos / $this->linearTravel;

        $this->radialPos = $relPos * $this->radialTravel;

        $signalLength = $this->travelRangeSig[self::RANGE_MIN] + $relPos * $this->signalTravel;

        if ($signalLength > $this->travelRangeSig[self::RANGE_MAX]) {
            $signalLength = $this->travelRangeSig[self::RANGE_MAX];
        }


    }

    /**
     * Sends signal to device. This is internal method since it doesn't update all position properties.
     */
    protected function send() {
        if ($this->signalLength < $this->travelRangeSig[self::RANGE_MIN]) {
            throw new HellfireException('Servo signal out of range!');
        }

        if ($this->signalLength > $this->travelRangeSig[self::RANGE_MAX]) {
            throw new HellfireException('Servo signal out of range!');
        }

        system('echo "' . $this->gpio . '=' . $this->signalLength . 'us" > /dev/servoblaster');

        return $this;
    }



    /**
     * Sets servo arm position in relation to hour 12
     * @var int $position position in degrees
     */
    public function setPositionByDeg($position) {
    }

}

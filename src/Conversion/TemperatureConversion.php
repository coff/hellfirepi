<?php
namespace Hellfire\Conversion;

use Hellfire\Enum\TemperatureUnit;

class TemperatureConversion
{
    const
        KELVIN  =  273.15,
        FAHRENHEIT = 459.67;

    /**
     * Value in Kelvin degrees
     * @var double
     */
    protected $value;

    /**
     * @var TemperatureUnit
     */
    protected $inputUnit;

    public function __construct($inputValue, TemperatureUnit $inputUnit)
    {
        $this->inputUnit = $inputUnit;

        switch ((string)$inputUnit) {
            case TemperatureUnit::CELSIUS:
                $this->value = $inputValue + self::KELVIN;
                break;
            case TemperatureUnit::FAHRENHEIT:
                $this->value = ($inputValue + self::FAHRENHEIT) * 5 / 9;
                break;
            default:
                $this->value = $inputValue;
        }
    }

    public function to(TemperatureUnit $outputUnit) {
        switch ((string)$outputUnit) {
            case TemperatureUnit::CELSIUS:
                return $this->value - self::KELVIN; break;

            case TemperatureUnit::FAHRENHEIT:
                return ($this->value / (5/9)) * self::FAHRENHEIT; break;

            default:
                return $this->value;
        }
    }
}

<?php


namespace Coff\Hellfire\ControlMap;


use Coff\DataSource\DataSourceInterface;
use Coff\DataSource\Exception\DataSourceException;

abstract class ControlMap implements DataSourceInterface
{

    protected $path;

    protected $x, $y;

    /** @var array */
    protected $map;

    protected $stamp;


    public function setPath($filepath)
    {
        $this->path =  $filepath;

        return $this;
    }

    public function init()
    {
        $this->update();
    }

    /**
     * @return $this
     * @throws DataSourceException
     */
    public function update()
    {
        $h = fopen ($this->path, "r");

        if (false === $h) {
            throw new DataSourceException("Can't read map file " . $this->path);
        }

        $this->map = [];

        $xArray = fgetcsv($h);

        if (count($xArray) < 2) {
            throw new DataSourceException("Wrong format! Check map file: " . $this->path);
        }

        while (false !== ($arr = fgetcsv($h))) {
            if (count($arr) !== count($xArray)) {
                throw new DataSourceException("Expected " . count($xArray) . " columns, got " . count($arr));
            }


            $yValue = array_shift($arr);

            foreach ($xArray as $key => $xValue) {
                $this->map[$xValue][$yValue] = $this->parseValue($arr[$key]);
            }

        }

        fclose($h);

        $this->stamp = time();

        return $this;
    }

    public function parseValue($value) {
        return $value;
    }

    public function getValue()
    {
        return $this->map[$this->x][$this->y];
    }

    public function getStamp()
    {
        return $this->stamp;
    }
}
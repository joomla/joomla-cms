<?php

namespace OzdemirBurak\Iris\Color;

use OzdemirBurak\Iris\BaseColor;
use OzdemirBurak\Iris\Traits\HsTrait;

class Hsv extends BaseColor
{
    use HsTrait;

    /**
     * @var int
     */
    protected $value;

    /**
     * @param string $color
     *
     * @return array
     */
    protected function initialize($color)
    {
        return list($this->hue, $this->saturation, $this->value) = explode(',', $color);
    }

    /**
     * @return array
     */
    public function values()
    {
        return $this->getValues();
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \OzdemirBurak\Iris\Color\Hex
     */
    public function toHex()
    {
        return $this->toRgb()->toHex();
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Hsl
     */
    public function toHsl()
    {
        list($h, $s, $v) = $this->valuesInUnitInterval();
        $l = (2 - $s) * $v / 2;
        $s = $l && $l < 1 ? $s * $v / ($l < 0.5 ? $l * 2 : 2 - $l * 2) : $s;
        $code = implode(',', [round($h * 360), round($s * 100), round($l * 100)]);
        return new Hsl($code);
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Hsla
     */
    public function toHsla()
    {
        return $this->toHsl()->toHsla();
    }

    /**
     * @return \Ozdemirburak\Iris\Color\Hsv
     */
    public function toHsv()
    {
        return $this;
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Rgb
     */
    public function toRgb()
    {
        list($h, $s, $v) = $this->valuesInUnitInterval();
        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);
        switch ($i % 6) {
            case 0:
                list($r, $g, $b) = [$v, $t, $p];
                break;
            case 1:
                list($r, $g, $b) = [$q, $v, $p];
                break;
            case 2:
                list($r, $g, $b) = [$p, $v, $t];
                break;
            case 3:
                list($r, $g, $b) = [$p, $q, $v];
                break;
            case 4:
                list($r, $g, $b) = [$t, $p, $v];
                break;
            case 5:
                list($r, $g, $b) = [$v, $p, $q];
                break;
        }
        $code = implode(',', [round($r * 255), round($g * 255), round($b * 255)]);
        return new Rgb($code);
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Rgba
     */
    public function toRgba()
    {
        return $this->toRgb()->toRgba();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'hsv(' . implode(',', $this->values()) . ')';
    }

    /**
     * @param int|string $value
     *
     * @return int|$this
     */
    public function value($value = null)
    {
        if (is_numeric($value)) {
            $this->value = $value >= 0 && $value <= 100 ? $value : $this->value;
            return $this;
        }
        return (int) $this->value;
    }
}

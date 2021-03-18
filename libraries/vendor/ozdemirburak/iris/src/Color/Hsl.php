<?php

namespace OzdemirBurak\Iris\Color;

use OzdemirBurak\Iris\BaseColor;
use OzdemirBurak\Iris\Traits\HslTrait;

class Hsl extends BaseColor
{
    use HslTrait;

    /**
     * @param string $color
     *
     * @return array
     */
    protected function initialize($color)
    {
        return list($this->hue, $this->saturation, $this->lightness) = explode(',', $color);
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
     * @return \Ozdemirburak\Iris\Color\Hsl
     */
    public function toHsl()
    {
        return $this;
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \OzdemirBurak\Iris\Color\Hsla
     */
    public function toHsla()
    {
        return new Hsla(implode(',', array_merge($this->values(), [1.0])));
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Hsv
     */
    public function toHsv()
    {
        list($h, $s, $l) = $this->valuesInUnitInterval();
        $t = $s * $l < 0.5 ? $l : 1 - $l;
        $s = 2 * $t / ($l + $t);
        $l += $t;
        $code = implode(',', [round($h * 360), round($s * 100), round($l * 100)]);
        return new Hsv($code);
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Rgb
     */
    public function toRgb()
    {
        return $this->convertToRgb();
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \OzdemirBurak\Iris\Color\Rgba
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
        return 'hsl(' . implode(',', $this->values()) . ')';
    }
}

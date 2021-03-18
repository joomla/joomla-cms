<?php

namespace OzdemirBurak\Iris\Color;

use OzdemirBurak\Iris\BaseColor;
use OzdemirBurak\Iris\Helpers\DefinedColor;
use OzdemirBurak\Iris\Traits\AlphaTrait;
use OzdemirBurak\Iris\Traits\HslTrait;

class Hsla extends BaseColor
{
    use AlphaTrait, HslTrait;

    /**
     * @param string $code
     *
     * @return bool|mixed|string
     */
    protected function validate($code)
    {
        list($class, $index) = property_exists($this, 'lightness') ? ['hsl', 2] : ['hsv', 3];
        $color = str_replace(["{$class}a", '(', ')', ' ', '%'], '', DefinedColor::find($code, $index));
        if (substr_count($color, ',') === 2) {
            $color = "{$color},1.0";
        }
        $color = $this->fixPrecision($color);
        if (preg_match($this->validationRules(), $color, $matches)) {
            if ($matches[1] > 360 || $matches[2] > 100 || $matches[3] > 100 || $matches[4] > 1) {
                return false;
            }
            return $color;
        }
        return false;
    }

    /**
     * @param string $color
     *
     * @return void
     */
    protected function initialize($color)
    {
        list($this->hue, $this->saturation, $this->lightness, $this->alpha) = explode(',', $color);
        $this->alpha = (double) $this->alpha;
    }

    /**
     * @return array
     */
    public function values()
    {
        return array_merge($this->getValues(), [$this->alpha()]);
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Hsl
     */
    public function toHsl()
    {
        return $this->toRgba()->toHsl();
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Rgba
     */
    public function toRgba()
    {
        return $this->convertToRgb()->toRgba()->alpha($this->alpha());
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Rgb
     */
    public function toRgb()
    {
        return $this->toRgba()->toRgb();
    }

    /**
     * @return \Ozdemirburak\Iris\Color\Hsla
     */
    public function toHsla()
    {
        return $this;
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Hsv
     */
    public function toHsv()
    {
        return $this->toRgba()->toHsv();
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Hex
     */
    public function toHex()
    {
        return $this->toRgba()->toHex();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'hsla(' . implode(',', $this->values()) . ')';
    }
}

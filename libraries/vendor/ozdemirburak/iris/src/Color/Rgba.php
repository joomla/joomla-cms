<?php

namespace OzdemirBurak\Iris\Color;

use OzdemirBurak\Iris\BaseColor;
use OzdemirBurak\Iris\Helpers\DefinedColor;
use OzdemirBurak\Iris\Traits\AlphaTrait;
use OzdemirBurak\Iris\Traits\RgbTrait;

class Rgba extends BaseColor
{
    use AlphaTrait, RgbTrait;

    /**
     * @var \OzdemirBurak\Iris\Color\Rgb
     */
    protected $background;

    /**
     * @param string $code
     *
     * @return bool|mixed|string
     */
    protected function validate($code)
    {
        $color = str_replace(['rgba', '(', ')', ' '], '', DefinedColor::find($code, 1));
        if (substr_count($color, ',') === 2) {
            $color = "{$color},1.0";
        }
        $color = $this->fixPrecision($color);
        if (preg_match($this->validationRules(), $color, $matches)) {
            if ($matches[1] > 255 || $matches[2] > 255 || $matches[3] > 255 || $matches[4] > 1) {
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
        $colors = explode(',', $color);
        list($this->red, $this->green, $this->blue) = array_map('intval', $colors);
        $this->alpha = (double) $colors[3];
        $this->background = $this->defaultBackground();
    }

    /**
     * @return array
     */
    public function values()
    {
        return [
            $this->red(),
            $this->green(),
            $this->blue(),
            $this->alpha()
        ];
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \OzdemirBurak\Iris\Color\Rgb
     */
    public function toRgb()
    {
        list($red, $green, $blue) = array_map(function ($attribute) {
            $value = (1 - $this->alpha()) * $this->background->{$attribute}() + $this->alpha() * $this->{$attribute}();
            return floor($value);
        }, ['red', 'green', 'blue']);
        return new Rgb(implode(',', [$red, $green, $blue]));
    }

    /**
     * @return \Ozdemirburak\Iris\Color\Rgba
     */
    public function toRgba()
    {
        return $this;
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
        return $this->toRgb()->toHsl();
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Hsla|float
     */
    public function toHsla()
    {
        return $this->toHsl()->toHsla()->alpha($this->alpha());
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Hsv
     */
    public function toHsv()
    {
        return $this->toRgb()->toHsv();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'rgba(' . implode(',', $this->values()) . ')';
    }

    /**
     * @param \OzdemirBurak\Iris\Color\Rgb $rgb
     *
     * @return $this
     */
    public function background(Rgb $rgb)
    {
        $this->background = $rgb;
        return $this;
    }

    /**
     * @return \OzdemirBurak\Iris\Color\Rgb
     */
    protected function defaultBackground()
    {
        return new Rgb('255,255,255');
    }
}

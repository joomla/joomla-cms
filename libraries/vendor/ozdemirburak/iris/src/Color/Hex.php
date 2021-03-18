<?php

namespace OzdemirBurak\Iris\Color;

use OzdemirBurak\Iris\BaseColor;
use OzdemirBurak\Iris\Helpers\DefinedColor;
use OzdemirBurak\Iris\Traits\RgbTrait;

class Hex extends BaseColor
{
    use RgbTrait;

    /**
     * @param string $code
     *
     * @return string|bool
     */
    protected function validate($code)
    {
        $color = str_replace('#', '', DefinedColor::find($code));
        if (strlen($color) === 3) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }
        return preg_match('/^[a-f0-9]{6}$/i', $color) ? $color : false;
    }

    /**
     * @param string $color
     *
     * @return array
     */
    protected function initialize($color)
    {
        return list($this->red, $this->green, $this->blue) = str_split($color, 2);
    }

    /**
     * @return \OzdemirBurak\Iris\Color\Hex
     */
    public function toHex()
    {
        return $this;
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
     * @return \Ozdemirburak\Iris\Color\Hsla
     */
    public function toHsla()
    {
        return $this->toHsl()->toHsla();
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
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Rgb
     */
    public function toRgb()
    {
        $rgb = implode(',', array_map('hexdec', $this->values()));
        return new Rgb($rgb);
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
        return '#' . implode('', $this->values());
    }
}

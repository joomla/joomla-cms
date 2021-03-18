<?php

namespace OzdemirBurak\Iris;

use OzdemirBurak\Iris\Exceptions\InvalidColorException;

abstract class BaseColor
{
    /**
     * @param string $code
     *
     * @return mixed
     */
    abstract protected function validate($code);

    /**
     * @param string $color
     *
     * @return mixed
     */
    abstract protected function initialize($color);

    /**
     * @return array
     */
    abstract public function values();

    /**
     * @return \Ozdemirburak\Iris\Color\Hex
     */
    abstract public function toHex();

    /**
     * @return \Ozdemirburak\Iris\Color\Hsl
     */
    abstract public function toHsl();

    /**
     * @return \Ozdemirburak\Iris\Color\Hsla
     */
    abstract public function toHsla();

    /**
     * @return \Ozdemirburak\Iris\Color\Hsv
     */
    abstract public function toHsv();

    /**
     * @return \Ozdemirburak\Iris\Color\Rgb
     */
    abstract public function toRgb();

    /**
     * @return \Ozdemirburak\Iris\Color\Rgba
     */
    abstract public function toRgba();

    /**
     * @return string
     */
    abstract public function __toString();

    /**
     * Color constructor.
     *
     * @param string $code
     *
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     */
    public function __construct($code)
    {
        if (($color = $this->validate($code)) === false) {
            throw new InvalidColorException($this->getExceptionMessage() . ' => ' . $code);
        }
        $this->initialize($color);
    }

    /**
     * @param int $percent
     *
     * @return mixed
     */
    public function saturate($percent)
    {
        $color = $this->toHsl();
        $saturation = $this->clamp(($color->saturation() + $percent) / 100);
        return $color->saturation($saturation * 100)->back($this);
    }

    /**
     * @param int $percent
     *
     * @return mixed
     */
    public function desaturate($percent)
    {
        $color = $this->toHsl();
        $saturation = $this->clamp(($color->saturation() - $percent) / 100);
        return $color->saturation($saturation * 100)->back($this);
    }

    /**
     * @return mixed
     */
    public function grayscale()
    {
        return $this->desaturate(100);
    }

    /**
     * @param int $percent
     *
     * @return mixed
     */
    public function brighten($percent)
    {
        $percent *= -1;
        $color = $this->toRgb();
        $color->red(max(0, min(255, $color->red() - round(255 * ($percent / 100)))));
        $color->green(max(0, min(255, $color->green() - round(255 * ($percent / 100)))));
        $color->blue(max(0, min(255, $color->blue() - round(255 * ($percent / 100)))));
        return $color->back($this);
    }

    /**
     * @param int $percent
     *
     * @return mixed
     */
    public function lighten($percent)
    {
        $color = $this->toHsl();
        $lightness = $this->clamp(($color->lightness() + $percent) / 100);
        return $color->lightness($lightness * 100)->back($this);
    }

    /**
     * @param int $percent
     *
     * @return mixed
     */
    public function darken($percent)
    {
        $color = $this->toHsl();
        $lightness = $this->clamp(($color->lightness() - $percent) / 100);
        return $color->lightness($lightness * 100)->back($this);
    }

    /**
     * @link https://en.wikipedia.org/wiki/Luma_(video) Magic numbers taken from link
     * @return boolean
     */
    public function isLight()
    {
        $color = $this->toRgb();
        $darkness = 1 - (0.299 * $color->red() + 0.587 * $color->green() + 0.114 * $color->blue()) / 255;
        return $darkness < 0.5;
    }

    /**
     * @return boolean
     */
    public function isDark()
    {
        return !$this->isLight();
    }

    /**
     * @param int $percent
     *
     * @return mixed
     */
    public function spin($percent)
    {
        $color = $this->toHsl();
        $hue = ($color->hue() + $percent) % 360;
        return $color->hue($hue < 0 ? 360 + $hue : $hue)->back($this);
    }

    /**
     * @param \OzdemirBurak\Iris\BaseColor $color
     * @param int                          $percent
     *
     * @return mixed
     */
    public function mix(BaseColor $color, $percent = 50)
    {
        $first = $this->toRgb();
        $second = $color->toRgb();
        $weight = $percent / 100;
        $red = $first->red() * (1 - $weight) + $second->red() * $weight;
        $green = $first->green() * (1 - $weight) + $second->green() * $weight;
        $blue = $first->blue() * (1 - $weight) + $second->blue() * $weight;
        return $first->red($red)->green($green)->blue($blue)->back($this);
    }

    /**
     * @param int $percent
     *
     * @return mixed
     */
    public function tint($percent = 50)
    {
        $clone = clone $this;
        $white = $clone->toRgb()->red(255)->green(255)->blue(255);
        return $this->mix($white, $percent);
    }

    /**
     * @param int $percent
     *
     * @return mixed
     */
    public function shade($percent = 50)
    {
        $clone = clone $this;
        $black = $clone->toRgb()->red(0)->green(0)->blue(0);
        return $this->mix($black, $percent);
    }

    /**
     * @param $value
     *
     * @return float
     */
    protected function clamp($value)
    {
        return min(1, max(0, $value));
    }

    /**
     * @param \OzdemirBurak\Iris\BaseColor $color
     *
     * @return $this|\Ozdemirburak\Iris\Color\Hex|\Ozdemirburak\Iris\Color\Hsl|\Ozdemirburak\Iris\Color\Hsv|\Ozdemirburak\Iris\Color\Rgb
     */
    protected function back(BaseColor $color)
    {
        return $this->{'to' . substr(strrchr(get_class($color), '\\'), 1)}();
    }

    /**
     * @return string
     */
    protected function getExceptionMessage()
    {
        return 'Invalid ' . strtoupper(substr(static::class, strrpos(static::class, '\\') + 1)) . ' value';
    }
}

<?php

namespace OzdemirBurak\Iris\Traits;

use OzdemirBurak\Iris\Helpers\DefinedColor;

trait HsTrait
{
    /**
     * @var int
     */
    protected $hue;

    /**
     * @var int
     */
    protected $saturation;

    /**
     * @param string $code
     *
     * @return string|bool
     */
    protected function validate($code)
    {
        list($class, $index) = property_exists($this, 'lightness') ? ['hsl', 2] : ['hsv', 3];
        $color = str_replace([$class, '(', ')', ' ', '%'], '', DefinedColor::find($code, $index));
        if (preg_match('/^(\d{1,3}),(\d{1,3}),(\d{1,3})$/', $color, $matches)) {
            if ($matches[1] > 360 || $matches[2] > 100 || $matches[3] > 100) {
                return false;
            }
            return $color;
        }
        return false;
    }

    /**
     * @param int|string $hue
     *
     * @return int|$this
     */
    public function hue($hue = null)
    {
        if (is_numeric($hue)) {
            $this->hue = $hue >= 0 && $hue <= 360 ? $hue : $this->hue;
            return $this;
        }
        return (int) $this->hue;
    }

    /**
     * @param int|string $saturation
     *
     * @return int|$this
     */
    public function saturation($saturation = null)
    {
        if (is_numeric($saturation)) {
            $this->saturation = $saturation >= 0 && $saturation <= 100 ? $saturation : $this->saturation;
            return $this;
        }
        return (int) $this->saturation;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return [
            $this->hue(),
            $this->saturation() . '%',
            (property_exists($this, 'lightness') ? $this->lightness() : $this->value()) . '%'
        ];
    }

    /**
     * Values in [0, 1] range
     *
     * @return array
     */
    public function valuesInUnitInterval()
    {
        return [
            $this->hue() / 360,
            $this->saturation() / 100,
            (property_exists($this, 'lightness') ? $this->lightness() : $this->value()) / 100
        ];
    }
}

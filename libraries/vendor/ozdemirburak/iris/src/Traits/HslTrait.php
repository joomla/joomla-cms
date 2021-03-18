<?php

namespace OzdemirBurak\Iris\Traits;

use OzdemirBurak\Iris\Color\Rgb;

trait HslTrait
{
    use HsTrait;

    /**
     * @var int
     */
    protected $lightness;

    /**
     * @param int|string $lightness
     *
     * @return int|$this
     */
    public function lightness($lightness = null)
    {
        if (is_numeric($lightness)) {
            $this->lightness = $lightness >= 0 && $lightness <= 100 ? $lightness : $this->lightness;
            return $this;
        }
        return (int) $this->lightness;
    }

    /**
     * @throws \OzdemirBurak\Iris\Exceptions\InvalidColorException
     * @return \Ozdemirburak\Iris\Color\Rgb
     */
    public function convertToRgb()
    {
        list($h, $s, $l) = $this->valuesInUnitInterval();
        if ($s === 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) :
                $l + $s - $l * $s;
            $p = 2 * $l - $q;
            list($r, $g, $b) = [
                $this->hueToRgb($p, $q, $h + 1/3),
                $this->hueToRgb($p, $q, $h),
                $this->hueToRgb($p, $q, $h - 1/3)
            ];
        }
        $code = implode(',', [round($r * 255), round($g * 255), round($b * 255)]);
        return new Rgb($code);
    }

    /**
     * @param float $p
     * @param float $q
     * @param float $t
     *
     * @return mixed
     */
    protected function hueToRgb($p, $q, $t)
    {
        if ($t < 0) {
            $t++;
        }
        if ($t > 1) {
            $t--;
        }
        if ($t < 1/6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1/2) {
            return $q;
        }
        if ($t < 2/3) {
            return $p + ($q - $p) * (2/3 - $t) * 6;
        }
        return $p;
    }
}

<?php

/*
 * This file is part of the Imagine package.
 *
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Imagine\Image;

use Imagine\Exception\InvalidArgumentException;

/**
 * The color class
 */
final class Color
{
    /**
     * @var integer
     */
    private $r;

    /**
     * @var integer
     */
    private $g;

    /**
     * @var integer
     */
    private $b;

    /**
     * @var integer
     */
    private $alpha;

    /**
     * Constructs image color, e.g.:
     *     - new Color('fff') - will produce non-transparent white color
     *     - new Color('ffffff', 50) - will product 50% transparent white
     *     - new Color(array(255, 255, 255)) - another way of getting white
     *     - new Color(0x00FF00) - hexadecimal notation for green
     *
     * @param array|string|integer $color
     * @param integer              $alpha
     */
    public function __construct($color, $alpha = 0)
    {
        $this->setColor($color);
        $this->setAlpha($alpha);
    }

    /**
     * Returns RED value of the color
     *
     * @return integer
     */
    public function getRed()
    {
        return $this->r;
    }

    /**
     * Returns GREEN value of the color
     *
     * @return integer
     */
    public function getGreen()
    {
        return $this->g;
    }

    /**
     * Returns BLUE value of the color
     *
     * @return integer
     */
    public function getBlue()
    {
        return $this->b;
    }

    /**
     * Returns percentage of transparency of the color
     *
     * @return integer
     */
    public function getAlpha()
    {
        return $this->alpha;
    }

    /**
     * Returns a copy of current color, incrementing the alpha channel by the
     * given amount
     *
     * @param integer $alpha
     *
     * @return Color
     */
    public function dissolve($alpha)
    {
        return new Color((string) $this, $this->alpha + $alpha);
    }

    /**
     * Returns a copy of the current color, lightened by the specified number
     * of shades
     *
     * @param integer $shade
     *
     * @return Color
     */
    public function lighten($shade)
    {
        return new Color(
            array(
                min(255, $this->r + $shade),
                min(255, $this->g + $shade),
                min(255, $this->b + $shade),
            ),
            $this->alpha
        );
    }

    /**
     * Returns a copy of the current color, darkened by the specified number of
     * shades
     *
     * @param integer $shade
     *
     * @return Color
     */
    public function darken($shade)
    {
        return new Color(
            array(
                max(0, $this->r - $shade),
                max(0, $this->g - $shade),
                max(0, $this->b - $shade),
            ),
            $this->alpha
        );
    }

    /**
     * Internal
     *
     * Performs checks for validity of given alpha value and sets it
     *
     * @param integer $alpha
     *
     * @throws InvalidArgumentException
     */
    private function setAlpha($alpha)
    {
        if (!is_int($alpha) || $alpha < 0 || $alpha > 100) {
            throw new InvalidArgumentException(sprintf(
                'Alpha must be an integer between 0 and 100, %s given', $alpha
            ));
        }

        $this->alpha = $alpha;
    }

    /**
     * Internal
     *
     * Performs checks for color validity (hex or array of array(R, G, B))
     *
     * @param string|array $color
     *
     * @throws InvalidArgumentException
     */
    private function setColor($color)
    {
        if (!is_string($color) && !is_array($color) && !is_int($color)) {
            throw new InvalidArgumentException(sprintf(
                'Color must be specified as a hexadecimal string, array '.
                'or integer, %s given', gettype($color)
            ));
        }
        if (is_array($color) && count($color) !== 3) {
            throw new InvalidArgumentException(
                'Color argument if array, must look like array(R, G, B), '.
                'where R, G, B are the integer values between 0 and 255 for '.
                'red, green and blue color indexes accordingly'
            );
        }

        if (is_string($color)) {
            $color = ltrim($color, '#');

            if (strlen($color) !== 3 && strlen($color) !== 6) {
                throw new InvalidArgumentException(sprintf(
                    'Color must be a hex value in regular (6 characters) or '.
                    'short (3 characters) notation, "%s" given', $color
                ));
            }

            if (strlen($color) === 3) {
                $color = $color[0].$color[0].
                         $color[1].$color[1].
                         $color[2].$color[2];
            }

            $color = array_map('hexdec', str_split($color, 2));
        }

        if (is_int($color)) {
            $color = array(
                255 & ($color >> 16),
                255 & ($color >> 8),
                255 & $color
            );
        }

        list($this->r, $this->g, $this->b) = array_values($color);
    }

    /**
     * Returns hex representation of the color
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('#%02x%02x%02x', $this->r, $this->g, $this->b);
    }

    /**
     * Checks if the current color is opaque
     *
     * @return Boolean
     */
    public function isOpaque()
    {
        return 0 === $this->alpha;
    }
}

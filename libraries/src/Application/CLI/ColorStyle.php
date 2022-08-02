<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application\CLI;

/**
 * Class defining ANSI-color styles for command line output
 *
 * @since       4.0.0
 * @deprecated  5.0  Use the `joomla/console` package instead
 */
final class ColorStyle implements \Stringable
{
    /**
     * Known colors
     *
     * @since  4.0.0
     */
    private static array $knownColors = [
        'black'   => 0,
        'red'     => 1,
        'green'   => 2,
        'yellow'  => 3,
        'blue'    => 4,
        'magenta' => 5,
        'cyan'    => 6,
        'white'   => 7,
    ];

    /**
     * Known styles
     *
     * @since  4.0.0
     */
    private static array $knownOptions = [
        'bold'       => 1,
        'underscore' => 4,
        'blink'      => 5,
        'reverse'    => 7,
    ];

    /**
     * Foreground base value
     *
     * @since  4.0.0
     */
    private static int $fgBase = 30;

    /**
     * Background base value
     *
     * @since  4.0.0
     */
    private static int $bgBase = 40;

    /**
     * Foreground color
     *
     * @since  4.0.0
     */
    private int $fgColor = 0;

    /**
     * Background color
     *
     * @since  4.0.0
     */
    private int $bgColor = 0;

    /**
     * Array of style options
     *
     * @since  4.0.0
     */
    private array $options = [];

    /**
     * Constructor
     *
     * @param   string  $fg       Foreground color.
     * @param   string  $bg       Background color.
     * @param   array   $options  Style options.
     *
     * @since   4.0.0
     * @throws  \InvalidArgumentException
     */
    public function __construct(string $fg = '', string $bg = '', array $options = [])
    {
        if ($fg) {
            if (\array_key_exists($fg, static::$knownColors) == false) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Invalid foreground color "%1$s" [%2$s]',
                        $fg,
                        implode(', ', $this->getKnownColors())
                    )
                );
            }

            $this->fgColor = static::$fgBase + static::$knownColors[$fg];
        }

        if ($bg) {
            if (\array_key_exists($bg, static::$knownColors) == false) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Invalid background color "%1$s" [%2$s]',
                        $bg,
                        implode(', ', $this->getKnownColors())
                    )
                );
            }

            $this->bgColor = static::$bgBase + static::$knownColors[$bg];
        }

        foreach ($options as $option) {
            if (\array_key_exists($option, static::$knownOptions) == false) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Invalid option "%1$s" [%2$s]',
                        $option,
                        implode(', ', $this->getKnownOptions())
                    )
                );
            }

            $this->options[] = $option;
        }
    }

    /**
     * Convert to a string.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function __toString(): string
    {
        return $this->getStyle();
    }

    /**
     * Create a color style from a parameter string.
     *
     * Example: fg=red;bg=blue;options=bold,blink
     *
     * @param   string  $string  The parameter string.
     *
     * @return  $this
     *
     * @since   4.0.0
     * @throws  \RuntimeException
     */
    public static function fromString(string $string): self
    {
        $fg      = '';
        $bg      = '';
        $options = [];

        $parts = explode(';', $string);

        foreach ($parts as $part) {
            $subParts = explode('=', $part);

            if (\count($subParts) < 2) {
                continue;
            }

            switch ($subParts[0]) {
                case 'fg':
                    $fg = $subParts[1];

                    break;

                case 'bg':
                    $bg = $subParts[1];

                    break;

                case 'options':
                    $options = explode(',', $subParts[1]);

                    break;

                default:
                    throw new \RuntimeException('Invalid option: ' . $subParts[0]);
            }
        }

        return new self($fg, $bg, $options);
    }

    /**
     * Get the translated color code.
     *
     *
     * @since   4.0.0
     */
    public function getStyle(): string
    {
        $values = [];

        if ($this->fgColor) {
            $values[] = $this->fgColor;
        }

        if ($this->bgColor) {
            $values[] = $this->bgColor;
        }

        foreach ($this->options as $option) {
            $values[] = static::$knownOptions[$option];
        }

        return implode(';', $values);
    }

    /**
     * Get the known colors.
     *
     * @return  string[]
     *
     * @since   4.0.0
     */
    public function getKnownColors(): array
    {
        return array_keys(static::$knownColors);
    }

    /**
     * Get the known options.
     *
     * @return  string[]
     *
     * @since   4.0.0
     */
    public function getKnownOptions(): array
    {
        return array_keys(static::$knownOptions);
    }
}

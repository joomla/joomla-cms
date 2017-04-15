<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Cli;

/**
 * Class defining ANSI-color styles for command line output
 *
 * @since  1.0
 */
final class ColorStyle
{
	/**
	 * Known colors
	 *
	 * @var    array
	 * @since  1.0
	 */
	private static $knownColors = [
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
	 * @var    array
	 * @since  1.0
	 */
	private static $knownOptions = [
		'bold'       => 1,
		'underscore' => 4,
		'blink'      => 5,
		'reverse'    => 7,
	];

	/**
	 * Foreground base value
	 *
	 * @var    integer
	 * @since  1.0
	 */
	private static $fgBase = 30;

	/**
	 * Background base value
	 *
	 * @var    integer
	 * @since  1.0
	 */
	private static $bgBase = 40;

	/**
	 * Foreground color
	 *
	 * @var    integer
	 * @since  1.0
	 */
	private $fgColor = 0;

	/**
	 * Background color
	 *
	 * @var    integer
	 * @since  1.0
	 */
	private $bgColor = 0;

	/**
	 * Array of style options
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $options = [];

	/**
	 * Constructor
	 *
	 * @param   string  $fg       Foreground color.
	 * @param   string  $bg       Background color.
	 * @param   array   $options  Style options.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function __construct($fg = '', $bg = '', array $options = [])
	{
		if ($fg)
		{
			if (false == array_key_exists($fg, static::$knownColors))
			{
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

		if ($bg)
		{
			if (false == array_key_exists($bg, static::$knownColors))
			{
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

		foreach ($options as $option)
		{
			if (false == array_key_exists($option, static::$knownOptions))
			{
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
	 * @since   1.0
	 */
	public function __toString()
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
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public static function fromString($string)
	{
		$fg      = '';
		$bg      = '';
		$options = [];

		$parts = explode(';', $string);

		foreach ($parts as $part)
		{
			$subParts = explode('=', $part);

			if (count($subParts) < 2)
			{
				continue;
			}

			switch ($subParts[0])
			{
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
					break;
			}
		}

		return new self($fg, $bg, $options);
	}

	/**
	 * Get the translated color code.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getStyle()
	{
		$values = [];

		if ($this->fgColor)
		{
			$values[] = $this->fgColor;
		}

		if ($this->bgColor)
		{
			$values[] = $this->bgColor;
		}

		foreach ($this->options as $option)
		{
			$values[] = static::$knownOptions[$option];
		}

		return implode(';', $values);
	}

	/**
	 * Get the known colors.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getKnownColors()
	{
		return array_keys(static::$knownColors);
	}

	/**
	 * Get the known options.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getKnownOptions()
	{
		return array_keys(static::$knownOptions);
	}
}

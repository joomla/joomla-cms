<?php
/**
 * Part of the Joomla Framework Image Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Image\Filter;

use InvalidArgumentException;
use Joomla\Image\ImageFilter;

/**
 * Image Filter class fill background with color;
 *
 * @since       1.0
 * @deprecated  The joomla/image package is deprecated
 */
class Backgroundfill extends ImageFilter
{
	/**
	 * Method to apply a background color to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *                           color  Background matte color
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function execute(array $options = array())
	{
		// Validate that the color value exists and is an integer.
		if (!isset($options['color']))
		{
			throw new InvalidArgumentException('No color value was given. Expected string or array.');
		}

		$colorCode = (!empty($options['color'])) ? $options['color'] : null;

		// Get resource dimensions
		$width  = imagesx($this->handle);
		$height = imagesy($this->handle);

		// Sanitize color
		$rgba = $this->sanitizeColor($colorCode);

		// Enforce alpha on source image
		if (imageistruecolor($this->handle))
		{
			imagealphablending($this->handle, false);
			imagesavealpha($this->handle, true);
		}

		// Create background
		$bg = imagecreatetruecolor($width, $height);
		imagesavealpha($bg, empty($rgba['alpha']));

		// Allocate background color.
		$color = imagecolorallocatealpha($bg, $rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha']);

		// Fill background
		imagefill($bg, 0, 0, $color);

		// Apply image over background
		imagecopy($bg, $this->handle, 0, 0, 0, 0, $width, $height);

		// Move flattened result onto curent handle.
		// If handle was palette-based, it'll stay like that.
		imagecopy($this->handle, $bg, 0, 0, 0, 0, $width, $height);

		// Free up memory
		imagedestroy($bg);
	}

	/**
	 * Method to sanitize color values
	 * and/or convert to an array
	 *
	 * @param   mixed  $input  Associative array of colors and alpha,
	 *                         or hex RGBA string when alpha FF is opaque.
	 *                         Defaults to black and opaque alpha
	 *
	 * @return  array  Associative array of red, green, blue and alpha
	 *
	 * @since   1.0
	 *
	 * @note    '#FF0000FF' returns an array with alpha of 0 (opaque)
	 */
	protected function sanitizeColor($input)
	{
		// Construct default values
		$colors = array('red' => 0, 'green' => 0, 'blue' => 0, 'alpha' => 0);

		// Make sure all values are in
		if (\is_array($input))
		{
			$colors = array_merge($colors, $input);
		}
		elseif (\is_string($input))
		{
			// Convert RGBA 6-9 char string
			$hex = ltrim($input, '#');

			$hexValues = array(
				'red'   => substr($hex, 0, 2),
				'green' => substr($hex, 2, 2),
				'blue'  => substr($hex, 4, 2),
				'alpha' => substr($hex, 6, 2),
			);

			$colors = array_map('hexdec', $hexValues);

			// Convert Alpha to 0..127 when provided
			if (\strlen($hex) > 6)
			{
				$colors['alpha'] = floor((255 - $colors['alpha']) / 2);
			}
		}
		else
		{
			// Cannot sanitize such type
			return $colors;
		}

		// Make sure each value is within the allowed range
		foreach ($colors as &$value)
		{
			$value = max(0, min(255, (int) $value));
		}

		$colors['alpha'] = min(127, $colors['alpha']);

		return $colors;
	}
}

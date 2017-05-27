<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Image Filter class fill background with color;
 *
 * @package     Joomla.Platform
 * @subpackage  Image
 * @since       3.4
 */
class JImageFilterBackgroundfill extends JImageFilter
{
	/**
	 * Method to apply a background color to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *                           color  Background matte color
	 *
	 * @return  void
	 *
	 * @since   3.4
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
		$width = imagesX($this->handle);
		$height = imagesY($this->handle);

		// Sanitize color
		$rgba = $this->sanitizeColor($colorCode);

		// Enforce alpha on source image
		if (imageIsTrueColor($this->handle))
		{
			imageAlphaBlending($this->handle, false);
			imageSaveAlpha($this->handle, true);
		}

		// Create background
		$bg = imageCreateTruecolor($width, $height);
		imageSaveAlpha($bg, empty($rgba['alpha']));

		// Allocate background color.
		$color = imageColorAllocateAlpha($bg, $rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha']);

		// Fill background
		imageFill($bg, 0, 0, $color);

		// Apply image over background
		imageCopy($bg, $this->handle, 0, 0, 0, 0, $width, $height);

		// Move flattened result onto curent handle.
		// If handle was palette-based, it'll stay like that.
		imageCopy($this->handle, $bg, 0, 0, 0, 0, $width, $height);

		// Free up memory
		imageDestroy($bg);

		return;
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
	 * @since   3.4
	 *
	 * @note    '#FF0000FF' returns an array with alpha of 0 (opaque)
	 */
	protected function sanitizeColor($input)
	{
		// Construct default values
		$colors = array('red' => 0, 'green' => 0, 'blue' => 0, 'alpha' => 0);

		// Make sure all values are in
		if (is_array($input))
		{
			$colors = array_merge($colors, $input);
		}
		// Convert RGBA 6-9 char string
		elseif (is_string($input))
		{
			$hex = ltrim($input, '#');

			$hexValues = array(
				'red' => substr($hex, 0, 2),
				'green' => substr($hex, 2, 2),
				'blue' => substr($hex, 4, 2),
				'alpha' => substr($hex, 6, 2),
			);

			$colors = array_map('hexdec', $hexValues);

			// Convert Alpha to 0..127 when provided
			if (strlen($hex) > 6)
			{
				$colors['alpha'] = floor((255 - $colors['alpha']) / 2);
			}
		}
		// Cannot sanitize such type
		else
		{
			return $colors;
		}

		// Make sure each value is within the allowed range
		foreach ($colors as &$value)
		{
			$value = max(0, min(255, (float) $value));
		}

		$colors['alpha'] = min(127, $colors['alpha']);

		return $colors;
	}
}

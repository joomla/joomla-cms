<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Template\Atum\Administrator\Service\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use OzdemirBurak\Iris\Color\Hex;
use OzdemirBurak\Iris\Color\Hsl;
use OzdemirBurak\Iris\Color\Rgb;
use OzdemirBurak\Iris\Color\Hsv;
use OzdemirBurak\Iris\Color\Hsla;
use Joomla\Registry\Registry;

/**
 * Template Atum HTML Helper
 *
 * @since  4.0.0
 */
class Atum
{
	/**
	 * Calculates the different template colors and set the CSS variables
	 *
	 * @param   array   $params    Template parameters.
	 *
	 * @return void
	 *
	 * @since  4.0.0
	 */
	public static function rootcolors(Registry $params): void
	{
		$monochrome = (bool) $params->get('monochrome');
		$root       = [];

		if ($params->exists('hue'))
		{
			$root = static::bgdarkcalc($params->get('hue'), $monochrome);
		}

		$bgLight = $params->get('bg-light');

		if (static::isHex($bgLight))
		{
			try
			{
				$bgLight = new Hex($bgLight);

				$root[] = '--atum-bg-light: ' . ($monochrome ? $bgLight->grayscale() : $bgLight) . ';';
				$root[] = '--toolbar-bg: ' . ($monochrome ? $bgLight->grayscale()->lighten(5) : $bgLight->lighten(5)) . ';';
			}
			catch (Exception $ex)
			{
				// Just ignore exceptions
			}
		}

		$textDark = $params->get('text-dark');

		if (static::isHex($textDark))
		{
			try
			{
				$textDark = new Hex($textDark);
				$root[]   = '--atum-text-dark: ' . (($monochrome) ? $textDark->grayscale() : $textDark) . ';';
			}
			catch (Exception $ex)
			{
				// Just ignore exceptions
			}
		}

		$textLight = $params->get('text-light');

		if (static::isHex($textLight))
		{
			try
			{
				$textLight = new Hex($textLight);
				$root[]    = '--atum-text-light: ' . (($monochrome) ? $textLight->grayscale() : $textLight) . ';';
			}
			catch (Exception $ex)
			{
				// Just ignore exceptions
			}
		}

		$linkColor = $params->get('link-color');

		if (static::isHex($linkColor))
		{
			try
			{
				$linkColor = new Hex($linkColor);

				$root[] = '--atum-link-color: ' . (($monochrome) ? $linkColor->grayscale() : $linkColor) . ';';

				$root[] = '--atum-link-hover-color: ' . (($monochrome) ? $linkColor->grayscale()->darken(20) : $linkColor->darken(20)) . ';';
			}
			catch (Exception $ex)
			{
				// Just ignore exceptions
			}
		}

		$specialColor = $params->get('special-color');

		if (static::isHex($specialColor))
		{
			try
			{
				$specialcolor = new Hex($specialColor);
				$root[]       = '--atum-special-color: ' . (($monochrome) ? $specialcolor->grayscale() : $specialcolor) . ';';
			}
			catch (Exception $ex)
			{
				// Just ignore exceptions
			}
		}

		if (count($root))
		{
			Factory::getDocument()->addStyleDeclaration(':root {' . implode($root) . '}');
		}
	}

	/**
	 * Calculates the different template colors
	 *
	 * @param   string   $hue           Template parameter color.
	 * @param   int      $monochrome    Template parameter monochrome.
	 *
	 * @return  array  An array of calculated color values and css variables
	 *
	 * @since  4.0.0
	 */
	protected static function bgdarkcalc($hue, $monochrome = false): array
	{
		$multiplier = $monochrome ? 0 : 1;

		if (strpos($hue, 'hsl(') !== false)
		{
			try
			{
				$hue = new Hsl($hue);
				$hue = $hue->hue();
			}
			catch (Exception $ex)
			{
				// Just ignore exceptions
			}
		}
		elseif (static::isHex($hue))
		{
			try
			{
				$hue = new Hex($hue);
				$hue = $hue->toHsl()->hue();
			}
			catch (Exception $ex)
			{
				// Just ignore exceptions
			}
		}
		elseif (strpos($hue, 'hsla(') !== false)
		{
			try
			{
				$hue = new Hsla($hue);
				$hue = $hue->toHsl()->hue();
			}
			catch (Exception $ex)
			{
				// Just ignore exceptions
			}
		}
		elseif (strpos($hue, 'hsv(') !== false)
		{
			try
			{
				$hue = new Hsv($hue);
				$hue = $hue->toHsl()->hue();
			}
			catch (Exception $ex)
			{
				// Just ignore exceptions
			}
		}
		elseif (strpos($hue, 'rgb(') !== false)
		{
			try
			{
				$hue = new Rgb($hue);
				$hue = $hue->toHsl()->hue();
			}
			catch (Exception $ex)
			{
				// Just ignore exceptions
			}
		}

		$hue  = min(359, max(0, (int) $hue));
		$root = [];

		try
		{
			$bgcolor = new Hsl('hsl(' . $hue . ', ' . (61 * $multiplier) . ', 26)');

			$root[] = '--atum-bg-dark: ' . (new Hsl('hsl(' . $hue . ', ' . (61 * $multiplier) . ', 26)'))->toHex() . ';';
			$root[] = '--atum-contrast: ' . (new Hsl('hsl(' . $hue . ',' . (61 * $multiplier) . ', 26)'))->spin(-40)->lighten(18)->toHex() . ';';
			$root[] = '--atum-bg-dark-0: ' . (clone $bgcolor)->desaturate(86 * $multiplier)->lighten(71.4)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-5: ' . (clone $bgcolor)->desaturate(85 * $multiplier)->lighten(65.1)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-10: ' . (clone $bgcolor)->desaturate(80 * $multiplier)->lighten(59.4)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-20: ' . (clone $bgcolor)->desaturate(75 * $multiplier)->lighten(47.3)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-30: ' . (clone $bgcolor)->desaturate(55 * $multiplier)->lighten(34.3)->spin(-5)->toHex() . ';';
			$root[] = '--atum-bg-dark-40: ' . (clone $bgcolor)->desaturate(40 * $multiplier)->lighten(21.4)->spin(-3)->toHex() . ';';
			$root[] = '--atum-bg-dark-50: ' . (clone $bgcolor)->desaturate(15 * $multiplier)->lighten(10)->spin(-1)->toHex() . ';';
			$root[] = '--atum-bg-dark-70: ' . (clone $bgcolor)->lighten(-6)->spin(4)->toHex() . ';';
			$root[] = '--atum-bg-dark-80: ' . (clone $bgcolor)->lighten(-11.5)->spin(7)->toHex() . ';';
			$root[] = '--atum-bg-dark-90: ' . (clone $bgcolor)->desaturate(-1 * $multiplier)->lighten(-17)->spin(10)->toHex() . ';';
		}
		catch (Exception $ex)
		{
			// Just ignore exceptions
		}

		return $root;
	}

	/**
	 * Determinates if the given string is a color hex value
	 *
	 * @param   string  $hex  The string to test
	 *
	 * @return boolean  True when color hex value otherwise false
	 */
	protected static function isHex($hex): bool
	{
		if (substr($hex, 0, 1) !== '#')
		{
			return false;
		}

		$value = ltrim($hex, '#');

		return (strlen($value) == 6 || strlen($value) == 3) && ctype_xdigit($value);
	}
}

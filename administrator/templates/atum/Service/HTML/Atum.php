<?php
/**
 * @package     Joomla.Site
 * @subpackage     com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

//namespace Joomla\Component\Content\Administrator\Service\HTML;
namespace Joomla\Template\Atum\Administrator\Service\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use OzdemirBurak\Iris\Color\Hex;
use OzdemirBurak\Iris\Color\Hsl;

/**
 * Content Component HTML Helper
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
	 *
	 * @since  4.0.0
	 */
	public static function rootcolors($params)
	{
		$monochrome = (bool) $params->get('monochrome');

		$root = [];

		if (!is_null($params->get('hue')))
		{
			$root = static::bgdarkcalc($params->get('hue'), $monochrome);
		}

		$bgLight = $params->get('bg-light');

		if (strlen($bgLight) === 7 && substr($bgLight, 0, 1) === '#')
		{
			$bgLight = new Hex($bgLight);

			try
			{
				$root[] = '--atum-bg-light: ' . ($monochrome ? $bgLight->grayscale() : $bgLight) . ';';
				$root[] = '--toolbar-bg: ' . ($monochrome ? $bgLight->grayscale()->lighten(5) : $bgLight->lighten(5)) . ';';
			}
			catch (Exception $ex)
			{

			}
		}

		if ($params->get('text-dark'))
		{
			$textdark = new Hex('#' . trim($params->get('text-dark'), '#'));
			$root[] = '--atum-text-dark: ' . (($monochrome) ? $textdark->grayscale() : $textdark) . ';';
		}

		if ($params->get('text-light'))
		{
			$textlight = new Hex('#' . trim($params->get('text-light'), '#'));
			$root[] = '--atum-text-light: ' . (($monochrome) ? $textlight->grayscale() : $textlight) . ';';
		}

		if ($params->get('link-color'))
		{
			$linkcolor = trim($params->get('link-color'), '#');

			$linkcolor2 = new Hex('#' . $linkcolor);
			$root[] = '--atum-link-color: ' . (($monochrome) ? $linkcolor2->grayscale() : $linkcolor2) . ';';

			try
			{
				$color = new Hex($linkcolor);
				$root[] = '--atum-link-hover-color: ' . (($monochrome) ? $bgLight->grayscale()->darken(20) : $bgLight->darken(20)) . ';';
			}
			catch (Exception $ex)
			{

			}
		}

		if ($params->get('special-color'))
		{
			$specialcolor = new Hex('#' . trim($params->get('special-color'), '#'));
			$root[] = '--atum-special-color: ' . (($monochrome) ? $specialcolor->grayscale() : $specialcolor) . ';';
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
	 * @since  4.0.0
	 */
	protected static function bgdarkcalc($hue, $monochrome = false)
	{
		$multiplier = $monochrome ? 0 : 1;
		$hue = min(360, max(0, (int) $hue));

		$root = [];

		$bgcolor = new Hsl("hsl(" . $hue . ", " . (61 * $multiplier) . ", 26)");
		$root[] = '--atum-bg-dark: ' . (new Hsl("hsl(" . $hue . ", " . (61 * $multiplier) . ", 26)"))->toHex() . ';';

		try
		{
			$root[] = '--atum-contrast: ' . (new Hsl("hsl(" . $hue . ", 61, 42)"))->spin(30)->toHex() . ';';
			$root[] = '--atum-bg-dark-0: ' . (clone $bgcolor)->desaturate(86 * $multiplier)->lighten(71.4)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-5: ' . (clone $bgcolor)->desaturate(86 * $multiplier)->lighten(65.1)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-10: ' . (clone $bgcolor)->desaturate(86 * $multiplier)->lighten(59.4)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-20: ' . (clone $bgcolor)->desaturate(76 * $multiplier)->lighten(47.3)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-30: ' . (clone $bgcolor)->desaturate(60 * $multiplier)->lighten(34.3)->spin(-5)->toHex() . ';';
			$root[] = '--atum-bg-dark-40: ' . (clone $bgcolor)->desaturate(41 * $multiplier)->lighten(21.4)->spin(-3)->toHex() . ';';
			$root[] = '--atum-bg-dark-50: ' . (clone $bgcolor)->desaturate(19 * $multiplier)->lighten(10)->spin(-1)->toHex() . ';';
			$root[] = '--atum-bg-dark-70: ' . (clone $bgcolor)->lighten(-6)->spin(4)->toHex() . ';';
			$root[] = '--atum-bg-dark-80: ' . (clone $bgcolor)->lighten(-11.5)->spin(7)->toHex() . ';';
			$root[] = '--atum-bg-dark-90: ' . (clone $bgcolor)->desaturate(1 * $multiplier)->lighten(-17)->spin(10)->toHex() . ';';
		}
		catch (Exception $ex)
		{

		}

		return $root;
	}

	//protected static function isHex($hex)
}

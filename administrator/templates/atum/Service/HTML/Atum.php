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
	 * root_colors()
	 *
	 * @param   array   $params    Template parameters.
	 *
	 *
	 * @since  4.0.0
	 */
	public static function rootcolors($params)
	{
		$monochrome = $params->get('monochrome');

		$root = static::bgdarkcalc($params->get('hue'), $monochrome);

		if ($params->get('bg-light'))
		{
			$lightcolor = trim($params->get('bg-light'), '#');
			list($red, $green, $blue) = str_split($lightcolor, 2);

			$bgLight=new Hex('#' . $lightcolor);

			$root[] = '--atum-bg-light: ' . (($monochrome) ? $bgLight->grayscale() : $bgLight) . ';';

			try
			{
				$root[] = '--toolbar-bg: ' . (($monochrome) ? $bgLight->grayscale()->lighten(5) : $bgLight->lighten(5)) . ';';
			}
			catch (Exception $ex)
			{

			}
		}

		if ($params->get('text-dark'))
		{
			$root[] = '--atum-text-dark: ' . $params->get('text-dark') . ';';
		}

		if ($params->get('text-light'))
		{
			$root[] = '--atum-text-light: ' . $params->get('text-light') . ';';
		}

		if ($params->get('link-color'))
		{
			$linkcolor = trim($params->get('link-color'), '#');
			list($red, $green, $blue) = str_split($linkcolor, 2);
			$root[] = '--atum-link-color: #' . $linkcolor . ';';

			try
			{
				$color = new Hex($linkcolor);
				$root[] = '--atum-link-hover-color: ' . (clone $color)->darken(20) . ';';
			}
			catch (Exception $ex)
			{

			}
		}

		if ($params->get('special-color'))
		{
			$root[] = '--atum-special-color: ' . $params->get('special-color') . ';';
		}

		if ($params->get('contrast-color'))
		{
			$root[] = '--atum-contrast: ' . $params->get('contrast-color') . ';';
		}

		if (count($root))
		{
			Factory::getDocument()->addStyleDeclaration(':root {' . implode($root) . '}');
		}
	}

	/**
	 * bgdarkcalc()
	 *
	 * @param   string   $hue           Template parameter color.
	 * @param   int      $monochrome    Template parameter monochrome.
	 *
	 * @since  4.0.0
	 */
	protected static function bgdarkcalc($hue, $monochrome = false)
	{
		$multiplier = $monochrome ? 0 : 1;
		$hue = min(355, max(0, (int) $hue));
		
		$root = [];

		$bgcolor = new Hsl("hsl(" . $hue . ", " . (61 * $monochrome) . ", 26)");
		$root[] = '--atum-bg-dark: ' . (new Hsl("hsl(" . $hue . ", " . (61 * $monochrome) . ", 26)"))->toHex() . ';';

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
}

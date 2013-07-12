<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_random_image
 *
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 * @since       1.5
 */
class ModImageSliderHelper
{
	public static function getSlides(&$params)
	{
		$slides = array();

		for ($i = 1; $i <= 5; $i++)
		{
			$slides[$i] = new stdClass;
			$slides[$i]->image = $params->get('image' . $i);
			$slides[$i]->link = $params->get('link' . $i);
			$slides[$i]->heading = $params->get('heading' . $i);
			$slides[$i]->description = $params->get('description' . $i);
		}

		return $slides;
	}
}

<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Finder module helper.
 *
 * @package     Joomla.Site
 * @subpackage  mod_finder
 * @since       2.5
 */
class ModFinderHelper
{
	/**
	 * Method to get hidden input fields for a get form so that control variables
	 * are not lost upon form submission.
	 *
	 * @param   string  $route  The route to the page. [optional]
	 *
	 * @return  string  A string of hidden input form fields
	 *
	 * @since   2.5
	 */
	public static function getGetFields($route = null)
	{
		$fields = null;
		$uri = JURI::getInstance(JRoute::_($route));
		$uri->delVar('q');

		// Create hidden input elements for each part of the URI.
		foreach ($uri->getQuery(true) as $n => $v)
		{
			$fields .= '<input type="hidden" name="' . $n . '" value="' . $v . '" />';
		}

		return $fields;
	}
}

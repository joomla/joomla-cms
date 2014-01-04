<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
	 * @param   string   $route      The route to the page. [optional]
	 * @param   integer  $paramItem  The menu item ID. (@since 3.1) [optional]
	 *
	 * @return  string  A string of hidden input form fields
	 *
	 * @since   2.5
	 */
	public static function getGetFields($route = null, $paramItem = 0)
	{
		$fields = null;
		$uri = JUri::getInstance(JRoute::_($route));
		$uri->delVar('q');
		$elements = $uri->getQuery(true);

		// Create hidden input elements for each part of the URI.
		// Add the current menu id if it doesn't have one
		foreach ($elements as $n => $v)
		{
			if ($n == 'Itemid')
			{
				continue;
			}
			$fields .= '<input type="hidden" name="' . $n . '" value="' . $v . '" />';
		}

		/*
		 * Figure out the Itemid value
		 * First, check if the param is set.  If not, fall back to the Itemid from the JInput object
		 */
		$Itemid = $paramItem > 0 ? $paramItem : JFactory::getApplication()->input->getInt('Itemid');
		$fields .= '<input type="hidden" name="Itemid" value="' . $Itemid . '" />';

		return $fields;
	}

	/**
	 * Get Smart Search query object.
	 *
	 * @param   JRegistry  $params  Module parameters.
	 *
	 * @return  FinderIndexerQuery object
	 *
	 * @since   2.5
	 */
	public static function getQuery($params)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$request = $input->request;
		$filter = JFilterInput::getInstance();

		// Get the static taxonomy filters.
		$options = array();
		$options['filter'] = ($request->get('f', 0, 'int') != 0) ? $request->get('f', '', 'int') : $params->get('searchfilter');
		$options['filter'] = $filter->clean($options['filter'], 'int');

		// Get the dynamic taxonomy filters.
		$options['filters'] = $request->get('t', '', 'array');
		$options['filters'] = $filter->clean($options['filters'], 'array');
		JArrayHelper::toInteger($options['filters']);

		// Instantiate a query object.
		$query = new FinderIndexerQuery($options);

		return $query;
	}
}

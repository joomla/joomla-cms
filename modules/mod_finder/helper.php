<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
		// Determine if there is an item id before routing.
		$needId = !JURI::getInstance($route)->getVar('Itemid');

		$fields = array();
		$uri = JURI::getInstance(JRoute::_($route));
		$uri->delVar('q');

		// Create hidden input elements for each part of the URI.
		foreach ($uri->getQuery(true) as $n => $v)
		{
			$fields[] = '<input type="hidden" name="' . $n . '" value="' . $v . '" />';
		}

		// Add a field for Itemid if we need one.
		if ($needId)
		{
			$id = JFactory::getApplication()->input->get('Itemid', '0', 'int');
			$fields[] = '<input type="hidden" name="Itemid" value="' . $id . '" />';
		}

		return implode('', $fields);
	}

	/**
	 * Get Smart Search query object.
	 *
	 * @param   JRegistry object containing module parameters.
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

<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Suggestions JSON controller for Finder.
 *
 * @package     Joomla.Site
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderControllerSuggestions extends JControllerLegacy
{
	/**
	 * Method to find search query suggestions.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function display()
	{
		$return = array();

		$params = JComponentHelper::getParams('com_finder');
		if ($params->get('show_autosuggest', 1))
		{
			// Get the suggestions.
			$model = $this->getModel('Suggestions', 'FinderModel');
			$return = $model->getItems();
		}

		// Check the data.
		if (empty($return))
		{
			$return = array();
		}

		// Use the correct json mime-type
		header('Content-Type: application/json');

		// Send the response.
		echo json_encode($return);
		JFactory::getApplication()->close();
	}
}

<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Suggestions JSON controller for Finder.
 *
 * @package     Joomla.Site
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderControllerSuggestions extends JController
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
		// Get the suggestions.
		$model = $this->getModel('Suggestions', 'FinderModel');
		$return = $model->getItems();

		// Check the data.
		if (empty($return))
		{
			$return = array();
		}

		// Send the response.
		echo json_encode($return);
		JFactory::getApplication()->close();
	}
}

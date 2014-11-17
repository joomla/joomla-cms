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
 * Suggester JSON controller for Finder.
 *
 * @since  3.4
 */
class FinderControllerSuggester extends JControllerLegacy
{
	/**
	 * Method to find search query suggestions.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$return = array();

		$params = JComponentHelper::getParams('com_finder');
		if ($params->get('show_autosuggest', 1))
		{
			// Get the suggestions.
			$model = $this->getModel('Suggester', 'FinderModel');
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
		echo '{ "suggestions": ' . json_encode($return) . ' }';
		JFactory::getApplication()->close();
	}
}

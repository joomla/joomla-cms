<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Suggestions JSON controller for Finder.
 *
 * @since  2.5
 */
class FinderControllerSuggestions extends JControllerLegacy
{
	/**
	 * Method to find search query suggestions. Uses jQuery and autocopleter.js
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function suggest()
	{
		$app = JFactory::getApplication();
		$app->mimeType = 'application/json';

		$suggestions = $this->getSuggestions();

		// Send the response.
		$app->setHeader('Content-Type', $app->mimeType . '; charset=' . $app->charSet);
		$app->sendHeaders();
		echo '{ "suggestions": ' . json_encode($suggestions) . ' }';
		$app->close();
	}

	/**
	 * Method to find search query suggestions. Uses Mootools and autocompleter.js
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 * @deprecated 3.4
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$app = JFactory::getApplication();
		$app->mimeType = 'application/json';

		$suggestions = $this->getSuggestions();

		// Send the response.
		$app->setHeader('Content-Type', $app->mimeType . '; charset=' . $app->charSet);
		$app->sendHeaders();
		echo json_encode($suggestions);
		$app->close();
	}

	/**
	 * Method to retrieve the data from the database
	 *
	 * @return  array  The suggested words
	 *
	 * @since   3.4
	 */
	protected function getSuggestions()
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

		return $return;
	}
}

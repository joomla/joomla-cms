<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Finder\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Suggestions \JSON controller for Finder.
 *
 * @since  2.5
 */
class SuggestionsController extends BaseController
{
	/**
	 * Method to find search query suggestions. Uses jQuery and autocompleter.js
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function suggest()
	{
		$app = $this->app;
		$app->mimeType = 'application/json';

		$suggestions = $this->getSuggestions();

		// Send the response.
		$app->setHeader('Content-Type', $app->mimeType . '; charset=' . $app->charSet);
		$app->sendHeaders();
		echo '{ "suggestions": ' . json_encode($suggestions) . ' }';
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

		$params = ComponentHelper::getParams('com_finder');

		if ($params->get('show_autosuggest', 1))
		{
			// Get the suggestions.
			$model = $this->getModel('Suggestions');
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

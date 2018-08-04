<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Finder\Site\View\Search;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\Opensearch\OpensearchUrl;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\AbstractView;

/**
 * OpenSearch View class for Finder
 *
 * @since  2.5
 */
class OpensearchView extends AbstractView
{
	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		$doc = Factory::getDocument();
		$app = Factory::getApplication();

		$params = ComponentHelper::getParams('com_finder');
		$doc->setShortName($params->get('opensearch_name', $app->get('sitename')));
		$doc->setDescription($params->get('opensearch_description', $app->get('MetaDesc')));

		// Prevent any output when OpenSearch Support is disabled
		if (!$params->get('opensearch', 1))
		{
			return;
		}

		// Add the URL for the search
		$searchUri = 'index.php?option=com_finder&view=search&q={searchTerms}';
		$suggestionsUri = 'index.php?option=com_finder&task=suggestions.opensearchsuggest&format=json&q={searchTerms}';
		$baseUrl = \JUri::getInstance()->toString(array('host', 'port', 'scheme'));
		$active = $app->getMenu()->getActive();

		if ($active->component == 'com_finder')
		{
			$searchUri .= '&Itemid=' . $active->id;
			$suggestionsUri .= '&Itemid=' . $active->id;
		}

		// Add the HTML result view
		$htmlSearch           = new OpenSearchUrl;
		$htmlSearch->template = $baseUrl . \JRoute::_($searchUri, false);
		$doc->addUrl($htmlSearch);

		// Add the RSS result view
		$htmlSearch           = new OpenSearchUrl;
		$htmlSearch->template = $baseUrl . \JRoute::_($searchUri . '&format=feed&type=rss', false);
		$htmlSearch->type     = 'application/rss+xml';
		$doc->addUrl($htmlSearch);

		// Add the Atom result view
		$htmlSearch           = new OpenSearchUrl;
		$htmlSearch->template = $baseUrl . \JRoute::_($searchUri . '&format=feed&type=atom', false);
		$htmlSearch->type     = 'application/atom+xml';
		$doc->addUrl($htmlSearch);

		// Add suggestions URL
		if ($params->get('show_autosuggest', 1))
		{
			$htmlSearch           = new OpenSearchUrl;
			$htmlSearch->template = $baseUrl . \JRoute::_($suggestionsUri, false);
			$htmlSearch->type     = 'application/x-suggestions+json';
			$doc->addUrl($htmlSearch);
		}
	}
}

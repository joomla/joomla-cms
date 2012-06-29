<?php
/**
 * @package		Joomla.Site
 * @subpackage	Weblinks
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.environment.uri');

/**
 * OpenSearch View class for the Search component
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	Search
 * @since 1.7
 */
class SearchViewSearch extends JViewLegacy
{
	function display($tpl = null)
	{
		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();

		$params = JComponentHelper::getParams('com_search');
		$doc->setShortName($params->get('opensearch_name', $app->getCfg('sitename')));
		$doc->setDescription($params->get('opensearch_description', $app->getCfg('MetaDesc')));

		// Add the URL for the search
		$searchUri = JURI::base().'index.php?option=com_search&searchword={searchTerms}';

		// Find the menu item for the search
		$menu	= $app->getMenu();
		$items	= $menu->getItems('link', 'index.php?option=com_search&view=search');
		if (isset($items[0])) {
			$searchUri .= '&Itemid='.$items[0]->id;
		}

		$htmlSearch = new JOpenSearchUrl;
		$htmlSearch->template = JRoute::_($searchUri);
		$doc->addUrl($htmlSearch);
	}
}

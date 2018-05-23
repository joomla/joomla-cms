<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * OpenSearch View class for the Search component
 *
 * @since  1.7
 */
class SearchViewSearch extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  name of the template
	 *
	 * @throws Exception
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();

		$params = JComponentHelper::getParams('com_search');
		$doc->setShortName($params->get('opensearch_name', $app->get('sitename')));
		$doc->setDescription($params->get('opensearch_description', $app->get('MetaDesc')));

		// Add the URL for the search
		$searchUri = JUri::base() . 'index.php?option=com_search&searchword={searchTerms}';

		// Find the menu item for the search
		$menu  = $app->getMenu();
		$items = $menu->getItems('link', 'index.php?option=com_search&view=search');

		if (isset($items[0]))
		{
			$searchUri .= '&Itemid=' . $items[0]->id;
		}

		$htmlSearch           = new JOpenSearchUrl;
		$htmlSearch->template = JRoute::_($searchUri);
		$doc->addUrl($htmlSearch);
	}
}

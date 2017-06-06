<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('FinderHelperRoute', JPATH_SITE . '/components/com_finder/helpers/route.php');
JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

// Include the helper.
JLoader::register('ModFinderHelper', __DIR__ . '/helper.php');

if (!defined('FINDER_PATH_INDEXER'))
{
	define('FINDER_PATH_INDEXER', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer');
}

JLoader::register('FinderIndexerQuery', FINDER_PATH_INDEXER . '/query.php');

// Check for OpenSearch
if ($params->get('opensearch', 1))
{
/*
This code intentionally commented
	$doc = JFactory::getDocument();
	$app = JFactory::getApplication();

	$ostitle = $params->get('opensearch_title', JText::_('MOD_FINDER_SEARCHBUTTON_TEXT') . ' ' . $app->get('sitename'));
	$doc->addHeadLink(
						JUri::getInstance()->toString(array('scheme', 'host', 'port')) . JRoute::_('&option=com_finder&format=opensearch'),
						'search', 'rel', array('title' => $ostitle, 'type' => 'application/opensearchdescription+xml')
					);
*/
}

// Initialize module parameters.
$params->def('field_size', 20);

// Get the route.
$route = FinderHelperRoute::getSearchRoute($params->get('searchfilter', null));

// Load component language file.
FinderHelperLanguage::loadComponentLanguage();

// Load plugin language files.
FinderHelperLanguage::loadPluginLanguage();

// Get Smart Search query object.
$query = ModFinderHelper::getQuery($params);

require JModuleHelper::getLayoutPath('mod_finder', $params->get('layout', 'default'));

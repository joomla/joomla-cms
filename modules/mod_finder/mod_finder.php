<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('FinderHelperRoute', JPATH_SITE . '/components/com_finder/helpers/route.php');
JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

// Include the helper.
require_once __DIR__ . '/helper.php';

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

	$ostitle = $params->get('opensearch_title', JText::_('MOD_FINDER_SEARCHBUTTON_TEXT') . ' ' . $app->getCfg('sitename'));
	$doc->addHeadLink(
						JURI::getInstance()->toString(array('scheme', 'host', 'port')) . JRoute::_('&option=com_finder&format=opensearch'),
						'search', 'rel', array('title' => $ostitle, 'type' => 'application/opensearchdescription+xml')
					);
*/

}

// Initialize module parameters.
$params->def('field_size', 20);

// Get the route.
$route = FinderHelperRoute::getSearchRoute($params->get('f', null));

// Load component language file.
FinderHelperLanguage::loadComponentLanguage();

// Load plug-in language files.
FinderHelperLanguage::loadPluginLanguage();

// Get Smart Search query object.
$query = modFinderHelper::getQuery($params);

require JModuleHelper::getLayoutPath('mod_finder', $params->get('layout', 'default'));

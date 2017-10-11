<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Finder\Site\Helper\FinderHelper;
use Joomla\CMS\Factory;

JLoader::register('FinderHelperRoute', JPATH_SITE . '/components/com_finder/helpers/route.php');
JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

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
	$ostitle = $params->get('opensearch_title', JText::_('MOD_FINDER_SEARCHBUTTON_TEXT') . ' ' . Factory::getApplication()->get('sitename'));
	Factory::getDocument()->addHeadLink(
		JUri::getInstance()->toString(array('scheme', 'host', 'port')) . JRoute::_('&option=com_finder&format=opensearch'),
		'search', 'rel', array('title' => $ostitle, 'type' => 'application/opensearchdescription+xml')
	);
*/
}

// Get the route.
$route = FinderHelperRoute::getSearchRoute($params->get('searchfilter', null));

// Load component language file.
FinderHelperLanguage::loadComponentLanguage();

// Load plugin language files.
FinderHelperLanguage::loadPluginLanguage();

// Get Smart Search query object.
$query = FinderHelper::getQuery($params);

require ModuleHelper::getLayoutPath('mod_finder', $params->get('layout', 'default'));

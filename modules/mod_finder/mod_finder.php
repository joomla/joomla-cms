<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Finder\Site\Helper\FinderHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

JLoader::register('FinderHelperRoute', JPATH_SITE . '/components/com_finder/helpers/route.php');
JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

if (!defined('FINDER_PATH_INDEXER'))
{
	define('FINDER_PATH_INDEXER', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer');
}

JLoader::register('FinderIndexerQuery', FINDER_PATH_INDEXER . '/query.php');

$cparams = ComponentHelper::getParams('com_finder');

// Check for OpenSearch
if ($params->get('opensearch', $cparams->get('opensearch', 1)))
{
	$defaultTitle = Text::_('MOD_FINDER_OPENSEARCH_NAME') . ' ' . Factory::getApplication()->get('sitename');
	$ostitle = $params->get('opensearch_name', $cparams->get('opensearch_name', $defaultTitle));
	Factory::getDocument()->addHeadLink(
		Uri::getInstance()->toString(array('scheme', 'host', 'port')) . Route::_('index.php?option=com_finder&view=search&format=opensearch'),
		'search', 'rel', array('title' => $ostitle, 'type' => 'application/opensearchdescription+xml')
	);
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

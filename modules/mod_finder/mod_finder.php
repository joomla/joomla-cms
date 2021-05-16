<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;
use Joomla\Component\Finder\Site\Helper\RouteHelper;
use Joomla\Module\Finder\Site\Helper\FinderHelper;

$cparams = ComponentHelper::getParams('com_finder');

// Check for OpenSearch
if ($params->get('opensearch', $cparams->get('opensearch', 1)))
{
	$defaultTitle = Text::_('MOD_FINDER_OPENSEARCH_NAME') . ' ' . $app->get('sitename');
	$ostitle = $params->get('opensearch_name', $cparams->get('opensearch_name', $defaultTitle));
	$app->getDocument()->addHeadLink(
		Uri::getInstance()->toString(array('scheme', 'host', 'port')) . Route::_('index.php?option=com_finder&view=search&format=opensearch'),
		'search', 'rel', array('title' => $ostitle, 'type' => 'application/opensearchdescription+xml')
	);
}

// Get the route.
$route = RouteHelper::getSearchRoute($params->get('searchfilter', null));

// Load component language file.
LanguageHelper::loadComponentLanguage();

// Load plugin language files.
LanguageHelper::loadPluginLanguage();

// Get Smart Search query object.
$query = FinderHelper::getQuery($params);

require ModuleHelper::getLayoutPath('mod_finder', $params->get('layout', 'default'));

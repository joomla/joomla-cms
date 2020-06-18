<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_breadcrumbs
 *
 * @copyright   © 2006 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the breadcrumbs functions only once
JLoader::register('ModBreadCrumbsHelper', __DIR__ . '/helper.php');

// Get the breadcrumbs
$list  = ModBreadCrumbsHelper::getList($params);
$count = count($list);

// Set the default separator
$separator = ModBreadCrumbsHelper::setSeparator($params->get('separator'));
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require JModuleHelper::getLayoutPath('mod_breadcrumbs', $params->get('layout', 'default'));

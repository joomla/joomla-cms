<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_stats_admin
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the mod_stats functions only once
JLoader::register('ModStatsHelper', __DIR__ . '/helper.php');

$serverinfo      = $params->get('serverinfo');
$siteinfo        = $params->get('siteinfo');
$list            = ModStatsHelper::getStats($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_COMPAT, 'UTF-8');

require JModuleHelper::getLayoutPath('mod_stats_admin', $params->get('layout', 'default'));

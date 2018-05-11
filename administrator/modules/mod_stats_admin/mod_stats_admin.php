<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_stats_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\StatsAdmin\Administrator\Helper\StatsAdminHelper;

$serverinfo      = $params->get('serverinfo');
$siteinfo        = $params->get('siteinfo');
$list            = StatsAdminHelper::getStats($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require ModuleHelper::getLayoutPath('mod_stats_admin', $params->get('layout', 'default'));

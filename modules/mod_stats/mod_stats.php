<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_stats
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Stats\Site\Helper\StatsHelper;

$serverinfo = $params->get('serverinfo', 0);
$siteinfo   = $params->get('siteinfo', 0);
$list       = StatsHelper::getList($params);

require ModuleHelper::getLayoutPath('mod_stats', $params->get('layout', 'default'));

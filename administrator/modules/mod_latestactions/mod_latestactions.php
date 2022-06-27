<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latestactions
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\LatestActions\Administrator\Helper\LatestActionsHelper;

// Only super user can view this data
if (!$app->getIdentity()->authorise('core.admin')) {
    return;
}

$list = LatestActionsHelper::getList($params);

if ($params->get('automatic_title', 0)) {
    $module->title = LatestActionsHelper::getTitle($params);
}

require ModuleHelper::getLayoutPath('mod_latestactions', $params->get('layout', 'default'));

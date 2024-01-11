<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Module\Logged\Administrator\Helper\LoggedHelper;

if ($params->get('automatic_title', 0)) {
    $module->title = LoggedHelper::getTitle($params);
}

// Check if session metadata tracking is enabled
if ($app->get('session_metadata', true)) {
    $users = LoggedHelper::getList($params, $app, Factory::getContainer()->get(DatabaseInterface::class));

    require ModuleHelper::getLayoutPath('mod_logged', $params->get('layout', 'default'));
} else {
    require ModuleHelper::getLayoutPath('mod_logged', 'disabled');
}

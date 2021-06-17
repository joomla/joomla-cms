<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_users_latest
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\UsersLatest\Site\Helper\UsersLatestHelper;

$shownumber = $params->get('shownumber', 5);
$names      = UsersLatestHelper::getUsers($params);

require ModuleHelper::getLayoutPath('mod_users_latest', $params->get('layout', 'default'));

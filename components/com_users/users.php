<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/helpers/route.php';

$task = JFactory::getApplication()->input->get('task', 'display');

$controller = JControllerLegacy::getInstance('Users');
$controller->execute($task);
$controller->redirect();

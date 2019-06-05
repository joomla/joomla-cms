<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

// No access check.

$controller = JControllerLegacy::getInstance('Admin');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

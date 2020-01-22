<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// No access check.

$controller = JControllerLegacy::getInstance('Cpanel');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

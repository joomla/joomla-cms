<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Use own exception handler
require_once JPATH_COMPONENT.'/helpers/error.php';
set_exception_handler(array('AjaxError', 'display'));


if(!JFactory::getApplication()->input->get('format'))
{
	throw new InvalidArgumentException('No format given. Please specify response format.', 500);
}

$controller = JControllerLegacy::getInstance('Ajax');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();


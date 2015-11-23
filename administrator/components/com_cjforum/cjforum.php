<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
JHtml::_('behavior.tabstate');

if (! JFactory::getUser()->authorise('core.manage', 'com_cjforum'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

////////////////////////////////////////// CjLib Includes ///////////////////////////////////////////////
require_once JPATH_ROOT.'/components/com_cjlib/framework.php';
require_once JPATH_ROOT.'/components/com_cjlib/framework/api.php';
CJLib::import('corejoomla.framework.core');

require_once JPATH_COMPONENT_SITE.'/lib/api.php';
require_once JPATH_COMPONENT_SITE.'/helpers/constants.php';
////////////////////////////////////////// CjLib Includes ///////////////////////////////////////////////

JLoader::register('CjForumHelper', __DIR__ . '/helpers/cjforum.php');
JFactory::getLanguage()->load('com_cjforum', JPATH_ROOT);

$controller = JControllerLegacy::getInstance('CjForum');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

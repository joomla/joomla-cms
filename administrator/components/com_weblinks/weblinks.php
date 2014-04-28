<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

if (!JFactory::getUser()->authorise('core.manage', 'com_weblinks'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

require_once JPATH_COMPONENT_ADMINISTRATOR.'/autoloader.php';

$app = JFactory::getApplication();
$input = $app->input;
$config = array('default_view' => 'weblink');

$controller	= new WeblinksController($input, $app, $config);

$controller->execute();

$controller->redirect();


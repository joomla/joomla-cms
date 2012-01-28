<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_banners')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

/*
/* @var JXMLElement $xxa
$xxa = simplexml_load_string('<gaga />', 'JXMLElement');

$xxa->addChild('susu', 'gaaa');
$cc = $xxa->addChild('blee');
$cc->addAttribute('huhu', 'duhu');
var_dump($xxa);
$a = '<a>sss</a>';
echo '<pre>';
echo htmlentities($xxa->asFormattedXML(false, '  '));
echo '</pre>';
return;
*/
$options = new JObject;
$options->set('aha', 'beha');

$exporter = JDatabaseExporter::getInstance(JFactory::getDbo(), $options)->from('#__users');

var_dump((string)$exporter);
// Include dependancies
jimport('joomla.application.component.controller');

// Execute the task.
$controller	= JController::getInstance('Banners');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

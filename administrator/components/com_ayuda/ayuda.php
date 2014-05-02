<?php
/**
 * @package		Jokte.Administrator
 * @subpackage	com_ayuda
 * @copyleft 	Copyleft 2012 - 2014 Comunidad Juuntos & Jokte.org
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_ayuda')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$controller	= JControllerLegacy::getInstance('Ayuda');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

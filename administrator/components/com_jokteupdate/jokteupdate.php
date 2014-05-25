<?php
/**
 * @package		Jokte.Administrator
 * @subpackage	com_jokteupdate
 * @copyright	Copyleft (C) 2012 - 2014 Comunidad Juuntos. Ningún derecho reservado.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.2.0
 */

defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_jokteupdate')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$controller	= JControllerLegacy::getInstance('Jokteupdate');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

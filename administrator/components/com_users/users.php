<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   © 2005 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

if (!JFactory::getUser()->authorise('core.manage', 'com_users'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

JLoader::register('UsersHelper', __DIR__ . '/helpers/users.php');

$controller = JControllerLegacy::getInstance('Users');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

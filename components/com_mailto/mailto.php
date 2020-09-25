<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('MailtoHelper', JPATH_COMPONENT . '/helpers/mailto.php');

$controller = JControllerLegacy::getInstance('Mailto');
$controller->registerDefaultTask('mailto');
$controller->execute(JFactory::getApplication()->input->get('task'));

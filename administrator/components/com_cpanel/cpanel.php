<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   © 2006 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// No access check.

$controller = JControllerLegacy::getInstance('Cpanel');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

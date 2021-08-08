<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$controller = JControllerLegacy::getInstance('Wrapper');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

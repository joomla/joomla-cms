<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   © 2006 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$controller = JControllerLegacy::getInstance('Banners');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

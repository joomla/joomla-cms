<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('FinderHelperRoute', JPATH_COMPONENT . '/helpers/route.php');

$controller = JControllerLegacy::getInstance('Finder');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

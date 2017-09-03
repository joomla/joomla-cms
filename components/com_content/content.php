<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
JLoader::register('ContentHelperQuery', JPATH_SITE . '/components/com_content/helpers/query.php');
JLoader::register('ContentHelperAssociation', JPATH_SITE . '/components/com_content/helpers/association.php');

$controller = JControllerLegacy::getInstance('Content');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

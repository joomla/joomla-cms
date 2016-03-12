<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$controller   = JControllerLegacy::getInstance('Modules');
$language     = JFactory::getLanguage();
$extension    = 'com_modules';
$base_dir     = JPATH_ADMINISTRATOR;
$language_tag = $language->getTag();

$language->load('', $base_dir, $language_tag, true);
$language->load($extension, $base_dir, $language_tag, true);

$controller->execute(JFactory::getApplication()->input->get('task'));

$controller->redirect();

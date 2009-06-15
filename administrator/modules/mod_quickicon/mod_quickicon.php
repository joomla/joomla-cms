<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

require_once dirname(__FILE__).DS.'helper.php';

$buttons = array(
	array(
		'link' => JRoute::_('index.php?option=com_content&task=add'), 
		'image' => 'icon-48-article-add.png', 
		'text' => JText::_('Add New Article')
	),
	array(
		'link' => JRoute::_('index.php?option=com_content'), 
		'image' => 'icon-48-article.png', 
		'text' => JText::_('Article Manager')
	),
	array(
		'link' => JRoute::_('index.php?option=com_frontpage'), 
		'image' => 'icon-48-frontpage.png', 
		'text' => JText::_('Frontpage Manager')
	),
	array(
		'link' => JRoute::_('index.php?option=com_categories&extension=com_content'), 
		'image' => 'icon-48-category.png', 
		'text' => JText::_('Category Manager')
	),
	array(
		'link' => JRoute::_('index.php?option=com_media'), 
		'image' => 'icon-48-media.png', 
		'text' => JText::_('Media Manager')
	),
	array(
		'link' => JRoute::_('index.php?option=com_menus'), 
		'image' => 'icon-48-menumgr.png', 
		'text' => JText::_('Menu Manager'), 
		'access' => 'core.menus.manage'
	),
	array(
		'link' => JRoute::_('index.php?option=com_languages'), 
		'image' => 'icon-48-language.png', 
		'text' => JText::_('Language Manager'), 
		'access' => 'core.languages.manage'
	),
	array(
		'link' => JRoute::_('index.php?option=com_users'), 
		'image' => 'icon-48-user.png', 
		'text' => JText::_('User Manager'), 
		'access' => 'core.users.manage'
	),
	array(
		'link' => JRoute::_('index.php?option=com_config'),
		'image' => 'icon-48-config.png', 
		'text' => JText::_('Global Configuration'), 
		'access' => 'core.config.manage'
	)
);

require JModuleHelper::getLayoutPath('mod_quickicon');
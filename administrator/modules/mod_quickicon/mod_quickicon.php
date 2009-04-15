<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once dirname(__FILE__).DS.'helper.php';

$buttons = array(
	array('index.php?option=com_content&task=add', 'icon-48-article-add.png', JText::_('Add New Article'), 'com_content.manage'),
	array('index.php?option=com_content', 'icon-48-article.png', JText::_('Article Manager'), 'com_content.manage'),
	array('index.php?option=com_content&controller=frontpage', 'icon-48-frontpage.png', JText::_('Frontpage Manager'), 'com_content.manage'),
	array('index.php?option=com_categories&extension=com_content', 'icon-48-category.png', JText::_('Category Manager'), 'core.categories.manage'),
	array('index.php?option=com_media', 'icon-48-media.png', JText::_('Media Manager'), 'core.media.manage'),
	array('index.php?option=com_menus', 'icon-48-menumgr.png', JText::_('Menu Manager'), 'core.menus.manage'),
	array('index.php?option=com_languages', 'icon-48-language.png', JText::_('Language Manager'), 'core.languages.manage'),
	array('index.php?option=com_members', 'icon-48-user.png', JText::_('Member Manager'), 'core.members.manage'),
	array('index.php?option=com_config', 'icon-48-config.png', JText::_('Global Configuration'), 'core.config.manage')
);

require JModuleHelper::getLayoutPath('mod_quickicon');
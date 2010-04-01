<?php
/**
 * @version		$Id:mod_menu.php 2463 2006-02-18 06:05:38Z webImagery $
 * @package		Joomla.Administrator
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

//
// Site SubMenu
//
$menu->addChild(
	new JMenuNode(JText::_('JSITE'), '#'), true
);
$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_CONTROL_PANEL'), 'index.php', 'class:cpanel')
);

$menu->addSeparator();

if ($user->authorise('core.admin')) {
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_CONFIGURATION'), 'index.php?option=com_config', 'class:config'));
	$menu->addSeparator();
}

$chm = $user->authorise('core.manage', 'com_checkin');
$cam = $user->authorise('core.manage', 'com_cache');

if ($chm || $cam )
{
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_SITE_MAINTENANCE'), '#', 'class:maintenance'), true
	);

	if ($chm)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_GLOBAL_CHECKIN'), 'index.php?option=com_checkin', 'class:checkin'));
		$menu->addSeparator();
	}
	if ($cam)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_CLEAR_CACHE'), 'index.php?option=com_cache', 'class:clear'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_PURGE_EXPIRED_CACHE'), 'index.php?option=com_cache&view=purge', 'class:purge'));
	}

	$menu->getParent();
}

$menu->addSeparator();
	$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_SYSTEM_INFORMATION'), 'index.php?option=com_admin&view=sysinfo', 'class:info')
);
$menu->addSeparator();

$menu->addChild(new JMenuNode(JText::_('MOD_MENU_LOGOUT'), 'index.php?option=com_login&task=logout', 'class:logout'));

$menu->getParent();


//
// Users Submenu
//
if ($user->authorise('core.manage', 'com_users'))
{
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_USERS'), '#'), true
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_USER_MANAGER'), 'index.php?option=com_users&view=users', 'class:user')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_GROUPS'), 'index.php?option=com_users&view=groups', 'class:groups')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_LEVELS'), 'index.php?option=com_users&view=levels', 'class:levels')
	);

	$menu->addSeparator();
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_USER'), 'index.php?option=com_users&task=user.add', 'class:newuser')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_GROUP'), 'index.php?option=com_users&task=group.add', 'class:newgroup')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_LEVEL'), 'index.php?option=com_users&task=level.add', 'class:newlevel')
	);

	$menu->addSeparator();

	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_MASS_MAIL_USERS'), 'index.php?option=com_users&view=mail', 'class:massmail')
	);

	$menu->getParent();
}

//
// Menus Submenu
//
if ($user->authorise('core.manage', 'com_menus'))
{
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_MENUS'), '#'), true
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER'), 'index.php?option=com_menus&view=menus', 'class:menumgr')
	);
	$menu->addSeparator();

	// Menu Types
	foreach (ModMenuHelper::getMenus() as $menuType)
	{
		$menu->addChild(
			new JMenuNode(
				$menuType->title.($menuType->home ? ' <span>'.JHTML::_('image','menu/icon-16-default.png', NULL, NULL, true).'</span>' : ''),
				'index.php?option=com_menus&view=items&menutype='.$menuType->menutype, 'class:menu'
			)
		);
	}
	$menu->getParent();
}

//
// Content Submenu
//
if ($user->authorise('core.manage', 'com_content'))
{
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_CONTENT'), '#'), true
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_ARTICLE_MANAGER'), 'index.php?option=com_content', 'class:article')
	);

	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_CATEGORY_MANAGER'), 'index.php?option=com_categories&extension=com_content', 'class:category')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_FEATURED'), 'index.php?option=com_content&view=featured', 'class:featured')
	);
	$menu->addSeparator();
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_ARTICLE'), 'index.php?option=com_content&task=article.add', 'class:newarticle')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'), 'index.php?option=com_categories&task=category.add&extension=com_content', 'class:newcategory')
	);

	$menu->addSeparator();
	if ($user->authorise('core.manage', 'com_media')) {
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_MEDIA_MANAGER'), 'index.php?option=com_media', 'class:media'));
	}

	$menu->getParent();
}

//
// Components Submenu
//
$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COMPONENTS'), '#'), true);

// Get the authorised components and sub-menus.
$components = ModMenuHelper::getComponents( true );

foreach ($components as &$component)
{
	$text = $lang->hasKey($component->title) ? JText::_($component->title) : $component->alias;

	if (!empty($component->submenu))
	{
		// This component has a db driven submenu.
		$menu->addChild(new JMenuNode($text, $component->link, $component->img), true);
		foreach ($component->submenu as $sub)
		{
			$text = $lang->hasKey($sub->title) ? JText::_($sub->title) : $sub->alias;
			$menu->addChild(new JMenuNode($text, $sub->link, $sub->img));
		}
		$menu->getParent();
	}
	else {
		$menu->addChild(new JMenuNode($text, $component->link, $component->img));
	}
}
$menu->getParent();

//
// Extensions Submenu
//
$im = $user->authorise('core.manage', 'com_installer');
$mm = $user->authorise('core.manage', 'com_modules');
$pm = $user->authorise('core.manage', 'com_plugins');
$tm = $user->authorise('core.manage', 'com_templates');
$lm = $user->authorise('core.manage', 'com_languages');

if ($im || $mm || $pm || $tm || $lm)
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSIONS'), '#'), true);

	if ($im)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSION_MANAGER'), 'index.php?option=com_installer', 'class:install'));
		$menu->addSeparator();
	}
	if ($mm) {
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_MODULE_MANAGER'), 'index.php?option=com_modules', 'class:module'));
	}
	if ($pm) {
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_PLUGIN_MANAGER'), 'index.php?option=com_plugins', 'class:plugin'));
	}
	if ($tm) {
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_TEMPLATE_MANAGER'), 'index.php?option=com_templates', 'class:themes'));
	}
	if ($lm) {
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_LANGUAGE_MANAGER'), 'index.php?option=com_languages', 'class:language'));
	}
	$menu->getParent();
}

//
// Help Submenu
//
$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_HELP'), '#'), true
);
$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_HELP_JOOMLA'), 'index.php?option=com_admin&view=help', 'class:help')
);
$menu->addSeparator();

$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_FORUM'), 'http://forum.joomla.org', 'class:help-forum', false, '_blank')
);
$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_HELP_DOCUMENTATION'), 'http://docs.joomla.org', 'class:help-docs', false, '_blank')
);
$menu->addSeparator();
$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_HELP_EXTENSIONS'), 'http://extensions.joomla.org', 'class:help-jed', false, '_blank')
);
$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_HELP_TRANSLATIONS'), 'http://community.joomla.org/translations.html', 'class:help-trans', false, '_blank')
);
$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_HELP_RESOURCES'), 'http://resources.joomla.org', 'class:help-jrd', false, '_blank')
);
$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_HELP_COMMUNITY'), 'http://community.joomla.org', 'class:help-community', false, '_blank')
);
$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_HELP_SECURITY'), 'http://developer.joomla.org/security.html', 'class:help-security', false, '_blank')
);
$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_HELP_DEVELOPER'), 'http://developer.joomla.org', 'class:help-dev', false, '_blank')
);
$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_HELP_SHOP'), 'http://shop.joomla.org', 'class:help-shop', false, '_blank')
);
$menu->getParent();

<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var $menu JAdminCSSMenu */

$shownew = (boolean) $params->get('shownew', 1);
$showhelp = $params->get('showhelp', 1);
$user = JFactory::getUser();
$lang = JFactory::getLanguage();

/**
 * System submenu
 */
$systemMenu = new JMenuNode(JText::_('MOD_MENU_SYSTEM'), '#');
$systemMenu->addChild(new JMenuNode(JText::_('MOD_MENU_CONTROL_PANEL'), 'index.php'));
$systemMenu->addSeparator();

if ($user->authorise('core.admin'))
{
	$systemMenu->addChild(new JMenuNode(JText::_('MOD_MENU_CONFIGURATION'), 'index.php?option=com_config'));
	$systemMenu->addSeparator();
}

$chm = $user->authorise('core.admin', 'com_checkin');
$cam = $user->authorise('core.manage', 'com_cache');

if ($chm || $cam )
{
	if ($chm)
	{
		$systemMenu->addChild(new JMenuNode(JText::_('MOD_MENU_GLOBAL_CHECKIN'), 'index.php?option=com_checkin'));
		$systemMenu->addSeparator();
	}

	if ($cam)
	{
		$systemMenu->addChild(new JMenuNode(JText::_('MOD_MENU_CLEAR_CACHE'), 'index.php?option=com_cache'));
		$systemMenu->addChild(new JMenuNode(JText::_('MOD_MENU_PURGE_EXPIRED_CACHE'), 'index.php?option=com_cache&view=purge'));
	}
}

$systemMenu->addSeparator();

if ($user->authorise('core.admin'))
{
	$systemMenu->addChild(new JMenuNode(JText::_('MOD_MENU_SYSTEM_INFORMATION'), 'index.php?option=com_admin&view=sysinfo'));
}

$menu->addChild($systemMenu);

/**
 * Users submenu
 */
if ($user->authorise('core.manage', 'com_users'))
{
	$userMenu = new JMenuNode(JText::_('MOD_MENU_COM_USERS_USERS'), '#');

	$createUser = $shownew && $user->authorise('core.create', 'com_users');
	$createGrp  = $user->authorise('core.admin', 'com_users');

	$userManager = new JMenuNode(JText::_('MOD_MENU_COM_USERS_USER_MANAGER'), 'index.php?option=com_users&view=users');

	if ($createUser)
	{
		$userManager->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_USER'), 'index.php?option=com_users&task=user.add'));
	}

	$userMenu->addChild($userManager, $createUser);

	if ($createGrp)
	{
		$userGroups = new JMenuNode(JText::_('MOD_MENU_COM_USERS_GROUPS'), 'index.php?option=com_users&view=groups');

		if ($createUser)
		{
			$userGroups->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_GROUP'), 'index.php?option=com_users&task=group.add'));
		}

		$userMenu->addChild($userGroups, $createUser);

		$userLevels = new JMenuNode(JText::_('MOD_MENU_COM_USERS_LEVELS'), 'index.php?option=com_users&view=levels');

		if ($createUser)
		{
			$userLevels->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_LEVEL'), 'index.php?option=com_users&task=level.add'));
		}

		$userMenu->addChild($userLevels, $createUser);
	}

	$userMenu->addSeparator();

	// Submenu: User Notes
	$userNotes = new JMenuNode(JText::_('MOD_MENU_COM_USERS_NOTES'), 'index.php?option=com_users&view=notes');

	if ($createUser)
	{
		$userNotes->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_NOTE'), 'index.php?option=com_users&task=note.add'));
	}

	$userMenu->addChild($userNotes, $createUser);

	// Submenu: User Note categories
	$userNoteCategories = new JMenuNode(JText::_('MOD_MENU_COM_USERS_NOTE_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_users');

	if ($createUser)
	{
		$userNoteCategories->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'), 'index.php?option=com_categories&task=category.add&extension=com_users.notes'));
	}

	$userMenu->addChild($userNoteCategories, $createUser);

	$userMenu->addSeparator();
	$userMenu->addChild(new JMenuNode(JText::_('MOD_MENU_MASS_MAIL_USERS'), 'index.php?option=com_users&view=mail'));
}

$menu->addChild($userMenu);

/**
 * Menus submenu
 */
if ($user->authorise('core.manage', 'com_menus'))
{
	$menusMenu = new JMenuNode(JText::_('MOD_MENU_MENUS'), '#');

	$createMenu = $shownew && $user->authorise('core.create', 'com_menus');

	// Submenu: Menu manager
	$menuManager = new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER'), 'index.php?option=com_menus&view=menus', 'class:menumgr');

	if ($createMenu)
	{
		$menuManager->addChild(new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER_NEW_MENU'), 'index.php?option=com_menus&view=menu&layout=edit'));
	}

	$menusMenu->addChild($menuManager, $createMenu);

	$menusMenu->addSeparator();

	// Menu Types
	foreach (ModMenuHelper::getMenus() as $menuType)
	{
		$alt = '*' . $menuType->sef . '*';

		if ($menuType->home == 0)
		{
			$titleicon = '';
		}
		elseif ($menuType->home == 1 && $menuType->language == '*')
		{
			$titleicon = ' <i class="icon-home"></i>';
		}
		elseif ($menuType->home > 1)
		{
			$titleicon = ' <span>' . JHtml::_('image', 'mod_languages/icon-16-language.png', $menuType->home, array('title' => JText::_('MOD_MENU_HOME_MULTIPLE')), true) . '</span>';
		}
		else
		{
			$image = JHtml::_('image', 'mod_languages/' . $menuType->image . '.gif', null, null, true, true);

			if (!$image)
			{
				$titleicon = ' <span>' . JHtml::_('image', 'mod_languages/icon-16-language.png', $alt, array('title' => $menuType->title_native), true) . '</span>';
			}
			else
			{
				$titleicon = ' <span>' . JHtml::_('image', 'mod_languages/' . $menuType->image . '.gif', $alt, array('title' => $menuType->title_native), true) . '</span>';
			}
		}

		$dinamicMenu = new JMenuNode($menuType->title, 'index.php?option=com_menus&view=items&menutype=' . $menuType->menutype, '', null, null, $titleicon);

		if ($createMenu)
		{
			$dinamicMenu->addChild(new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER_NEW_MENU_ITEM'), 'index.php?option=com_menus&view=item&layout=edit&menutype=' . $menuType->menutype));
		}

		$menusMenu->addChild($dinamicMenu, $createMenu);
	}

	$menu->addChild($menusMenu, true);
}

/**
 * Content submenu
 */
if ($user->authorise('core.manage', 'com_content'))
{
	$contentMenu = new JMenuNode(JText::_('MOD_MENU_COM_CONTENT'), '#');

	$createContent = $shownew && $user->authorise('core.create', 'com_content');
	$articleManager = new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_ARTICLE_MANAGER'), 'index.php?option=com_content');

	if ($createContent)
	{
		$articleManager->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_ARTICLE'), 'index.php?option=com_content&task=article.add'));
	}

	$contentMenu->addChild($articleManager, $createContent);

	$categoryManager = new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_CATEGORY_MANAGER'), 'index.php?option=com_categories&extension=com_content');

	if ($createContent)
	{
		$categoryManager->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'), 'index.php?option=com_categories&task=category.add&extension=com_content'));
	}

	$contentMenu->addChild($categoryManager, $createContent);

	$contentMenu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_FEATURED'), 'index.php?option=com_content&view=featured'));
	$contentMenu->addSeparator();

	if ($user->authorise('core.manage', 'com_media'))
	{
		$contentMenu->addChild(new JMenuNode(JText::_('MOD_MENU_MEDIA_MANAGER'), 'index.php?option=com_media', 'class:media'));
	}

	$menu->addChild($contentMenu, true);
}


/**
 * Components Submenu
 */

// Get the authorised components and sub-menus.
$components = ModMenuHelper::getComponents(true);

// Check if there are any components, otherwise, don't render the menu
if ($components)
{
	$componentsMenu = new JMenuNode(JText::_('MOD_MENU_COMPONENTS'), '#');

	foreach ($components as $component)
	{
		$componentMenu = new JMenuNode($component->text, $component->link, $component->img);

		if (!empty($component->submenu))
		{
			// This component has a db driven submenu.
			foreach ($component->submenu as $sub)
			{
				$componentMenu->addChild(new JMenuNode($sub->text, $sub->link, $sub->img));
			}

			$componentsMenu->addChild($componentMenu, true);
		}
	}

	$menu->addChild($componentsMenu, true);
}

/**
 * Extensions Submenu
 */
$im = $user->authorise('core.manage', 'com_installer');
$mm = $user->authorise('core.manage', 'com_modules');
$pm = $user->authorise('core.manage', 'com_plugins');
$tm = $user->authorise('core.manage', 'com_templates');
$lm = $user->authorise('core.manage', 'com_languages');

if ($im || $mm || $pm || $tm || $lm)
{
	$extensionsMenu = new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSIONS'), '#');

	if ($im)
	{
		$extensionsMenu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSION_MANAGER'), 'index.php?option=com_installer', 'class:install'));
		$extensionsMenu->addSeparator();
	}

	if ($mm)
	{
		$extensionsMenu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_MODULE_MANAGER'), 'index.php?option=com_modules', 'class:module'));
	}

	if ($pm)
	{
		$extensionsMenu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_PLUGIN_MANAGER'), 'index.php?option=com_plugins', 'class:plugin'));
	}

	if ($tm)
	{
		$extensionsMenu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_TEMPLATE_MANAGER'), 'index.php?option=com_templates', 'class:themes'));
	}

	if ($lm)
	{
		$extensionsMenu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_LANGUAGE_MANAGER'), 'index.php?option=com_languages', 'class:language'));
	}

	$menu->addChild($extensionsMenu, true);
}

/**
 * Help Submenu
 */
if ($showhelp == 1)
{
	$helpMenu = new JMenuNode(JText::_('MOD_MENU_HELP'), '#');

	$helpMenu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_JOOMLA'), 'index.php?option=com_admin&view=help', 'class:help')
	);
	$helpMenu->addSeparator();

	$helpMenu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_FORUM'), 'http://forum.joomla.org', 'class:help-forum', false, '_blank')
	);

	if ($forum_url = $params->get('forum_url'))
	{
		$helpMenu->addChild(
			new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_CUSTOM_FORUM'), $forum_url, 'class:help-forum', false, '_blank')
		);
	}

	$debug = $lang->setDebug(false);

	if ($lang->hasKey('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE') && JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE') != '')
	{
		$forum_url = 'http://forum.joomla.org/viewforum.php?f=' . (int) JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE');
		$lang->setDebug($debug);
		$helpMenu->addChild(
			new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM'), $forum_url, 'class:help-forum', false, '_blank')
		);
	}

	$lang->setDebug($debug);
	$helpMenu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_DOCUMENTATION'), 'http://docs.joomla.org', 'class:help-docs', false, '_blank')
	);
	$helpMenu->addSeparator();

	$helpMenu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_EXTENSIONS'), 'http://extensions.joomla.org', 'class:help-jed', false, '_blank')
	);
	$helpMenu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_TRANSLATIONS'), 'http://community.joomla.org/translations.html', 'class:help-trans', false, '_blank')
	);
	$helpMenu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_RESOURCES'), 'http://resources.joomla.org', 'class:help-jrd', false, '_blank')
	);
	$helpMenu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_COMMUNITY'), 'http://community.joomla.org', 'class:help-community', false, '_blank')
	);
	$helpMenu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_SECURITY'), 'http://developer.joomla.org/security.html', 'class:help-security', false, '_blank')
	);
	$helpMenu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_DEVELOPER'), 'http://developer.joomla.org', 'class:help-dev', false, '_blank')
	);
	$helpMenu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_SHOP'), 'http://shop.joomla.org', 'class:help-shop', false, '_blank')
	);

	$menu->addChild($helpMenu, true);
}

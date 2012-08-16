<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var $menu JAdminCSSMenu */

$shownew = (boolean) $params->get('shownew', 1);
$showhelp = $params->get('showhelp', 1);
$user = JFactory::getUser();
$lang = JFactory::getLanguage();

//
// Site SubMenu
//
$menu->addChild(
	new JMenuNode(JText::_('MOD_MENU_CONTROL_PANEL'), 'index.php', 'class:cpanel'), true
);

$menu->getParent();

//
// Users Submenu
//
if ($user->authorise('core.manage', 'com_users'))
{
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_USERS'), '#'), true
	);
	$createUser = $shownew && $user->authorise('core.create', 'com_users');
	$createGrp = $user->authorise('core.admin', 'com_users');

	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_USER_MANAGER'), 'index.php?option=com_users&view=users', 'class:user')
	);

	if ($createGrp)
	{
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_USERS_GROUPS'), 'index.php?option=com_users&view=groups', 'class:groups')
		);

		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_USERS_LEVELS'), 'index.php?option=com_users&view=levels', 'class:levels')
		);
	}

	$menu->addSeparator();
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_NOTES'), 'index.php?option=com_users&view=notes', 'class:user-note')
	);

	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_NOTE_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_users.notes', 'class:category')
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
		$alt = '*' .$menuType->sef. '*';
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
			$titleicon = ' <span>'.JHtml::_('image', 'mod_languages/icon-16-language.png', $menuType->home, array('title' => JText::_('MOD_MENU_HOME_MULTIPLE')), true).'</span>';
		}
		else
		{
			$image = JHtml::_('image', 'mod_languages/'.$menuType->image.'.gif', null, null, true, true);
			if (!$image)
			{
				$titleicon = ' <span>'.JHtml::_('image', 'mod_languages/icon-16-language.png', $alt, array('title' => $menuType->title_native), true).'</span>';
			}
			else
			{
				$titleicon = ' <span>' . JHtml::_('image', 'mod_languages/' . $menuType->image . '.gif', $alt, array('title' => $menuType->title_native), true) . '</span>';
			}
		}
		$menu->addChild(
			new JMenuNode($menuType->title,	'index.php?option=com_menus&view=items&menutype='.$menuType->menutype, 'class:menu', null, null, $titleicon)
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
	if ($user->authorise('core.manage', 'com_media'))
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_MEDIA_MANAGER'), 'index.php?option=com_media', 'class:media'));
	}
	$menu->getParent();
}

//
// Components Submenu
//

// Get the authorised components and sub-menus.
$components = ModMenuHelper::getComponents(true);

// Check if there are any components, otherwise, don't render the menu
if ($components)
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COMPONENTS'), '#'), true);

	foreach ($components as &$component)
	{
		if (!empty($component->submenu))
		{
			// This component has a db driven submenu.
			$menu->addChild(new JMenuNode($component->text, $component->link, $component->img));

		}
		else
		{
			$menu->addChild(new JMenuNode($component->text, $component->link, $component->img));
		}
	}
	$menu->getParent();
}

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

	if ($mm)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_MODULE_MANAGER'), 'index.php?option=com_modules', 'class:module'));
	}

	if ($pm)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_PLUGIN_MANAGER'), 'index.php?option=com_plugins', 'class:plugin'));
	}

	if ($tm)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_TEMPLATE_MANAGER'), 'index.php?option=com_templates', 'class:themes'));
	}

	if ($lm)
	{
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_LANGUAGE_MANAGER'), 'index.php?option=com_languages', 'class:language'));
	}
	$menu->getParent();
}

//
// Help Submenu
//
if ($showhelp == 1)
{
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP'), '#'), true
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_JOOMLA'), 'index.php?option=com_admin&view=help', 'class:help')
	);
	$menu->addSeparator();

	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_FORUM'), 'http://forum.joomla.org', 'class:help-forum', false, '_blank')
	);
	if ($forum_url = $params->get('forum_url'))
	{
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_CUSTOM_FORUM'), $forum_url, 'class:help-forum', false, '_blank')
		);
	}
	$debug = $lang->setDebug(false);
	if ($lang->hasKey('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE') && JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE') != '')
	{
		$forum_url = 'http://forum.joomla.org/viewforum.php?f=' . (int) JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE');
		$lang->setDebug($debug);
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM'), $forum_url, 'class:help-forum', false, '_blank')
		);
	}
	$lang->setDebug($debug);
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
}

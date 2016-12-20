<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

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
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_USER_MANAGER'), 'index.php?option=com_users&view=users', 'class:user'), $createUser
	);

	if ($createUser)
	{
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_USER'), 'index.php?option=com_users&task=user.add', 'class:newarticle')
		);
		$menu->getParent();
	}

	if ($createGrp)
	{
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_USERS_GROUPS'), 'index.php?option=com_users&view=groups', 'class:groups'), $createUser
		);
		if ($createUser)
		{
			$menu->addChild(
				new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_GROUP'), 'index.php?option=com_users&task=group.add', 'class:newarticle')
			);
			$menu->getParent();
		}

		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_USERS_LEVELS'), 'index.php?option=com_users&view=levels', 'class:levels'), $createUser
		);
		if ($createUser)
		{
			$menu->addChild(
				new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_LEVEL'), 'index.php?option=com_users&task=level.add', 'class:newarticle')
			);
			$menu->getParent();
		}
	}

	$menu->addSeparator();
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_NOTES'), 'index.php?option=com_users&view=notes', 'class:user-note'), $createUser
	);
	if ($createUser)
	{
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_NOTE'), 'index.php?option=com_users&task=note.add', 'class:newarticle')
		);
		$menu->getParent();
	}

	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_USERS_NOTE_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_users', 'class:category'), $createUser
	);
	if ($createUser)
	{
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'), 'index.php?option=com_categories&task=category.add&extension=com_users', 'class:newarticle')
		);
		$menu->getParent();
	}

	if (JFactory::getApplication()->get('massmailoff', 0) != 1)
	{
		$menu->addSeparator();
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_MASS_MAIL_USERS'), 'index.php?option=com_users&view=mail', 'class:massmail')
		);
	}

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
	$createMenu = $shownew && $user->authorise('core.create', 'com_menus');

	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER'), 'index.php?option=com_menus&view=menus', 'class:menumgr'), $createMenu
	);
	if ($createMenu)
	{
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER_NEW_MENU'), 'index.php?option=com_menus&view=menu&layout=edit', 'class:newarticle')
		);
		$menu->getParent();
	}
	$menu->addSeparator();

	// Menu Types
	$menuTypes = ModMenuHelper::getMenus();
	$menuTypes = ArrayHelper::sortObjects($menuTypes, 'title', 1, false);

	foreach ($menuTypes as $menuType)
	{
		if (!$user->authorise('core.manage', 'com_menus.menu.' . (int) $menuType->id))
		{
			continue;
		}

		$alt = '*' .$menuType->sef. '*';
		if ($menuType->home == 0)
		{
			$titleicon = '';
		}
		elseif ($menuType->home == 1 && $menuType->language == '*')
		{
			$titleicon = ' <span class="icon-home"></span>';
		}
		elseif ($menuType->home > 1)
		{
			$titleicon = ' <span>'.JHtml::_('image', 'mod_languages/icon-16-language.png', $menuType->home, array('title' => JText::_('MOD_MENU_HOME_MULTIPLE')), true).'</span>';
		}
		elseif ($menuType->image && JHtml::_('image', 'mod_languages/' . $menuType->image . '.gif', null, null, true, true))
		{
			$titleicon = ' <span>' . JHtml::_('image', 'mod_languages/' . $menuType->image . '.gif', $alt, array('title' => $menuType->title_native), true) . '</span>';
		}
		else
		{
			$titleicon = ' <span class="label" title="' . $menuType->title_native . '">' . $menuType->sef . '</span>';
		}

		$menu->addChild(
			new JMenuNode($menuType->title,	'index.php?option=com_menus&view=items&menutype='.$menuType->menutype, 'class:menu', null, null, $titleicon),
				$user->authorise('core.create', 'com_menus.menu.' . (int) $menuType->id)
		);

		if ($user->authorise('core.create', 'com_menus.menu.' . (int) $menuType->id))
		{
			$menu->addChild(
				new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER_NEW_MENU_ITEM'), 'index.php?option=com_menus&view=item&layout=edit&menutype='.$menuType->menutype, 'class:newarticle')
			);
			$menu->getParent();
		}
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
	$createContent = $shownew && $user->authorise('core.create', 'com_content');
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_ARTICLE_MANAGER'), 'index.php?option=com_content', 'class:article'), $createContent
	);
	if ($createContent)
	{
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_ARTICLE'), 'index.php?option=com_content&task=article.add', 'class:newarticle')
		);
		$menu->getParent();
	}
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_CATEGORY_MANAGER'), 'index.php?option=com_categories&extension=com_content', 'class:category'), $createContent
	);
	if ($createContent)
	{
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'), 'index.php?option=com_categories&task=category.add&extension=com_content', 'class:newarticle')
		);
		$menu->getParent();
	}
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
			$menu->addChild(new JMenuNode($component->text, $component->link, $component->img), true);
			foreach ($component->submenu as $sub)
			{
				$menu->addChild(new JMenuNode($sub->text, $sub->link, $sub->img));
			}
			$menu->getParent();
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
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSION_MANAGER'), 'index.php?option=com_installer', 'class:install'), $im);

		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_INSTALL'), 'index.php?option=com_installer', 'class:install'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_UPDATE'), 'index.php?option=com_installer&view=update', 'class:install'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_MANAGE'), 'index.php?option=com_installer&view=manage', 'class:install'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_DISCOVER'), 'index.php?option=com_installer&view=discover', 'class:install'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_DATABASE'), 'index.php?option=com_installer&view=database', 'class:install'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_WARNINGS'), 'index.php?option=com_installer&view=warnings', 'class:install'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_LANGUAGES'), 'index.php?option=com_installer&view=languages', 'class:install'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_UPDATESITES'), 'index.php?option=com_installer&view=updatesites', 'class:install'));
		$menu->getParent();
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
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_LANGUAGE_MANAGER'), 'index.php?option=com_languages', 'class:language'), $lm);

		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_LANGUAGES_SUBMENU_INSTALLED'), 'index.php?option=com_languages&view=installed', 'class:language'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_LANGUAGES_SUBMENU_CONTENT'), 'index.php?option=com_languages&view=languages', 'class:language'));
		$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_LANGUAGES_SUBMENU_OVERRIDES'), 'index.php?option=com_languages&view=overrides', 'class:language'));
		$menu->getParent();

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
		new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_FORUM'), 'https://forum.joomla.org/', 'class:help-forum', false, '_blank')
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
		$forum_url = 'https://forum.joomla.org/viewforum.php?f=' . (int) JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE');
		$lang->setDebug($debug);
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM'), $forum_url, 'class:help-forum', false, '_blank')
		);
	}
	$lang->setDebug($debug);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_DOCUMENTATION'), 'https://docs.joomla.org', 'class:help-docs', false, '_blank')
	);
	$menu->addSeparator();

	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_EXTENSIONS'), 'https://extensions.joomla.org', 'class:help-jed', false, '_blank')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_TRANSLATIONS'), 'https://community.joomla.org/translations.html', 'class:help-trans', false, '_blank')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_RESOURCES'), 'https://resources.joomla.org', 'class:help-jrd', false, '_blank')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_COMMUNITY'), 'https://community.joomla.org', 'class:help-community', false, '_blank')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_SECURITY'), 'https://developer.joomla.org/security-centre.html', 'class:help-security', false, '_blank')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_DEVELOPER'), 'https://developer.joomla.org', 'class:help-dev', false, '_blank')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_XCHANGE'), 'https://joomla.stackexchange.com', 'class:help-dev', false, '_blank')
	);
	$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_SHOP'), 'https://community.joomla.org/the-joomla-shop.html', 'class:help-shop', false, '_blank')
	);
	$menu->getParent();
}
//
// Admin Settingss Submenu
//
$su = $user->authorise('core.admin');
$cam = $user->authorise('core.manage', 'com_cache');
$cim = $user->authorise('core.manage', 'com_checkin');

	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_SETTINGS'), '#'), true);

	if ($su):
		$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_CONFIGURATION'), 'index.php?option=com_config', 'class:config')
		);
		$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_SYSTEM_INFORMATION'), 'index.php?option=com_admin&view=sysinfo', 'class:info')
	);
	endif;
	if  ($cam):
		$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_CLEAR_CACHE'), 'index.php?option=com_cache', 'class:clear')
		);
		$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_PURGE_EXPIRED_CACHE'), 'index.php?option=com_cache&view=purge', 'class:purge')
		);
	endif;
	if  ($cim):
		$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_GLOBAL_CHECKIN'), 'index.php?option=com_checkin', 'class:checkin')
		);
	endif;
		$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_USER_PROFILE'), 'index.php?option=com_admin&task=profile.edit&id='. $user->id, 'class:profile')
		);
		$menu->addChild(
		new JMenuNode(JText::_('MOD_MENU_LOGOUT'), 'index.php?option=com_login&task=logout&'. JSession::getFormToken() .'=1', 'class:logout')
		);

	$menu->getParent();

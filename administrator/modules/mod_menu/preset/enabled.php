<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/* @var  $this    JAdminCSSMenu */
/* @var  $params  Joomla\Registry\Registry */

$recovery = (boolean) $params->get('recovery', 0);
$shownew  = (boolean) $params->get('shownew', 1);
$showhelp = (boolean) $params->get('showhelp', 1);
$user     = JFactory::getUser();
$lang     = JFactory::getLanguage();

$rootClass = $recovery ? 'class:' : null;
/**
 * Site Submenu
 */
$this->addChild(new JMenuNode(JText::_('MOD_MENU_SYSTEM'), '#', 'class:cog fa-fw'), true);
$this->addChild(new JMenuNode(JText::_('MOD_MENU_CONTROL_PANEL'), 'index.php'));

if ($user->authorise('core.admin'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_CONFIGURATION'), 'index.php?option=com_config'));
}

if ($user->authorise('core.manage', 'com_checkin'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_GLOBAL_CHECKIN'), 'index.php?option=com_checkin'));
}

if ($user->authorise('core.manage', 'com_cache'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_CLEAR_CACHE'), 'index.php?option=com_cache'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_PURGE_EXPIRED_CACHE'), 'index.php?option=com_cache&view=purge'));
}

if ($user->authorise('core.admin'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_SYSTEM_INFORMATION'), 'index.php?option=com_admin&view=sysinfo'));
}

$this->getParent();

/**
 * Users Submenu
 */
if ($user->authorise('core.manage', 'com_users'))
{
	$createUser = $shownew && $user->authorise('core.create', 'com_users');
	$createGrp  = $user->authorise('core.admin', 'com_users');

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_USERS'), '#', 'class:users fa-fw'), true);
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_USER_MANAGER'), 'index.php?option=com_users&view=users'), $createUser);

	if ($createUser)
	{
		$this->getParent();
	}

	if ($createGrp)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_GROUPS'), 'index.php?option=com_users&view=groups'), $createUser);

		if ($createUser)
		{
			$this->getParent();
		}

		$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_LEVELS'), 'index.php?option=com_users&view=levels'), $createUser);

		if ($createUser)
		{
			$this->getParent();
		}
	}

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_NOTES'), 'index.php?option=com_users&view=notes'), $createUser);

	if ($createUser)
	{
		$this->getParent();
	}

	$this->addChild(
		new JMenuNode(
			JText::_('MOD_MENU_COM_USERS_NOTE_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_users'),
		$createUser
	);

	if ($createUser)
	{
		$this->getParent();
	}

	if (JComponentHelper::isEnabled('com_fields') && JComponentHelper::getParams('com_users')->get('custom_fields_enable', '1'))
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_FIELDS'), 'index.php?option=com_fields&context=com_users.user'));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_FIELDS_GROUP'), 'index.php?option=com_fields&view=groups&context=com_users.user'));
	}

	if (JFactory::getApplication()->get('massmailoff') != 1)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_MASS_MAIL_USERS'), 'index.php?option=com_users&view=mail'));
	}

	$this->getParent();
}

/**
 * Menus Submenu
 */
if ($user->authorise('core.manage', 'com_menus'))
{
	$createMenu = $shownew && $user->authorise('core.create', 'com_menus');

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_MENUS'), '#', 'class:list fa-fw'), true);
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER'), 'index.php?option=com_menus&view=menus'), $createMenu);

	if ($createMenu)
	{
		$this->getParent();
	}

	// Menu Types
	$menuTypes = ModMenuHelper::getMenus();
	$menuTypes = JArrayHelper::sortObjects($menuTypes, 'title', 1, false);

	foreach ($menuTypes as $mti => $menuType)
	{
		if (!$user->authorise('core.manage', 'com_menus.menu.' . (int) $menuType->id))
		{
			continue;
		}

		$alt = '*' . $menuType->sef . '*';

		if ($menuType->home == 0)
		{
			$titleicon = '';
		}
		elseif ($menuType->home == 1 && $menuType->language == '*')
		{
			$titleicon = ' <span class="fa fa-home"></span>';
		}
		elseif ($menuType->home > 1)
		{
			$titleicon = ' <span>'
				. JHtml::_('image', 'mod_languages/icon-16-language.png', $menuType->home, array('title' => JText::_('MOD_MENU_HOME_MULTIPLE')), true)
				. '</span>';
		}
		elseif ($menuType->image && JHtml::_('image', 'mod_languages/' . $menuType->image . '.gif', null, null, true, true))
		{
			$titleicon = ' <span>' .
				JHtml::_('image', 'mod_languages/' . $menuType->image . '.gif', $alt, array('title' => $menuType->title_native), true) . '</span>';
		}
		else
		{
			$titleicon = ' <span class="label" title="' . $menuType->title_native . '">' . $menuType->sef . '</span>';
		}

		if (isset($menuTypes[$mti - 1]) && $menuTypes[$mti - 1]->client_id != $menuType->client_id)
		{
			$this->addSeparator();
		}

		$this->addChild(
			new JMenuNode(
				$menuType->title, 'index.php?option=com_menus&view=items&menutype=' . $menuType->menutype, null, null, null, $titleicon
			),
			$user->authorise('core.create', 'com_menus.menu.' . (int) $menuType->id)
		);

		$this->getParent();
	}

	$this->getParent();
}

/**
 * Media Submenu
 */
if ($user->authorise('core.manage', 'com_media'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_MEDIA_MANAGER'), 'index.php?option=com_media', 'class:file-picture-o fa-fw'), true);
	$this->getParent();
}

/**
 * Content Submenu
 */
if ($user->authorise('core.manage', 'com_content'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT'), '#', 'class:file-text-o fa-fw'), true);
	$createContent = $shownew && $user->authorise('core.create', 'com_content');

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_ARTICLE_MANAGER'), 'index.php?option=com_content'), $createContent);

	if ($createContent)
	{
		$this->getParent();
	}

	$this->addChild(
		new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_CATEGORY_MANAGER'), 'index.php?option=com_categories&extension=com_content'), $createContent
	);

	if ($createContent)
	{
		$this->getParent();
	}

	if (JComponentHelper::isEnabled('com_fields') && JComponentHelper::getParams('com_content')->get('custom_fields_enable', '1'))
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_FIELDS'), 'index.php?option=com_fields&context=com_content.article'));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_FIELDS_GROUP'), 'index.php?option=com_fields&view=groups&context=com_content.article'));
	}

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_FEATURED'), 'index.php?option=com_content&view=featured'));

	$this->getParent();
}

/**
 * Components Submenu
 */

// Get the authorised components and sub-menus.
$components = ModMenuHelper::getComponents(true);

// Check if there are any components, otherwise, don't render the menu
if ($components)
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COMPONENTS'), '#', 'class:cube fa-fw'), true);

	foreach ($components as &$component)
	{
		if (!empty($component->submenu))
		{
			// This component has a db driven submenu.
			$this->addChild(new JMenuNode($component->text, $component->link), true);

			foreach ($component->submenu as $sub)
			{
				$this->addChild(new JMenuNode($sub->text, $sub->link));
			}

			$this->getParent();
		}
		else
		{
			$this->addChild(new JMenuNode($component->text, $component->link));
		}
	}

	$this->getParent();
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
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSIONS'), '#', 'class:cubes fa-fw'), true);

	if ($im)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSION_MANAGER'), '#'), $im);

		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_INSTALL'), 'index.php?option=com_installer'));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_UPDATE'), 'index.php?option=com_installer&view=update'));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_MANAGE'), 'index.php?option=com_installer&view=manage'));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_DISCOVER'), 'index.php?option=com_installer&view=discover'));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_DATABASE'), 'index.php?option=com_installer&view=database'));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_WARNINGS'), 'index.php?option=com_installer&view=warnings'));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_LANGUAGES'), 'index.php?option=com_installer&view=languages'));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_UPDATESITES'), 'index.php?option=com_installer&view=updatesites'));
		$this->getParent();
	}

	if ($mm)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_MODULE_MANAGER'), 'index.php?option=com_modules'));
	}

	if ($pm)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_PLUGIN_MANAGER'), 'index.php?option=com_plugins'));
	}

	if ($tm)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_TEMPLATE_MANAGER'), 'index.php?option=com_templates'), $tm);

		$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_TEMPLATES_SUBMENU_STYLES'), 'index.php?option=com_templates&view=styles'));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_TEMPLATES_SUBMENU_TEMPLATES'), 'index.php?option=com_templates&view=templates'));
		$this->getParent();
	}

	if ($lm)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_LANGUAGE_MANAGER'), '#'), $lm);
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_LANGUAGES_SUBMENU_INSTALLED'), 'index.php?option=com_languages&view=installed'));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_LANGUAGES_SUBMENU_CONTENT'), 'index.php?option=com_languages&view=languages'));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_LANGUAGES_SUBMENU_OVERRIDES'), 'index.php?option=com_languages&view=overrides'));
		$this->getParent();
	}

	$this->getParent();
}

/**
 * Help Submenu
 */
if ($showhelp == 1)
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP'), '#', 'class:info-circle fa-fw'), true);
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_JOOMLA'), 'index.php?option=com_admin&view=help'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_FORUM'), 'https://forum.joomla.org', null, false, '_blank'));

	if ($forum_url = $params->get('forum_url'))
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_CUSTOM_FORUM'), $forum_url, null, false, '_blank'));
	}

	$debug = $lang->setDebug(false);

	if ($lang->hasKey('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE') && JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE') != '')
	{
		$forum_url = 'https://forum.joomla.org/viewforum.php?f=' . (int) JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE');
		$lang->setDebug($debug);
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM'), $forum_url, null, false, '_blank'));
	}

	$lang->setDebug($debug);
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_DOCUMENTATION'), 'https://docs.joomla.org', null, false, '_blank'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_EXTENSIONS'), 'https://extensions.joomla.org', null, false, '_blank'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_TRANSLATIONS'), 'https://community.joomla.org/translations.html', null, false, '_blank'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_RESOURCES'), 'http://resources.joomla.org', null, false, '_blank'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_COMMUNITY'), 'https://community.joomla.org', null, false, '_blank'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SECURITY'), 'https://developer.joomla.org/security-centre.html', null, false, '_blank'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_DEVELOPER'), 'https://developer.joomla.org', null, false, '_blank'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_XCHANGE'), 'https://joomla.stackexchange.com', null, false, '_blank'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SHOP'), 'https://community.joomla.org/the-joomla-shop.html', null, false, '_blank'));
	$this->getParent();
}

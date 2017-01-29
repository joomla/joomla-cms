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
$this->addChild(new JMenuNode(JText::_('MOD_MENU_SYSTEM'), '#', $rootClass), true);
$this->addChild(new JMenuNode(JText::_('MOD_MENU_CONTROL_PANEL'), 'index.php', 'class:cpanel'));

if ($user->authorise('core.admin'))
{
	$this->addSeparator();
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_CONFIGURATION'), 'index.php?option=com_config', 'class:config'));
}

if ($user->authorise('core.manage', 'com_checkin'))
{
	$this->addSeparator();
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_GLOBAL_CHECKIN'), 'index.php?option=com_checkin', 'class:checkin'));
}

if ($user->authorise('core.manage', 'com_cache'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_CLEAR_CACHE'), 'index.php?option=com_cache', 'class:clear'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_PURGE_EXPIRED_CACHE'), 'index.php?option=com_cache&view=purge', 'class:purge'));
}

if ($user->authorise('core.admin'))
{
	$this->addSeparator();
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_SYSTEM_INFORMATION'), 'index.php?option=com_admin&view=sysinfo', 'class:info'));
}

$this->getParent();

/**
 * Users Submenu
 */
if ($user->authorise('core.manage', 'com_users'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_USERS'), '#', $rootClass), true);
	$createUser = $shownew && $user->authorise('core.create', 'com_users');
	$createGrp  = $user->authorise('core.admin', 'com_users');

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_USER_MANAGER'), 'index.php?option=com_users&view=users', 'class:user'), $createUser);

	if ($createUser)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_USER'), 'index.php?option=com_users&task=user.add', 'class:newarticle'));
		$this->getParent();
	}

	if ($createGrp)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_GROUPS'), 'index.php?option=com_users&view=groups', 'class:groups'), $createUser);

		if ($createUser)
		{
			$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_GROUP'), 'index.php?option=com_users&task=group.add', 'class:newarticle'));
			$this->getParent();
		}

		$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_LEVELS'), 'index.php?option=com_users&view=levels', 'class:levels'), $createUser);

		if ($createUser)
		{
			$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_LEVEL'), 'index.php?option=com_users&task=level.add', 'class:newarticle'));
			$this->getParent();
		}
	}

	$this->addSeparator();
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_NOTES'), 'index.php?option=com_users&view=notes', 'class:user-note'), $createUser);

	if ($createUser)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_NOTE'), 'index.php?option=com_users&task=note.add', 'class:newarticle'));
		$this->getParent();
	}

	$this->addChild(
		new JMenuNode(
			JText::_('MOD_MENU_COM_USERS_NOTE_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_users', 'class:category'),
		$createUser
	);

	if ($createUser)
	{
		$this->addChild(
			new JMenuNode(
				JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'), 'index.php?option=com_categories&task=category.add&extension=com_users',
				'class:newarticle'
			)
		);
		$this->getParent();
	}

	if (JComponentHelper::isEnabled('com_fields') && JComponentHelper::getParams('com_users')->get('custom_fields_enable', '1'))
	{
		$this->addChild(
				new JMenuNode(
						JText::_('MOD_MENU_FIELDS'), 'index.php?option=com_fields&context=com_users.user', 'class:fields')
				);

		$this->addChild(
				new JMenuNode(
						JText::_('MOD_MENU_FIELDS_GROUP'), 'index.php?option=com_fields&view=groups&context=com_users.user', 'class:category')
				);
	}

	if (JFactory::getApplication()->get('massmailoff') != 1)
	{
		$this->addSeparator();
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_MASS_MAIL_USERS'), 'index.php?option=com_users&view=mail', 'class:massmail'));
	}

	$this->getParent();
}

/**
 * Menus Submenu
 */
if ($user->authorise('core.manage', 'com_menus'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_MENUS'), '#', $rootClass), true);
	$createMenu = $shownew && $user->authorise('core.create', 'com_menus');

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER'), 'index.php?option=com_menus&view=menus', 'class:menumgr'), $createMenu);

	if ($createMenu)
	{
		$this->addChild(
			new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER_NEW_MENU'), 'index.php?option=com_menus&view=menu&layout=edit', 'class:newarticle')
		);
		$this->getParent();
	}

	$this->addSeparator();

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_MENUS_ALL_ITEMS'), 'index.php?option=com_menus&view=items&menutype=', 'class:allmenu'));
	$this->addSeparator();

	// Menu Types
	$menuTypes = ModMenuHelper::getMenus();
	$menuTypes = ArrayHelper::sortObjects($menuTypes, array('client_id', 'title'), 1, false);

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
			$titleicon = ' <span class="icon-home"></span>';
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
				$menuType->title, 'index.php?option=com_menus&view=items&menutype=' . $menuType->menutype, 'class:menu', null, null, $titleicon
			),
			$user->authorise('core.create', 'com_menus.menu.' . (int) $menuType->id)
		);

		if ($user->authorise('core.create', 'com_menus.menu.' . (int) $menuType->id))
		{
			$this->addChild(
				new JMenuNode(
					JText::_('MOD_MENU_MENU_MANAGER_NEW_MENU_ITEM'),
					'index.php?option=com_menus&view=item&layout=edit&menutype=' . $menuType->menutype, 'class:newarticle'
				)
			);

			$this->getParent();
		}
	}

	$this->getParent();
}

/**
 * Content Submenu
 */
if ($user->authorise('core.manage', 'com_content'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT'), '#', $rootClass), true);
	$createContent = $shownew && $user->authorise('core.create', 'com_content');

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_ARTICLE_MANAGER'), 'index.php?option=com_content', 'class:article'), $createContent);

	if ($createContent)
	{
		$this->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_ARTICLE'), 'index.php?option=com_content&task=article.add', 'class:newarticle')
		);
		$this->getParent();
	}

	$this->addChild(
		new JMenuNode(
			JText::_('MOD_MENU_COM_CONTENT_CATEGORY_MANAGER'), 'index.php?option=com_categories&extension=com_content', 'class:category'
		),
		$createContent
	);

	if ($createContent)
	{
		$this->addChild(
			new JMenuNode(
				JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'),
				'index.php?option=com_categories&task=category.add&extension=com_content', 'class:newarticle'
			)
		);
		$this->getParent();
	}

	if (JComponentHelper::isEnabled('com_fields') && JComponentHelper::getParams('com_content')->get('custom_fields_enable', '1'))
	{
		$this->addChild(
			new JMenuNode(
				JText::_('MOD_MENU_FIELDS'), 'index.php?option=com_fields&context=com_content.article', 'class:fields')
		);

		$this->addChild(
			new JMenuNode(
				JText::_('MOD_MENU_FIELDS_GROUP'), 'index.php?option=com_fields&view=groups&context=com_content.article', 'class:category')
		);
	}

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_FEATURED'), 'index.php?option=com_content&view=featured', 'class:featured'));
	$this->->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_SHARED_DRAFTS'), 'index.php?option=com_content&view=shared', 'class:shared'));

	if ($user->authorise('core.manage', 'com_media'))
	{
		$this->addSeparator();
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_MEDIA_MANAGER'), 'index.php?option=com_media', 'class:media'));
	}

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
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COMPONENTS'), '#', $rootClass), true);

	foreach ($components as &$component)
	{
		if (!empty($component->submenu))
		{
			// This component has a db driven submenu.
			$this->addChild(new JMenuNode($component->text, $component->link, $component->img), true);

			foreach ($component->submenu as $sub)
			{
				$this->addChild(new JMenuNode($sub->text, $sub->link, $sub->img));
			}

			$this->getParent();
		}
		else
		{
			$this->addChild(new JMenuNode($component->text, $component->link, $component->img));
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
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSIONS'), '#', $rootClass), true);

	if ($im)
	{
		$cls = 'class:install';

		$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSION_MANAGER'), 'index.php?option=com_installer', $cls), $im);

		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_INSTALL'), 'index.php?option=com_installer', $cls));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_UPDATE'), 'index.php?option=com_installer&view=update', $cls));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_MANAGE'), 'index.php?option=com_installer&view=manage', $cls));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_DISCOVER'), 'index.php?option=com_installer&view=discover', $cls));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_DATABASE'), 'index.php?option=com_installer&view=database', $cls));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_WARNINGS'), 'index.php?option=com_installer&view=warnings', $cls));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_LANGUAGES'), 'index.php?option=com_installer&view=languages', $cls));
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_INSTALLER_SUBMENU_UPDATESITES'), 'index.php?option=com_installer&view=updatesites', $cls));
		$this->getParent();
	}

	if ($im && ($mm || $pm || $tm || $lm))
	{
		$this->addSeparator();
	}

	if ($mm)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_MODULE_MANAGER'), 'index.php?option=com_modules', 'class:module'));
	}

	if ($pm)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_PLUGIN_MANAGER'), 'index.php?option=com_plugins', 'class:plugin'));
	}

	if ($tm)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_TEMPLATE_MANAGER'), 'index.php?option=com_templates', 'class:themes'), $tm);

		$this->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_TEMPLATES_SUBMENU_STYLES'), 'index.php?option=com_templates&view=styles', 'class:themes')
		);
		$this->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_TEMPLATES_SUBMENU_TEMPLATES'), 'index.php?option=com_templates&view=templates', 'class:themes')
		);
		$this->getParent();
	}

	if ($lm)
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_LANGUAGE_MANAGER'), 'index.php?option=com_languages', 'class:language'), $lm);

		$this->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_LANGUAGES_SUBMENU_INSTALLED'), 'index.php?option=com_languages&view=installed', 'class:language')
		);
		$this->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_LANGUAGES_SUBMENU_CONTENT'), 'index.php?option=com_languages&view=languages', 'class:language')
		);
		$this->addChild(
			new JMenuNode(JText::_('MOD_MENU_COM_LANGUAGES_SUBMENU_OVERRIDES'), 'index.php?option=com_languages&view=overrides', 'class:language')
		);
		$this->getParent();
	}

	$this->getParent();
}

/**
 * Help Submenu
 */
if ($showhelp == 1)
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP'), '#', $rootClass), true);
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_JOOMLA'), 'index.php?option=com_admin&view=help', 'class:help'));
	$this->addSeparator();

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_FORUM'), 'https://forum.joomla.org', 'class:help-forum', false, '_blank'));

	if ($forum_url = $params->get('forum_url'))
	{
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_CUSTOM_FORUM'), $forum_url, 'class:help-forum', false, '_blank'));
	}

	$debug = $lang->setDebug(false);

	if ($lang->hasKey('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE') && JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE') != '')
	{
		$forum_url = 'https://forum.joomla.org/viewforum.php?f=' . (int) JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE');
		$lang->setDebug($debug);
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM'), $forum_url, 'class:help-forum', false, '_blank'));
	}

	$lang->setDebug($debug);
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_DOCUMENTATION'), 'https://docs.joomla.org', 'class:help-docs', false, '_blank'));
	$this->addSeparator();

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_EXTENSIONS'), 'https://extensions.joomla.org', 'class:help-jed', false, '_blank'));
	$this->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_TRANSLATIONS'), 'https://community.joomla.org/translations.html', 'class:help-trans', false, '_blank')
	);
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_RESOURCES'), 'http://resources.joomla.org', 'class:help-jrd', false, '_blank'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_COMMUNITY'), 'https://community.joomla.org', 'class:help-community', false, '_blank'));
	$this->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_SECURITY'), 'https://developer.joomla.org/security-centre.html', 'class:help-security', false, '_blank')
	);
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_DEVELOPER'), 'https://developer.joomla.org', 'class:help-dev', false, '_blank'));
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP_XCHANGE'), 'https://joomla.stackexchange.com', 'class:help-dev', false, '_blank'));
	$this->addChild(
		new JMenuNode(JText::_('MOD_MENU_HELP_SHOP'), 'https://community.joomla.org/the-joomla-shop.html', 'class:help-shop', false, '_blank')
	);
	$this->getParent();
}

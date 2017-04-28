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

$shownew  = (boolean) $params->get('shownew', 1);
$showhelp = (boolean) $params->get('showhelp', 1);
$user     = JFactory::getUser();
$lang     = JFactory::getLanguage();

/**
 * Site Submenu
 */
$this->addChild(new JMenuNode(JText::_('Dashboard'), 'index.php', 'class:home fa-fw'));

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
		new JMenuNode(
			JText::_('MOD_MENU_COM_CONTENT_CATEGORY_MANAGER'), 'index.php?option=com_categories&extension=com_content'
		),
		$createContent
	);

	if ($createContent)
	{
		$this->getParent();
	}

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_FEATURED'), 'index.php?option=com_content&view=featured'));

	if (JComponentHelper::isEnabled('com_fields') && JComponentHelper::getParams('com_content')->get('custom_fields_enable', '1'))
	{
		$this->addSeparator('Fields');
		$this->addChild(
			new JMenuNode(
				JText::_('MOD_MENU_FIELDS'), 'index.php?option=com_fields&context=com_content.article')
		);

		$this->addChild(
			new JMenuNode(
				JText::_('MOD_MENU_FIELDS_GROUP'), 'index.php?option=com_fields&view=groups&context=com_content.article')
		);
	}

	/**
	 * Modules
	 */
	if ($user->authorise('core.manage', 'com_modules'))
	{
		$this->addSeparator('Modules');
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_MODULE_MANAGER'), 'index.php?option=com_modules'));
	}

	/**
	 * Media Submenu
	 */
	if ($user->authorise('core.manage', 'com_media'))
	{
		$this->addSeparator('Media');
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_MEDIA_MANAGER'), 'index.php?option=com_media'));
	}

	$this->getParent();
}

/**
 * Menus Submenu
 */
if ($user->authorise('core.manage', 'com_menus'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_MENUS'), '#', 'class:list fa-fw'), true);
	$createMenu = $shownew && $user->authorise('core.create', 'com_menus');

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER'), 'index.php?option=com_menus&view=menus'), $createMenu);

	if ($createMenu)
	{
		$this->getParent();
	}

	$this->addSeparator();

	$this->addChild(new JMenuNode(JText::_('MOD_MENU_MENUS_ALL_ITEMS'), 'index.php?option=com_menus&view=items&menutype='));
	$this->addSeparator(JText::_('JSITE'));

	// Menu Types
	$menuTypes = ModMenuHelper::getMenus();
	$menuTypes = ArrayHelper::sortObjects($menuTypes, isset($menuTypes[0]->client_id) ? array('client_id', 'title') : 'title', 1, false);

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

		if (isset($menuTypes[$mti - 1], $menuType->client_id) && $menuTypes[$mti - 1]->client_id != $menuType->client_id)
		{
			$this->addSeparator(JText::_('JADMINISTRATOR'));
		}

		$this->addChild(
			new JMenuNode(
				$menuType->title, 'index.php?option=com_menus&view=items&menutype=' . $menuType->menutype, '', null, null, $titleicon
			),
			$user->authorise('core.create', 'com_menus.menu.' . (int) $menuType->id)
		);

		if ($user->authorise('core.create', 'com_menus.menu.' . (int) $menuType->id))
		{
			$this->getParent();
		}
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
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COMPONENTS'), '#', 'class:cube fa-fw'), true);

	foreach ($components as &$component)
	{
		if (!empty($component->submenu))
		{
			// This component has a db driven submenu.
			$this->addChild(new JMenuNode($component->text, $component->link, ''), true);

			foreach ($component->submenu as $sub)
			{
				$this->addChild(new JMenuNode($sub->text, $sub->link, ''));
			}

			$this->getParent();
		}
		else
		{
			$this->addChild(new JMenuNode($component->text, $component->link, ''));
		}
	}

	$this->getParent();
}

/**
 * Users Submenu
 */
if ($user->authorise('core.manage', 'com_users'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS_USERS'), '#', 'class:users fa-fw'), true);
	$createUser = $shownew && $user->authorise('core.create', 'com_users');
	$createGrp  = $user->authorise('core.admin', 'com_users');

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

	$this->addSeparator();
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
		$this->addChild(
			new JMenuNode(
				JText::_('MOD_MENU_FIELDS'), 'index.php?option=com_fields&context=com_users.user')
		);

		$this->addChild(
			new JMenuNode(
				JText::_('MOD_MENU_FIELDS_GROUP'), 'index.php?option=com_fields&view=groups&context=com_users.user')
		);
	}

	if (JFactory::getApplication()->get('massmailoff') != 1)
	{
		$this->addSeparator();
		$this->addChild(new JMenuNode(JText::_('MOD_MENU_MASS_MAIL_USERS'), 'index.php?option=com_users&view=mail'));
	}

	$this->getParent();
}

$this->addChild(new JMenuNode(JText::_('Control Panel'), 'index.php?option=com_cpanel&view=system', 'class:cog fa-fw'), false);
$this->addChild(new JMenuNode(JText::_('Help'), 'index.php?option=com_cpanel&view=help', 'class:info fa-fw'), false);

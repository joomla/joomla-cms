<?php
/**
 * @version		$Id:mod_menu.php 2463 2006-02-18 06:05:38Z webImagery $
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

if (!class_exists('JAdminCssMenu')) {
	require dirname(__FILE__).DS.'menu.php';
}

/**
 * Renders the main administrator menu
 */
class MenuModuleHelper
{
	/**
	 * Show the menu
	 * @param	boolean $enabled	Whether to display in enabled or disabled mode
	 */
	function buildMenu($enabled = true)
	{
		// Get the menu object
		$menu = new JAdminCSSMenu();

		$user = &JFactory::getUser();
		/*
		 * Site SubMenu
		 */
		if ($enabled)
		{
			$menu->addChild(new JMenuNode(JText::_('Site')), true);
			$menu->addChild(new JMenuNode(JText::_('Control Panel'), 'index.php', 'class:cpanel'));
			$menu->addSeparator();
			if ($user->authorize('core.users.manage')) {
				$menu->addChild(new JMenuNode(JText::_('User Manager'), 'index.php?option=com_users', 'class:user'));
			}
			if ($user->authorize('core.media.manage')) {
				$menu->addChild(new JMenuNode(JText::_('Media Manager'), 'index.php?option=com_media', 'class:media'));
			}
			$menu->addSeparator();
			if ($user->authorize('core.config.manage')) {
				$menu->addChild(new JMenuNode(JText::_('Configuration'), 'index.php?option=com_config', 'class:config'));
				$menu->addSeparator();
			}
			$menu->addChild(new JMenuNode(JText::_('Logout'), 'index.php?option=com_login&task=logout', 'class:logout'));

			$menu->getParent();
		}
		else {
			$menu->addChild(new JMenuNode(JText::_('Site'), null, 'disabled'));
		}

		/*
		 * Menus SubMenu
		 */
		if ($user->authorize('core.menus.manage'))
		{
			if ($enabled)
			{
				$menu->addChild(new JMenuNode(JText::_('Menus')), true);
				$menu->addChild(new JMenuNode(JText::_('Menu Manager'), 'index.php?option=com_menus&view=menus', 'class:menu'));

				$menu->addSeparator();
				/*
				 * SPLIT HR
				 */

				// Menu Types
				$db = &JFactory::getDbo();
				$query = 'SELECT a.*, SUM(b.home) AS home' .
						' FROM #__menu_types AS a' .
						' LEFT JOIN #__menu AS b ON b.menutype = a.menutype' .
						' GROUP BY a.id';
				$db->setQuery($query);
				$menuTypes = $db->loadObjectList();


				if (count($menuTypes)) {
					foreach ($menuTypes as $menuType) {
						$menu->addChild(new JMenuNode($menuType->title.($menuType->home ? ' *' : ''), 'index.php?option=com_menus&view=items&menutype='.$menuType->menutype, 'class:menu'));
					}
				}

				$menu->getParent();
			}
			else {
				$menu->addChild(new JMenuNode(JText::_('Menus'), null, 'disabled'));
			}
		}

		/*
		 * Content SubMenu
		 */
		/*

		// Let's try something different.

		if ($user->authorize('com_content.manage'))
		{
			if ($enabled)
			{
				$menu->addChild(new JMenuNode(JText::_('Content')), true);
				$menu->addChild(new JMenuNode(JText::_('Article Manager'), 'index.php?option=com_content', 'class:article'));
				$menu->addSeparator();
				$menu->addChild(new JMenuNode(JText::_('Category Manager'), 'index.php?option=com_categories&extension=com_content', 'class:category'));
				$menu->getParent();
			}
			else {
				$menu->addChild(new JMenuNode(JText::_('Content'), null, 'disabled'));
			}
		}
		*/

		/*
		 * Components SubMenu
		 */
		if ($enabled)
		{
			$lang	= &JFactory::getLanguage();
			$user	= &JFactory::getUser();
			$db		= &JFactory::getDbo();

			$menu->addChild(new JMenuNode(JText::_('Components')), true);

			$query = 'SELECT c.*' .
				' FROM #__components c LEFT JOIN #__extensions e ON c.option = e.element ' .
				' WHERE '.$db->NameQuote('option').' <> "com_media"' .
				' AND e.enabled = 1' .
				' AND e.state > -1' .
				' ORDER BY ordering, name';
			$db->setQuery($query);
			$comps = $db->loadObjectList(); // component list
			$subs = array(); // sub menus
			$langs = array(); // additional language files to load

			// first pass to collect sub-menu items
			foreach ($comps as $row)
			{
				if ($row->parent)
				{
					if (!array_key_exists($row->parent, $subs)) {
						$subs[$row->parent] = array ();
					}
					$subs[$row->parent][] = $row;
					$langs[$row->option.'.menu'] = true;
				}
				else if (trim($row->admin_menu_link)) {
					$langs[$row->option.'.menu'] = true;
				}
			}

			// Load additional language files
			if (array_key_exists('.menu', $langs)) {
				unset($langs['.menu']);
			}
			foreach ($langs as $lang_name => $nothing) {
				// 1.5 or Core
				$lang->load($lang_name);
				// 1.6 3PD Extensions
				$lang->load( 'menu', JPATH_ADMINISTRATOR.DS.'components'.DS.str_replace('.menu','',$lang_name));
			}

			foreach ($comps as $row)
			{
				if ($user->authorize($row->option.'.manage'))
				{
					if ($row->parent == 0 && (trim($row->admin_menu_link) || array_key_exists($row->id, $subs)))
					{
						$text = $lang->hasKey($row->option) ? JText::_($row->option) : $row->name;
						$link = $row->admin_menu_link ? "index.php?$row->admin_menu_link" : "index.php?option=$row->option";
						if (array_key_exists($row->id, $subs))
						{
							$menu->addChild(new JMenuNode($text, $link, $row->admin_menu_img), true);
							foreach ($subs[$row->id] as $sub)
							{
								$key  = $row->option.'_'.str_replace(' ', '_', $sub->name);
								$text = $lang->hasKey($key) ? JText::_($key) : $sub->name;
								$link = $sub->admin_menu_link ? "index.php?$sub->admin_menu_link" : null;
								$menu->addChild(new JMenuNode($text, $link, $sub->admin_menu_img));
							}
							$menu->getParent();
						} else {
							$menu->addChild(new JMenuNode($text, $link, $row->admin_menu_img));
						}
					}
				}
			}
			$menu->getParent();
		}
		else {
			$menu->addChild(new JMenuNode(JText::_('Components'), null, 'disabled'));
		}

		/*
		 * Extensions SubMenu
		 */
		$im = $user->authorize('core.installer.manage');
		$mm = $user->authorize('core.modules.manage');
		$pm = $user->authorize('core.plugins.manage');
		$tm = $user->authorize('core.templates.manage');
		$lm = $user->authorize('core.languages.manage');

		if ($im || $mm || $pm || $tm || $lm)
		{
			if ($enabled)
			{
				$menu->addChild(new JMenuNode(JText::_('Extensions')), true);

				if ($im) {
					$menu->addChild(new JMenuNode(JText::_('Extension Manager'), 'index.php?option=com_installer', 'class:install'));
					$menu->addSeparator();
				}
				if ($mm) {
					$menu->addChild(new JMenuNode(JText::_('Module Manager'), 'index.php?option=com_modules', 'class:module'));
				}
				if ($pm) {
					$menu->addChild(new JMenuNode(JText::_('Plugin Manager'), 'index.php?option=com_plugins', 'class:plugin'));
				}
				if ($tm) {
					$menu->addChild(new JMenuNode(JText::_('Template Manager'), 'index.php?option=com_templates', 'class:themes'));
				}
				if ($lm) {
					$menu->addChild(new JMenuNode(JText::_('Language Manager'), 'index.php?option=com_languages', 'class:language'));
				}
				$menu->getParent();
			}
			else {
				$menu->addChild(new JMenuNode(JText::_('Extensions'), null, 'disabled'));
			}
		}

		/*
		 * Tools SubMenu
		 */
		if ($enabled)
		{
			$menu->addChild(new JMenuNode(JText::_('Tools')), true);

			$menu->addChild(new JMenuNode(JText::_('Redirect'), 'index.php?option=com_redirect', 'class:component'));
			$menu->addSeparator();

			if ($user->authorize('core.messages.manage')) {
				$menu->addChild(new JMenuNode(JText::_('Read Messages'), 'index.php?option=com_messages', 'class:messages'));
				$menu->addChild(new JMenuNode(JText::_('Write Message'), 'index.php?option=com_messages&task=add', 'class:messages'));
				$menu->addSeparator();
			}
			if ($user->authorize('core.massmail.manage')) {
				$menu->addChild(new JMenuNode(JText::_('Mass Mail'), 'index.php?option=com_massmail', 'class:massmail'));
				$menu->addSeparator();
			}
			if ($user->authorize('core.checkin.manage')) {
				$menu->addChild(new JMenuNode(JText::_('Global Checkin'), 'index.php?option=com_checkin', 'class:checkin'));
				$menu->addSeparator();
			}
			if ($user->authorize('core.cache.manage')) {
				$menu->addChild(new JMenuNode(JText::_('Clean Cache'), 'index.php?option=com_cache', 'class:config'));
				$menu->addChild(new JMenuNode(JText::_('Purge Expired Cache'), 'index.php?option=com_cache&view=purge', 'class:config'));
			}

			$menu->getParent();
		}
		else {
			$menu->addChild(new JMenuNode(JText::_('Tools'),  null, 'disabled'));
		}

		/*
		 * Help SubMenu
		 */
		if ($enabled)
		{
			$menu->addChild(new JMenuNode(JText::_('Help')), true);
			$menu->addChild(new JMenuNode(JText::_('Joomla! Help'), 'index.php?option=com_admin&view=help', 'class:help'));
			$menu->addChild(new JMenuNode(JText::_('System Info'), 'index.php?option=com_admin&view=sysinfo', 'class:info'));
			$menu->getParent();
		}
		else {
			$menu->addChild(new JMenuNode(JText::_('Help'),  null, 'disabled'));
		}

		$menu->renderMenu('menu', $enabled ? '' : 'disabled');
	}
}

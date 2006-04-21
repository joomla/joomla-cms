<?php
/**
* @version $Id:mod_menu.php 2463 2006-02-18 06:05:38Z webImagery $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(dirname(__FILE__).DS.'menu.php');

/*
 * Lets get some variables we will need to render the menu
 */
$user	= & $mainframe->getUser();
$hide	= JRequest::getVar('hidemainmenu', 0);

/*
 * If we are disabling the menu, show the disabled menu... otherwise show the
 * full menu.
 */
if ($hide) {
	$menu = & buildDisabledMenu($user->get('usertype'));
} else {
	$menu = & buildMenu($user->get('usertype'));
}

$menu->renderMenu();

/**
* Show the menu
* @param string The current user type
*/
function & buildMenu($usertype = '')
{
	global $mainframe;

	$lang			= & $mainframe->getLanguage();
	$user			= & $mainframe->getUser();
	$database		= & $mainframe->getDBO();
	$enableStats	= $mainframe->getCfg('enable_stats');
	$enableSearches	= $mainframe->getCfg('enable_log_searches');
	$caching		= $mainframe->getCfg('caching');

	// cache some acl checks
	$canCheckin			= $user->authorize('com_checkin', 'manage');
	$canConfig			= $user->authorize('com_config', 'manage');
	$manageTemplates	= $user->authorize('com_templates', 'manage');
	$manageTrash		= $user->authorize('com_trash', 'manage');
	$manageMenuMan		= $user->authorize('com_menumanager', 'manage');
	$manageLanguages	= $user->authorize('com_languages', 'manage');
	$installModules		= $user->authorize('com_installer', 'module');
	$editAllModules		= $user->authorize('com_modules', 'manage');
	$installPlugins		= $user->authorize('com_installer', 'plugin');
	$editAllPlugins		= $user->authorize('com_plugins', 'manage');
	$installComponents	= $user->authorize('com_installer', 'component');
	$editAllComponents	= $user->authorize('com_components', 'manage');
	$canMassMail		= $user->authorize('com_massmail', 'manage');
	$canManageUsers		= $user->authorize('com_users', 'manage');

	$query = "SELECT a.id, a.title, a.name, COUNT( DISTINCT c.id ) AS numcat, COUNT( DISTINCT b.id ) AS numarc" .
			"\n FROM #__sections AS a" .
			"\n LEFT JOIN #__categories AS c ON c.section = a.id" .
			"\n LEFT JOIN #__content AS b ON b.sectionid = a.id AND b.state = -1" .
			"\n WHERE a.scope = 'content'" .
			"\n GROUP BY a.id" .
			"\n ORDER BY a.ordering";
	$database->setQuery($query);
	$sections = $database->loadObjectList();
	$nonemptySections = 0;
	if (count($sections) > 0)
	{
		foreach ($sections as $section)
		{
			if ($section->numcat > 0) {
				$nonemptySections ++;
			}
		}
	}
	$menuTypes = mosAdminMenus::menutypes();
	
	/*
	 * Get the menu object
	 */
	$menu = new JAdminCSSMenu();

	/*
	 * Site SubMenu
	 */
	$site = new JMenuNode(JText::_('Site'));
	$site->addChild(new JMenuNode(JText::_('Control Panel'), 'index2.php', 'class:cpanel'));
	$site->addSeperator();
	if ($canManageUsers) {
		$site->addChild(new JMenuNode(JText::_('User Manager'), 'index2.php?option=com_users&task=view', 'class:user'));
	}
	$site->addChild(new JMenuNode(JText::_('Media Manager'), 'index2.php?option=com_media', 'class:media'));
	$site->addSeperator();
	//$site->addChild(new JMenuNode(JText::_('Preview...'), 'index2.php?option=com_templates&task=preview', 'class:preview'));
	if ($enableStats || $enableSearches) {
		$site->addChild(new JMenuNode(JText::_('Statistics'), 'index2.php?option=com_statistics', 'class:stats'));
	}
	if ($canConfig) {
		$site->addChild(new JMenuNode(JText::_('Configuration'), 'index2.php?option=com_config&hidemainmenu=1', 'class:config'));
		$site->addSeperator();
	}
	$site->addChild(new JMenuNode(JText::_('Logout'), 'index2.php?option=com_logout', 'class:logout'));

	$menu->addTree($site);

	/*
	 * Menus SubMenu
	 */
	$menus = new JMenuNode(JText::_('Menus'));
	if ($manageMenuMan) {
		$menus->addChild(new JMenuNode(JText::_('Menu Manager'), 'index2.php?option=com_menumanager', 'class:menumgr'));
	}
	if ($manageTrash) {
		$menus->addChild(new JMenuNode(JText::_('Trash Manager'), 'index2.php?option=com_trash&task=viewMenu', 'class:trash'));
	}
	
	if($manageTrash || $maangeMenuMan) {
		$menus->addSeperator();
	}
	/*
	 * SPLIT HR
	 */
	foreach ($menuTypes as $menuType) {
		$menus->addChild(new JMenuNode($menuType, 'index2.php?option=com_menus&menutype='.$menuType, 'class:menu'));
	}

	$menu->addTree($menus);

	/*
	 * Content SubMenu
	 */
	$content = new JMenuNode(JText::_('Content'));
	$content->addChild(new JMenuNode(JText::_('Article Manager'), 'index2.php?option=com_content&sectionid=0', 'class:content'));
	$content->addSeperator();
//	$content->addChild(new JMenuNode(JText::_('Static Content Manager'), 'index2.php?option=com_typedcontent', 'class:static'));
	$content->addChild(new JMenuNode(JText::_('Section Manager'), 'index2.php?option=com_sections&scope=content', 'class:category'));
	$content->addChild(new JMenuNode(JText::_('Category Manager'), 'index2.php?option=com_categories&section=content', 'class:category'));
	$content->addSeperator();
	$content->addChild(new JMenuNode(JText::_('Frontpage Manager'), 'index2.php?option=com_frontpage', 'class:frontpage'));
	$content->addChild(new JMenuNode(JText::_('Archive Manager'), 'index2.php?option=com_content&task=showarchive&sectionid=0', 'class:archive'));
	if ($manageTrash) {
		$content->addSeperator();
		$content->addChild(new JMenuNode(JText::_('Trash Manager'), 'index2.php?option=com_trash&task=viewContent', 'class:trash'));
		$content->addChild(new JMenuNode(JText::_('Page Hits'), 'index2.php?option=com_statistics&task=pageimp', 'class:stats'));
	}

	$menu->addTree($content);

	/*
	 * Components SubMenu
	 */
	if ($installComponents) {
		$components = new JMenuNode(JText::_('Components'));

		$query = "SELECT *" .
				"\n FROM #__components" .
				"\n WHERE name <> 'frontpage'" .
				"\n AND name <> 'media manager'" .
				"\n ORDER BY ordering, name";
		$database->setQuery($query);
		$comps = $database->loadObjectList(); // component list
		$subs = array (); // sub menus
		// first pass to collect sub-menu items
		foreach ($comps as $row)
		{
			if ($row->parent)
			{
				if (!array_key_exists($row->parent, $subs)) {
					$subs[$row->parent] = array ();
				}
				$subs[$row->parent][] = $row;
			}
		}
		foreach ($comps as $row)
		{
			if ($editAllComponents | $user->authorize('administration', 'edit', 'components', $row->option))
			{
				if ($row->parent == 0 && (trim($row->admin_menu_link) || array_key_exists($row->id, $subs)))
				{
					$alt = $row->admin_menu_alt;
					$link = $row->admin_menu_link ? "index2.php?$row->admin_menu_link" : "index2.php?option=$row->option";
					$component = & new JMenuNode(JText::_($row->name), $link, $row->admin_menu_img);
					if (array_key_exists($row->id, $subs)) 	{
						foreach ($subs[$row->id] as $sub) {
							$alt = $sub->admin_menu_alt;
							$link = $sub->admin_menu_link ? "index2.php?$sub->admin_menu_link" : null;
							$component->addChild(new JMenuNode(JText::_($sub->name), $link, $sub->admin_menu_img));
						}
					}
					$components->addChild($component);
				}
			}
		}

		$menu->addTree($components);
	}

	/*
	 * Extensions SubMenu
	 */
	if ($installModules)
	{
		$extensions = new JMenuNode(JText::_('Extensions'));

		$extensions->addChild(new JMenuNode(JText::_('Install/Uninstall'), 'index2.php?option=com_installer', 'class:install'));
		$extensions->addSeperator();
		if ($editAllModules) {
			$extensions->addChild(new JMenuNode(JText::_('Module Manager'), 'index2.php?option=com_modules', 'class:module'));
		}
		if ($editAllPlugins) {
			$extensions->addChild(new JMenuNode(JText::_('Plugin Manager'), 'index2.php?option=com_plugins', 'class:plugin'));
		}
		if ($manageTemplates) {
			$extensions->addChild(new JMenuNode(JText::_('Template Manager'), 'index2.php?option=com_templates', 'class:themes'));
		}
		if ($manageLanguages) {
			$extensions->addChild(new JMenuNode(JText::_('Language Manager'), 'index2.php?option=com_languages', 'class:language'));
		}

		$menu->addTree($extensions);
	}

	/*
	 * System SubMenu
	 */
	if ($canConfig)
	{
		$tools = new JMenuNode(JText::_('Tools'));

		$tools->addChild(new JMenuNode(JText::_('Read Messages'), 'index2.php?option=com_messages', 'class:messages'));
		$tools->addChild(new JMenuNode(JText::_('New Messages'), 'index2.php?option=com_messages&task=new', 'class:messages'));
		$tools->addSeperator();
		if ($canMassMail) {
			$tools->addChild(new JMenuNode(JText::_('Mass Mail'), 'index2.php?option=com_massmail', 'class:massmail'));
			$site->addSeperator();
		}
		if ($canCheckin) {
			$tools->addChild(new JMenuNode(JText::_('Global Checkin'), 'index2.php?option=com_checkin', 'class:checkin'));
			$tools->addSeperator();
		}
		if ($caching) {
			$tools->addChild(new JMenuNode(JText::_('Clean Content Cache'), 'index2.php?option=com_admin&task=clean_cache', 'class:config'));
			$tools->addChild(new JMenuNode(JText::_('Clean All Cache'), 'index2.php?option=com_admin&task=clean_all_cache', 'class:config'));
		}
		$tools->addChild(new JMenuNode(JText::_('System Info'), 'index2.php?option=com_admin&task=sysinfo', 'class:info'));

		$menu->addTree($tools);
	}

	/*
	 * Home SubMenu
	 */
	$help = new JMenuNode(JText::_('Help'));
	$help->addChild(new JMenuNode(JText::_('Help'), 'index2.php?option=com_admin&task=help', 'class:help'));

	$menu->addTree($help);
	
	return $menu;
}

/**
* Show an disbaled version of the menu, used in edit pages
*
* @param string The current user type
*/
function & buildDisabledMenu($usertype = '')
{
	global $mainframe;

	$lang	= & $mainframe->getLanguage();
	$user	= & $mainframe->getUser();

	$canConfig			= $user->authorize('com_config', 'manage');
	$installModules		= $user->authorize('com_installer', 'module');
	$editAllModules		= $user->authorize('com_modules', 'manage');
	$installPlugins		= $user->authorize('com_installer', 'plugin');
	$editAllPlugins		= $user->authorize('com_plugins', 'manage');
	$installComponents	= $user->authorize('com_installer', 'component');
	$editAllComponents	= $user->authorize('com_components', 'manage');
	$canMassMail		= $user->authorize('com_massmail', 'manage');
	$canManageUsers	= $user->authorize('com_users', 'manage');

	$text = JText::_('Menu inactive for this Page', true);

	/*
	 * Get the menu object
	 */
	$menu = new JAdminCSSMenu();
	
		/*
	 * Site SubMenu
	 */
	$site = new JMenuNode(JText::_('Site'), null, 'disabled');
	$menu->addTree($site);

	/*
	 * Menus SubMenu
	 */
	$menus = new JMenuNode(JText::_('Menus'), null, 'disabled');
	$menu->addTree($menus);

	/*
	 * Content SubMenu
	 */
	$content = new JMenuNode(JText::_('Content'), null, 'disabled');
	$menu->addTree($content);

	/*
	 * Components SubMenu
	 */
	if ($installComponents) {
		$components = new JMenuNode(JText::_('Components'), null, 'disabled');
		$menu->addTree($components);
	}

	/*
	 * Extensions SubMenu
	 */
	if ($installModules)
	{
		$extensions = new JMenuNode(JText::_('Extensions'), null, 'disabled');
		$menu->addTree($extensions);
	}

	/*
	 * System SubMenu
	 */
	if ($canConfig)
	{
		$tools = new JMenuNode(JText::_('Tools'), null, 'disabled');
		$menu->addTree($tools);
	}

	/*
	 * Home SubMenu
	 */
	$help = new JMenuNode(JText::_('Help'), null, 'disabled');
	$menu->addTree($help);
	
	return $menu;
}
?>
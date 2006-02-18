<?php
/**
* @version $Id:mod_menu.php 2463 2006-02-18 06:05:38Z webImagery $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Lets get some variables we will need to render the menu
 */
$lang 	= & $mainframe->getLanguage();
$doc 	= & $mainframe->getDocument();
$user 	= & $mainframe->getUser();
$hide 	= JRequest :: getVar( 'hidemainmenu', 0 );

// Add the javascript to the page head
/*
$doc->addScript('../includes/js/moofx/prototype.lite.js');
$doc->addScript('../includes/js/moofx/moo.fx.js');
$doc->addScript('../includes/js/moofx/moo.fx.pack.js');

$js = 	"\t\t\t//the main function, call to the effect object" .
		"\n\t\t\tfunction init(){" .
		"\n\n\t\t\t	var stretchers = document.getElementsByClassName('section_smenu'); //div that stretches" .
		"\n\t\t\t	var toggles = document.getElementsByClassName('title_smenu'); //h3s where I click on" .
		"\n\n\t\t\t	//accordion effect" .
		"\n\t\t\t	var smenuAccordion = new fx.Accordion(" .
		"\n\t\t\t	toggles, stretchers, {opacity: true, duration: 400}" .
		"\n\t\t\t	);" .
		"\n\n\t\t\t	//hash functions" .
		"\n\t\t\t	var found = false;" .
		"\n\t\t\t	toggles.each(function(h3, i){" .
		"\n\t\t\t		var div = Element.find(h3, 'nextSibling'); //element.find is located in prototype.lite" .
		"\n\t\t\t		if (window.location.href.indexOf(h3.title) > 0) {" .
		"\n\t\t\t			smenuAccordion.showThisHideOpen(div);" .
		"\n\t\t\t			found = true;" .
		"\n\t\t\t		}" .
		"\n\t\t\t	});" .
		"\n\t\t\t	if (!found) smenuAccordion.showThisHideOpen(stretchers[0]);" .
		"\n\t\t\t}";

$doc->addScriptDeclaration($js);
*/

/*
 * If we are disabling the menu, show the disabled menu... otherwise show the
 * full menu.
 */
echo "<div id=\"sidemenu\">";
if ($hide) {
	JAdminMenu :: showDisabled($user->get('usertype'));
} else {
	JAdminMenu :: show($user->get('usertype'));
}
echo "</div>";


/**
 * Admin Side Menu
 *
 * @package Joomla
 */
class JAdminMenu {
	/**
	* Show the menu
	* @param string The current user type
	*/
	function show($usertype = '') {
		global $mainframe;

		$lang 			= & $mainframe->getLanguage();
		$user 			= & $mainframe->getUser();
		$database 		= & $mainframe->getDBO();
		$enableStats 	= $mainframe->getCfg('enable_stats');
		$enableSearches = $mainframe->getCfg('enable_log_searches');
		$caching 		= $mainframe->getCfg('caching');

		// cache some acl checks
		$canCheckin 		= $user->authorize('com_checkin', 		'manage');
		$canConfig 			= $user->authorize('com_config', 		'manage');
		$manageTemplates 	= $user->authorize('com_templates', 	'manage');
		$manageTrash 		= $user->authorize('com_trash', 		'manage');
		$manageMenuMan 		= $user->authorize('com_menumanager', 	'manage');
		$manageLanguages 	= $user->authorize('com_languages', 	'manage');
		$installModules 	= $user->authorize('com_installer', 	'module');
		$editAllModules 	= $user->authorize('com_modules', 		'manage');
		$installPlugins 	= $user->authorize('com_installer', 	'plugin');
		$editAllPlugins 	= $user->authorize('com_plugins', 		'manage');
		$installComponents 	= $user->authorize('com_installer', 	'component');
		$editAllComponents 	= $user->authorize('com_components', 	'manage');
		$canMassMail 		= $user->authorize('com_massmail', 		'manage');
		$canManageUsers 	= $user->authorize('com_users', 		'manage');

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
		if (count($sections) > 0) {
			foreach ($sections as $section) {
				if ($section->numcat > 0) {
					$nonemptySections ++;
				}
			}
		}
		$menuTypes = mosAdminMenus :: menutypes();

	/*
	 * Home SubMenu
	 */
		$homeList[] = array( 'title' => JText::_('Control Panel'), 'link' => 'index2.php', 'img' => '../includes/js/ThemeOffice/controlpanel.png' );
		$homeList[] = array( 'title' => JText::_('Help'), 'link' => 'index2.php?option=com_admin&task=help', 'img' => '../includes/js/ThemeOffice/help.png' );
		echo JAdminMenu::buildDiv ( JText::_('Home'), $homeList );

	/*
	 * Site SubMenu
	 */
		if ($canConfig)
		{
			$siteList[] = array( 'title' => JText::_('Global Configuration'), 'link' => 'index2.php?option=com_config&hidemainmenu=1', 'img' => '../includes/js/ThemeOffice/config.png' );
		}
		if ($canManageUsers || $canMassMail) {
			$siteList[] = array( 'title' => JText::_('User Manager'), 'link' => 'index2.php?option=com_users&task=view', 'img' => '../includes/js/ThemeOffice/users.png' );
		}
		$siteList[] = array( 'title' => JText::_('Media Manager'), 'link' => 'index2.php?option=com_media', 'img' => '../includes/js/ThemeOffice/media.png' );
		if ($canConfig)	{
			$siteList[] = array( 'title' => JText::_('Messages'), 'link' => 'index2.php?option=com_messages', 'img' => '../includes/js/ThemeOffice/messaging_inbox.png' );
		}
		if ($manageTemplates) {
			$siteList[] = array( 'title' => JText::_('Template Manager'), 'link' => 'index2.php?option=com_templates', 'img' => '../includes/js/ThemeOffice/template.png' );
		}
		if ($manageLanguages) {
			$siteList[] = array( 'title' => JText::_('Language Manager'), 'link' => 'index2.php?option=com_languages', 'img' => '../includes/js/ThemeOffice/language.png' );
		}
		if ($enableStats || $enableSearches ) {
			$siteList[] = array( 'title' => JText::_('Statistics'), 'link' => 'index2.php?option=com_stastistics', 'img' => '../includes/js/ThemeOffice/globe1.png' );
		}
		echo JAdminMenu::buildDiv ( JText::_('Site'), $siteList );

	/*
	 * Menus SubMenu
	 */
		if ($manageMenuMan) {
			$menusList[] = array( 'title' => JText::_('Menu Manager'), 'link' => 'index2.php?option=com_menumanager', 'img' => '../includes/js/ThemeOffice/menus.png' );
		}
		if ($manageTrash) {
			$menusList[] = array( 'title' => JText::_('Trash Manager'), 'link' => 'index2.php?option=com_trash&task=viewMenu', 'img' => '../includes/js/ThemeOffice/trash.png' );
		}
		/*
		 * SPLIT HR
		 */
		foreach ($menuTypes as $menuType) {
			$menusList[] = array( 'title' => $menuType, 'link' => 'index2.php?option=com_menus&menutype='.$menuType, 'img' => '../includes/js/ThemeOffice/menus.png' );
		}
		echo JAdminMenu::buildDiv ( JText::_('Menus'), $menusList );

	/*
	 * Content SubMenu
	 */
		$contentList[] = array( 'title' => JText::_('All Content Items'), 'link' => 'index2.php?option=com_content&sectionid=0', 'img' => '../includes/js/ThemeOffice/edit.png' );
		$contentList[] = array( 'title' => JText::_('Static Content Manager'), 'link' => 'index2.php?option=com_typedcontent', 'img' => '../includes/js/ThemeOffice/edit.png' );
		/*
		 * SPLIT HR
		 */
		$contentList[] = array( 'title' => JText::_('Section Manager'), 'link' => 'index2.php?option=com_sections&scope=content', 'img' => '../includes/js/ThemeOffice/add_section.png' );
		$contentList[] = array( 'title' => JText::_('Category Manager'), 'link' => 'index2.php?option=com_categories&section=content', 'img' => '../includes/js/ThemeOffice/add_section.png' );
		/*
		 * SPLIT HR
		 */
		$contentList[] = array( 'title' => JText::_('Frontpage Manager'), 'link' => 'index2.php?option=com_frontpage', 'img' => '../includes/js/ThemeOffice/home.png' );
		$contentList[] = array( 'title' => JText::_('Archive Manager'), 'link' => 'index2.php?option=com_content&task=showarchive&sectionid=0', 'img' => '../includes/js/ThemeOffice/edit.png' );
		if ($manageTrash) {
			/*
			 * SPLIT HR
			 */
			$contentList[] = array( 'title' => JText::_('Trash Manager'), 'link' => 'index2.php?option=com_trash&task=viewContent', 'img' => '../includes/js/ThemeOffice/trash.png' );
			$contentList[] = array( 'title' => JText::_('Page Hits'), 'link' => 'index2.php?option=com_statistics&task=pageimp', 'img' => '../includes/js/ThemeOffice/globe3.png' );
		}
		echo JAdminMenu::buildDiv ( JText::_('Content'), $contentList );

	/*
	 * Components SubMenu
	 */
		if ($installComponents) {
			$query = "SELECT *" .
					"\n FROM #__components" .
					"\n WHERE name <> 'frontpage'" .
					"\n AND name <> 'media manager'" .
					"\n ORDER BY ordering, name";
			$database->setQuery($query);
			$comps 	= $database->loadObjectList(); // component list
			$subs	 = array (); // sub menus
			// first pass to collect sub-menu items
			foreach ($comps as $row) {
				if ($row->parent) {
					if (!array_key_exists($row->parent, $subs)) {
						$subs[$row->parent] = array ();
					}
					$subs[$row->parent][] = $row;
				}
			}
			foreach ($comps as $row) {
				if ($editAllComponents | $user->authorize('administration', 'edit', 'components', $row->option)) {
					if ($row->parent == 0 && (trim($row->admin_menu_link) || array_key_exists($row->id, $subs))) {
						$alt 	= $row->admin_menu_alt;
						$link 	= $row->admin_menu_link ? "'index2.php?$row->admin_menu_link'" : "null";
						$componentsList[] = array( 'title' => JText::_($row->name), 'link' => $link, 'img' => '../includes/'.$row->admin_menu_img );
					}
				}
			}
			echo JAdminMenu::buildDiv ( JText::_('Components'), $componentsList );
		}

	/*
	 * Modules SubMenu
	 */
		if ($installModules | $editAllModules) {
			if ($editAllModules) {
				$modulesList[] = array( 'title' => JText::_('Site Modules'), 'link' => 'index2.php?option=com_modules', 'img' => '../includes/js/ThemeOffice/module.png' );
				$modulesList[] = array( 'title' => JText::_('Administrator Modules'), 'link' => 'index2.php?option=com_modules&client=admin', 'img' => '../includes/js/ThemeOffice/module.png' );
			}
			echo JAdminMenu::buildDiv ( JText::_('Modules'), $modulesList );
		}

	/*
	 * Plugins SubMenu
	 */
		if ($installPlugins | $editAllPlugins) 	{
			if ($editAllPlugins) {
				$pluginsList[] = array( 'title' => JText::_('Site Plugins'), 'link' => 'index2.php?option=com_plugins', 'img' => '../includes/js/ThemeOffice/module.png' );
			}
			echo JAdminMenu::buildDiv ( JText::_('Plugins'), $pluginsList );
		}

	/*
	 * Extensions SubMenu
	 */
		if ($installModules) {
			$extensionsList[] = array( 'title' => JText::_('Extension Manager'), 'link' => 'index2.php?option=com_installer', 'img' => '../includes/js/ThemeOffice/install.png' );
			echo JAdminMenu::buildDiv ( JText::_('Extensions'), $extensionsList );
		}

	/*
	 * System SubMenu
	 */
		if ($canConfig)	{
			$systemList[] = array( 'title' => JText::_('System Info'), 'link' => 'index2.php?option=com_admin&task=sysinfo', 'img' => '../includes/js/ThemeOffice/sysinfo.png' );
			if ($canCheckin) {
				$systemList[] = array( 'title' => JText::_('Global Checkin'), 'link' => 'index2.php?option=com_checkin', 'img' => '../includes/js/ThemeOffice/checkin.png' );
			}
			if ($caching) {
				$systemList[] = array( 'title' => JText::_('Clean Content Cache'), 'link' => 'index2.php?option=com_admin&task=clean_cache', 'img' => '../includes/js/ThemeOffice/config.png' );
				$systemList[] = array( 'title' => JText::_('Clean All Cache'), 'link' => 'index2.php?option=com_admin&task=clean_all_cache', 'img' => '../includes/js/ThemeOffice/config.png' );
			}
			echo JAdminMenu::buildDiv ( JText::_('System'), $systemList );
		}
	}

	/**
	* Show an disbaled version of the menu, used in edit pages
	*
	* @param string The current user type
	*/
	function showDisabled($usertype = '') {
		global $mainframe;

		$lang 	= & $mainframe->getLanguage();
		$user 	= & $mainframe->getUser();

		$canConfig 			= $user->authorize('com_config', 		'manage');
		$installModules 	= $user->authorize('com_installer', 	'module');
		$editAllModules 	= $user->authorize('com_modules', 		'manage');
		$installPlugins 	= $user->authorize('com_installer', 	'plugin');
		$editAllPlugins 	= $user->authorize('com_plugins', 		'manage');
		$installComponents 	= $user->authorize('com_installer', 	'component');
		$editAllComponents 	= $user->authorize('com_components',	'manage');
		$canMassMail 		= $user->authorize('com_massmail', 		'manage');
		$canManageUsers 	= $user->authorize('com_users', 		'manage');

		$text = JText :: _('Menu inactive for this Page', true);
	}
	
	function buildDiv ( $title, $list, $suffix = '-smenu' )
	{

		$txt = 	"<h3 class=\"title".$suffix."\" title=\"$title\">$title</h3>";		
		$txt .=	"<div class=\"section".$suffix."\">\n";
		
		/*
		 * Iterate through the link items for building the menu items
		 */
		foreach ($list as $item)
		{
			if (isset($item['active']) && $item['active'] == 1)
			{
				$sfx = $suffix.'_active';
			} else
			{
				$sfx = $suffix;
			}
			$txt .=	"<li class=\"item".$sfx."\">";
			$txt .= "<a href=\"".$item['link']."\"><img src=\"".$item['img']."\" border=\"0\" />&nbsp;&nbsp;".$item['title']."</a>";
			$txt .=	"</li>"; 
		}
		$txt .=	"\n</div>";

		return $txt;		
	}
}
?>
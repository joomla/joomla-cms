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

/*
 * Lets get some variables we will need to render the menu
 */
$lang	= & $mainframe->getLanguage();
$doc	= & $mainframe->getDocument();
$user	= & $mainframe->getUser();

// If hidemainmenu is true, we don't want to render this module at all
$contents = JAdminSubMenu::get();

// Only show the module if there are items to actually show
if ($contents) {
	echo "<div class=\"submenu-box\">\n<div class=\"submenu-pad\">\n";
	echo "$contents\n";
	echo "<div class=\"clr\"></div>\n";
	echo "</div></div>";
}


/**
 * Admin Submenu
 *
 * @package Joomla
 */
class JAdminSubMenu
{
	function get()
	{
		global $mainframe;

		/*
		 * Lets get some variables we are going to need
		 */
		$menu = false;
		$lang = & $mainframe->getLanguage();
		$user = & $mainframe->getUser();
		$db = & $mainframe->getDBO();
		$enableStats = $mainframe->getCfg('enable_stats');
		$enableSearches = $mainframe->getCfg('enable_log_searches');
		$option = JRequest::getVar('option');
		$task = JRequest::getVar('task');
		$act = JRequest::getVar('act');

		/*
		 * If there is no option set, then we obviously have no submenu to view
		 * so return false
		 */
		if (empty ($option)) {
			return false;
		}

		/*
		 * Basically this module is a big switch statement.... this way we only display menu
		 * items that are relevant to the option/task request
		 */
		switch ($option)
		{
			case 'com_templates' :
				$task	= JRequest::getVar('task');
				$client	= JRequest::getVar('client', 0, '', 'int');
				if ($client == 1) {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index2.php?option=com_templates&client=0', 'img' => '../includes/js/ThemeOffice/template.png');
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index2.php?option=com_templates&client=1', 'img' => '../includes/js/ThemeOffice/template.png', 'active' => 1);
				} elseif ($client == 0 && !$task) {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index2.php?option=com_templates&client=0', 'img' => '../includes/js/ThemeOffice/template.png', 'active' => 1);
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index2.php?option=com_templates&client=1', 'img' => '../includes/js/ThemeOffice/template.png');
				} else {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index2.php?option=com_templates&client=0', 'img' => '../includes/js/ThemeOffice/template.png');
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index2.php?option=com_templates&client=1', 'img' => '../includes/js/ThemeOffice/template.png');
				}

				if ($task == 'positions') {
					$subMenuList[] = array ('title' => JText::_('Module Positions'), 'link' => 'index2.php?option=com_templates&task=positions', 'img' => '../includes/js/ThemeOffice/template.png', 'active' => 1);
				} else {
					$subMenuList[] = array ('title' => JText::_('Module Positions'), 'link' => 'index2.php?option=com_templates&task=positions', 'img' => '../includes/js/ThemeOffice/template.png');
				}

				if ($task == 'preview') {
					$subMenuList[] = array ('title' => JText::_('Preview'), 'link' => 'index2.php?option=com_templates&task=preview', 'img' => '../includes/js/ThemeOffice/preview.png', 'active' => 1);
				} else {
					$subMenuList[] = array ('title' => JText::_('Preview'), 'link' => 'index2.php?option=com_templates&task=preview', 'img' => '../includes/js/ThemeOffice/preview.png');
				}
				$menu = JAdminSubMenu::buildList($subMenuList);
				break;

			case 'com_languages' :
				$client	= JRequest::getVar('client', 0, '', 'int');
				if ($client == 1) {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index2.php?option=com_languages&client=0', 'img' => '../includes/js/ThemeOffice/language.png');
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index2.php?option=com_languages&client=1', 'img' => '../includes/js/ThemeOffice/language.png', 'active' => 1);
				} else {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index2.php?option=com_languages&client=0', 'img' => '../includes/js/ThemeOffice/language.png', 'active' => 1);
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index2.php?option=com_languages&client=1', 'img' => '../includes/js/ThemeOffice/language.png');
				}
				$menu = JAdminSubMenu::buildList($subMenuList);
				break;

			case 'com_modules' :
				$client	= JRequest::getVar('client', 0, '', 'int');
				if ($client == 1) {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index2.php?option=com_modules&client=0', 'img' => '../includes/js/ThemeOffice/module.png');
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index2.php?option=com_modules&client=1', 'img' => '../includes/js/ThemeOffice/module.png', 'active' => 1);
				} else {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index2.php?option=com_modules&client=0', 'img' => '../includes/js/ThemeOffice/module.png', 'active' => 1);
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index2.php?option=com_modules&client=1', 'img' => '../includes/js/ThemeOffice/module.png');
				}
				//$subMenuList[] = array ('title' => JText::_('Manage Positions'), 'link' => 'index2.php?option=com_templates&task=positions', 'img' => '../includes/js/ThemeOffice/preview.png');
				$menu = JAdminSubMenu::buildList($subMenuList);
				break;

			case 'com_installer' :
				$ext	= JRequest::getVar('extension');
				switch ($ext) {
					case 'component':
						$subMenuList[] = array ('title' => JText::_('Install'), 'link' => 'index2.php?option=com_installer&amp;task=installer', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Components'), 'link' => 'index2.php?option=com_installer&extension=component', 'img' => '../includes/js/ThemeOffice/installer.png', 'active' => 1);
						$subMenuList[] = array ('title' => JText::_('Modules'), 'link' => 'index2.php?option=com_installer&extension=module', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Plugins'), 'link' => 'index2.php?option=com_installer&extension=plugin', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Languages'), 'link' => 'index2.php?option=com_installer&extension=language', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Templates'), 'link' => 'index2.php?option=com_installer&extension=template', 'img' => '../includes/js/ThemeOffice/installer.png');
						break;
					case 'module':
						$subMenuList[] = array ('title' => JText::_('Install'), 'link' => 'index2.php?option=com_installer&amp;task=installer', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Components'), 'link' => 'index2.php?option=com_installer&extension=component', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Modules'), 'link' => 'index2.php?option=com_installer&extension=module', 'img' => '../includes/js/ThemeOffice/installer.png', 'active' => 1);
						$subMenuList[] = array ('title' => JText::_('Plugins'), 'link' => 'index2.php?option=com_installer&extension=plugin', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Languages'), 'link' => 'index2.php?option=com_installer&extension=language', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Templates'), 'link' => 'index2.php?option=com_installer&extension=template', 'img' => '../includes/js/ThemeOffice/installer.png');
						break;
					case 'plugin':
						$subMenuList[] = array ('title' => JText::_('Install'), 'link' => 'index2.php?option=com_installer&amp;task=installer', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Components'), 'link' => 'index2.php?option=com_installer&extension=component', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Modules'), 'link' => 'index2.php?option=com_installer&extension=module', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Plugins'), 'link' => 'index2.php?option=com_installer&extension=plugin', 'img' => '../includes/js/ThemeOffice/installer.png', 'active' => 1);
						$subMenuList[] = array ('title' => JText::_('Languages'), 'link' => 'index2.php?option=com_installer&extension=language', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Templates'), 'link' => 'index2.php?option=com_installer&extension=template', 'img' => '../includes/js/ThemeOffice/installer.png');
						break;
					case 'language':
						$subMenuList[] = array ('title' => JText::_('Install'), 'link' => 'index2.php?option=com_installer&amp;task=installer', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Components'), 'link' => 'index2.php?option=com_installer&extension=component', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Modules'), 'link' => 'index2.php?option=com_installer&extension=module', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Plugins'), 'link' => 'index2.php?option=com_installer&extension=plugin', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Languages'), 'link' => 'index2.php?option=com_installer&extension=language', 'img' => '../includes/js/ThemeOffice/installer.png', 'active' => 1);
						$subMenuList[] = array ('title' => JText::_('Templates'), 'link' => 'index2.php?option=com_installer&extension=template', 'img' => '../includes/js/ThemeOffice/installer.png');
						break;
					case 'template':
						$subMenuList[] = array ('title' => JText::_('Install'), 'link' => 'index2.php?option=com_installer&amp;task=installer', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Components'), 'link' => 'index2.php?option=com_installer&extension=component', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Modules'), 'link' => 'index2.php?option=com_installer&extension=module', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Plugins'), 'link' => 'index2.php?option=com_installer&extension=plugin', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Languages'), 'link' => 'index2.php?option=com_installer&extension=language', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Templates'), 'link' => 'index2.php?option=com_installer&extension=template', 'img' => '../includes/js/ThemeOffice/installer.png', 'active' => 1);
						break;
					default:
						$subMenuList[] = array ('title' => JText::_('Install'), 'link' => 'index2.php?option=com_installer&amp;task=installer', 'img' => '../includes/js/ThemeOffice/installer.png', 'active' => 1);
						$subMenuList[] = array ('title' => JText::_('Components'), 'link' => 'index2.php?option=com_installer&extension=component', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Modules'), 'link' => 'index2.php?option=com_installer&extension=module', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Plugins'), 'link' => 'index2.php?option=com_installer&extension=plugin', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Languages'), 'link' => 'index2.php?option=com_installer&extension=language', 'img' => '../includes/js/ThemeOffice/installer.png');
						$subMenuList[] = array ('title' => JText::_('Templates'), 'link' => 'index2.php?option=com_installer&extension=template', 'img' => '../includes/js/ThemeOffice/installer.png');
						break;
				}
				$menu = JAdminSubMenu::buildList($subMenuList);
				break;

			case 'com_statistics' :
				$subMenuList = array();
				if ($enableStats) {
					$subMenuList[] = array ('title' => JText::_('Browser, OS, Domain'), 'link' => 'index2.php?option=com_statistics', 'img' => '../includes/js/ThemeOffice/globe4.png');
				}
				if ($enableSearches) {
					$subMenuList[] = array ('title' => JText::_('Search Text'), 'link' => 'index2.php?option=com_statistics&task=searches', 'img' => '../includes/js/ThemeOffice/search_text.png');
				}
				$menu = JAdminSubMenu::buildList($subMenuList);
				break;

			default :
				/*
				 * This is where we handle all third party components or
				 * otherwise unhandled components
				 */
				$query = "SELECT `id`" .
						"\n FROM `#__components`" .
						"\n WHERE `parent` = '0'" .
						"\n AND `option` = '$option'";
				$db->setQuery($query);
				$compid = $db->loadResult();

				if ($compid > 0) {
					$query = "SELECT *" .
							"\n FROM `#__components`" .
							"\n WHERE `parent` = '$compid'" .
							"\n ORDER BY `ordering`, `name`";
					$db->setQuery($query);
					$items = $db->loadObjectList();
				} else {
					$items = false;
				}

				/*
				 * Only process the data if we have menu items returned from the
				 * database query.
				 */
				if (is_array($items) && count($items)) {
					foreach ($items as $item)
					{
						if (trim($item->admin_menu_link)) {
							$subMenuList[] = array ('title' => JText::_($item->name), 'link' => 'index2.php?'.$item->admin_menu_link, 'img' => '../includes/'.$item->admin_menu_img);
						}
					}
					$menu = JAdminSubMenu::buildList($subMenuList);
				}
				break;
		}
		return $menu;
	}

	function buildList($list, $suffix = '-smenu')
	{

		if (!is_array($list) || !count($list)) {
			return null;
		}

		$hide = JRequest::getVar('hidemainmenu', 0);
		$txt = "<ul id=\"submenu\">\n";

		/*
		 * Iterate through the link items for building the menu items
		 */
		foreach ($list as $item)
		{
			$txt .= "<li class=\"item".$suffix."\">\n";
			if ($hide) {
				if (isset ($item['active']) && $item['active'] == 1) {
					$txt .= "<span class=\"nolink active\">".$item['title']."</span>\n";
				} else {
					$txt .= "<span class=\"nolink\">".$item['title']."</span>\n";
				}
			} else {
				if (isset ($item['active']) && $item['active'] == 1) {
					$txt .= "<a class=\"active\" href=\"".$item['link']."\">".$item['title']."</a>\n";
				} else {
					$txt .= "<a href=\"".$item['link']."\">".$item['title']."</a>\n";
				}
			}
			$txt .= "</li>\n";
		}

		$txt .= "</ul>\n";

		return $txt;
	}
}
?>

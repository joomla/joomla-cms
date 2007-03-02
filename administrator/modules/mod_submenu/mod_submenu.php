<?php
/**
* @version		$Id:mod_menu.php 2463 2006-02-18 06:05:38Z webImagery $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Lets get some variables we will need to render the menu
$lang	= & JFactory::getLanguage();
$doc	= & JFactory::getDocument();
$user	= & JFactory::getUser();

// If hidemainmenu is true, we don't want to render this module at all
echo JAdminSubMenu::get();

/**
 * Admin Submenu
 *
 * @package		Joomla
 * @since 1.5
 */
class JAdminSubMenu
{
	function get()
	{
		global $mainframe;

		// Lets get some variables we are going to need
		$menu 			= false;
		$db 			=& JFactory::getDBO();
		$lang 			=& JFactory::getLanguage();
		$user			=& JFactory::getUser();
		$enableSearches = $mainframe->getCfg('enable_log_searches');
		$option 		= JRequest::getVar('option');
		$task 			= JRequest::getVar('task');
		$act 			= JRequest::getVar('act');

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
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index.php?option=com_templates&client=0', 'img' => '../includes/js/ThemeOffice/template.png');
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index.php?option=com_templates&client=1', 'img' => '../includes/js/ThemeOffice/template.png', 'active' => 1);
				} elseif ($client == 0 && !$task) {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index.php?option=com_templates&client=0', 'img' => '../includes/js/ThemeOffice/template.png', 'active' => 1);
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index.php?option=com_templates&client=1', 'img' => '../includes/js/ThemeOffice/template.png');
				} else {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index.php?option=com_templates&client=0', 'img' => '../includes/js/ThemeOffice/template.png');
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index.php?option=com_templates&client=1', 'img' => '../includes/js/ThemeOffice/template.png');
				}

				$menu = JAdminSubMenu::_buildList($subMenuList);
				break;

			case 'com_banners' :
				$c	= JRequest::getVar('c');

				if($c == 'client') {
					$subMenuList[] = array('title' => JText::_('Banners'), 'link' => 'index.php?option=com_banners', 'img' => '' );
					$subMenuList[] = array('title' => JText::_('Clients'), 'link' => 'index.php?option=com_banners&c=client', 'img' => '', 'active' => 1 );
					$subMenuList[] = array('title' => JText::_('Categories'), 'link' => 'index.php?option=com_categories&section=com_banner', 'img' => '' );
				} else {
					$subMenuList[] = array('title' => JText::_('Banners'), 'link' => 'index.php?option=com_banners', 'img' => '', 'active' => 1 );
					$subMenuList[] = array('title' => JText::_('Clients'), 'link' => 'index.php?option=com_banners&c=client', 'img' => '' );
					$subMenuList[] = array('title' => JText::_('Categories'), 'link' => 'index.php?option=com_categories&section=com_banner', 'img' => '' );
				}

				$menu = JAdminSubMenu::_buildList($subMenuList);
				break;

			case 'com_languages' :
				$client	= JRequest::getVar('client', 0, '', 'int');
				if ($client == 1) {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => '#" onclick="javascript:document.adminForm.client.value=\'0\';submitbutton(\'\');', 'img' => '../includes/js/ThemeOffice/language.png');
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => '#" onclick="javascript:document.adminForm.client.value=\'1\';submitbutton(\'\');', 'img' => '../includes/js/ThemeOffice/language.png', 'active' => 1);
				} else {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => '#" onclick="javascript:document.adminForm.client.value=\'0\';submitbutton(\'\');', 'img' => '../includes/js/ThemeOffice/language.png', 'active' => 1);
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => '#" onclick="javascript:document.adminForm.client.value=\'1\';submitbutton(\'\');', 'img' => '../includes/js/ThemeOffice/language.png');
				}
				$menu = JAdminSubMenu::_buildList($subMenuList);
				break;

			case 'com_modules' :
				$client	= JRequest::getVar('client', 0, '', 'int');
				if ($client == 1) {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index.php?option=com_modules&client_id=0', 'img' => '../includes/js/ThemeOffice/module.png');
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index.php?option=com_modules&client=1', 'img' => '../includes/js/ThemeOffice/module.png', 'active' => 1);
				} else {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index.php?option=com_modules&client_id=0', 'img' => '../includes/js/ThemeOffice/module.png', 'active' => 1);
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index.php?option=com_modules&client=1', 'img' => '../includes/js/ThemeOffice/module.png');
				}
				//$subMenuList[] = array ('title' => JText::_('Manage Positions'), 'link' => 'index.php?option=com_templates&task=positions', 'img' => '../includes/js/ThemeOffice/preview.png');
				$menu = JAdminSubMenu::_buildList($subMenuList);
				break;

			case 'com_cache' :
				$client	= JRequest::getVar('client', 0, '', 'int');
				if ($client == 1) {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index.php?option=com_cache&client=0', 'img' => '../includes/js/ThemeOffice/cache.png');
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index.php?option=com_cache&client=1', 'img' => '../includes/js/ThemeOffice/cache.png', 'active' => 1);
				} else {
					$subMenuList[] = array ('title' => JText::_('Site'), 'link' => 'index.php?option=com_cache&client=0', 'img' => '../includes/js/ThemeOffice/cache.png', 'active' => 1);
					$subMenuList[] = array ('title' => JText::_('Administrator'), 'link' => 'index.php?option=com_cache&client=1', 'img' => '../includes/js/ThemeOffice/cache.png');
				}
				//$subMenuList[] = array ('title' => JText::_('Manage Positions'), 'link' => 'index.php?option=com_templates&task=positions', 'img' => '../includes/js/ThemeOffice/preview.png');
				$menu = JAdminSubMenu::_buildList($subMenuList);
				break;

			case 'com_installer' :
				$ext	= JRequest::getVar('type');

				$subMenus = array(
					'Components' => 'components',
					'Modules' => 'modules',
					'Plugins' => 'plugins',
					'Languages' => 'languages',
					'Templates' => 'templates',
				);

				$subMenuItem['title']	= JText::_( 'Install' );
				$subMenuItem['link']	= '#" onclick="javascript:document.adminForm.type.value=\'\';submitbutton(\'installer\');';
				$subMenuItem['img']		= '../includes/js/ThemeOffice/installer.png';
				$subMenuItem['active']	= !in_array( $ext, $subMenus);
				$subMenuList[] = $subMenuItem;

				foreach ($subMenus as $name => $extension)
				{
					$subMenuItem['title']	= JText::_( $name );
					$subMenuItem['link']	= '#" onclick="javascript:document.adminForm.type.value=\''.$extension.'\';submitbutton(\'manage\');';
					$subMenuItem['img']		= '../includes/js/ThemeOffice/installer.png';
					$subMenuItem['active']	= ($extension == $ext);
					$subMenuList[] = $subMenuItem;
				}

				$menu = JAdminSubMenu::_buildList($subMenuList);
				break;

			case 'com_categories' :
				$section = JRequest::getVar('section');

				if ($section) {
					if ($section != 'content') {
						// special handling for specific core components
						$map['com_contact_details']	= 'com_contact';
						$map['com_banner']			= 'com_banners';

						$componentOption = isset( $map[$section] ) ? $map[$section] : $section;

						$subMenuList = JAdminSubMenu::_getComponentSubMenus( $componentOption );
						$menu = JAdminSubMenu::_buildList( $subMenuList );
					}
				}
				break;

			default :
				// This is where we handle all third party components
				//or otherwise unhandled components

				$subMenuList = JAdminSubMenu::_getComponentSubMenus( $option );
				$menu = JAdminSubMenu::_buildList( $subMenuList );

				break;
		}
		return $menu;
	}

	/**
	 * Gets the component submenu items
	 * @param string The option of the parent
	 * @return array
	 * @access protected
	 */
	function _getComponentSubMenus( $componentOption )
	{
		$db = & JFactory::getDBO();

		$query = 'SELECT a.name, a.admin_menu_link, a.admin_menu_img' .
		' FROM #__components AS a' .
		' INNER JOIN #__components AS b ON b.id = a.parent' .
		' WHERE b.option = ' . $db->Quote( $componentOption ) .
		' AND b.parent = 0'.
		' ORDER BY a.ordering ASC';

		$db->setQuery($query);
		$items = $db->loadObjectList();

		// Process the items
		$subMenuList = array();

		foreach ($items as $item) {
			if (trim($item->admin_menu_link)) {
				// handling for active sub menu item
				$active = 0;
				if (strpos( @$_SERVER['QUERY_STRING'], $item->admin_menu_link ) !== false ) {
					$active = 1;
				}

				$subMenuItem['title']	= JText::_( $item->name );
				$subMenuItem['link']	= 'index.php?'. $item->admin_menu_link;
				$subMenuItem['img']		= '../includes/'.$item->admin_menu_img;
				$subMenuItem['active']	= $active;

				$subMenuList[] = $subMenuItem;
			}
		}

		return $subMenuList;
	}

	/**
	 * Builds the submenu list
	 * @param array		An array of menu items
	 * @return string	The HTML for the submenu
	 * @access protected
	 */
	function _buildList($list)
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
			$txt .= "<li>\n";
			if ($hide)
			{
				if (isset ($item['active']) && $item['active'] == 1)
				{
					$txt .= "<span class=\"nolink active\">".$item['title']."</span>\n";
				}
				else
				{
					$txt .= "<span class=\"nolink\">".$item['title']."</span>\n";
				}
			}
			else
			{
				if (isset ($item['active']) && $item['active'] == 1)
				{
					$txt .= "<a class=\"active\" href=\"".$item['link']."\">".$item['title']."</a>\n";
				}
				else
				{
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
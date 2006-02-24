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
$lang		= & $mainframe->getLanguage();
$doc		= & $mainframe->getDocument();
$user	= & $mainframe->getUser();
$hide	= JRequest::getVar('hidemainmenu', 0);

/*
 * If hidemainmenu is true, we don't want to render this module at all
 */
if (!$hide)
{
	$contents = JAdminSubMenu::get();

	/*
	 * Only show the module if there are items to actually show
	 */
	if ($contents)
	{
		echo "<div class=\"submenu-box\">\n<div class=\"submenu-pad\">\n";
		echo "$contents\n";
		echo "<div class=\"clr\"></div>\n";
		echo "</div></div>";
	}
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
		$menu					= false;
		$lang						= & $mainframe->getLanguage();
		$user					= & $mainframe->getUser();
		$db						= & $mainframe->getDBO();
		$enableStats			= $mainframe->getCfg('enable_stats');
		$enableSearches	= $mainframe->getCfg('enable_log_searches');
		$option					= JRequest::getVar('option');
		$task						= JRequest::getVar('task');
		$act						= JRequest::getVar('act');

		/*
		 * If there is no option set, then we obviously have no submenu to view
		 * so return false
		 */
		if (empty($option))
		{
			return false;
		}
		
		/*
		 * Basically this module is a big switch statement.... this way we only display menu
		 * items that are relevant to the option/task request
		 */
		switch ($option)
		{
			case 'com_messages' :
				$subMenuList[] = array ('title' => JText::_('Inbox'), 'link' => 'index2.php?option=com_messages', 'img' => '../includes/js/ThemeOffice/messaging_inbox.png');
				$subMenuList[] = array ('title' => JText::_('Configuration'), 'link' => 'index2.php?option=com_messages&task=config&hidemainmenu=1', 'img' => '../includes/js/ThemeOffice/messaging_config.png');
				$menu = JAdminSubMenu::buildList($subMenuList);
				break;

			case 'com_templates' :
				$subMenuList[] = array ('title' => JText::_('Site Templates'), 'link' => 'index2.php?option=com_templates&client=0', 'img' => '../includes/js/ThemeOffice/template.png');
				$subMenuList[] = array ('title' => JText::_('Administrator Templates'), 'link' => 'index2.php?option=com_templates&client=1', 'img' => '../includes/js/ThemeOffice/template.png');
				$subMenuList[] = array ('title' => JText::_('Module Positions'), 'link' => 'index2.php?option=com_templates&task=positions', 'img' => '../includes/js/ThemeOffice/template.png');
				$subMenuList[] = array ('title' => JText::_('Preview'), 'link' => 'index2.php?option=com_admin&task=preview', 'img' => '../includes/js/ThemeOffice/preview.png');
				$menu = JAdminSubMenu::buildList($subMenuList);
				break;

			case 'com_languages' :
				$subMenuList[] = array ('title' => JText::_('Site Languages'), 'link' => 'index2.php?option=com_languages&client=site', 'img' => '../includes/js/ThemeOffice/language.png');
				$subMenuList[] = array ('title' => JText::_('Administrator Languages'), 'link' => 'index2.php?option=com_languages&client=administrator', 'img' => '../includes/js/ThemeOffice/language.png');
				$menu = JAdminSubMenu::buildList($subMenuList);
				break;

			case 'com_statistics' :
				if ($enableStats)
				{
					$subMenuList[] = array ('title' => JText::_('Browser, OS, Domain'), 'link' => 'index2.php?option=com_statistics', 'img' => '../includes/js/ThemeOffice/globe4.png');
				}
				if ($enableSearches)
				{
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

				if ($compid > 0)
				{
					$query = "SELECT *" .
							"\n FROM `#__components`" .
							"\n WHERE `parent` = '$compid'" .
							"\n ORDER BY `ordering`, `name`";
					$db->setQuery($query);
					$items = $db->loadObjectList();
				} else
				{
					$items = false;
				}

				/*
				 * Only process the data if we have menu items returned from the
				 * database query.
				 */
				if (is_array($items) && count($items))
				{
					foreach ($items as $item)
					{
						if (trim($item->admin_menu_link))
						{
							$subMenuList[] = array ('title' => JText::_($item->name), 'link' => 'index2.php?'.$item->admin_menu_link, 'img' => '../includes/'.$row->admin_menu_img);
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

		if (!is_array($list) || !count($list))
		{
			return null;
		}
		
		$txt = "<ul>\n";

		/*
		 * Iterate through the link items for building the menu items
		 */
		foreach ($list as $item)
		{
			if (isset ($item['active']) && $item['active'] == 1)
			{
				$sfx = $suffix.'_active';
			}
			else
			{
				$sfx = $suffix;
			}
			$txt .= "<li class=\"item".$sfx."\">\n";
			$txt .= "<a href=\"".$item['link']."\">".$item['title']."</a>\n";
			$txt .= "</li>\n";
		}

		$txt .= "</ul>\n";

		return $txt;
	}
}
?>

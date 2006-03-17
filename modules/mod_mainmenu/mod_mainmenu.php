<?php
/**
* @version $Id$
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

if (!defined('_MOS_MAINMENU_MODULE'))
{
	/** ensure that functions are declared only once */
	define('_MOS_MAINMENU_MODULE', 1);

	/**
	* Utility function for writing a menu link
	*/
	function mosGetMenuLink($mitem, $level = 0, & $params, $open = null)
	{
		global $Itemid, $mainframe;
		$txt = '';

		switch ($mitem->type)
		{
			case 'separator' :
			case 'component_item_link' :
				break;
			case 'content_item_link' :
				$temp = split("&task=view&id=", $mitem->link);
				$cache = JFactory :: getCache('getItemid');
				require_once (JApplicationHelper::getPath('front', 'com_content'));
				
				$_Itemid = $cache->call('JContentHelper::getItemid', $temp[1]);
				$mitem->link .= '&Itemid='.$_Itemid;
				break;
			case 'url' :
				if (eregi('index.php\?', $mitem->link))
				{
					if (!eregi('Itemid=', $mitem->link))
					{
						$mitem->link .= '&Itemid='.$mitem->id;
					}
				}
				break;
			case 'content_typed' :
			default :
				$mitem->link .= '&Itemid='.$mitem->id;
				break;
		}

		// Active Menu highlighting
		$current_itemid = $Itemid;
		if (!$current_itemid)
		{
			$id = '';
		}
		else
			if ($current_itemid == $mitem->id)
			{
				$id = 'id="active_menu'.$params->get('class_sfx').'"';
			}
			else
				if ($params->get('activate_parent') && isset ($open) && in_array($mitem->id, $open))
				{
					$id = 'id="active_menu'.$params->get('class_sfx').'"';
				}
				else
					if ($mitem->type == 'url' && ItemidContained($mitem->link, $current_itemid))
					{
						$id = 'id="active_menu'.$params->get('class_sfx').'"';
					}
					else
					{
						$id = '';
					}

		$mitem->link = ampReplace($mitem->link);

		$menu_params = new stdClass();
		$menu_params = & new JParameter($mitem->params);
		$menu_secure = $menu_params->def('secure', 0);

		if (strcasecmp(substr($mitem->link, 0, 4), 'http'))
		{
			$mitem->link = josURL($mitem->link, $menu_secure);
		}

		$menuclass = 'mainlevel'.$params->get('class_sfx');
		if ($level > 0)
		{
			$menuclass = 'sublevel'.$params->get('class_sfx');
		}

		$mitem->name = stripslashes(ampReplace($mitem->name));

		switch ($mitem->browserNav)
		{
			// cases are slightly different
			case 1 :
				// open in a new window
				$txt = '<a href="'.$mitem->link.'" target="_blank" class="'.$menuclass.'" '.$id.'>'.$mitem->name.'</a>';
				break;

			case 2 :
				// open in a popup window
				$txt = "<a href=\"#\" onclick=\"javascript: window.open('".$mitem->link."', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\" class=\"$menuclass\" ".$id.">".$mitem->name."</a>\n";
				break;

			case 3 :
				// don't link it
				$txt = '<span class="'.$menuclass.'" '.$id.'>'.$mitem->name.'</span>';
				break;

			default : // formerly case 2
				// open in parent window
				$txt = '<a href="'.$mitem->link.'" class="'.$menuclass.'" '.$id.'>'.$mitem->name.'</a>';
				break;
		}

		if ($params->get('menu_images'))
		{
			$menu_params = new stdClass();
			$menu_params = new JParameter($mitem->params);
			$menu_image = $menu_params->def('menu_image', -1);
			if (($menu_image <> '-1') && $menu_image)
			{
				$image = '<img src="images/stories/'.$menu_image.'" border="0" alt="'.$mitem->name.'"/>';
				if ($params->get('menu_images_align'))
				{
					$txt = $txt.' '.$image;
				}
				else
				{
					$txt = $image.' '.$txt;
				}
			}
		}

		return $txt;
	}

	/**
	* Vertically Indented Menu
	*/
	function mosShowVIMenu(& $params)
	{
		global $database, $mainframe, $Itemid;
		global $mosConfig_shownoauth;

		$template = $mainframe->getTemplate();
		$menu     =& JMenu::getInstance();
		$user 	  =& $mainframe->getUser();

		// indent icons
		switch ($params->get('indent_image'))
		{
			case '1' :
			{
				// Default images
				$imgpath = 'images/M_images';
				for ($i = 1; $i < 7; $i ++) {
					$img[$i] = '<img src="'.$imgpath.'/indent'.$i.'.png" alt="" />';
				}
			} break;
			
			case '2' :
			{
				// Use Params
				$imgpath = 'images/M_images';
				for ($i = 1; $i < 7; $i ++)
				{
					if ($params->get('indent_image'.$i) == '-1') {
						$img[$i] = NULL;
					} else {
						$img[$i] = '<img src="'.$imgpath.'/'.$params->get('indent_image'.$i).'" alt="" />';
					}
				}
			} break;
			
			case '3' :
			{
				// None
				for ($i = 1; $i < 7; $i ++) {
					$img[$i] = NULL;
				}
			} break;
			
			default :
			{
				// Template
				$imgpath = 'templates/'.$template.'/images';
				for ($i = 1; $i < 7; $i ++) {
					$img[$i] = '<img src="'.$imgpath.'/indent'.$i.'.png" alt="" />';
				}
			}
		}

		$indents = array (
			// block prefix / item prefix / item suffix / block suffix
			array ('<table width="100%" border="0" cellpadding="0" cellspacing="0">', '<tr ><td>', '</td></tr>', '</table>'), 
			array ('', '<div style="padding-left: 4px">'.$img[1], '</div>', ''), 
			array ('', '<div style="padding-left: 8px">'.$img[2], '</div>', ''), 
			array ('', '<div style="padding-left: 12px">'.$img[3], '</div>', ''), 
			array ('', '<div style="padding-left: 16px">'.$img[4], '</div>', ''), 
			array ('', '<div style="padding-left: 20px">'.$img[5], '</div>', ''), 
			array ('', '<div style="padding-left: 24px">'.$img[6], '</div>', ''),
		);

		// establish the hierarchy of the menu
		$children = array ();
		
		//get menu items
		$rows = $menu->getItems('menutype', $params->get('menutype'));
		
		// first pass - collect children
		foreach ($rows as $v)
		{
			if($v->access <= $user->get('gid'))
			{
				$pt = $v->parent;
				$list = @ $children[$pt] ? $children[$pt] : array ();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		// second pass - collect 'open' menus
		$open = array ($Itemid);
		$count = 20; // maximum levels - to prevent runaway loop
		$id = $Itemid;
		
		while (-- $count)
		{
			if (isset ($rows[$id]) && $rows[$id]->parent > 0) {
				$id = $rows[$id]->parent;
				$open[] = $id;
			} else {
				break;
			}
		}
		
		mosRecurseVIMenu(0, 0, $children, $open, $indents, $params);
	}

	/**
	* Utility function to recursively work through a vertically indented
	* hierarchial menu
	*/
	function mosRecurseVIMenu($id, $level, & $children, & $open, & $indents, & $params)
	{
		global $Itemid;
		
		if (@ $children[$id])
		{
			$n = min($level, count($indents) - 1);

			echo "\n".$indents[$n][0];
			foreach ($children[$id] as $row)
			{

				echo "\n".$indents[$n][1];

				echo mosGetMenuLink($row, $level, $params, $open);

				// show menu with menu expanded - submenus visible
				if (!$params->get('expand_menu'))
				{
					if (in_array($row->id, $open))
					{
						mosRecurseVIMenu($row->id, $level +1, $children, $open, $indents, $params);
					}
				}
				else
				{
					mosRecurseVIMenu($row->id, $level +1, $children, $open, $indents, $params);
				}
				echo $indents[$n][2];
			}
			echo "\n".$indents[$n][3];
		}
	}

	/**
	* Draws a horizontal 'flat' style menu (very simple case)
	*/
	function mosShowHFMenu(& $params, $style = 0)
	{
		global $database, $mainframe, $Itemid;
		global $mosConfig_shownoauth;
		
		$menu     =& JMenu::getInstance();
		$user 	  =& $mainframe->getUser();

		//get menu items
		$rows = $menu->getItems('menutype', $params->get('menutype'));

		$links = array ();
		foreach ($rows as $row) {
			if($v->access <= $user->get('gid')) {
				$links[] = mosGetMenuLink($row, 0, $params);
			}
		}

		$menuclass = 'mainlevel'.$params->get('class_sfx');
		
		if (count($links))
		{
			switch ($style)
			{
				case 1 :
					echo '<ul id="'.$menuclass.'">';
					foreach ($links as $link)
					{
						echo '<li>'.$link.'</li>';
					}
					echo '</ul>';
					break;
				default :
					echo '<table width="100%" border="0" cellpadding="0" cellspacing="1">';
					echo '<tr>';
					echo '<td nowrap="nowrap">';
					echo '<span class="'.$menuclass.'"> '.$params->get('end_spacer').' </span>';
					echo implode('<span class="'.$menuclass.'"> '.$params->get('spacer').' </span>', $links);
					echo '<span class="'.$menuclass.'"> '.$params->get('end_spacer').' </span>';
					echo '</td></tr>';
					echo '</table>';
					break;
			}
		}
	}

	/**
	* Search for Itemid in link
	*/
	function ItemidContained($link, $Itemid)
	{
		$link = str_replace('&amp;', '&', $link);
		$temp = split("&", $link);
		$linkItemid = "";
		foreach ($temp as $value)
		{
			$temp2 = split("=", $value);
			if ($temp2[0] == "Itemid")
			{
				$linkItemid = $temp2[1];
				break;
			}
		}
		if ($linkItemid != "" && $linkItemid == $Itemid)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

}
$params->def('menutype', 'mainmenu');
$params->def('class_sfx', '');
$params->def('menu_images', 0);
$params->def('menu_images_align', 0);
$params->def('expand_menu', 0);
$params->def('activate_parent', 0);
$params->def('indent_image', 0);
$params->def('indent_image1', 'indent1.png');
$params->def('indent_image2', 'indent2.png');
$params->def('indent_image3', 'indent3.png');
$params->def('indent_image4', 'indent4.png');
$params->def('indent_image5', 'indent5.png');
$params->def('indent_image6', 'indent.png');
$params->def('spacer', '');
$params->def('end_spacer', '');

$menu_style = $params->get('menu_style', 'vert_indent');

switch ($menu_style)
{
	case 'list_flat' :
		mosShowHFMenu($params, 1);
		break;

	case 'horiz_flat' :
		mosShowHFMenu($params, 0);
		break;

	default :
		mosShowVIMenu($params);
		break;
}
?>
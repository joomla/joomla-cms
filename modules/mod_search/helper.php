<?php
/**
* @version		$Id$
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

class modSearchHelper
{
	function renderInputField($params)
	{
		$button				= $params->get('button', '');
		$imagebutton		= $params->get('imagebutton', '');
		$button_pos			= $params->get('button_pos', 'left');
		$button_text		= $params->get('button_text', JText::_('Search'));
		$width				= intval($params->get('width', 20));
		$text				= $params->get('text', JText::_('search...'));
		$set_Itemid			= intval($params->get('set_itemid', 0));
		$moduleclass_sfx	= $params->get('moduleclass_sfx', '');

		$output = '<input name="searchword" id="mod_search_searchword" maxlength="20" alt="'.$button_text.'" class="inputbox'.$moduleclass_sfx.'" type="text" size="'.$width.'" value="'.$text.'"  onblur="if(this.value==\'\') this.value=\''.$text.'\';" onfocus="if(this.value==\''.$text.'\') this.value=\'\';" />';

		if ($button)
		{
			if ($imagebutton)
			{
				$img = JHTML::_('image.site', 'searchButton.gif', '/images/M_images/', NULL, NULL, $button_text, $button_text, 0);
				$button = '<input type="image" value="'.$button_text.'" class="button'.$moduleclass_sfx.'" src="'.$img.'"/>';
			}
			else
			{
				$button = '<input type="submit" value="'.$button_text.'" class="button'.$moduleclass_sfx.'"/>';
			}
		}

		switch ($button_pos)
		{
			case 'top' :
				$button = $button.'<br/>';
				$output = $button.$output;
				break;

			case 'bottom' :
				$button = '<br/>'.$button;
				$output = $output.$button;
				break;

			case 'right' :
				$output = $output.$button;
				break;

			case 'left' :
			default :
				$output = $button.$output;
				break;
		}

		return $output;
	}

	function getItemid()
	{
		// set Itemid id for links
		$menu = &JMenu::getInstance();
		$items	= $menu->getItems('link', 'index.php?option=com_search');
			
		if(isset($items[0])) {
			$itemid = $items[0]->id;
		} else {
			$default = $menu->getDefault();
			$itemid = $default->id;
		}
	
		return $itemid;
	}
}

<?php
/**
* @version $Id: mod_search.php 4184 2006-07-08 01:08:35Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class JModSearchController extends JController
{
	var $params;

	function display()
	{
		$button				= $this->params->get('button', '');
		$imagebutton		= $this->params->get('imagebutton', '');
		$button_pos			= $this->params->get('button_pos', 'left');
		$button_text		= $this->params->get('button_text', JText::_('Search'));
		$width				= intval($this->params->get('width', 20));
		$text				= $this->params->get('text', JText::_('search...'));
		$moduleclass_sfx	= $this->params->get('moduleclass_sfx');
		$set_Itemid			= intval($this->params->get('set_itemid', 0));
		
		$output = '<input name="searchword" id="mod_search_searchword" maxlength="20" alt="'.$button_text.'" class="inputbox'.$moduleclass_sfx.'" type="text" size="'.$width.'" value="'.$text.'"  onblur="if(this.value==\'\') this.value=\''.$text.'\';" onfocus="if(this.value==\''.$text.'\') this.value=\'\';" />';
		
		if ($button)
		{
			if ($imagebutton)
			{
				$img = mosAdminMenus::ImageCheck('searchButton.gif', '/images/M_images/', NULL, NULL, $button_text, $button_text, 0);
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
		
		// set Itemid id for links
		if ($set_Itemid)
		{
			// use param setting
			$_Itemid = $set_Itemid;
			$link = 'index.php?option=com_search&amp;Itemid='.$set_Itemid;
		}
		else
		{
			$menu = &JMenu::getInstance();
			$items	= $menu->getMenu();
		
			$_Itemid = null;
			foreach ($items as $item)
			{
				if ($item->link == "index.php?option=com_search")
				{
					$_Itemid = '&amp;Itemid='.$item->id;
				}
			}
		
			$link = 'index.php?option=com_search'.$_Itemid;
		}
		
		?>
<form action="<?php echo $link; ?>" method="get">
	<div class="search<?php echo $moduleclass_sfx; ?>">
		<?php echo $output; ?>
	</div>

	<input type="hidden" name="option" value="com_search" />
	<input type="hidden" name="Itemid" value="<?php echo $_Itemid; ?>" />
</form>
		<?php
	}
}
?>
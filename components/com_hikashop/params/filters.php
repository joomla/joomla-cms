<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class JElementFilters extends JElement
{
	function fetchElement($name, $value, &$node, $control_name)
	{
		JHTML::_('behavior.modal','a.modal');
		$link = 'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=choose&amp;task=filters&amp;values='.$value.'&amp;control='.$control_name;
		$text = '<input class="inputbox" id="'.$control_name.$name.'" name="'.$control_name.'['.$name.']" type="text" size="20" value="'.$value.'">';
		$text .= '<a class="modal" id="link'.$control_name.$name.'" title="Filters"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button class="btn" onclick="return false">Select</button></a>';
		return $text;
	}
}

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
class JFormFieldSearchfields extends JFormField
{
	var $type = 'help';
	function getInput() {
		JHTML::_('behavior.modal','a.modal');
		$link = 'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=choose&amp;task=searchfields&amp;values='.$this->value.'&amp;control=';
		$text = '<input class="inputbox" id="fields" name="'.$this->name.'" type="text" size="20" value="'.$this->value.'">';
		$text .= '<a class="modal" id="linkfields" title="Fields"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button class="btn" onclick="return false">Select</button></a>';
		return $text;
	}
}

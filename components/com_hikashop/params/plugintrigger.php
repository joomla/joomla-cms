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
class JElementPlugintrigger extends JElement{
	function fetchElement($name, $value, &$node, $control_name){
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			return 'This plugin can not work without the Hikashop Component';
		}

		$id = hikashop_getCID('id');
		if(!empty($id)){
			$text = '<a title="'.JText::_('Trigger').'"  href="'.JRoute::_('index.php?option=com_hikashop&ctrl=plugins&task=trigger&function='.$value.'&cid='.$id.'&'.hikashop_getFormToken().'=1').'" >'.JText::_('Trigger').'</a>';
		}

		return $text;
	}
}

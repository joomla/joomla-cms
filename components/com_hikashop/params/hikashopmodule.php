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
class JElementHikashopmodule extends JElement{
	function fetchElement($name, $value, &$node, $control_name){
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			return 'This module can not work without the Hikashop Component';
		}
		$config =& hikashop_config();
		if(!hikashop_isAllowed($config->get('acl_modules_manage','all'))){
			return 'Access to the HikaShop options of the modules is restricted';
		}

		$id = hikashop_getCID('id');
		if(!empty($id)){
			$app = JFactory::getApplication();
			if($app->isAmdin()){
				$link = JRoute::_('index.php?option=com_hikashop&ctrl=modules&task=edit&cid[]='.$id);
			}else{
				$link = JURI::base().'administrator/index.php?option=com_hikashop&ctrl=modules&task=edit&cid[]='.$id;
			}
			$text = '<a title="'.JText::_('HIKASHOP_OPTIONS').'"  href="'.$link.'" >'.JText::_('HIKASHOP_OPTIONS').'</a>';
			$config =& hikashop_config();
			$level = $config->get('params_'.$id);
		}else{
			$text = JText::_('HIKASHOP_OPTIONS_EDIT');
		}

		return $text;
	}
}

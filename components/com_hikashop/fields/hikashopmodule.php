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
class JFormFieldHikashopmodule extends JFormField{

	protected $type = 'hikashopmodule';

	protected function getInput() {
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!function_exists('hikashop_config') && !include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			return 'This module can not work without the Hikashop Component';
		}

		$config =& hikashop_config();
		$id = JRequest::getInt('id');
		if(HIKASHOP_J30 && !in_array(@$_REQUEST['option'],array('com_falang','com_joomfish'))){
			if(preg_match('/hikashopmodule/',$this->name)){
				$associated = false;
				$cid = JRequest::getVar('id','');
				if(empty($cid))
					$cid = hikashop_getCID();
				foreach($config->values as $name => $values){
					if(preg_match('#menu_[0-9]#',$name)){
						$params = unserialize(base64_decode($values->config_value));
						$modules = array();
						if(isset($params['modules']))
							$modules = explode(',',$params['modules']);
						if(in_array($cid,$modules)){
							$associated = str_replace('menu_','',$values->config_namekey);
							break;
						}
					}
				}
				if($associated){
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::sprintf('USE_MENU_SETTINGS_INSTEAD_OF_ASSOCIATED_ONES',JRoute::_('index.php?option=com_menus&view=item&layout=edit&id='.$associated)));
				}
				$layout = 'modules';
			}else{
				$layout = 'cartmodules';
			}
			$empty='';
			jimport('joomla.html.parameter');
			$params = new HikaParameter($empty);
			$js = '';
			$params->set('id',$this->id);
			$params->set('name',$this->name);
			$params->set('value',$this->value);
			$content = hikashop_getLayout($layout,'options',$params,$js,true);
			$text = '</div></div>'.$content.'<div><div>';
		}elseif(!empty($id)){
			if(!hikashop_isAllowed($config->get('acl_modules_manage','all'))){
				return 'Access to the HikaShop options of the modules is restricted';
			}
			$text = '<a style="float:left;" title="'.JText::_('HIKASHOP_OPTIONS').'"  href="'.JRoute::_('index.php?option=com_hikashop&ctrl=modules&fromjoomla=1&task=edit&cid[]='.$id).'" >'.JText::_('HIKASHOP_OPTIONS').'</a>';
		}else{
			$text = JText::_('HIKASHOP_OPTIONS_EDIT');
		}
		return $text;
	}
}

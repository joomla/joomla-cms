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
class JFormFieldPluginoptions extends JFormField{
	protected $type = 'pluginoptions';

	protected function getInput() {
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			return 'This plugin can not work without the Hikashop Component';
		}
		$id = JRequest::getInt('extension_id');
		$plugins = hikashop_get('class.plugins');
		$plugin = $plugins->get($id);
		$name = @$plugin->element;
		if(@$plugin->folder=='hikashopshipping'){
			$group = 'shipping';
		}elseif(@$plugin->folder=='hikashop'){
			$group = 'plugin';
		} else {
			$group = 'payment';
		}
		$config =& hikashop_config();
		if(!hikashop_isAllowed($config->get('acl_plugins_manage','all'))){
			return 'Access to the HikaShop options of the plugins is restricted';
		}

		$text = '<a style="float:left;" title="'.JText::_('HIKASHOP_OPTIONS').'"  href="'.JRoute::_('index.php?option=com_hikashop&ctrl=plugins&fromjoomla=1&task=listing&name='.$name.'&plugin_type='.$group).'" >'.JText::_('HIKASHOP_OPTIONS').'</a>';
		return $text;
	}
}


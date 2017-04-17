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
class hikashopItemType{
	function loadFromCustom($hikashopFiles, $template, $customDir, $files) {
		if (is_dir($customDir)) {
			$customFiles = JFolder::files($customDir);
			if (!empty($customFiles)) {
				$files = array();
				foreach ($customFiles as $file) {
					$notHikashop = true;
					foreach ($hikashopFiles as $hikashopfile) {
						if ($hikashopfile == $file) {
							$notHikashop = false;
							break;
						}
					}
					if ($notHikashop) $files[] = $file;
				}
				if (!empty($files)) {
					$files = array_keys(array_flip($files));
					$this->loadValues('-- ' . JText::sprintf('FROM_TEMPLATE',basename($template)) . ' --', $files);
				}
			}
		}
	}

	function loadFromTemplates($hikashopFiles) {
		$files = array();
		$templates = JFolder::folders(JPATH_SITE . DS . 'templates', '.', false, true);
		if (!empty($templates)) {
			foreach ($templates as $template) {
				$this->loadFromCustom($hikashopFiles, $template, $template . DS . 'html' . DS . 'com_hikashop' . DS . 'product', $files);
				$this->loadFromCustom($hikashopFiles, $template, $template . DS . 'html' . DS . 'com_hikashop' . DS . 'category', $files);
			}
		}
	}

	function loadValues($optGroup, $files) {
		$this->values[] = JHTML::_('select.optgroup', $optGroup);
		foreach($files as $file){
			if(preg_match('#^listing_((?!div|list|price|table|vote).*)\.php$#',$file,$match)){
				$val = strtoupper($match[1]);
				$trans = JText::_($val);
				if($trans==$val){
					$trans=$match[1];
				}
				$this->values[$match[1]] = JHTML::_('select.option', $match[1], $trans);
			}
		}
		if(version_compare(JVERSION,'1.6.0','>=')){
			$this->values[] = JHTML::_('select.optgroup', $optGroup);
		}
		if(JRequest::getVar('inherit',true) == true) {
			$config = hikashop_config();
			$defaultParams = $config->get('default_params');
			$default = '';
			if(isset($defaultParams['div_item_layout_type']))
				$default = ' ('.$this->values[$defaultParams['div_item_layout_type']]->text.')';
			$this->values[] = JHTML::_('select.option', 'inherit', JText::_('HIKA_INHERIT').$default);
		}
	}

	function load(){
		$this->values = array();
		jimport('joomla.filesystem.folder');
		$product_folder = HIKASHOP_FRONT.'views'.DS.'product'.DS.'tmpl'.DS;
		$category_folder = HIKASHOP_FRONT.'views'.DS.'category'.DS.'tmpl'.DS;
		$files = JFolder::files($product_folder);
		$files = array_keys(array_merge(array_flip($files),array_flip(JFolder::files($category_folder))));
		$this->loadValues('-- '.JText::_('FROM_HIKASHOP').' --', $files);
		$this->loadFromTemplates($files);
	}

	function display($map,$value,&$js, $option=''){
		$this->load();
		$options = 'class="inputbox" size="1" '.$option;
		return JHTML::_('select.genericlist',   $this->values, $map, $options, 'value', 'text', $value );
	}
}

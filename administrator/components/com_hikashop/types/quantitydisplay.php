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
class hikashopQuantitydisplayType {
	var $default = array(
		'show_default',
		'show_regrouped',
		'show_select',
		'show_simple',
		'show_leftright',
		'show_simplified',
		'show_default_div'
	);

	function load(){
		$this->values = array();
		if(JRequest::getCmd('from_display',false) == false)
			$this->values[] = JHTML::_('select.option', '', JText::_('HIKA_INHERIT'));
		$this->values[] = JHTML::_('select.optgroup', '-- '.JText::_('FROM_HIKASHOP').' --');
		foreach($this->default as $d) {
			$this->values[] = JHTML::_('select.option', $d, JText::_(strtoupper($d)));
		}
		if(version_compare(JVERSION,'1.6.0','>=')){
			$this->values[] = JHTML::_('select.optgroup', '-- '.JText::_('FROM_HIKASHOP').' --');
		}

		$closeOpt = '';
		$values = $this->getLayout();
		foreach($values as $value) {
			if(substr($value,0,1) == '#') {
				if(version_compare(JVERSION,'1.6.0','>=') && !empty($closeOpt)){
					$this->values[] = JHTML::_('select.optgroup', $closeOpt);
				}
				$value = substr($value,1);
				$closeOpt = '-- ' . JText::sprintf('FROM_TEMPLATE',basename($value)) . ' --';
				$this->values[] = JHTML::_('select.optgroup', $closeOpt);
			} else {
				$this->values[] = JHTML::_('select.option', $value, $value);
			}
		}
		if(version_compare(JVERSION,'1.6.0','>=') && !empty($closeOpt)){
			$this->values[] = JHTML::_('select.optgroup', $closeOpt);
		}
	}

	function display($map,$value) {
		$this->load();
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}

	function getLayout($template = '') {
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		static $values = null;
		if($values !== null)
			return $values;
		$client	= JApplicationHelper::getClientInfo(0); // 0: Front client
		$tplDir = $client->path.DS.'templates'.DS;
		$values = array();
		if(empty($template)) {
			$templates = JFolder::folders($tplDir);
			if(empty($templates))
				return null;
		} else {
			$templates = array($template);
		}
		$groupAdded = false;
		foreach($templates as $tpl) {
			$t = $tplDir.$tpl.DS.'html'.DS.HIKASHOP_COMPONENT.DS;
			if(!JFolder::exists($t))
				continue;
			$folders = JFolder::folders($t);
			if(empty($folders))
				continue;
			foreach($folders as $folder) {
				$files = JFolder::files($t.$folder.DS);
				if(empty($files))
					continue;
				foreach($files as $file) {
					if(substr($file,-4) == '.php')
						$file = substr($file,0,-4);
					if(substr($file,0,14) == 'show_quantity_' && !in_array($file,$this->default)) {
						if(!$groupAdded) {
							$values[] = '#'.$tpl;
							$groupAdded = true;
						}
						$values[] = $file;
					}
				}
			}
		}
		return $values;
	}
	function check($name,$template) {
		if($name == '' || in_array($name, $this->default))
			return true;
		$values = $this->getLayout($template);
		return in_array($name,$values);
	}
}
?>

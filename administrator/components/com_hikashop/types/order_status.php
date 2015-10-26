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
class hikashopOrder_statusType{
	function __construct() {
		$this->values = array();
	}

	function load() {
		$class = hikashop_get('class.category');
		$rows = $class->loadAllWithTrans('status');
		foreach($rows as $row) {
			if(!empty($row->translation))
				$this->values[$row->category_name] = JHTML::_('select.option', $row->category_name, hikashop_orderStatus($row->translation));
			else
				$this->values[$row->category_name] = JHTML::_('select.option', $row->category_name, hikashop_orderStatus($row->category_name));
		}
	}

	function display($map, $value, $extra = '', $addAll = false) {
		if(empty($this->values))
			$this->load();
		if($addAll) {
			$values = array_merge(
				array(JHTML::_('select.option', '', JText::_('ALL_STATUSES'))),
				$this->values
			);
		} else {
			$values = $this->values;
		}
		return JHTML::_('select.genericlist', $values, $map, $extra, 'value', 'text', $value);
	}

	public function displayMultiple($map, $values) {
		if(empty($this->values))
			$this->load();

		if(empty($values))
			$values = array();
		else if(is_string($values))
			$values = explode(',', $values);

		$config = hikashop_config();
		hikashop_loadJslib('otree');

		if(substr($map,-2) == '[]')
			$map = substr($map,0,-2);
		$id = str_replace(array('[',']'),array('_',''),$map);
		$ret = '<div class="nameboxes" id="'.$id.'" onclick="window.oNameboxes[\''.$id.'\'].focus(\''.$id.'_text\');">';
		if(!empty($values)) {
			foreach($values as $key) {
				if(isset($this->values[$key]))
					$name = $this->values[$key]->text;
				else
					$name = JText::sprintf('UNKNOWN_PACK_X', $key);

				$ret .= '<div class="namebox" id="'.$id.'_'.$key.'">'.
					'<input type="hidden" name="'.$map.'[]" value="'.$key.'"/>'.$name.
					' <a class="closebutton" href="#" onclick="window.oNameboxes[\''.$id.'\'].unset(this,\''.$key.'\');window.oNamebox.cancelEvent();return false;"><span>X</span></a>'.
					'</div>';
			}
		}

		$ret .= '<div class="namebox" style="display:none;" id="'.$id.'tpl">'.
				'<input type="hidden" name="{map}" value="{key}"/>{name}'.
				' <a class="closebutton" href="#" onclick="window.oNameboxes[\''.$id.'\'].unset(this,\'{key}\');window.oNamebox.cancelEvent();return false;"><span>X</span></a>'.
				'</div>';

		$ret .= '<div class="nametext">'.
			'<input id="'.$id.'_text" type="text" style="width:50px;min-width:60px" onfocus="window.oNameboxes[\''.$id.'\'].focus(this);" onkeyup="window.oNameboxes[\''.$id.'\'].search(this);" onchange="window.oNameboxes[\''.$id.'\'].search(this);"/>'.
			'<span style="position:absolute;top:0px;left:-2000px;visibility:hidden" id="'.$id.'_span">span</span>'.
			'</div>';

		$data = array();
		foreach($this->values as $key => $value) {
			if(empty($key))
				continue;
			$data[$key] = $value->text;
		}

		$namebox_options = array(
			'mode' => 'list',
			'img_dir' => HIKASHOP_IMAGES,
			'map' => $map,
			'min' => $config->get('namebox_search_min_length', 3),
			'multiple' => true
		);

		$ret .= '<div style="clear:both;float:none;"></div></div>
<div class="namebox-popup">
	<div id="'.$id.'_olist" style="display:none;" class="oList namebox-popup-content"></div>
</div>
<script type="text/javascript">
new window.oNamebox(
	\''.$id.'\',
	'.json_encode($data).',
	'.json_encode($namebox_options).'
);';
			if(!empty($values)) {
				$ret .= '
try{
	window.oNameboxes[\''.$id.'\'].content.block('.json_encode($values).');
}catch(e){}';
			}

			$ret .= '
</script>';
		return $ret;
	}
}

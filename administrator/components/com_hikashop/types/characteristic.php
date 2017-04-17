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
class hikashopCharacteristicType {
	var $characteristics=false;
	var $options = '';
	function load(){
		$ids = array_keys($this->characteristics);
		$list = '[\''.implode('\',\'',$ids).'\']';
		$js = '
		function hikashopUpdateVariant(obj){
			var options = '.$list.';
			var len = options.length;
			var selection = \'\';
			var found=false;
			if(typeof(obj) == "string")
				obj = document.getElementById(obj);
			if(!obj) return true;
			try { obj.blur(); } catch(e){}
			for (var i = 0; i < len; i++){
				if(document.getElementById(\'hikashop_product_characteristic_\'+options[i])){
					selection = selection + \'_\' + document.getElementById(\'hikashop_product_characteristic_\'+options[i]).value;
				}else{
					var form = document[\'hikashop_product_form\'];
					if(!form){
						continue;
					}
					var checkFields = form.elements[\'hikashop_product_characteristic[\'+options[i]+\']\'];
					if(checkFields){
						if(!checkFields.length && checkFields.value){
							selection = selection + \'_\' + checkFields.value;
							continue;
						}
						var len2 = checkFields.length;
						for (var j = 0; j < len2; j++){
							if(checkFields[j].checked){
								selection = selection + \'_\' + checkFields[j].value;
								found=true;
							}
						}
					}
					if(!found){
						return true;
					}
				}
			}

			hikashopUpdateVariantData(selection);
			if(window.Oby && window.Oby.fireAjax) window.Oby.fireAjax("hkAfterUpdateVariant", {obj:obj});
			return true;
		}

		function hikashopUpdateVariantData(selection){

			if(selection){
				var names = [\'id\',\'name\',\'code\',\'image\',\'price\',\'quantity\',\'description\',\'weight\',\'url\',\'width\',\'length\',\'height\',\'contact\',\'custom_info\',\'files\'];
				var len = names.length;
				for (var i = 0; i < len; i++){
					var el = document.getElementById(\'hikashop_product_\'+names[i]+\'_main\');
					var el2 = document.getElementById(\'hikashop_product_\'+names[i]+selection);

					if(el && el2) el.innerHTML = el2.innerHTML.replace(/_VARIANT_NAME/g, selection);
				}

				if(typeof this.window[\'hikashopRefreshOptionPrice\'] == \'function\') hikashopRefreshOptionPrice();
				if(window.Oby && window.Oby.fireAjax) window.Oby.fireAjax("hkContentChanged");
			}
			return true;
		}
		';
		if(!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
	}
	function displayFE(&$element,$params){
		if(empty($element->main->characteristics)) return '';
		$this->characteristics=&$element->main->characteristics;
		foreach($this->characteristics as $k => $characteristic){
			if(empty($this->characteristics[$k]->characteristic_display_method)){
				$this->characteristics[$k]->characteristic_display_method = $params->get('characteristic_display');
			}
		}
		$this->load();

		$app = JFactory::getApplication();
		$chromePath = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'hikashop_characteristics.php';
		if (file_exists($chromePath)){
			require_once ($chromePath);
			if(function_exists('hikashop_characteristics_html')){
				$html = hikashop_characteristics_html($element,$params,$this);
			}
		}

		if(empty($html)){
			switch($params->get('characteristic_display')){
				case 'table':
					if(count($this->characteristics)==2){
						$html = '';
						$firstCharacteristic = reset($this->characteristics);
						$secondCharacteristic = end($this->characteristics);

						$html.= '<table class="hikashop_product_characteristic_chooser"><tr><td></td>';
						if(!empty($secondCharacteristic->values)){
							foreach($secondCharacteristic->values as $value){
								$html.='<td>'.$value->characteristic_value.'</td>';
							}
						}
						$html.='</tr>';
						$this->options=' onclick="return hikashopUpdateVariantData(this.value);"';
						$size=0;
						if(!empty($firstCharacteristic->values)){
							foreach($firstCharacteristic->values as $value){
								$html.='<tr><td style="text-align:right">'.$value->characteristic_value.'</td>';
								if(strlen($value->characteristic_value)>$size)$size=strlen($value->characteristic_value);
								if(!empty($secondCharacteristic->values)){
									foreach($secondCharacteristic->values as $value2){
										$class = '';
										$classspan = '';
										foreach($element->variants as $k => $variant){
											$char1 = false;
											$char2 = false;
											foreach($variant->characteristics as $variantCharacteristic){
												if($variantCharacteristic->characteristic_id==$value->characteristic_id){
													$char1 = true;
												}elseif($variantCharacteristic->characteristic_id==$value2->characteristic_id){
													$char2 = true;
												}
												if($char1&&$char2){
													if(!$variant->product_published || $variant->product_quantity==0){
														$class = ' hikashop_product_variant_out_of_stock';
														$classspan=' hikashop_product_variant_out_of_stock_span';
													}
													break 2;
												}
											}
										}

										$name = '_'.$value->characteristic_id.'_'.$value2->characteristic_id;
										$radio="\n\t<span class=\"hikashop_product_characteristic_span".$classspan."\"><input type=\"radio\" class=\"hikashop_product_characteristic".$class."\" name=\"hikashop_product_characteristic\" id=\"hikashop_product_characteristic".$name."\" value=\"".$name."\" ".$this->options;
										if($this->characteristics[$value->characteristic_parent_id]->default->characteristic_id==$value->characteristic_id && !empty($this->characteristics[$value2->characteristic_parent_id]->default->characteristic_id) && $this->characteristics[$value2->characteristic_parent_id]->default->characteristic_id==$value2->characteristic_id){
											$radio.=' checked';
										}
										$radio.=" /></span>";
										$html.='<td>'.$radio.'</td>';
									}
								}
								$html.='</tr>';
							}
						}
						$html.='</table>';
						if($params->get('characteristic_display_text')){
							$space = '';
							for($i=0;$i<=$size;$i++){
								$space.='&nbsp;&nbsp;';
							}
							$html='<table class="hikashop_product_characteristic_chooser"><tr><td></td><td>'.$space.@$secondCharacteristic->characteristic_value.'</td></tr><tr><td>'.$firstCharacteristic->characteristic_value.'</td><td>'.$html.'</td></table>';
						}
						break;
					}
				default:
				case 'radio':
				case 'dropdown':
					$main_html = '<table class="hikashop_product_characteristics_table">';
					$config =& hikashop_config();
					foreach($this->characteristics as $characteristic){
						$main_html.='<tr>';
						$values = array();
						$switch_done = false;
						if(!empty($characteristic->values)){
							foreach($characteristic->values as $k => $value){
								if(!$config->get('show_out_of_stock',1)){
									$hasQuantity = false;
									foreach($element->variants as $variant){
										foreach($variant->characteristics as $variantCharacteristic){
											if($variantCharacteristic->characteristic_id==$value->characteristic_id){
												if($variant->product_quantity != 0){
													$hasQuantity = true;
												}elseif( $element->product_id==$variant->product_id && !$switch_done){
													$id = 'hikashop_product_characteristic_'.$characteristic->characteristic_id;
													$js = "
													window.hikashop.ready( function() {hikashopUpdateVariant(document.getElementById('".$id."'));});";
													if (!HIKASHOP_PHP5) {
														$doc =& JFactory::getDocument();
													}else{
														$doc = JFactory::getDocument();
													}
													$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
													$switch_done = true;
												}
											}
										}
									}
									if(!$hasQuantity) continue;
								}
								$values[$k]=$value->characteristic_value;
							}
						}


						$html=$this->display(@$characteristic->characteristic_id,@$characteristic->default->characteristic_id,$values,$characteristic->characteristic_display_method);
						if($params->get('characteristic_display_text') && isset($characteristic->characteristic_value)){
							$html=$characteristic->characteristic_value.'</td><td>'.$html;
						}
						$main_html.='<td>'.$html.'</td></tr>';
					}
					$main_html.='</table>';
					$html = $main_html;
					break;
			}
		}
		$html.='
		<noscript>
			<input type="submit" class="btn button" name="characteristic" value="'.JText::_('REFRESH_INFORMATION').'"/>
		</noscript>';
		return $html;
	}
	function display($map,$value,$values,$characteristic_display='dropdown'){
		if(empty($values) || !is_array($values)){
			return JText::_('NO_VALUES_FOUND');
		}
		if(is_array($this->characteristics)){
			$characteristic_id = $map;
			$map = 'hikashop_product_characteristic['.$characteristic_id.']';
			$id = 'hikashop_product_characteristic_'.$characteristic_id;
		}else{
			$id = $map;
		}

		$this->values = array();
		foreach($values as $key => $val){
			if(strlen($val)!=0 && empty($val)){
				$val = $val.'&nbsp;';
			}

			if(strpos($val, '<img ') !== false)
				$val = str_replace('<img ', '<img onclick="return hikashopUpdateVariant(\'hikashop_product_characteristic_'.$characteristic_id.$key.'\');" ', $val);

			$this->values[] = JHTML::_('select.option', $key,$val);
		}

		$type = 'onclick';
		if($characteristic_display!='radio'){
			$characteristic_display='generic';
			$type = 'onchange';
		}

		$options = ' '.$type.'="return hikashopUpdateVariant(this);"';

		$html = JHTML::_('select.'.$characteristic_display.'list',   $this->values, $map, 'class="inputbox" size="1"' . $options, 'value', 'text', (int)$value,$id );
		return $html;
	}
}

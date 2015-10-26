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
class hikashopCartHelper{
	function hikashopCartHelper(){
		static $done = false;
		static $override = false;
		if(!$done){
			$done = true;
			$app = JFactory::getApplication();
			$chromePath = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'hikashop_button.php';
			if (file_exists($chromePath)){
				require_once ($chromePath);
				$override = true;
			}
		}
		$this->override = $override;
	}

	function displayButton($name,$map,&$params,$url='',$ajax="",$options="",$max_quantity=0,$min_quantity=1,$classname='',$inc=true){
		$config =& hikashop_config();

		$button = $config->get('button_style','normal');
		static $i=0;
		if($inc)
			$i++;
		if(!empty($ajax)){
			$ajax = 'onclick="var field=document.getElementById(\'hikashop_product_quantity_field_'.$i.'\');'.$ajax.'" ';
		}
		if(!empty($classname) && substr($classname, 0, 1) != ' ')
			$classname = ' '.$classname;
		if($this->override && function_exists('hikashop_button_render')){
			$html = hikashop_button_render($map,$name,$ajax,$options,$url,$classname);
		}else{
			switch($button){
				case 'rounded': //deprecated
					$params->set('main_div_name', 'hikashop_button_'.$i);
					$moduleHelper = hikashop_get('helper.module');
					$moduleHelper->setCSS($params);
					$url = 'href="'.$url.'" ';
					$html='
					<div id="'.$params->get('main_div_name').'">
					<div class="hikashop_container">
					<div class="hikashop_subcontainer">
					<a rel="nofollow" class="hikashop_cart_rounded_button'.$classname.'" '.$url.$ajax.$options.'>'.$name.'</a>
					</div>
					</div>
					</div>
					';
					break;
				case 'css':
					if(empty($url))
						$url = '#';
					$url = 'href="'.$url.'" ';
					$html= '<a rel="nofollow" class="hikashop_cart_button'.$classname.'" '.$options.' '.$url.$ajax.'>'.$name.'</a>';
					break;
				case 'normal':
				default:
					$type = 'submit';
					if(in_array($map,array('new','refresh','wishlist'))){
						$type = 'button';
					}
					$app = JFactory::getApplication();
					if($app->isAdmin()){
						$class = 'btn';
					}else{
						$class = HK_GRID_BTN;
					}
					$html= '<input type="'.$type.'" class="'.$class.' button hikashop_cart_input_button'.$classname.'" name="'.$map.'" value="'.$name.'" '.$ajax.$options.'/>';
					break;
			}
		}

		if($map=='add'){

			$show_quantity_field=$config->get('show_quantity_field',0);
			if($params->get('show_quantity_field',0)=='-1')$params->set('show_quantity_field',$show_quantity_field);

			if($params->get('show_quantity_field',0)==1){
				$max_quantity=(int)$max_quantity;
				$min_quantity=(int)$min_quantity;

				static $first = false;
				if(!$first && $map=='add'){
					$first=true;
					$js = '
					function hikashopQuantityChange(field,plus,max,min){
						var fieldEl=document.getElementById(field);
						var current = fieldEl.value;
						current = parseInt(current);
						if(plus){
							if(max==0 || current<max){
								fieldEl.value=parseInt(fieldEl.value)+1;
							}else if(max && current==max){
								alert(\''.JText::_('NOT_ENOUGH_STOCK',true).'\');
							}
						}else{
							if(current>1 && current>min){
								fieldEl.value=current-1;
							}
						}
						return false;
					}
					function hikashopCheckQuantityChange(field,max,min){
						var fieldEl=document.getElementById(field);
						var current = fieldEl.value;
						current = parseInt(current);
						if(max && current>max){
							fieldEl.value=max;
							alert(\''.JText::_('NOT_ENOUGH_STOCK',true).'\');
						}else if(current<min){
							fieldEl.value=min;
						}
						return false;
					}
					';
					$setJS=$params->get('js');
					if(!$setJS){
						if (!HIKASHOP_PHP5) {
							$doc =& JFactory::getDocument();
						}else{
							$doc = JFactory::getDocument();
						}
						$doc->addScriptDeclaration("<!--\n".$js."\n//-->\n");
					}else{
						echo '<script type="text/javascript">'."<!--\n".$js."\n//-->\n".'</script>';
					}
				}
				if($this->override && function_exists('hikashop_quantity_render')){
					$html = hikashop_quantity_render($html,$i,$max_quantity,$min_quantity);
				}else{
					$js = '';
					$params->set('i',$i);
					$params->set('min_quantity',$min_quantity);
					$params->set('max_quantity',$max_quantity);
					$params->set('html',$html);
					$html = hikashop_getLayout('product', 'show_quantity', $params, $js);
				}
			}elseif($params->get('show_quantity_field',0)==0){
				$html.='<input id="hikashop_product_quantity_field_'.$i.'" type="hidden" value="'.$min_quantity.'" class="hikashop_product_quantity_field" name="quantity" />';
			}elseif($params->get('show_quantity_field',0)==-1){
				static $second = false;
				if(!$second){
					$second=true;
					$js = '

					function hikashopQuantityChange(field,plus,max,min){
						var fieldEl=document.getElementById(field);
						var current = fieldEl.value;
						current = parseInt(current);
						if(plus){
							if(max==0 || current<max){
								fieldEl.value=parseInt(fieldEl.value)+1;
							}else if(max && current==max){
								alert(\''.JText::_('NOT_ENOUGH_STOCK',true).'\');
							}
						}else{
							if(current>1 && current>min){
								fieldEl.value=current-1;
							}
						}
						return false;
					}

					';
					$setJS=$params->get('js');
					if(!$setJS){
						$doc = JFactory::getDocument();
						$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
					}else{
						echo '<script type="text/javascript">'."<!--\n".$js."\n//-->\n".'</script>';
					}
				}
				$html = '<input id="hikashop_product_quantity_field_'.$i.'" type="text" value="'.JRequest::getInt('quantity',$min_quantity).'" class="hikashop_product_quantity_field" name="quantity" onchange="hikashopCheckQuantityChange(\'hikashop_product_quantity_field_'.$i.'\','.$max_quantity.','.$min_quantity.');" />'.$html;
			}elseif($params->get('show_quantity_field',0)==2){
			}
		}
		return $html;
	}

	function cartCount($add = false) {
		static $carts = 0;
		if($add)
			$carts++;
		return $carts;
	}

	function getJS($url,$needNotice=true){
		static $first = true;
		if($first){
			$config =& hikashop_config();
			$redirect = $config->get('redirect_url_after_add_cart','stay_if_cart');
			global $Itemid;
			$url_itemid='';
			if(!empty($Itemid)){
				$url_itemid='&Itemid='.$Itemid;
			}
			$baseUrl = hikashop_completeLink('product&task=updatecart',true,true);
			if(strpos($baseUrl,'?')!==false){
				$baseUrl.='&';
			}else{
				$baseUrl.='?';
			}
			if($redirect=='ask_user' || hikashop_loadUser() == null){
				JHTML::_('behavior.modal');
				if($needNotice && JRequest::getVar('tmpl','')!='component'){
					if($this->override && function_exists('hikashop_popup_render')){
						echo hikashop_popup_render();
					}else{
						$config =& hikashop_config();
						$popupWidth = $config->get('add_to_cart_popup_width','480');
						$popupHeight = $config->get('add_to_cart_popup_height','140');
						echo '<div style="display:none;">'.
							'<a rel="{handler: \'iframe\',size: {x: '.$popupWidth.', y: '.$popupHeight.'}}"  id="hikashop_notice_box_trigger_link" href="'.hikashop_completeLink('checkout&task=notice&cart_type=cart'.$url_itemid,true).'"></a>'.
							'<a rel="{handler: \'iframe\',size: {x: '.$popupWidth.', y: '.$popupHeight.'}}" id="hikashop_notice_wishlist_box_trigger_link" href="'.hikashop_completeLink('checkout&task=notice&cart_type=wishlist'.$url_itemid,true).'"></a>'.
							'</div>';
					}
				}
				if($this->override && function_exists('hikashop_popup_js_render')){
					$js = hikashop_popup_js_render($url);
				}else{
					$popupJs = '';
					if($redirect == 'ask_user'){
						$popupJs = '
							if(cart_type == "wishlist"){
								SqueezeBox.fromElement("hikashop_notice_wishlist_box_trigger_link",{parse: "rel"});
							} else {
								SqueezeBox.fromElement("hikashop_notice_box_trigger_link",{parse: "rel"});
							}
						';
					}
					$addTo = JRequest::getString('add_to','');
					if(!empty($addTo))
						$addTo = '&addTo='.$addTo;
					$js = '
	function hikashopModifyQuantity(id,obj,add,form,type,moduleid){
		var d = document, cart_type="cart", addStr="", qty=1, e = null;
		if(type) cart_type = type;
		if(add) addStr = "&add=1";

		if(moduleid === undefined) moduleid = 0;

		if(obj){
			qty = parseInt(obj.value);
		}else if(document.getElementById("hikashop_product_quantity_field_"+id) && document.getElementById("hikashop_product_quantity_field_"+id).value){
			qty = document.getElementById("hikashop_product_quantity_field_"+id).value;
		}
		if(form && document[form]){
			var varform = document[form];
			e = d.getElementById("hikashop_cart_type_"+id+"_"+moduleid);

			if(!e)
				e = d.getElementById("hikashop_cart_type_"+id);
			if(cart_type == "wishlist"){
				if(e) e.value = "wishlist";
				if(varform.cid) varform.cid.value = id;
				f = d.getElementById("type");
				if(f) f.value = "wishlist";
			}else{
				if(e) e.value = "cart";
				if(varform.cid) varform.cid.value = id;
			}
			if(varform.task) {
				varform.task.value = "updatecart";
			}

			var input = document.createElement("input");
			input.type = "hidden";
			input.name = "from_form";
			input.value = "true";
			varform.appendChild(input);

			varform.submit();
		}else{
			if(qty){
				'.$popupJs.'
			}
			var url = "'.$baseUrl.'from=module&product_id="+id+"&cart_type="+cart_type+"&hikashop_ajax=1&quantity="+qty+addStr+"'.$url_itemid.$addTo.'&return_url='.urlencode(base64_encode(urldecode($url))).'";
			var completeFct = function(result) {
				var hikaModule = false;
				var checkmodule = false;
				if(result == "notLogged"){ // if the customer is not logged and use add to wishlist, display a popup for the notice
					SqueezeBox.fromElement("hikashop_notice_wishlist_box_trigger_link",{parse: "rel"});
				}else if(result.indexOf("URL|") != "-1"){ // id the option is set to redirect, do the redirection
					result = result.replace("URL|","");
					window.location = result;
					return false;
				}else if(result != ""){ // if the result is not empty check for the module
					checkmodule = true;
				}
				if(checkmodule){
					if(cart_type != "wishlist") {
						hikaModule = window.document.getElementById("hikashop_cart_module");
					}else{
						hikaModule = window.document.getElementById("hikashop_wishlist_module");
					}
				}
				if(hikaModule) hikaModule.innerHTML = result;
				if(window.jQuery && typeof(jQuery.noConflict) == "function" && !window.hkjQuery) {
					window.hkjQuery = jQuery.noConflict();
				}
				if(window.hkjQuery && typeof(hkjQuery().chosen) == "function") {
					hkjQuery( ".tochosen:not(.chzn-done)" ).removeClass(\'chzn-done\').removeClass(\'tochosen\').chosen();
				}
			};
			try{
				new Ajax(url, {method: "get", onComplete: completeFct}).request();
			}catch(err){
				new Request({url: url, method: "get", onComplete: completeFct}).send();
			}
		}
		return false;
	}
';
		}
	}else{
		if($this->override && function_exists('hikashop_cart_js_render')){
			$js = hikashop_cart_js_render($url);
		}else{
			$js='';
			if($this->cartCount()!=1 && !empty($url)){
				$js = 'window.location = \''.urldecode($url).'\';';
			}
			$addTo = JRequest::getString('add_to','');
			if(!empty($addTo))
				$addTo = '&addTo='.$addTo;
			$app = JFactory::getApplication();
			$js = '
	function hikashopModifyQuantity(id,obj,add,form,type,moduleid){
		var d = document, cart_type="cart", addStr="", qty=1, e = null;
		if(type) cart_type = type;
		if(add) addStr = "&add=1";

		if(moduleid === undefined) moduleid = 0;

		if(obj){
			qty = parseInt(obj.value);
		}else if(document.getElementById("hikashop_product_quantity_field_"+id) && document.getElementById("hikashop_product_quantity_field_"+id).value){
			qty = document.getElementById("hikashop_product_quantity_field_"+id).value;
		}
		if(form && document[form]){
			var varform = document[form];
			e = d.getElementById("hikashop_cart_type_"+id+"_"+moduleid);

			if(!e)
				e = d.getElementById("hikashop_cart_type_"+id);
			if(cart_type == "wishlist"){
				if(e) e.value = "wishlist";
				f = d.getElementById("type");
				if(f) f.value = "wishlist";
			}else{
				if(e) e.value = "cart";
			}
			if(varform.task) {
				varform.task.value = "updatecart";
			}

			var input = document.createElement("input");
			input.type = "hidden";
			input.name = "from_form";
			input.value = "true";
			varform.appendChild(input);

			varform.submit();
		}else{
			var url = "'.$baseUrl.'from=module&product_id="+id+"&cart_type="+cart_type+"&hikashop_ajax=1&quantity="+qty+addStr+"'.$url_itemid.$addTo.'&return_url='.urlencode(base64_encode(urldecode($url))).'";
			var completeFct = function(result) {
				var hikaModule = false;
				var checkmodule = false;
				if(result == "notLogged"){
					SqueezeBox.fromElement("hikashop_notice_wishlist_box_trigger_link",{parse: "rel"});
				}else if(result.indexOf("URL|") != "-1"){
					result = result.replace("URL|","");
					window.location = result;
					return false;
				}else if(result != ""){
					checkmodule = true;
				}
				if(checkmodule){
					if(cart_type != "wishlist") {
						hikaModule = window.document.getElementById("hikashop_cart_module");
					}else{
						hikaModule = window.document.getElementById("hikashop_wishlist_module");
					}
				}

				if(hikaModule) hikaModule.innerHTML = result;

				if(window.jQuery && typeof(jQuery.noConflict) == "function" && !window.hkjQuery) {
					window.hkjQuery = jQuery.noConflict();
				}
				if(window.hkjQuery && typeof(hkjQuery().chosen) == "function") {
					hkjQuery( ".tochosen:not(.chzn-done)" ).removeClass(\'chzn-done\').removeClass(\'tochosen\').chosen();
				}

				if(!hikaModule) {
					'.$js.'
				}
			};
			try{
				new Ajax(url, {method: "get", onComplete: completeFct}).request();
			}catch(err){
				new Request({url: url, method: "get", onComplete: completeFct}).send();
			}
		}
		return false;
	}
';
				}
				if(!HIKASHOP_J30)
					JHTML::_('behavior.mootools');
				else
					JHTML::_('behavior.framework');
			}
			if (!HIKASHOP_PHP5) {
				$doc =& JFactory::getDocument();
			}else{
				$doc = JFactory::getDocument();
			}
			$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
			$first = !$needNotice;
			return $js;
		}
	}
}

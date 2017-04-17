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
if(!defined('DS'))
	define('DS',DIRECTORY_SEPARATOR);
jimport('joomla.plugin.plugin');

class plgSystemHikashopmassaction extends JPlugin {

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('system', 'hikashopmassaction');
			if(version_compare(JVERSION,'2.5','<')){
				jimport('joomla.html.parameter');
				$this->params = new JParameter(@$plugin->params);
			} else {
				$this->params = new JRegistry(@$plugin->params);
			}
		}
	}

	function onMassactionTableTriggersLoad(&$table,&$triggers,&$triggers_html,&$loadedData){
		switch($table->table){
			case 'product':
				$triggers['onBeforeProductCreate']=JText::_('BEFORE_A_PRODUCT_IS_CREATED');
				$triggers['onBeforeProductUpdate']=JText::_('BEFORE_A_PRODUCT_IS_UPDATED');
				$triggers['onBeforeProductDelete']=JText::_('BEFORE_A_PRODUCT_IS_DELETED');
				$triggers['onBeforeProductCopy']=JText::_('BEFORE_A_PRODUCT_IS_COPIED');
				$triggers['onAfterProductCreate']=JText::_('AFTER_A_PRODUCT_IS_CREATED');
				$triggers['onAfterProductUpdate']=JText::_('AFTER_A_PRODUCT_IS_UPDATED');
				$triggers['onAfterProductDelete']=JText::_('AFTER_A_PRODUCT_IS_DELETED');
				$triggers['onAfterProductCopy']=JText::_('AFTER_A_PRODUCT_IS_COPIED');
				break;
			case 'category':
				$triggers['onBeforeCategoryCreate']=JText::_('BEFORE_A_CATEGORY_IS_CREATED');
				$triggers['onBeforeCategoryUpdate']=JText::_('BEFORE_A_CATEGORY_IS_UPDATED');
				$triggers['onBeforeCategoryDelete']=JText::_('BEFORE_A_CATEGORY_IS_DELETED');
				$triggers['onAfterCategoryCreate']=JText::_('AFTER_A_CATEGORY_IS_CREATED');
				$triggers['onAfterCategoryUpdate']=JText::_('AFTER_A_CATEGORY_IS_UPDATED');
				$triggers['onAfterCategoryDelete']=JText::_('AFTER_A_CATEGORY_IS_DELETED');
				break;
			case 'order':
				$triggers['onBeforeOrderCreate']=JText::_('BEFORE_AN_ORDER_IS_CREATED');
				$triggers['onBeforeOrderUpdate']=JText::_('BEFORE_AN_ORDER_IS_UPDATED');
				$triggers['onBeforeOrderDelete']=JText::_('BEFORE_AN_ORDER_IS_DELETED');
				$triggers['onAfterOrderCreate']=JText::_('AFTER_AN_ORDER_IS_CREATED');
				$triggers['onAfterOrderUpdate']=JText::_('AFTER_AN_ORDER_IS_UPDATED');
				$triggers['onAfterOrderDelete']=JText::_('AFTER_AN_ORDER_IS_DELETED');
				break;
			case 'user':
				$triggers['onBeforeUserCreate']=JText::_('BEFORE_A_USER_IS_CREATED');
				$triggers['onBeforeUserUpdate']=JText::_('BEFORE_A_USER_IS_UPDATED');
				$triggers['onBeforeUserDelete']=JText::_('BEFORE_A_USER_IS_DELETED');
				$triggers['onAfterUserCreate']=JText::_('AFTER_A_USER_IS_CREATED');
				$triggers['onAfterUserUpdate']=JText::_('AFTER_A_USER_IS_UPDATED');
				$triggers['onAfterUserDelete']=JText::_('AFTER_A_USER_IS_DELETED');
				break;
			case 'address':
				$triggers['onBeforeAddressCreate']=JText::_('BEFORE_AN_ADDRESS_IS_CREATED');
				$triggers['onBeforeAddressUpdate']=JText::_('BEFORE_AN_ADDRESS_IS_UPDATED');
				$triggers['onBeforeAddressDelete']=JText::_('BEFORE_AN_ADDRESS_IS_DELETED');
				$triggers['onAfterAddressCreate']=JText::_('AFTER_AN_ADDRESS_IS_CREATED');
				$triggers['onAfterAddressUpdate']=JText::_('AFTER_AN_ADDRESS_IS_UPDATED');
				$triggers['onAfterAddressDelete']=JText::_('AFTER_AN_ADDRESS_IS_DELETED');
				break;
		}
		$triggers['onHikashopCronTriggerMinutes']=JText::_('EVERY_MINUTES');
		$triggers['onHikashopCronTriggerHours']=JText::_('EVERY_HOURS');
		$triggers['onHikashopCronTriggerDays']=JText::_('EVERY_DAYS');
		$triggers['onHikashopCronTriggerWeeks']=JText::_('EVERY_WEEKS');
		$triggers['onHikashopCronTriggerMonths']=JText::_('EVERY_MONTHS');
		$triggers['onHikashopCronTriggerYears']=JText::_('EVERY_YEARS');
	}

	function onMassactionTableFiltersLoad(&$table,&$filters,&$filters_html,&$loadedData){
		$db = JFactory::getDBO();
		$operators = hikashop_get('type.operators');
		$cid = hikashop_getCID();
		$tables = array();
		$custom = '';
		$type = 'filter';
		$massactionClass = hikashop_get('class.massaction');
		if(empty($loadedData->massaction_filters)){
			$loadedData->massaction_filters = array();
		}
		switch($table->table){
			case 'product':
				$tables = array('product','price','category','characteristic','product_related','product_option');

				$filters['productType']=JText::_('PRODUCT_TYPE');
				$loadedData->massaction_filters['__num__'] = new stdClass();
				$loadedData->massaction_filters['__num__']->type = 'product';
				$loadedData->massaction_filters['__num__']->data = array();
				$loadedData->massaction_filters['__num__']->name = 'productType';
				$loadedData->massaction_filters['__num__']->data['type'] = 'all';
				$loadedData->massaction_filters['__num__']->html = '';

				foreach($loadedData->massaction_filters as $key => &$value) {
					if($value->name != 'productType' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$value->type = 'product';
					$product = hikashop_get('type.product');
					$product->onchange='countresults(\''.$table->table.'\','.$key.');';
					$output = $product->display('filter['.$table->table.']['.$key.'][productType][type]',$value->data['type'], 'chzn-done not-processed');
					$filters_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}

				$filters['csvImport']=JText::_('CSV_IMPORT');
				$loadedData->massaction_filters['__num__'] = new stdClass();
				$loadedData->massaction_filters['__num__']->type = 'product';
				$loadedData->massaction_filters['__num__']->data = array();
				$loadedData->massaction_filters['__num__']->name = 'csvImport';
				$loadedData->massaction_filters['__num__']->data['path'] = '';
				$loadedData->massaction_filters['__num__']->data['pathType'] = '';
				$loadedData->massaction_filters['__num__']->data['type'] = '';
				$loadedData->massaction_filters['__num__']->html = '';

				foreach($loadedData->massaction_filters as $key => &$value) {
					if($value->name != 'csvImport' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$value->type = 'product';
					$data = $value->data;

					if($data['type'] == 'in' || empty($data['type'])){
						$typeIn1 = ' selected="selected" ';
						$typeIn2 = '';
					}
					else{
						$typeIn2 = ' selected="selected" ';
						$typeIn1 = '';
					}
					$checked = '';
					if(isset($data['save']))
						$checked = 'checked="checked"';

					$output = '<select class="chzn-done not-processed" name="filter['.$table->table.']['.$key.'][csvImport][type]" onchange="countresults(\''.$table->table.'\','.$key.')"><option value="in" '.$typeIn1.'>'.JText::_('IN_CSV').'</option><option value="out" '.$typeIn2.'>'.JText::_('NOT_IN_CSV').'</option></select>';
					$output .= '<select class="chzn-done not-processed" name="filter['.$table->table.']['.$key.'][csvImport][pathType]" id="productfilter'.$key.'csvImport_pathType" onchange="hikashop_switchmode(this,'.$key.');"><option value="upload">'.JText::_('HIKA_FILE_MODE_UPLOAD').'</option><option value="path" selected="selected">'.JText::_('HIKA_FILE_MODE_PATH').'</option></select>';
					$output .= '<span id="productfilter'.$key.'csvImport_path"><input onchange="countresults(\''.$table->table.'\','.$key.')" type="input" value="'.$data['path'].'" size="50" id="productfilter'.$key.'csvImport_path_value" name="filter['.$table->table.']['.$key.'][csvImport][path]"/><input type="button" value="'.JText::_('VERIFY_FILE').'" onclick="hikashop_verifycsvcolumns('.$key.');"/></span>';
					$output .= '<span id="productfilter'.$key.'csvImport_upload" style="display: none;"><input onchange="countresults(\''.$table->table.'\','.$key.')" type="file" size="50" id="productfilter'.$key.'csvImport_upload" name="filter_'.$table->table.'_'.$key.'_csvImport_upload"/>';
					$output .= '<span id="productfilter'.$key.'csvImport_txt">'.JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')).'</span></span>';
					$output .= '<br/><input type="checkbox" value="1" id="importCsvSave" name="filter['.$table->table.']['.$key.'][csvImport][save]" '.$checked.'/><label for="importCsvSave">'.JText::_('SAVE_ON_CSV_IMPORT_MASSACTION').'</label>';
					$output .= '<div id="productfilter'.$key.'csvImport_verify"></div>';

					$filters_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}

				break;
			case 'category':
				$tables = array('category','parent_category');

				$filters['categoryType']=JText::_('CATEGORY_TYPE');
				$loadedData->massaction_filters['__num__'] = new stdClass();
				$loadedData->massaction_filters['__num__']->type = 'category';
				$loadedData->massaction_filters['__num__']->data = array();
				$loadedData->massaction_filters['__num__']->name = 'categoryType';
				$loadedData->massaction_filters['__num__']->data['type'] = 'all';
				$loadedData->massaction_filters['__num__']->html = '';

				foreach($loadedData->massaction_filters as $key => &$value) {
					if($value->name != 'categoryType' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$value->type = 'category';
					$category = hikashop_get('type.category');
					$category->onchange='countresults(\''.$table->table.'\','.$key.');';
					$output = $category->display('filter['.$table->table.']['.$key.'][categoryType][type]',$value->data['type']);
					$filters_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}
				break;
			case 'order':
				$tables = array('order','order_product','address','user');

				$filters['orderStatus']=JText::_('ORDER_STATUS');
				$loadedData->massaction_filters['__num__'] = new stdClass();
				$loadedData->massaction_filters['__num__']->type = 'order';
				$loadedData->massaction_filters['__num__']->data = array();
				$loadedData->massaction_filters['__num__']->name = 'orderStatus';
				$loadedData->massaction_filters['__num__']->html = '';

				$db->setQuery('SELECT `category_name` FROM '.hikashop_table('category').' WHERE `category_type` = '.$db->quote('status').' AND `category_name` != '.$db->quote('order status'));
				if(!HIKASHOP_J25){
					$orderStatuses = $db->loadResultArray();
				} else {
					$orderStatuses = $db->loadColumn();
				}
				foreach($loadedData->massaction_filters as $key => &$value) {

					if(!isset($value->data['type'])) $value->data['type'] = 'all';

					if($value->name != 'orderStatus' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$value->type = 'order';

					$output = '<select class="chzn-done not-processed" name="filter['.$table->table.']['.$key.'][orderStatus][type]" onchange="countresults(\''.$table->table.'\','.$key.')">';
					if(is_array($orderStatuses)){
						foreach($orderStatuses as $orderStatus){
							$selected = '';
							if($orderStatus == $value->data['type']) $selected = 'selected="selected"';
							$output .= '<option value="'.$orderStatus.'" '.$selected.'>'.JText::_($orderStatus).'</option>';
						}
					}
					$output .= '</select>';

					$filters_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}

				$filters['totalPurchase']=JText::_('USER_TOTAL_PURCHASE');
				$loadedData->massaction_filters['__num__'] = new stdClass();
				$loadedData->massaction_filters['__num__']->type = 'order';
				$loadedData->massaction_filters['__num__']->data = array();
				$loadedData->massaction_filters['__num__']->name = 'totalPurchase';
				$loadedData->massaction_filters['__num__']->html = '';

				$totalTypes = array('orderQty' => 'ORDER_TOTAL_QUANTITY', 'orderAmount' => 'ORDER_TOTAL_AMOUNT', 'productTotal' => 'PRODUCT_TOTAL_QUANTITY');
				foreach($loadedData->massaction_filters as $key => &$value) {
					if(!isset($value->data['type'])) $value->data['type'] = 'orderQty';
					if(!isset($value->data['operator'])) $value->data['operator'] = '=';
					if(!isset($value->data['value'])) $value->data['value'] = '';
					if($value->name != 'totalPurchase' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;
					$value->type = 'order';
					$output = '<select class="chzn-done not-processed" name="filter['.$table->table.']['.$key.'][totalPurchase][type]" onchange="countresults(\''.$table->table.'\','.$key.')">';
					if(is_array($totalTypes)){
						foreach($totalTypes as $selectKey => $selectValue){
							$selected = '';
							if($selectKey == $value->data['type']) $selected = 'selected="selected"';
							$output .= '<option value="'.$selectKey.'" '.$selected.'>'.JText::_($selectValue).'</option>';
						}
					}
					$output .= '</select>';
					$cOperators = array('=','!=','>','<','>=','<=');
					$output .= '<select class="chzn-done not-processed" name="filter['.$table->table.']['.$key.'][totalPurchase][operator]" onchange="countresults(\''.$table->table.'\',\''.$key.'\')">';
					foreach($cOperators as $cOperator){
						$selected = '';
						if($cOperator == $value->data['operator']) $selected = 'selected="selected"';
						$output .= '<option value="'.$cOperator.'" '.$selected.'>'.JText::_($cOperator).'</option>';
					}
					$output .= '</select>';
					$output .= ' <input class="inputbox" type="text" name="filter['.$table->table.']['.$key.'][totalPurchase][value]" size="50" value="'.$value->data['value'].'" onchange="countresults(\''.$table->table.'\',\''.$key.'\')" />';
					$filters_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}

				break;
			case 'user':
				$tables = array('user','address');


				$filters['haveDontHave']=JText::_('HAVE_DONT_HAVE');
				$loadedData->massaction_filters['__num__'] = new stdClass();
				$loadedData->massaction_filters['__num__']->type = 'user';
				$loadedData->massaction_filters['__num__']->data = array('have'=>'have','type'=>'','order_status'=>'created');
				$loadedData->massaction_filters['__num__']->name = 'haveDontHave';
				$loadedData->massaction_filters['__num__']->html = '';

				$db->setQuery('SELECT `category_name` FROM '.hikashop_table('category').' WHERE `category_type` = '.$db->quote('status').' AND `category_name` != '.$db->quote('order status'));
				if(!HIKASHOP_J25){
					$orderStatuses = $db->loadResultArray();
				} else {
					$orderStatuses = $db->loadColumn();
				}
				foreach($loadedData->massaction_filters as $key => &$value) {
					if($value->name != 'haveDontHave' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$value->type = 'user';

					$output= '<select class="chzn-done not-processed" name="filter['.$table->table.']['.$key.'][haveDontHave][have]" id="userfilter'.$key.'haveDontHavetype" onchange="countresults(\''.$table->table.'\','.$key.')">';
					$datas = array('have'=>'HIKA_HAVE','donthave'=>'DONT_HAVE');
					$display = 'style="display: none;"';
					foreach($datas as $k => $data){
						$selected = '';
						if($k == $value->data['have']) $selected = 'selected="selected"';
						if($value->data['have'] == 'order_status') $display = '';
						$output.= '<option value="'.$k.'" '.$selected.'>'.JText::_(''.$data.'').'</option>';
					}
					$output.= '</select>';

					$output.= '<select class="chzn-done not-processed" name="filter['.$table->table.']['.$key.'][haveDontHave][type]" id="userfilter'.$key.'haveDontHavetype" onchange="showSubSelect(this.value,'.$key.'); countresults(\''.$table->table.'\','.$key.')">';
					$datas = array('order'=>'HIKASHOP_ORDER','order_status'=>'ORDER_STATUS','address'=>'ADDRESS');
					$display = 'style="display: none;"';
					foreach($datas as $k => $data){
						$selected = '';
						if($k == $value->data['type']) $selected = 'selected="selected"';
						if($value->data['type'] == 'order_status') $display = '';
						$output.= '<option value="'.$k.'" '.$selected.'>'.JText::_(''.$data.'').'</option>';
					}
					$output.= '</select>';

					$output .= '<select class="chzn-done not-processed" id="userfilter'.$key.'haveDontHaveorderStatus" '.$display.' name="filter['.$table->table.']['.$key.'][haveDontHave][order_status]" onchange="countresults(\''.$table->table.'\','.$key.')">';
					if(is_array($orderStatuses)){
						foreach($orderStatuses as $orderStatus){
							$selected = '';
							if($orderStatus == $value->data['order_status']) $selected = 'selected="selected"';
							$output.='<option value="'.$orderStatus.'" '.$selected.'>'.JText::_($orderStatus).'</option>';
						}
					}
					$output.= '</select>';
					$output.= '<input type="hidden" id="userfilter'.$key.'haveDontHavehide" name="filter['.$table->table.']['.$key.'][haveDontHave][show]" value="0"/>';

					$filters_html['haveDontHave'] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}
				$filters_html['haveDontHave'] .= '
					<script type="text/javascript">
						var d = document;
						var hide = d.getElementById(\'userfilter'.$key.'haveDontHavehide\').value;
						if(hide != 0){d.getElementById(hide).style.display = \'inline-block\';}
						function showSubSelect(type, k){
							if(type == \'order_status\'){
								d.getElementById(\'userfilter\'+k+\'haveDontHaveorderStatus\').style.display = \'inline-block\';
								d.getElementById(\'userfilter\'+k+\'haveDontHavehide\').value = \'userfilter\'+k+\'haveDontHaveorderStatus\';
							}else{
								d.getElementById(\'userfilter\'+k+\'haveDontHaveorderStatus\').style.display = \'none\';
								d.getElementById(\'userfilter\'+k+\'haveDontHavehide\').value = \'0\';
							}
						}
					</script>
				';
				break;
			case 'address':
				$tables = array('address','user');
				break;
		}

		if(version_compare(JVERSION,'3.0','<')){
			$fieldsTable = $db->getTableFields('#__hikashop_user');
			$hkUsers = reset($fieldsTable);
			$fieldsTable = $db->getTableFields('#__users');
			$jUsers = reset($fieldsTable);
		} else {
			$hkUsers = $db->getTableColumns('#__hikashop_user');
			$jUsers = $db->getTableColumns('#__users');
		}
		ksort($hkUsers);
		ksort($jUsers);
		$loadedData->massaction_filters['__num__'] = new stdClass();
		$loadedData->massaction_filters['__num__']->type = $table->table;
		$loadedData->massaction_filters['__num__']->data = array();
		$loadedData->massaction_filters['__num__']->data['type'] = '';
		$loadedData->massaction_filters['__num__']->data['operator'] = '';
		$loadedData->massaction_filters['__num__']->data['value'] = '';
		$loadedData->massaction_filters['__num__']->name = '';
		$loadedData->massaction_filters['__num__']->html = '';

		foreach($loadedData->massaction_filters as $key => &$value) {
			if(!isset($value->data['type']))
				$value->data['type'] = '';
			if(!isset($value->data['operator']))
				$value->data['operator'] = '=';
			if(!isset($value->data['value']))
				$value->data['value'] = '';
			if(!isset($value->data['address']))
				$value->data['address'] = '';

			if(!empty($tables)){
				if(!is_array($tables)) $tables = array($tables);
				foreach($tables as $relatedTable){
					$column = $relatedTable.'Column';
					$loadedData->massaction_filters['__num__']->name = $column;
					$filters[$column]=JText::_(''.strtoupper($relatedTable).'_COLUMN');
					if($relatedTable == 'product_option') $relatedTable = 'product_related';
					if($relatedTable == 'parent_category') $relatedTable = 'category';
					if(version_compare(JVERSION,'3.0','<')){
						$fieldsTable = $db->getTableFields('#__hikashop_'.$relatedTable);
						$fields = reset($fieldsTable);
					} else {
						$fields = $db->getTableColumns('#__hikashop_'.$relatedTable);
					}
					ksort($fields);
					$typeField = array();
					if(!empty($fields)) {
						foreach($fields as $oneField => $fieldType){
							$typeField[] = JHTML::_('select.option',$oneField,$oneField);
						}
					}
					$user = '<select class="chzn-done not-processed" name="filter['.$table->table.']['.$key.'][userColumn][type]" onchange="countresults(\''.$table->table.'\','.$key.')" >';
						$user .='<optgroup label="HIKA_USER">';
							foreach($hkUsers as $key2 => $hkUser){
								$tmpVal = str_replace('hk_user.','',$value->data['type']);
								if($key2 == $tmpVal)
									$user .= '<option value="hk_user.'.$key2.'" selected="selected">'.$key2.'</option>';
								else
									$user .= '<option value="hk_user.'.$key2.'">'.$key2.'</option>';
							}
						$user .= '</optgroup>';
						$user .='<optgroup label="JOOMLA_USER">';
							foreach($jUsers as $key2 => $jUser){
								$tmpVal = str_replace('joomla_user.','',$value->data['type']);
								if($key2 == $tmpVal)
									$user .= '<option value="joomla_user.'.$key2.'" selected="selected">'.$key2.'</option>';
								else
									$user .= '<option value="joomla_user.'.$key2.'">'.$key2.'</option>';
							}
						$user .= '</optgroup>';
					$user .= '</select>';
					switch($table->table){
						case 'product':
							if($relatedTable == 'characteristic'){
								$db->setQuery('SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id = 0');
								$characteristics = $db->loadObjectList();
								if(is_array($characteristics)){
									$custom = '<select class="chzn-done not-processed" name="filter['.$table->table.']['.$key.'][characteristicColumn][type]" onchange="countresults('.$table->table.','.$key.')" >';
									foreach($characteristics as $charact){
										$selected = '';
										if($charact->characteristic_value == $value->data['type']) $selected = 'selected="selected"';
										$custom .= '<option value="'.$charact->characteristic_value.'" '.$selected.'>'.$charact->characteristic_value.'</option>';
									}
									$custom .= '</select>';
								}
							}
							elseif(in_array($relatedTable, array('product_related','product_option'))){
								if(version_compare(JVERSION,'3.0','<')){
									$fieldsTable = $db->getTableFields('#__hikashop_product');
									$fields = reset($fieldsTable);
								} else {
									$fields = $db->getTableColumns('#__hikashop_product');
								}
								ksort($fields);
								$typeField = array();
								if(!empty($fields)) {
									foreach($fields as $oneField => $fieldType){
										$typeField[] = JHTML::_('select.option',$oneField,$oneField);
									}
								}
								$custom = JHTML::_('select.genericlist', $typeField, "filter[".$table->table."][$key][".$column."][type]", 'class="inputbox chzn-done not-processed" onchange="countresults(\''.$table->table.'\','.$key.')" size="1"', 'value', 'text',$value->data['type']);
							}else{
								$custom = '';
							}
							break;
						case 'category':
							$custom = '';
							break;
						case 'order':
							if($relatedTable == 'address'){
								$datas = array('both' => 'DISPLAY_BOTH','bill' => 'HIKASHOP_BILLING_ADDRESS','ship' => 'HIKASHOP_SHIPPING_ADDRESS');
								$custom = '<select class="chzn-done not-processed" onchange="countresults(\''.$table->table.'\','.$key.')" name="filter['.$table->table.']['.$key.'][addressColumn][address]" >';
								foreach($datas as $k => $data){
									$selected = '';
									if($k == $value->data['address']) $selected = 'selected="selected"';
									$custom .= '<option value="'.$k.'" '.$selected.'>'.JText::sprintf(''.$data.'').'</option>';
								}
								$custom .= '</select>';
							}elseif($relatedTable == 'user'){
								$custom = $user;
							}else{
								$custom = '';
							}
							break;
						case 'user':
							if($relatedTable == 'user'){
								$custom = $user;
							}else{
								$custom = '';
							}
							break;
						case 'address':
							if($relatedTable == 'user'){
								$custom = $user;
							}else{
								$custom = '';
							}
							break;
					}
					if($value->name != $column || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$value->type = $relatedTable;
					$output = $custom;
					if(!in_array($relatedTable, array('characteristic','product_related','user'))){
						$output .= JHTML::_('select.genericlist', $typeField, "filter[".$table->table."][".$key."][".$column."][type]", 'class="inputbox chzn-done not-processed" onchange="countresults(\''.$table->table.'\',\''.$key.'\')" size="1"', 'value', 'text', $value->data['type']);
					}
					$output .= $operators->display('filter['.$table->table.']['.$key.']['.$column.'][operator]" onchange="countresults(\''.$table->table.'\',\''.$key.'\')"',$value->data['operator'], "chzn-done not-processed");
					$output .= ' <input class="inputbox" type="text" name="filter['.$table->table.']['.$key.']['.$column.'][value]" size="50" value="'.$value->data['value'].'" onchange="countresults(\''.$table->table.'\',\''.$key.'\')" />';

					$filters_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}
			}
		}

				$loadedData->massaction_filters['__num__'] = new stdClass();
				$loadedData->massaction_filters['__num__']->name = 'limit';
				$loadedData->massaction_filters['__num__']->type = $table->table;
				$loadedData->massaction_filters['__num__']->data = array();
				$loadedData->massaction_filters['__num__']->data['start'] = '0';
				$loadedData->massaction_filters['__num__']->data['value'] = '500';
				$loadedData->massaction_filters['__num__']->html = '';

				foreach($loadedData->massaction_filters as $key => &$value) {
					if($value->name != 'limit' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$value->type = $table->table;
					if(!isset($value->data['start'])) $value->data['start'] = 0;
					if(!isset($value->data['value'])) $value->data['value'] = 500;
					$output = '<div id="'.$table->table.'filter'.$key.'limit">'.JText::_('HIKA_START').' : <input type="text" name="filter['.$table->table.']['.$key.'][limit][start]" value="'.$value->data['start'].'" /> '.JText::_('VALUE').' : <input type="text" name="filter['.$table->table.']['.$key.'][limit][value]" value="'.$value->data['value'].'"/>'.'</div>';
					$filters_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}

				$filters['ordering']=JText::_('ORDERING');
				$loadedData->massaction_filters['__num__'] = new stdClass();
				$loadedData->massaction_filters['__num__']->name = 'ordering';
				$loadedData->massaction_filters['__num__']->type = $table->table;
				$loadedData->massaction_filters['__num__']->data = array();
				$loadedData->massaction_filters['__num__']->data['value'] = '';
				$loadedData->massaction_filters['__num__']->html = '';

				foreach($loadedData->massaction_filters as $key => &$value) {
					if($value->name != 'ordering' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$value->type = $table->table;
					if(!isset($value->data['value'])) $value->data['value'] = $table->table.'_id';

					if(version_compare(JVERSION,'3.0','<')){
						$fieldsTable = $db->getTableFields('#__hikashop_'.$table->table);
						$fields = reset($fieldsTable);
					} else {
						$fields = $db->getTableColumns('#__hikashop_'.$table->table);
					}
					ksort($fields);
					if(!isset($value->data['value'])) $value->data['value'] = $table->table.'_id';
					$output = '<div id="'.$table->table.'filter'.$key.'ordering">'.JText::_('VALUE').' : ';
					$output .= '<select class="chzn-done not-processed" name="filter['.$table->table.']['.$key.'][ordering][value]">';
					foreach($fields as $field => $fieldType){
					$selected = '';
						if($value->data['value'] == $field)
							$selected = 'selected="selected"';
						$output .= '<option value="'.$field.'" '.$selected.'>'.$field.'</option>';
					}
					$output .= '</select></div>';
					$filters_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}

				$filters['direction']=JText::_('MASSACTION_ORDERING_DIRECTION');
				$loadedData->massaction_filters['__num__'] = new stdClass();
				$loadedData->massaction_filters['__num__']->name = 'direction';
				$loadedData->massaction_filters['__num__']->type = $table->table;
				$loadedData->massaction_filters['__num__']->data = array();
				$loadedData->massaction_filters['__num__']->data['value'] = '';
				$loadedData->massaction_filters['__num__']->html = '';

				foreach($loadedData->massaction_filters as $key => &$value) {
					if($value->name != 'direction' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$value->type = $table->table;
					if(!isset($value->data['value'])) $value->data['value'] = 'ASC';
					$output = '<div id="'.$table->table.'filter'.$key.'direction">'.JText::_('VALUE').' : <select class="chzn-done not-processed" name="filter['.$table->table.']['.$key.'][direction][value]">';
					$values = array('ASC','DESC');
					foreach($values as $oneValue){
						$selected = '';
						if($value->data['value'] == $oneValue)
							$selected = 'selected="selected"';
						$output .= '<option value="'.$oneValue.'" '.$selected.'>'.$oneValue.'</option>';
					}
					$output .= '</select>'.'</div>';
					$filters_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}

			if(hikashop_level(2)){
				if(in_array($table->table,array('product','category'))){
					$filters['accessLevel']=JText::_('ACCESS_LEVEL');
				}else{
					$filters['accessLevel']=JText::_('USER_WITH_ACL');
				}
				$loadedData->massaction_filters['__num__'] = new stdClass();
				$loadedData->massaction_filters['__num__']->name = 'accessLevel';
				$loadedData->massaction_filters['__num__']->type = $table->table;
				$loadedData->massaction_filters['__num__']->data = array();
				$loadedData->massaction_filters['__num__']->data['type'] = '';
				$loadedData->massaction_filters['__num__']->data['group'] = '';
				$loadedData->massaction_filters['__num__']->html = '';
				if(!HIKASHOP_J16){
					$acl = JFactory::getACL();
					$groups = $acl->get_group_children_tree( null, 'USERS', false );
				}else{
					$db = JFactory::getDBO();
					$db->setQuery('SELECT a.*, a.title as text, a.id as value  FROM #__usergroups AS a ORDER BY a.lft ASC');
					$groups = $db->loadObjectList('id');
					foreach($groups as $id => $group){
						if(isset($groups[$group->parent_id])){
							$groups[$id]->level = intval(@$groups[$group->parent_id]->level) + 1;
							$groups[$id]->text = str_repeat('- - ',$groups[$id]->level).$groups[$id]->text;
						}
					}
				}
				$inoperator = hikashop_get('type.operatorsin');
				foreach($loadedData->massaction_filters as $key => &$value) {
					if($value->name != 'accessLevel' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$value->type = $table->table;
					$inoperator->js = 'onchange="countresults(\''.$table->table.'\','.$key.')"';
					$output = $inoperator->display("filter[".$table->table."][$key][accessLevel][type]",$value->data['type'], 'chzn-done not-processed').' '.JHTML::_('select.genericlist',   $groups, "filter[".$table->table."][$key][accessLevel][group]", 'class="inputbox chzn-done not-processed" size="1" onchange="countresults(\''.$table->table.'\','.$key.')"', 'value', 'text',$value->data['group']);

					$filters_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}
			}
?>
<script type="text/javascript">
	function hikashop_verifycsvcolumns(k){
		var target = "productfilter"+k+"csvImport_verify";
		var url = "<?php echo hikashop_completeLink('massaction&task=displayassociate&tmpl=component',false,false,true); ?>";
		var data = "cid=<?php echo hikashop_getCID(); ?>&current_filter="+k+"&csv_path=" + encodeURIComponent(document.getElementById("productfilter"+k+"csvImport_path_value").value);
		if(data != ""){
			window.Oby.xRequest(url, {update: target, mode: "POST", data: data});
		}
	}
	function hikashop_switchmode(el,k) {
		var d = document, v = el.value, modes = ['upload','path'], e = null;
		for(var i = 0; i < modes.length; i++) {
			mode = modes[i];
			e = d.getElementById('productfilter'+k+'csvImport_'+mode);
			if(!e) continue;
			if(v == mode) {
				e.style.display = '';
			} else {
				e.style.display = 'none';
			}
			if(v != 'upload'){
				d.getElementById('productfilter'+k+'csvImport_path').style.display = '';
				d.getElementById('productfilter'+k+'csvImport_upload').style.display = 'none';
			}else{
				d.getElementById('productfilter'+k+'csvImport_path').style.display = 'none';
				d.getElementById('productfilter'+k+'csvImport_upload').style.display = '';
			}
		}
	}
</script>
<?php
	}

	function onMassactionTableActionsLoad(&$table,&$actions,&$actions_html,&$loadedData){
		$db = JFactory::getDBO();
		$dispTables = array();
		$updTables = array();
		$customCheckboxes = '';
		$database = JFactory::getDBO();
		$type = 'action';
		if(empty($loadedData->massaction_filters)){
			$loadedData->massaction_filters = array();
		}
		$massactionClass = hikashop_get('class.massaction');
		$nameboxType = hikashop_get('type.namebox');
		$actions['displayResults']=JText::_('DISPLAY_RESULTS');
		$actions['exportCsv']=JText::_('EXPORT_CSV');
		$actions['updateValues']=JText::_('UPDATE_VALUES');
		$actions['deleteElements']=JText::_('DELETE_ELEMENTS');
		$actions['sendEmail']=JText::_('MASS_SEND_EMAIL');
		switch($table->table){
			case 'product':
				$dispTables = array('product','price','category');
				$updTables = array('product','price');

				$actions['updateCategories']=JText::_('UPDATE_CATEGORIES');
				$actions['updateRelateds']=JText::_('UPDATE_RELATEDS');
				$actions['updateOptions']=JText::_('UPDATE_OPTIONS');
				$actions['updateCharacteristics']=JText::_('UPDATE_CHARACTERISTICS');

				break;
			case 'category':
				$dispTables = array('category');
				$updTables = array('category');
				break;
			case 'order':
				$dispTables = array('order','order_product','address','user','joomla_users');
				$updTables = array('order','order_product');
				$actions['changeStatus']=JText::_('CHANGE_STATUS');
				$actions['addProduct']=JText::_('ADD_EXISTING_PRODUCT');
				$actions['changeGroup']=JText::_('CHANGE_USER_GROUP');
				break;
			case 'user':
				$dispTables = array('user','joomla_users','address');
				$updTables = array('user','joomla_users');
				$actions['changeGroup']=JText::_('CHANGE_USER_GROUP');
				break;
			case 'address':
				$dispTables = array('address','user','joomla_users');
				$updTables = array('address');
				break;
			case 'user':
				$dispTables = array('user','joomla_users','address');
				$updTables = array('user','joomla_users');
			default:
				return false;
			break;
		}

		if(is_array($dispTables)){

			$loadedData->massaction_actions['__num__'] = new stdClass();
			$loadedData->massaction_actions['__num__']->type = $table->table;
			$loadedData->massaction_actions['__num__']->data = array();
			$loadedData->massaction_actions['__num__']->name = 'displayResults';
			$loadedData->massaction_actions['__num__']->html = '';

			foreach($loadedData->massaction_actions as $key => &$value) {
				if($value->name != 'displayResults' || ($table->table != $loadedData->massaction_table && is_int($key)))
					continue;

				$value->type = $dispTables[0];

				$margin = '';
				$output = '';
				$customCheckboxes='';
				foreach($dispTables as $relatedTable){
					if(version_compare(JVERSION,'3.0','<')){
						if(preg_match('/joomla_/',$relatedTable)) $fieldsTable = $db->getTableFields('#__'.str_replace('joomla_','',$relatedTable));
						else $fieldsTable = $db->getTableFields('#__hikashop_'.$relatedTable);
						$fields = reset($fieldsTable);
					} else {
						if(preg_match('/joomla_/',$relatedTable)) $fields = $db->getTableColumns('#__'.str_replace('joomla_','',$relatedTable));
						else $fields = $db->getTableColumns('#__hikashop_'.$relatedTable);
					}
					ksort($fields);
					if(!empty($fields)) {
						$output .= '<div id="action_'.$table->table.'_'.$key.'_displayResults_'.$relatedTable.'Column_div" class="hika_massaction_checkbox"> <a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_displayResults_'.$relatedTable.'Column_div\',\'check\');">'.JText::_('SELECT_ALL').'</a> / <a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_displayResults_'.$relatedTable.'Column_div\',\'uncheck\');">'.JText::_('UNSELECT_ALL').'</a> <br/>';
						foreach($fields as $key2 => $field){
							$checked='';
							if(isset($value->data[$relatedTable]) && isset($value->data[$relatedTable][$key2])){
								$checked='checked="checked"';
							}
							$output .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_displayResults_'.$relatedTable.'Column_'.$key2.'" name="action['.$table->table.']['.$key.'][displayResults]['.$relatedTable.']['.$key2.']" value="'.$key2.'" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_displayResults_'.$relatedTable.'Column_'.$key2.'">'.$key2.'</label><br/>';
						}
						if($relatedTable == 'order'){
							$key2 = 'order_tax_amount';
							$checked='';
							if(isset($value->data[$relatedTable]) && isset($value->data[$relatedTable][$key2])){
								$checked='checked="checked"';
							}
							$output .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_displayResults_'.$relatedTable.'Column_'.$key2.'" name="action['.$table->table.']['.$key.'][displayResults]['.$relatedTable.']['.$key2.']" value="'.$key2.'" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_displayResults_'.$relatedTable.'Column_'.$key2.'">'.$key2.'</label><br/>';
							$key2 = 'order_tax_namekey';
							$checked='';
							if(isset($value->data[$relatedTable]) && isset($value->data[$relatedTable][$key2])){
								$checked='checked="checked"';
							}
							$output .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_displayResults_'.$relatedTable.'Column_'.$key2.'" name="action['.$table->table.']['.$key.'][displayResults]['.$relatedTable.']['.$key2.']" value="'.$key2.'" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_displayResults_'.$relatedTable.'Column_'.$key2.'">'.$key2.'</label><br/>';
						}
						$output .= '</div>';
						$margin = 'margin-left: 20px;';
					}
				}

				switch($table->table){
					case 'product':
						$db->setQuery('SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id = 0');
						$characteristics = $db->loadObjectList();
						if(is_array($characteristics)){
							$customCheckboxes .= '<div id="action_'.$table->table.'_'.$key.'_displayResults_characteristicColumn_div" class="hika_massaction_checkbox"><a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_displayResults_characteristicColumn_div\',\'check\');">'.JText::_('SELECT_ALL').'</a> / <a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_displayResults_characteristicColumn_div\',\'uncheck\');">'.JText::_('UNSELECT_ALL').'</a><br/>';
							foreach($characteristics as $characteristic){
								$checked='';
								if(isset($value->data['characteristic'][$characteristic->characteristic_value])){
									$checked='checked="checked"';
								}
								$customCheckboxes .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_displayResults_characteristicColumn_'.$characteristic->characteristic_value.'" name="action['.$table->table.']['.$key.'][displayResults][characteristic]['.$characteristic->characteristic_value.']" value="'.$characteristic->characteristic_value.'" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_displayResults_characteristicColumn_'.$characteristic->characteristic_value.'">'.$characteristic->characteristic_value.'</label><br/>';
							}
							$customCheckboxes .= '</div>';
						}
						$db->setQuery('SELECT * FROM '.hikashop_table('product_related'));
						$relateds = $db->loadObjectList();
						if(is_array($relateds)){
							$customCheckboxes .= '<div id="action_'.$table->table.'_'.$key.'_displayResults_relatedColumn_div" class="hika_massaction_checkbox"><a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_displayResults_relatedColumn_div\',\'check\');">'.JText::_('SELECT_ALL').'</a> / <a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_displayResults_relatedColumn_div\',\'uncheck\');">'.JText::_('UNSELECT_ALL').'</a><br/>';
							$displayed = array();
							foreach($relateds as $related){
								$checked='';
								if(isset($value->data['related'][$related->product_related_type])){
									$checked='checked="checked"';
								}
								if(!in_array($related->product_related_type, $displayed))
									$customCheckboxes .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_displayResults_relatedColumn_'.$related->product_related_type.'" name="action['.$table->table.']['.$key.'][displayResults][related]['.$related->product_related_type.']" value="'.$related->product_related_type.'" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_displayResults_relatedColumn_'.$related->product_related_type.'">'.$related->product_related_type.'</label><br/>';
								$displayed[] = $related->product_related_type;
							}
							$customCheckboxes .= '</div>';
						}

						break;
					case 'user':
						$checked='';
						if(isset($value->data['usergroups'])){
							$checked='checked="checked"';
						}
						$customCheckboxes .= '<div class="hika_massaction_checkbox">';
						$customCheckboxes .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_displayResults_usergroupsColumn_title" name="action['.$table->table.']['.$key.'][displayResults][usergroups][title]" value="usergroups" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_displayResults_usergroupsColumn_title">'.JText::_('GROUP_NAME').'</label><br/>';
						$customCheckboxes .= '</div>';
						break;
				}

				$output .= $customCheckboxes;
				$output .= '<div style="clear:both;"></div>';

				$actions_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);

			}


			$loadedData->massaction_actions['__num__'] = new stdClass();
			$loadedData->massaction_actions['__num__']->type = $table->table;
			$loadedData->massaction_actions['__num__']->data = array('path'=>'');
			$loadedData->massaction_actions['__num__']->name = 'exportCsv';
			$loadedData->massaction_actions['__num__']->html = '';
			foreach($loadedData->massaction_actions as $key => &$value) {
				if($value->name != 'exportCsv' || ($table->table != $loadedData->massaction_table && is_int($key)))
					continue;
				if(!isset($value->data['formatExport']['path'])) $value->data['formatExport']['path'] = '';
				if(!isset($value->data['formatExport']['email'])) $value->data['formatExport']['email'] = '';
				$output='';
				$margin = '';
				$customCheckboxes='';
				foreach($dispTables as $relatedTable){
					if(version_compare(JVERSION,'3.0','<')){
						if(preg_match('/joomla_/',$relatedTable)) $fieldsTable = $db->getTableFields('#__'.str_replace('joomla_','',$relatedTable));
						else $fieldsTable = $db->getTableFields('#__hikashop_'.$relatedTable);
						$fields = reset($fieldsTable);
					} else {
						if(preg_match('/joomla_/',$relatedTable)) $fields = $db->getTableColumns('#__'.str_replace('joomla_','',$relatedTable));
						else $fields = $db->getTableColumns('#__hikashop_'.$relatedTable);
					}
					ksort($fields);
					if(!empty($fields)) {
						$output .= '<div id="action_'.$table->table.'_'.$key.'_exportCsv_'.$relatedTable.'Column_div" class="hika_massaction_checkbox"> <a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_exportCsv_'.$relatedTable.'Column_div\',\'check\');">'.JText::_('SELECT_ALL').'</a> / <a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_exportCsv_'.$relatedTable.'Column_div\',\'uncheck\');">'.JText::_('UNSELECT_ALL').'</a> <br/>';
						foreach($fields as $key2 => $field){
							$checked='';
							if(isset($value->data[$relatedTable]) && isset($value->data[$relatedTable][$key2])){
								$checked='checked="checked"';
							}
							$output .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_exportCsv_'.$relatedTable.'Column_'.$key2.'" name="action['.$table->table.']['.$key.'][exportCsv]['.$relatedTable.']['.$key2.']" value="'.$key2.'" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_exportCsv_'.$relatedTable.'Column_'.$key2.'">'.$key2.'</label><br/>';
						}
						if($relatedTable == 'price'){
							if(isset($value->data[$relatedTable]) && isset($value->data[$relatedTable]['price_value_with_tax'])){
								$checked='checked="checked"';
							}
							$output .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_exportCsv_'.$relatedTable.'Column_price_value_with_tax" name="action['.$table->table.']['.$key.'][exportCsv]['.$relatedTable.'][price_value_with_tax]" value="price_value_with_tax" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_exportCsv_'.$relatedTable.'Column_price_value_with_tax">price_value_with_tax</label><br/>';
						}
						$output .= '</div>';
						$margin = 'margin-left: 20px;';
					}
				}

				switch($table->table){
					case 'product':
						$db->setQuery('SHOW COLUMNS FROM '.hikashop_table('file'));
						$imageColumns = $db->loadObjectList();
						$customCheckboxes .= '<div id="action_'.$table->table.'_'.$key.'_exportCsv_filesColumn_div" class="hika_massaction_checkbox"><a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_exportCsv_filesColumn_div\',\'check\');">'.JText::_('SELECT_ALL').'</a> / <a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_exportCsv_filesColumn_div\',\'uncheck\');">'.JText::_('UNSELECT_ALL').'</a><br/><b>'.JText::_('FILES').'</b><br/>';
						foreach($imageColumns as $imageColumn){
							$checked='';
							if(isset($value->data['files'][$imageColumn->Field])){
								$checked='checked="checked"';
							}
							$customCheckboxes .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_exportCsv_filesColumn_'.$imageColumn->Field.'" name="action['.$table->table.']['.$key.'][exportCsv][files]['.$imageColumn->Field.']" value="'.$imageColumn->Field.'" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_exportCsv_filesColumn_'.$imageColumn->Field.'">'.$imageColumn->Field.'</label><br/>';
						}
						$customCheckboxes .= '</div>';

						$db->setQuery('SHOW COLUMNS FROM '.hikashop_table('file'));
						$imageColumns = $db->loadObjectList();
						$customCheckboxes .= '<div id="action_'.$table->table.'_'.$key.'_exportCsv_imagesColumn_div" class="hika_massaction_checkbox"><a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_exportCsv_imagesColumn_div\',\'check\');">'.JText::_('SELECT_ALL').'</a> / <a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_exportCsv_imagesColumn_div\',\'uncheck\');">'.JText::_('UNSELECT_ALL').'</a><br/><b>'.JText::_('IMAGES').'</b><br/>';
						foreach($imageColumns as $imageColumn){
							$checked='';
							if(isset($value->data['images'][$imageColumn->Field])){
								$checked='checked="checked"';
							}
							$customCheckboxes .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_exportCsv_imagesColumn_'.$imageColumn->Field.'" name="action['.$table->table.']['.$key.'][exportCsv][images]['.$imageColumn->Field.']" value="'.$imageColumn->Field.'" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_exportCsv_imagesColumn_'.$imageColumn->Field.'">'.$imageColumn->Field.'</label><br/>';
						}
						$customCheckboxes .= '</div>';

						$db->setQuery('SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id = 0');
						$characteristics = $db->loadObjectList();
						if(is_array($characteristics)){
							$customCheckboxes .= '<div id="action_'.$table->table.'_'.$key.'_exportCsv_characteristicColumn_div" class="hika_massaction_checkbox"><a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_exportCsv_characteristicColumn_div\',\'check\');">'.JText::_('SELECT_ALL').'</a> / <a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_exportCsv_characteristicColumn_div\',\'uncheck\');">'.JText::_('UNSELECT_ALL').'</a><br/>';
							foreach($characteristics as $characteristic){
								$checked='';
								if(isset($value->data['characteristic'][$characteristic->characteristic_value])){
									$checked='checked="checked"';
								}
								$customCheckboxes .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_exportCsv_characteristicColumn_'.$characteristic->characteristic_value.'" name="action['.$table->table.']['.$key.'][exportCsv][characteristic]['.$characteristic->characteristic_value.']" value="'.$characteristic->characteristic_value.'" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_exportCsv_characteristicColumn_'.$characteristic->characteristic_value.'">'.$characteristic->characteristic_value.'</label><br/>';
							}
							$customCheckboxes .= '</div>';
						}
						$db->setQuery('SELECT * FROM '.hikashop_table('product_related'));
						$relateds = $db->loadObjectList();
						if(is_array($relateds)){
							$customCheckboxes .= '<div id="action_'.$table->table.'_'.$key.'_exportCsv_relatedColumn_div" class="hika_massaction_checkbox"><a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_exportCsv_relatedColumn_div\',\'check\');">'.JText::_('SELECT_ALL').'</a> / <a style="cursor: pointer;" onclick="checkAll(\'action_'.$table->table.'_'.$key.'_exportCsv_relatedColumn_div\',\'uncheck\');">'.JText::_('UNSELECT_ALL').'</a><br/>';
							$displayed = array();
							foreach($relateds as $related){
								$checked='';
								if(isset($value->data['related'][$related->product_related_type])){
									$checked='checked="checked"';
								}
								if(!in_array($related->product_related_type, $displayed))
									$customCheckboxes .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_exportCsv_relatedColumn_'.$related->product_related_type.'" name="action['.$table->table.']['.$key.'][exportCsv][related]['.$related->product_related_type.']" value="'.$related->product_related_type.'" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_exportCsv_relatedColumn_'.$related->product_related_type.'">'.$related->product_related_type.'</label><br/>';
								$displayed[] = $related->product_related_type;
							}
							$customCheckboxes .= '</div>';
						}

						break;
					case 'user':
						$checked='';
						if(isset($value->data['usergroups'])){
							$checked='checked="checked"';
						}
						$customCheckboxes .= '<div class="hika_massaction_checkbox">';
						$customCheckboxes .= '<input type="checkbox" '.$checked.' id="action_'.$table->table.'_'.$key.'_exportCsv_usergroupsColumn_title" name="action['.$table->table.']['.$key.'][exportCsv][usergroups][title]" value="usergroups" /><label style="width: 100%;" for="action_'.$table->table.'_'.$key.'_exportCsv_usergroupsColumn_title">'.JText::_('GROUP_NAME').'</label><br/>';
						$customCheckboxes .= '</div>';
						break;
				}

				$output .= $customCheckboxes;
				$output .= '<div style="clear:both;"></div>';
				$checked='';
				if(isset($value->data['formatExport']['format']) && $value->data['formatExport']['format']=='xls')
					$checked='checked="checked"';
				$output .='<input type="radio" id="action'.$table->table.''.$key.'exportCsvformatExportformatcsv" name="action['.$table->table.']['.$key.'][exportCsv][formatExport][format]" checked value="csv"> <label for="action'.$table->table.''.$key.'exportCsvformatExportformatcsv">'.JText::_('CSV').'</label> <input type="radio" id="action'.$table->table.''.$key.'exportCsvformatExportformatxls" name="action['.$table->table.']['.$key.'][exportCsv][formatExport][format]" '.$checked.' value="xls"> <label for="action'.$table->table.''.$key.'exportCsvformatExportformatxls">'.JText::_('XLS').'</label>';
				$output .= '<br/>'.JText::_('EXPORT_PATH').': <input type="text" name="action['.$table->table.']['.$key.'][exportCsv][formatExport][path]" value="'.$value->data['formatExport']['path'].'" />';
				$output .= '<br/>'.JText::_('TO_ADDRESS').': <input type="text" name="action['.$table->table.']['.$key.'][exportCsv][formatExport][email]" value="'.$value->data['formatExport']['email'].'" /> '.JText::_('FILL_PATH_TO_USE_EMAIL');
				$output .= '<div style="clear:both;"></div>';

				$actions_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
			}


			$loadedData->massaction_actions['__num__'] = new stdClass();
			$loadedData->massaction_actions['__num__']->type = $table->table;
			$loadedData->massaction_actions['__num__']->data = array('type' => '','value' => '','operation' => '');
			$loadedData->massaction_actions['__num__']->name = 'updateValues';
			$loadedData->massaction_actions['__num__']->html = '';

			foreach($loadedData->massaction_actions as $key => &$value) {
				if($value->name != 'updateValues' || ($table->table != $loadedData->massaction_table && is_int($key)))
					continue;

				$output='';
				$typeField = array();
				foreach($updTables as $relatedTable){
					if(version_compare(JVERSION,'3.0','<')){
						if(preg_match('/joomla_/',$relatedTable)){
							$fieldsTable = $db->getTableFields('#__'.str_replace('joomla_','',$relatedTable));
							$fields = reset($fieldsTable);
							foreach($fields as $key2 => $field){
								$fields[$relatedTable.'_'.$key2] = $fields[$key2];
								unset($fields[$key2]);
							}
							$fieldsTable = $fields;
						}
						else{
							$fieldsTable = $db->getTableFields('#__hikashop_'.$relatedTable);
							$fields = reset($fieldsTable);
						}
					} else {
						if(preg_match('/joomla_/',$relatedTable)){
							$fields = $db->getTableColumns('#__'.str_replace('joomla_','',$relatedTable));
							foreach($fields as $key2 => $field){
								$fields[$relatedTable.'_'.$key2] = $fields[$key2];
								unset($fields[$key2]);
							}
						}
						else $fields = $db->getTableColumns('#__hikashop_'.$relatedTable);
					}
					ksort($fields);
					$typeField[] = JHTML::_('select.option', '<OPTGROUP>',JText::_(strtoupper($relatedTable)));
					if(!empty($fields)) {
						foreach($fields as $key2 => $field){
							$typeField[] = JHTML::_('select.option',$key2,$key2);
						}
					}
					$typeField[] = JHTML::_('select.option', '</OPTGROUP>');
				}

				$selected1='';$selected2='';$selected3='';$selected4='';
				$operations=array('int', 'float', 'string', 'operation');
				$options='';
				foreach($operations as $op){
					$selected='';
					if($op==$value->data['operation'])
						$selected='selected="selected"';
					$options .='<option '.$selected.' value="'.$op.'">'.JText::_(strtoupper($op)).'</option>';
				}
				$output .= JHTML::_('select.genericlist', $typeField, "action[".$table->table."][".$key."][updateValues][type]", 'class="inputbox chzn-done not-processed"  size="1"', 'value', 'text', $value->data['type']);
				$output .= ' = <select class=" chzn-done not-processed" onchange="if(this.value == \'operation\'){document.getElementById(\'updateValues_message\').style.display = \'inline\';}" name="action['.$table->table.']['.$key.'][updateValues][operation]">
														'.$options.'
													 </select>';
				$output .= ' <input class="inputbox" type="text" name="action['.$table->table.']['.$key.'][updateValues][value]" size="50" value="'. htmlspecialchars($value->data['value'], ENT_COMPAT, 'UTF-8').'"  />';

				$actions_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
			}



			$loadedData->massaction_actions['__num__'] = new stdClass();
			$loadedData->massaction_actions['__num__']->type = $table->table;
			$loadedData->massaction_actions['__num__']->name = 'deleteElements';
			$loadedData->massaction_actions['__num__']->html = '';

			foreach($loadedData->massaction_actions as $key => &$value) {
				if($value->name != 'deleteElements' || ($table->table != $loadedData->massaction_table && is_int($key)))
					continue;

				$output = JText::_('DELETE_FILTERED_ELEMENTS'); //'This will delete all the elements returned in the filter.';
				$actions_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);

			}


			$loadedData->massaction_actions['__num__'] = new stdClass();
			$loadedData->massaction_actions['__num__']->type = $table->table;
			$loadedData->massaction_actions['__num__']->data = array('emailAddress' => '','emailSubject' => '','bodyData' => '');
			$loadedData->massaction_actions['__num__']->name = 'sendEmail';
			$loadedData->massaction_actions['__num__']->html = '';

			foreach($loadedData->massaction_actions as $key => &$value) {
				if($value->name != 'sendEmail' || ($table->table != $loadedData->massaction_table && is_int($key)))
					continue;
				if(!isset($value->data['emailAddress'])) $value->data['emailAddress'] = '';
				if(!isset($value->data['emailSubject'])) $value->data['emailSubject'] = '';
				if(!isset($value->data['bodyData'])) $value->data['bodyData'] = '';
				$output .= '<br/>'.JText::_('TO_ADDRESS').': <input type="text" name="action['.$table->table.']['.$key.'][sendEmail][emailAddress]" value="'.$value->data['emailAddress'].'" />';
				$output .= '<br/>'.JText::_('EMAIL_SUBJECT').': <input type="text" name="action['.$table->table.']['.$key.'][sendEmail][emailSubject]" value="'.$value->data['emailSubject'].'" />';
				$output .= '<br/>'.JText::_('MASS_EMAIL_BODY_DATA').': <textarea name="action['.$table->table.']['.$key.'][sendEmail][bodyData]">'.$value->data['bodyData'].'</textarea>';
				$actions_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);

			}

			if($loadedData->massaction_table == 'order'){

				$loadedData->massaction_actions['__num__'] = new stdClass();
				$loadedData->massaction_actions['__num__']->type = $table->table;
				$loadedData->massaction_actions['__num__']->name = 'changeStatus';
				$loadedData->massaction_actions['__num__']->html = '';
				$loadedData->massaction_actions['__num__']->data = array('value' => '', 'notify' => '');

				foreach($loadedData->massaction_actions as $key => &$value) {
					if(($value->name != 'changeStatus' || ($table->table != $loadedData->massaction_table && is_int($key))))
						continue;

					$db->setQuery('SELECT `category_name` FROM '.hikashop_table('category').' WHERE `category_type` = '.$db->quote('status').' AND `category_name` != '.$db->quote('order status'));
					$orderStatuses = $db->loadObjectList();

					$output='<div id="'.$table->table.'action'.$key.'changeStatus">';
					$output.= JText::_('NEW_ORDER_STATUS').': <select class="chzn-done not-processed" id="action_'.$table->table.'_'.$key.'_changeStatus_value" name="action['.$table->table.']['.$key.'][changeStatus][value]">';
					if(is_array($orderStatuses)){
						foreach($orderStatuses as $orderStatus){
							$orderStatus = $orderStatus->category_name;
							$selected='';
							if($orderStatus==$value->data['value']){
								$selected='selected="selected"';
							}
							$output.='<option '.$selected.' value="'.$orderStatus.'">'.JText::_($orderStatus).'</option>';
						}
					}
					$checked='';
					if(isset($value->data['notify']) && $value->data['notify']==1){
						$checked='checked="checked"';
					}
					$output.='</select><input type="checkbox" '.$checked.' value="1" id="action_'.$table->table.'_'.$key.'_changeStatus_notify" name="action['.$table->table.']['.$key.'][changeStatus][notify]"/><label for="action_'.$table->table.'_'.$key.'_changeStatus_notify">'.JText::_('SEND_NOTIFICATION_EMAIL').'</label></div>';
					$actions_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}


				$loadedData->massaction_actions['__num__'] = new stdClass();
				$loadedData->massaction_actions['__num__']->type = $table->table;
				$loadedData->massaction_actions['__num__']->name = 'addProduct';
				$loadedData->massaction_actions['__num__']->html = '';
				$loadedData->massaction_actions['__num__']->data = array('value' => '', 'type' => '', 'quantity' => '1');

				foreach($loadedData->massaction_actions as $key => &$value) {
					if($value->name != 'addProduct' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;
					if(!isset($value->data['type'])) $value->data['type'] = 'add';
					$products=array();
					if(!empty($value->data) && !empty($value->data['value'])){
						JArrayHelper::toInteger($value->data['value']);
						$query = 'SELECT product_id,product_name FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$value->data['value']).')';
						$database->setQuery($query);
						if(!HIKASHOP_J25){
							$products = $database->loadResultArray();
						} else {
							$products = $database->loadColumn();
						}
					}
					if(!isset($value->data['quantity'])) $value->data['quantity'] = '1';

					$productSelect = $nameboxType->display(
						'action['.$table->table.']['.$key.'][addProduct][value]',
						$products,
						hikashopNameboxType::NAMEBOX_MULTIPLE,
						'product',
						array(
							'delete' => true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
						)
					);

					$output ='<select class="chzn-done not-processed" id="action_'.$table->table.'_'.$key.'_addProduct_type" name="action['.$table->table.']['.$key.'][addProduct][type]">';
					$datas = array('add'=>'ADD', 'remove'=>'REMOVE');
					foreach($datas as $k => $data){
						$selected = '';
						if($k == $value->data['type']) $selected = 'selected="selected"';
						$output .='<option value="'.$k.'" '.$selected.'>'.JText::_($data).'</option>';
					}
					$output .='</select>';
					$output .='<input class="inputbox" type="text" name="action['.$table->table.']['.$key.'][addProduct][quantity]" size="10" value="'.$value->data['quantity'].'"  /> '.JText::_('PRODUCTS');
					$output .= $productSelect;
					if(isset($value->data['update'])) $checked = 'checked="checked"';
					else $checked = '';
					$output .= '<input type="checkbox" value="update" id="action_'.$table->table.'_'.$key.'_addProduct_update" name="action['.$table->table.']['.$key.'][addProduct][update]" '.$checked.'/><label for="action_'.$table->table.'_'.$key.'_addProduct_update">'.JText::_('UPDATE_PRODUCT_STOCK').'</label>';

					$actions_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}
			}

			if($loadedData->massaction_table == 'product'){


				$loadedData->massaction_actions['__num__'] = new stdClass();
				$loadedData->massaction_actions['__num__']->type = $table->table;
				$loadedData->massaction_actions['__num__']->name = 'updateCategories';
				$loadedData->massaction_actions['__num__']->html = '';
				$loadedData->massaction_actions['__num__']->data = array('value' => '', 'type' => '');

				foreach($loadedData->massaction_actions as $key => &$value) {
					if($value->name != 'updateCategories' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					if(empty($value->data['type']))
						$value->data['type'] = 'add';

					$categories=array();
					if(!empty($value->data) && !empty($value->data['value'])){
						$query = 'SELECT category_id, category_name FROM '.hikashop_table('category').' WHERE category_id IN ('.implode($value->data['value'],',').')';
						$database->setQuery($query);
						if(!HIKASHOP_J25){
							$categories = $database->loadResultArray();
						} else {
							$categories = $database->loadColumn();
						}
					}

					$categorySelect = $nameboxType->display(
						'action['.$table->table.']['.$key.'][updateCategories][value]',
						$categories,
						hikashopNameboxType::NAMEBOX_MULTIPLE,
						'category',
						array(
							'delete' => true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
						)
					);

					$output ='<select id="action_'.$table->table.'_'.$key.'_updateCategories_type" class="select-listing chzn-done not-processed" name="action['.$table->table.']['.$key.'][updateCategories][type]">';
					$datas = array('add'=>'ADD', 'replace'=>'REPLACE','remove'=>'REMOVE');
					foreach($datas as $k => $data){
						$selected = '';
						if($k == $value->data['type']) $selected = 'selected="selected"';
						$output .='<option value="'.$k.'" '.$selected.'>'.JText::_($data).'</option>';
					}
					$output .='</select>';
					$output .= $categorySelect;

					$actions_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}




				$loadedData->massaction_actions['__num__'] = new stdClass();
				$loadedData->massaction_actions['__num__']->type = $table->table;
				$loadedData->massaction_actions['__num__']->name = 'updateRelateds';
				$loadedData->massaction_actions['__num__']->html = '';
				$loadedData->massaction_actions['__num__']->data = array('value' => '', 'type' => '');


				foreach($loadedData->massaction_actions as $key => &$value) {
					if($value->name != 'updateRelateds' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$products=array();
					if(!empty($value->data) && !empty($value->data['value'])){
						JArrayHelper::toInteger($value->data['value']);
						$query = 'SELECT product_id,product_name FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$value->data['value']).')';
						$database->setQuery($query);
						if(!HIKASHOP_J25){
							$products = $database->loadResultArray();
						} else {
							$products = $database->loadColumn();
						}
					}
					$productSelect = $nameboxType->display(
						'action['.$table->table.']['.$key.'][updateRelateds][value]',
						$products,
						hikashopNameboxType::NAMEBOX_MULTIPLE,
						'product',
						array(
							'delete' => true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
						)
					);

					$output ='<select class="chzn-done not-processed" id="action_'.$table->table.'_'.$key.'_updateRelateds_type" name="action['.$table->table.']['.$key.'][updateRelateds][type]">';
					$datas = array('add'=>'ADD', 'replace'=>'REPLACE');
					foreach($datas as $k => $data){
						$selected = '';
						if($k == $value->data['type']) $selected = 'selected="selected"';
						$output .='<option value="'.$k.'" '.$selected.'>'.JText::_($data).'</option>';
					}
					$output .='</select>';
					$output .= $productSelect;

					$actions_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}



				$loadedData->massaction_actions['__num__'] = new stdClass();
				$loadedData->massaction_actions['__num__']->type = $table->table;
				$loadedData->massaction_actions['__num__']->name = 'updateOptions';
				$loadedData->massaction_actions['__num__']->html = '';
				$loadedData->massaction_actions['__num__']->data = array('value' => '', 'type' => '');


				foreach($loadedData->massaction_actions as $key => &$value) {
					if($value->name != 'updateOptions' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$options=array();
					if(!empty($value->data) && !empty($value->data['value'])){
						JArrayHelper::toInteger($value->data['value']);
						$query = 'SELECT product_id,product_name FROM '.hikashop_table('product').' WHERE product_id IN ('.implode($value->data['value'],',').')';
						$database->setQuery($query);
						if(!HIKASHOP_J25){
							$options = $database->loadResultArray();
						} else {
							$options = $database->loadColumn();
						}
					}
					$productSelect = $nameboxType->display(
						'action['.$table->table.']['.$key.'][updateOptions][value]',
						$options,
						hikashopNameboxType::NAMEBOX_MULTIPLE,
						'product',
						array(
							'delete' => true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
						)
					);

					$output ='<select class="chzn-done not-processed" id="action_'.$table->table.'_'.$key.'_updateOptions_type" name="action['.$table->table.']['.$key.'][updateOptions][type]">';
					$datas = array('add'=>'ADD', 'replace'=>'REPLACE');
					foreach($datas as $k => $data){
						$selected = '';
						if($k == $value->data['type']) $selected = 'selected="selected"';
						$output .='<option value="'.$k.'" '.$selected.'>'.JText::_($data).'</option>';
					}
					$output .='</select>';
					$output .= $productSelect;

					$actions_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}



				$loadedData->massaction_actions['__num__'] = new stdClass();
				$loadedData->massaction_actions['__num__']->type = $table->table;
				$loadedData->massaction_actions['__num__']->name = 'updateCharacteristics';
				$loadedData->massaction_actions['__num__']->html = '';
				$loadedData->massaction_actions['__num__']->data = array('value' => '', 'type' => '');


				foreach($loadedData->massaction_actions as $key => &$value) {
					if($value->name != 'updateCharacteristics' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;

					$characteristics=array();
					$query = 'SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id = 0';
					$database->setQuery($query);
					$characteristics = $database->loadObjectList();

					if(!empty($characteristics)){
						$output ='<select class="chzn-done not-processed" id="action_'.$table->table.'_'.$key.'_updateCharacteristics_type" name="action['.$table->table.']['.$key.'][updateCharacteristics][type]">';
						$datas = array('add'=>'ADD', 'delete'=>'HIKA_DELETE');
						foreach($datas as $k => $data){
							$selected = '';
							if($k == $value->data['type']) $selected = 'selected="selected"';
							$output .='<option value="'.$k.'" '.$selected.'>'.JText::_($data).'</option>';
						}
						$output .='</select><br/><div class="hika_massaction_checkbox" >';
						if(!isset($value->data['value'])) $value->data['value'] = '';
						if(!is_array($value->data['value'])) $value->data['value'] = (array)$value->data['value'];
							foreach($characteristics as $characteristic){
								$checked = '';
								if(in_array($characteristic->characteristic_id,$value->data['value'])) $checked = 'checked="checked"';
								$output .= '<br/><input type="checkbox" name="action['.$table->table.']['.$key.'][updateCharacteristics][value][]" '.$checked.' value="'.$characteristic->characteristic_id.'" />'.$characteristic->characteristic_value;
							}
						$output .= '</div>';
					}else{
						$output = '<div class="alert">'.JText::_('MASSACTION_NO_CHARACTERISTICS').'</div>';
					}

					$actions_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}
			}

			if($loadedData->massaction_table == 'order' || $loadedData->massaction_table == 'user'){
				$loadedData->massaction_actions['__num__'] = new stdClass();
				$loadedData->massaction_actions['__num__']->type = $table->table;
				$loadedData->massaction_actions['__num__']->name = 'changeGroup';
				$loadedData->massaction_actions['__num__']->html = '';
				$loadedData->massaction_actions['__num__']->data = array('value' => '', 'type' => '');


				foreach($loadedData->massaction_actions as $key => &$value) {
					if($value->name != 'changeGroup' || ($table->table != $loadedData->massaction_table && is_int($key)))
						continue;
					if(!isset($value->data['type'])) $value->data['type'] = 'add';
					if(!isset($value->data['value'])) $value->data['value'] = '1';
					if(HIKASHOP_J25){
						$query = 'SELECT * FROM '.hikashop_table('usergroups',false);
					}else{
						$query = 'SELECT * FROM '.hikashop_table('core_acl_aro_groups',false);
					}
					$database->setQuery($query);
					$groups = $database->loadObjectList();
					if(HIKASHOP_J25){
						$output ='<select class="chzn-done not-processed" id="action_'.$table->table.'_'.$key.'_changeGroup_type" name="action['.$table->table.']['.$key.'][changeGroup][type]">';
						$datas = array('add'=>'ADD', 'replace'=>'REPLACE');
						foreach($datas as $k => $data){
							$selected = '';
							if($k == $value->data['type']) $selected = 'selected="selected"';
							$output .='<option value="'.$k.'" '.$selected.'>'.JText::_($data).'</option>';
						}
						$output .='</select>';
					}else{
						$output = JText::_('REPLACE_BY').' ';
					}
					$output .= '<select class="chzn-done not-processed" id="action_'.$table->table.'_'.$key.'_changeGroup_value" name="action['.$table->table.']['.$key.'][changeGroup][value]">'; // categories
					foreach($groups as $group){
						$selected = '';
						if($group->id == $value->data['value']) $selected = 'selected="selected"';
						if(HIKASHOP_J25){
							$output .='<option value="'.$group->id.'" '.$selected.'>'.JText::_($group->title).'</option>';
						}else{
							$output .='<option value="'.$group->id.'" '.$selected.'>'.JText::_($group->name).'</option>';
						}
					}
					$output .= '</select>';

					$actions_html[$value->name] = $massactionClass->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
				}
			}
		}else{
			$actions_html['displayResults'] = '<div id="'.$table->table.'action__num__displayResults"></div>';
			$actions_html['exportCsv'] = '<div id="'.$table->table.'action__num__exportCsv"></div>';
			$actions_html['updateValues'] = '<div id="'.$table->table.'action__num__updateValues"></div>';
		}

		$js="
			function checkAll(id, type){
				var toCheck = document.getElementById(id).getElementsByTagName('input');
				for (i = 0 ; i < toCheck.length ; i++) {
					if (toCheck[i].type == 'checkbox') {
						if(type == 'check'){
							toCheck[i].checked = true;
						}else{
							toCheck[i].checked = false;
						}
					}
				}
			}";

		if(!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		} else {
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration( "<!--\n".$js."\n//-->\n" );
	}

	function onReloadPageMassActionAfterEdition(&$reload){
		$reload['category']['category_id']='category_id';
		$reload['price']['price_product_id']='price_product_id';
		$reload['price']['price_currency_id']='price_currency_id';
		$reload['product']['product_dimension_unit']='product_dimension_unit';
		$reload['address']['address_id']='address_id';
		$reload['product'][]='';
		$reload['product'][]='';
	}

	function onSaveEditionSquareMassAction($data,$data_id,$table,$column,$value,$id,$type){
		$database = JFactory::getDBO();
		switch($data){
			case 'product':
			$class = hikashop_get('class.product');
				switch($table){
					case 'category':
						if($class->getProducts($data_id)){
							$object = $class->get($data_id);
						}
						$query = 'SELECT category_id';
						$query .= ' FROM '.hikashop_table('product_category');
						$query .= ' WHERE product_id='.$database->Quote($data_id).' AND category_id !='.$database->Quote($id);
						$database->setQuery($query);
						$tmp = $database->loadObjectList();
						$categories[$value] = $value;
						foreach($tmp as $val){
							$categories[$val->category_id] = $val->category_id;
						}
						unset($object->alias);
						$object->categories = $categories;
						$class->updateCategories($object,$data_id);
						break;
					case 'price':
						if($class->getProducts($data_id)){
							$object = $class->get($data_id);
						}
						$query = 'SELECT *';
						$query .= ' FROM '.hikashop_table('price');
						$query .= ' WHERE price_product_id='.$database->Quote($data_id);
						$database->setQuery($query);
						$prices = $database->loadObjectList();
						foreach($prices as $price){
							if($price->price_id == $id){
								$price->$column = $value;
							}
						}
						unset($object->alias);
						$object->prices = $prices;
						$class->updatePrices($object,$data_id);
						break;
					case 'characteristic':
						if($class->getProducts($data_id)){
							$object = $class->get($data_id);
						}
						$characteristics = array();

						$query = 'SELECT c1.characteristic_id as \'default_id\',c2.characteristic_id,c1.characteristic_parent_id,v2.ordering,c2.characteristic_value';
						$query .= ' FROM '.hikashop_table('variant').' AS v1';
						$query .= ' INNER JOIN '.hikashop_table('characteristic').' AS c1 ON c1.characteristic_id = v1.variant_characteristic_id';
						$query .= ' INNER JOIN '.hikashop_table('variant').' AS v2 ON c1.characteristic_parent_id = v2.variant_characteristic_id';
						$query .= ' INNER JOIN '.hikashop_table('characteristic').' AS c2 ON c2.characteristic_parent_id = c1.characteristic_parent_id';
						$query .= ' WHERE c1.characteristic_parent_id!=0 AND v1.variant_product_id='.$database->Quote($data_id);
						$database->setQuery($query);
						$results = $database->loadObjectList();

						foreach($results as $result){
							$test = false;
							foreach($characteristics as $charac){
								if($charac->characteristic_id == $result->characteristic_parent_id){
									$charac->values[$result->characteristic_id] = $result->characteristic_value;
									if($result->characteristic_value == $value){
										$charac->default_id = $result->characteristic_id;
									}
									$test = true;
								}
							}
							if(!$test){
								$tmp = new stdClass();
								$tmp->characteristic_id = $result->characteristic_parent_id;
								$tmp->ordering = $result->ordering;
								if($result->characteristic_value == $value){
									$tmp->default_id = $result->characteristic_id;
								}else{
									$tmp->default_id = $result->default_id;
								}
								$tmp->values[$result->characteristic_id] = $result->characteristic_value;
								$characteristics[] = $tmp;
								$object->oldCharacteristics[] = $result->characteristic_parent_id;
							}
						}
						foreach($characteristics as $characteristic){
							foreach($characteristic->values as $v=>$k){
								if($v == $value){
									$characteristic->default_id = $value;
								}
							}
						}
						$object->characteristics = $characteristics;
						$class->updateCharacteristics($object,$data_id);
						break;
					case 'product' :
						if($class->getProducts($data_id)){
							$object = $class->get($data_id);
						}
						unset($object->alias);
						$object->$column = $value;
						$class->save($object);
						break;

					case 'related' :
						if($class->getProducts($data_id)){
							$object = $class->get($data_id);
						}
						unset($object->alias);

						$query = 'SELECT product_related_id';
						$query .= ' FROM '.hikashop_table('product_related');
						$query .= ' WHERE product_id='.$database->Quote($data_id).' AND product_related_type = \'related\'';
						$database->setQuery($query);
						$results = $database->loadObjectList();
						$related = array();
						foreach($results as $result){
							if($result->product_related_id != $id){
								$related[$result->product_related_id] = new stdClass();
								$related[$result->product_related_id]->product_related_id = $result->product_related_id;
								$related[$result->product_related_id]->product_related_ordering = 0;
							}
						}
						$related[$value] = new stdClass();
						$related[$value]->product_related_id = $value;
						$related[$value]->product_related_ordering = 0;
						$object->related = $related;
						$class->updateRelated($object,$data_id,'related');
						break;
					case 'options' :
						if($class->getProducts($data_id)){
							$object = $class->get($data_id);
						}
						unset($object->alias);

						$query = 'SELECT product_related_id';
						$query .= ' FROM '.hikashop_table('product_related');
						$query .= ' WHERE product_id='.$database->Quote($data_id).' AND product_related_type = \'options\'';
						$database->setQuery($query);
						$results = $database->loadObjectList();
						$options = array();
						foreach($results as $result){
							if($result->product_related_id != $id){
								$options[$result->product_related_id] = new stdClass();
								$options[$result->product_related_id]->product_related_id = $result->product_related_id;
								$options[$result->product_related_id]->product_related_ordering = 0;
							}
						}
						$options[$value] = new stdClass();
						$options[$value]->product_related_id = $value;
						$options[$value]->product_related_ordering = 0;
						$object->options = $options;
						$class->updateRelated($object,$data_id,'options');
						break;
				}
				break;

			case 'category':
				$class = hikashop_get('class.category');
				switch($table){
					case 'category':
						$object = $class->get($data_id);
						if($object){
							$object->$column = $value;
							$class->save($object);
						}
						break;
				}
				break;
			case 'user':
				$class = hikashop_get('class.user');
				switch($table){
					case 'user':
						$object = $class->get($data_id);
						foreach($object as $key=>$element){
							if(!strstr($key,'user_')){
								unset($object->$key);
							}
						}
						if($object){
							$object->$column = $value;
							$class->save($object);
						}
						break;
					case 'address':
						$address = hikashop_get('class.address');
						$object = $address->get($id);
						$object->$column = $value;
						$address->save($object);
						break;
					case 'usergroup':
						die('Never');
						break;
					case 'joomla_users':
						if($column == 'joomla_users_id'){
							$column = 'id';
						}
						$user = JFactory::getUser($id);
						if(!empty($user)){
							$user->$column = $value;
							$user->save();
						}
						break;
				}
				break;

			case 'order':
				$class = hikashop_get('class.order');
				switch($table){
					case 'address':
						$order = $class->get($data_id);
						$address = hikashop_get('class.address');
						$object = $address->get($id);
						$object->$column = $value;

						if($order->order_shipping_address_id == $id && $order->order_billing_address_id == $id){
							$address->save($object);
							$class->save($order);
						}else if($order->order_shipping_address_id == $id){
							$order->order_shipping_address_id = $address->save($object,$data_id,'shipping');
							$class->save($order);
						}else if($order->order_billing_address_id == $id){
							$order->order_billing_address_id = $address->save($object,$data_id,'billing');
							$class->save($order);
						}
						break;
					case 'order_product':
						$info = $class->get($data_id);

						$query = 'SELECT *';
						$query .= ' FROM '.hikashop_table('order_product');
						$query .= ' WHERE order_product_id='.$database->Quote($id);
						$database->setQuery($query);
						$row = $database->loadObject();
						$object = new stdClass();
						$object->order_id = $data_id;
						$object->product = $row;
						$object->product->$column = $value;

						$history = new stdClass();
						$history->history_reason = JText::sprintf('MODIFICATION_USERS');
						$history->history_notified = '0';
						$history->history_type = 'modification';

						$object->history = $history;
						$class->save($object);
						break;
					case 'order' :
						$object = $class->get($data_id);
						if($object){
							if(isset($_POST['checkbox'])){
								$history->history_reason = JText::sprintf('MODIFICATION_USERS');
								$history->history_notified = '1';
								$history->history_type = 'modification';
							}
							$object->$column = $value;
							$class->save($object);
						}
						break;
					case 'payment':
						$query = 'SELECT payment_type';
						$query .= ' FROM '.hikashop_table('payment');
						$query .= ' WHERE payment_id='.$database->Quote($value);
						$database->setQuery($query);
						$row = $database->loadObject();

						$object = $class->get($data_id);
						$object->order_payment_id = $value;
						$object->order_payment_method = $row->payment_type;
						$history = new stdClass();
						$history->history_reason = JText::sprintf('MODIFICATION_USERS');
						$history->history_notified = '0';
						$history->history_type = 'modification';
						$object->history = $history;
						$class->save($object);

						break;
					case 'shipping':
						$query = 'SELECT shipping_type';
						$query .= ' FROM '.hikashop_table('shipping');
						$query .= ' WHERE shipping_id='.$database->Quote($value);
						$database->setQuery($query);
						$row = $database->loadObject();

						$object = $class->get($data_id);
						$object->order_shipping_id = $value;
						$object->order_shipping_method = $row->shipping_type;
						$history = new stdClass();
						$history->history_reason = JText::sprintf('MODIFICATION_USERS');
						$history->history_notified = '0';
						$history->history_type = 'modification';
						$object->history = $history;
						$class->save($object);

						break;

						case 'user':
							die('Never');
							break;
						case 'joomla_users':
							die('Never');
							break;
					}
				break;

			case 'address':
				$class = hikashop_get('class.address');
				switch($table){
					case 'address':
						$object = $class->get($data_id);
						$object->$column = $value;
						$class->save($object);
						break;
					case 'user':
						$user = hikashop_get('class.user');
						$object = $user->get($id);
						foreach($object as $key=>$element){
							if(!strstr($key,'user_')){
								unset($object->$key);
							}
						}
						if($object){
							$object->$column = $value;
							$user->save($object,true);
						}

						break;

					case 'joomla_users':
						if($column == 'joomla_users_id'){
							$column = 'id';
						}
						$user = JFactory::getUser($id);
						if(!empty($user)){
							$user->$column = $value;
							$user->save();
						}
						break;
				}
				break;
		}
	}

	function onLoadDatatMassActionBeforeEdition($data,$data_id,$table,$column,$type,$ids,&$query,&$view){
		$database = JFactory::getDBO();
		JArrayHelper::toInteger($ids);
		switch($type){
			case 'price':
				$query = 'SELECT '.$column.','.$table.'_id';
				$query .= ' FROM '.hikashop_table($table);
				$query .= ' WHERE '.$table.'_id IN ('.implode(',',$ids).')';

				break;

			case 'joomla_users':
				if($column == 'jommla_users_id'){
					$column = 'id';
				}
				$query = 'SELECT DISTINCT '.$column.', id as \'joomla_users_id\'';
				$query .= ' FROM '.hikashop_table('users',false);
				$query .= ' WHERE id IN ('.implode(',',$ids).')';
				break;

			case 'layout':
				$layout = hikashop_get('type.layout');
				$view->assignRef('layout',$layout);

				$query = 'SELECT '.$column.','.$table.'_id';
				$query .= ' FROM '.hikashop_table($table);
				$query .= ' WHERE '.$table.'_id IN ('.implode(',',$ids).')';
				break;

			case 'method_name':

				$query = 'SELECT '.$column.','.$table.'_id,'.$table.'_type';
				$query .= ' FROM '.hikashop_table($table);

				break;

			case 'usergroups':

				$query = 'SELECT DISTINCT title, id as \'usergroups_id\'';
				$query .= ' FROM '.hikashop_table('usergroups',false);

				break;
			case 'status':
				$status = hikashop_get('type.categorysub');
				$status->type = 'status';
				$view->assignRef('status',$status);


				$query = 'SELECT '.$column.','.$table.'_id';
				$query .= ' FROM '.hikashop_table($table);
				$query .= ' WHERE '.$table.'_id IN ('.implode(',',$ids).')';

				break;
			case 'yesno':
				$query = 'SELECT '.$column.','.$table.'_id';
				$query .= ' FROM '.hikashop_table($table);
				$query .= ' WHERE '.$table.'_id IN ('.implode(',',$ids).')';

				break;
			case 'currency':
				$types = hikashop_get('type.currency');
				$view->assignRef('types',$types);

				$query = 'SELECT '.$column.','.$table.'_id';
				$query .= ' FROM '.hikashop_table($table);
				$query .= ' WHERE '.$table.'_id IN ('.implode(',',$ids).')';

				break;
			case 'dimension':
				$query = 'SELECT '.$column.','.$table.'_id';
				$query .= ' FROM '.hikashop_table($table);
				$query .= ' WHERE '.$table.'_id IN ('.implode(',',$ids).')';
				break;

			case 'dimension_unit':
				$volume = hikashop_get('type.volume');
				$view->assignRef('volume',$volume);
				$query = 'SELECT '.$column.','.$table.'_id';
				$query .= ' FROM '.hikashop_table($table);
				$query .= ' WHERE '.$table.'_id IN ('.implode(',',$ids).')';
				break;
			case 'weight':
				$query = 'SELECT '.$column.','.$table.'_id';
				$query .= ' FROM '.hikashop_table($table);
				$query .= ' WHERE '.$table.'_id IN ('.implode(',',$ids).')';
				break;

			case 'weight_unit':
				$weight = hikashop_get('type.weight');
				$view->assignRef('weight',$weight);
				$query = 'SELECT '.$column.','.$table.'_id';
				$query .= ' FROM '.hikashop_table($table);
				$query .= ' WHERE '.$table.'_id IN ('.implode(',',$ids).')';
				break;

			case 'characteristic':
				$query = 'SELECT DISTINCT c1.characteristic_id, c1.characteristic_value';
				$query .= ' FROM '.hikashop_table('characteristic').' AS c1';
				$query .= ' INNER JOIN '.hikashop_table('characteristic').' AS c2 ON c1.characteristic_parent_id = c2.characteristic_id';
				$query .= ' WHERE  c2.characteristic_value='.$database->Quote($column);
				break;
			case 'related' :
				$query = 'SELECT DISTINCT p.product_id as \'related_id\',p.product_name';
				$query .= ' FROM '.hikashop_table('product').' AS p';
				break;
			case 'options' :
				$query = 'SELECT DISTINCT p.product_id as \'options_id\',p.product_name';
				$query .= ' FROM '.hikashop_table('product').' AS p';
				break;
			case 'parent':
				$query = 'SELECT '.$column.','.$table.'_id, '.$table.'_name';
				$query .= ' FROM '.hikashop_table($table);
				$view->assignRef('ids', $ids);
				break;
			case 'id':
				$query = 'SELECT '.$column;
				if($table != 'price' && $table!='address'){
					$query .= ','.$table.'_name';
				}
				$query .= ' FROM '.hikashop_table($table);
				if($table == 'category'){
					$query .= ' WHERE category_type = \'product\' ';
				}
				$view->assignRef('ids', $ids);
				break;

			case 'sub_id':
				if(strstr($column, '_')!==false){
					$a = explode("_", $column);
				}
				if(strstr($column, 'partner')===false){
					foreach($a as $k=>$chaine){
						if($chaine === $table || $chaine === 'id'){
							unset($a[$k]);
						}
					}
					$table_tmp = implode('_',$a);
				}else{
					$table_tmp = 'user';
				}
				$column_tmp = $table_tmp.'_id';
				$query = 'SELECT '.$column_tmp.' as '.$column;
				$query .= ' FROM '.hikashop_table($table_tmp);
				$view->assignRef('ids', $ids);

				$view->table = 'order';

				break;

			default:
				$joomlaTable = true;
				if(preg_match('/joomla_/',$table)){
					$table = str_replace('joomla_','',$table);
					$joomlaTable = false;
				}

				if(strpos($type,'custom_') === 0){
					$f = substr_replace($view->type,'',0,7);
					$fields = hikashop_get('class.field');
					$view->assignRef('fields',$fields);
					$query = 'SELECT *';
					$query .= ' FROM '.hikashop_table($table,$joomlaTable);
					$query .= ' WHERE '.$table.'_id IN ('.implode(',',$ids).')';
					$database->setQuery($query);
					$elements = $database->loadObjectList();

					if($elements == null){
						die('Undefined row');
					}

					$view->assignRef('elements',$elements);

					$allFields = array();
					$column_id = $view->table.'_id';
					foreach($elements as $element){
						$f = $fields->getFields('backend',$element,$table,'user&task=state');
						if(preg_match('/joomla_users_/',$column_id)){
							$column_id = str_replace('joomla_users_','',$column_id);
						}
						$f['id'] = $element->$column_id;
						$allFields[] = $f;
					}

					$view->assignRef('allFields',$allFields);

					$query = 'SELECT '.$column.','.$table.'_id';
					$query .= ' FROM '.hikashop_table($table,$joomlaTable);
					$query .= ' WHERE '.$table.'_id IN ('.implode(',',$ids).')';
				}else if(!$joomlaTable){
					$query = 'SELECT '.$column.',id';
					$query .= ' FROM '.hikashop_table($table,$joomlaTable);
					$query .= ' WHERE id IN ('.implode(',',$ids).')';
					$view->assignRef('ids', $ids);
				}else{
					$query = 'SELECT '.$column.','.$table.'_id';
					$query .= ' FROM '.hikashop_table($table);
					$query .= ' WHERE '.$table.'_id IN ('.implode(',',$ids).')';
					$view->assignRef('ids', $ids);
				}
				break;
		}
	}

	function onLoadResultMassActionAfterEdition($data,$data_id,$table,$column,$type,$id,$value,&$query){
		$database = JFactory::getDBO();
		switch($data){
			case 'product':
				switch($table){
					case 'product':
						$query = 'SELECT '.$column.',product_id,product_dimension_unit';
						$query .= ' FROM '.hikashop_table('product');
						$query .= ' WHERE product_id = '.$database->Quote($id);

						break;

					case 'price':
						$query = 'SELECT '.$column.',price_currency_id, price_product_id, price_id';
						$query .= ' FROM '.hikashop_table('price');
						$query .= ' WHERE price_id ='.$database->Quote($id);

						break;

					case 'category':
						$query = 'SELECT '.hikashop_table('category').'.'.$column.','.hikashop_table('product_category').'.product_id,'.hikashop_table('category').'.category_id';
						$query .= ' FROM '.hikashop_table('category');
						$query .= ' INNER JOIN '.hikashop_table('product_category').' ON '.hikashop_table('product_category').'.category_id = '.hikashop_table('category').'.category_id';
						$query .= ' WHERE product_id = '.$database->Quote($id);

						break;

					case 'characteristic':

						$query = 'SELECT c1.characteristic_value,c1.characteristic_id';
						$query .= ' FROM '.hikashop_table('characteristic').' as c1';
						$query .= ' WHERE c1.characteristic_id ='.$database->Quote($value);

						break;
					case 'related':
						$query = 'SELECT p.product_id as \'related_id\',product_name,r.product_related_type';
						$query .= ' FROM '.hikashop_table('product').' AS p';
						$query .= ' INNER JOIN '.hikashop_table('product_related').' AS r ON r.product_related_id = p.product_id';
						$query .= ' WHERE r.product_id = '.$database->Quote($data_id).' AND r.product_related_type = \'related\'';

						$database->setQuery($query);
						$query = $database->loadObjectList();
						break;
					case 'options':
						$query = 'SELECT p.product_id as \'options_id\',product_name,r.product_related_type';
						$query .= ' FROM '.hikashop_table('product').' AS p';
						$query .= ' INNER JOIN '.hikashop_table('product_related').' AS r ON r.product_related_id = p.product_id';
						$query .= ' WHERE r.product_id = '.$database->Quote($data_id).' AND r.product_related_type = \'options\'';

						$database->setQuery($query);
						$query = $database->loadObjectList();
						break;
				}
				break;
			case 'user':
				switch($table){
					case 'user':
						$query = 'SELECT '.$column.',user_id';
						$query .= ' FROM '.hikashop_table('user');
						$query .= ' WHERE user_id = '.$database->Quote($id);
						break;

					case 'joomla_users':
						$query = 'SELECT DISTINCT '.$column.', id as \'joomla_users_id\'';
						$query .= ' FROM '.hikashop_table('users',false);
						$query .= ' WHERE id = '.$database->Quote($id);
						break;

					case 'usergroups':
						$query = 'SELECT DISTINCT usergroups.title, hk_user.user_id, usergroups.id as \'usergroups_id\'';
						$query .= ' FROM '.hikashop_table('usergroups',false).' AS usergroups';
						$query .= ' INNER JOIN '.hikashop_table('user_usergroup_map',false).' AS user_usergroup ON usergroups.id = user_usergroup.group_id';
						$query .= ' INNER JOIN '.hikashop_table('users',false).' AS user ON user.id = user_usergroup.user_id';
						$query .= ' INNER JOIN '.hikashop_table('user').' AS hk_user ON user.id = hk_user.user_cms_id';
						$query .= ' WHERE usergroups.id = '.$database->Quote($id);
						break;

					case 'address':
						$query = 'SELECT '.$column.', address_user_id, address_id';
						$query .= ' FROM '.hikashop_table('address');
						$query .= ' WHERE address_id = '.$database->Quote($id);
						break;
				}
				break;
			case 'category':
				switch($table){
					case 'category':
						$query = 'SELECT '.$column.',category_id';
						$query .= ' FROM '.hikashop_table('category');
						$query .= ' WHERE category_id = '.$database->Quote($id);
						break;
				}
			case 'order':
				switch($table){
					case 'order':
						$query = 'SELECT '.$column.', order_id,order_currency_id,order_partner_currency_id';
						$query .= ' FROM '.hikashop_table('order');
						$query .= ' WHERE order_id = '.$database->Quote($id);
						break;
					case 'order_product':
						$query = 'SELECT '.$column.', order_id, order_product_id';
						$query .= ' FROM '.hikashop_table('order_product');
						$query .= ' WHERE order_id = '.$database->Quote($id);
						break;
					case 'payment':
						$query = 'SELECT o.order_id,p.payment_name, p.payment_id';
						$query .= ' FROM '.hikashop_table('order').' as o';
						$query .= ' LEFT JOIN '.hikashop_table('payment').' as p ON o.order_payment_id = p.payment_id';
						$query .= ' WHERE p.payment_id = '.$database->Quote($value);
						break;
					case 'shipping':
						$query = 'SELECT o.order_id,s.shipping_name, s.shipping_id';
						$query .= ' FROM '.hikashop_table('order').' as o';
						$query .= ' LEFT JOIN '.hikashop_table('shipping').' as s ON o.order_shipping_id = s.shipping_id';
						$query .= ' WHERE s.shipping_id = '.$database->Quote($value);
						break;
					case 'address':
						$query = 'SELECT '.$column.', address_id';
						$query .= ' FROM '.hikashop_table('address');
						$query .= ' WHERE address_id ='.$database->Quote($id);
						break;
				}
				break;
			case 'address':
				switch($table){
					case 'user':
						$query = 'SELECT '.$column.', user_id, address_id';
						$query .= ' FROM '.hikashop_table('user').' AS user';
						$query .= ' INNER JOIN '.hikashop_table('address').' AS address ON user.user_id = address.address_user_id';
						$query .= ' WHERE user.user_id = '.$database->Quote($id);
						break;
					case 'joomla_users':
						$query = 'SELECT user.'.$column.', id as \'joomla_users_id\', address.address_id';
						$query .= ' FROM '.hikashop_table('users',false).' AS user';
						$query .= ' INNER JOIN '.hikashop_table('user').' AS hk_user ON user.id = hk_user.user_cms_id';
						$query .= ' INNER JOIN '.hikashop_table('address').' AS address ON hk_user.user_id = address.address_user_id';
						$query .= ' WHERE user.id = '.$database->Quote($id);
						break;
					case 'address':
						$query = 'SELECT a.'.$column.', a.address_id';
						$query .= ' FROM '.hikashop_table('address').' as a';
						$query .= ' WHERE a.address_id ='.$database->Quote($data_id);
						break;
				}
		}
	}

	function onBeforeMassactionUpdate(&$element){
		if(!empty($element->massaction_filters)){
			foreach($element->massaction_filters as $k => $filter){
				if($filter->name == 'csvImport'){
					if($element->massaction_filters[$k]->data['pathType'] == 'upload'){
						$importFile = JRequest::getVar('filter_product_'.$k.'_csvImport_upload', array(), 'files','array');
						$importHelper = hikashop_get('helper.import');
						$element->massaction_filters[$k]->data['path'] = $importHelper->importFromFile($importFile, false);
						$element->massaction_filters[$k]->data['pathType'] = 'path';
					}
				}
			}
		}
	}

	function onHikashopCronTrigger(&$messages){
		$config =& hikashop_config();
		$periods = array('minutes','hours','days','weeks','months','years');
		$massactionClass = hikashop_get('class.massaction');
		foreach($periods as $period){
			$last_trigger = $config->get('massaction_last_trigger_'.$period);
			$next_trigger = strtotime('+1 '.$period,(int)$last_trigger);
			if(time()<$next_trigger) continue;
			$pref = new stdClass();
			$key = 'massaction_last_trigger_'.$period;
			$pref->$key =  time();
			$config->save($pref);
			$massactionClass->_trigger('onHikashopCronTrigger'.ucfirst($period));
		}
		if(count($massactionClass->report)) $messages = array_merge($messages,$massactionClass->report);
	}
}

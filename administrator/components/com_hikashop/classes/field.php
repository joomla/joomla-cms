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

class hikashopFieldClass extends hikashopClass {

	var $tables = array('field');
	var $pkeys = array('field_id');
	var $namekeys = array();
	var $errors = array();
	var $prefix = '';
	var $suffix = '';
	var $excludeValue = array();
	var $toggle = array('field_required'=>'field_id','field_published'=>'field_id','field_backend'=>'field_id','field_backend_listing'=>'field_id','field_frontcomp'=>'field_id','field_core'=>'field_id');
	var $where = array();
	var $skipAddressName=false;
	var $report = true;
	var $messages = array();
	var $externalValues = null;
	var $regexs = array();

	function &getData($area, $type, $notcoreonly = false, $categories = null) {
		static $data = array();
		$key = $area.'_'.$type.'_'.$notcoreonly;
		if(!empty($categories)) {
			if(!empty($categories['originals']))
				$key .= '_' . implode('/', $categories['originals']);

			if(!empty($categories['parents']))
				$key .= '_' . implode('/', $categories['parents']);
		}
		if(!empty($categories['products'])){
			$key.='_'.implode('/',$categories['products']);
		}
		if(!isset($data[$key])){
			$this->where = array();
			$this->where[] = 'a.`field_published` = 1';
			if($area == 'backend'){
				$this->where[] = 'a.`field_backend` = 1';
			}elseif($area == 'frontcomp'){
				$this->where[] = 'a.`field_frontcomp` = 1';
			}elseif($area=='backend_listing'){
				$this->where[] = 'a.`field_backend_listing` = 1';
			}elseif($area != 'all'){
				$db = JFactory::getDBO();
				$clauses = explode(';', trim($area,';'));
				foreach($clauses as $clause) {
					if(empty($clause))
						continue;

					$v = '=1';
					if(strpos($clause, '=') !== false) {
						list($clause,$v) = explode('=', $clause, 2);
						$v = '=' . (int)$v;
					}
					if(substr($clause, 0, 8) == 'display:') {
						$cond = substr($clause, 8) . $v;
						if(HIKASHOP_J25)
							$cond = $db->escape($cond, true);
						else
							$cond = $db->getEscaped($cond, true);
						$this->where[] = 'a.`field_display` LIKE \'%;'.$cond.';%\'';
					} else {
						if(HIKASHOP_J25)
							$this->where[] = 'a.' . $db->quoteName($clause) . $v;
						else
							$this->where[] = 'a.' . $db->nameQuote($clause) . $v;
					}
				}
			}
			if($notcoreonly){
				$this->where[] = 'a.`field_core` = 0';
			}
			if($this->skipAddressName){
				$this->where[] = 'a.field_namekey!=\'address_name\'';
			}
			$this->where[] = 'a.field_table='.$this->database->Quote($type);
			$filters='';
			if(!empty($categories)){
				$categories_filter = array('((field_with_sub_categories=0 AND (field_categories="all" OR field_categories=""');
				if(!empty($categories['originals'])){
					foreach($categories['originals'] as $cat){
						$categories_filter[]='field_categories LIKE \'%,'.$cat.',%\'';
					}
				}
				$filters = implode(' OR ',$categories_filter).'))';
				$categories_filter = array('OR (field_with_sub_categories=1 AND (field_categories="all" OR field_categories=""');
				if(!empty($categories['parents'])){
					foreach($categories['parents'] as $cat){
						$categories_filter[]='field_categories LIKE \'%,'.$cat.',%\'';
					}
				}
				$filters .= implode(' OR ',$categories_filter).')))';
			}
			if(!empty($categories['products']) && is_array($categories['products']) && count($categories['products'])){
				$products_filter = array();
				foreach($categories['products'] as $p){
					$products_filter[]='field_products LIKE \'%,'.$p.',%\'';
				}
				if(empty($filters))
					$filters = '(field_products="" OR '.implode(' OR ',$products_filter).')';
				else
					$filters = '(('.$filters.' AND field_products="") OR ('.implode(' OR ',$products_filter).'))';
			}
			if(!empty($filters)) $filters = ' AND '.$filters;
			hikashop_addACLFilters($this->where,'field_access','a');
			$this->database->setQuery('SELECT * FROM '.hikashop_table('field').' as a WHERE '.implode(' AND ',$this->where).' '.$filters.' ORDER BY a.`field_ordering` ASC');
			$data[$key] = $this->database->loadObjectList('field_namekey');
		}
		return $data[$key];
	}

	function getField($fieldid,$type=''){
		if(is_numeric($fieldid)){
			$element = parent::get($fieldid);
		}else{
			$this->database->setQuery('SELECT * FROM '.hikashop_table('field').' WHERE field_table='.$this->database->Quote($type).' AND field_namekey='.$this->database->Quote($fieldid));
			$element = $this->database->loadObject();
		}
		$fields = array($element);
		$data = null;
		$this->prepareFields($fields,$data,$fields[0]->field_type,'',true);
		return $fields[0];
	}

	function getFields($area, &$data, $type = 'user', $url = 'checkout&task=state') {
		$allCat = $this->getCategories($type, $data);
		$fields = $this->getData($area, $type, false, $allCat);

		if($type == 'item' && !empty($fields)) {
			$checkProductFields = array();
			foreach($fields as $itemKey => $itemField) {
				if(!in_array($itemField->field_type, array('radio', 'checkox', 'singledropdown', 'multidropdown')) || !empty($itemField->field_value))
					continue;
				$checkProductFields[] = $itemKey;
			}
			if(!empty($checkProductFields)) {
				$null = null;
				$productFields = $this->getData('backend', 'product');
				foreach($checkProductFields as $key) {
					$product_key = $key.'_values';
					if(!isset($productFields[$product_key]))
						continue;
					$productField = $productFields[$product_key];
					if(!empty($productField->field_frontcomp))
						continue;

					if(empty($data->$product_key)) {
						$fields[$key]->field_value = $productField->field_value;
					} else {
						$product_data = explode(',', $data->$product_key);
						$field_data = explode("\n", $productField->field_value);
						$item_data = array();
						foreach($field_data as $fd) {
							list($k,$v) = explode('::', $fd, 2);
							if(in_array($k, $product_data))
								$item_data[] = $fd;
						}
						$fields[$key]->field_value = implode("\n", $item_data);
					}
				}
				unset($productFields);
			}
		}

		$this->prepareFields($fields, $data, $type, $url);
		return $fields;
	}

	function getCategories($type, &$data) {
		$allCat = null;
		if(empty($data))
			return $allCat;

		if(in_array($type, array('product', 'item', 'contact'))) {
			$id = 0;
			if(is_object($data) && !empty($data->product_id))
				$id = (int)$data->product_id;

			$ids = array();
			if(is_array($data)) {
				foreach($data as $d) {
					if(!empty($d->product_id))
						$ids[] = (int)$d->product_id;
				}
				if(!empty($ids)) {
					sort($ids);
					$id = implode(',', $ids);
				}
			}

			static $categories = array();
			$parents = array();
			if(!isset($categories[$id]) && !is_array($data)) {
				$categories[$id]['originals'] = array();
				$categories[$id]['parents'] = array();
				$categories[$id]['products'] = array($id);

				$categoryClass = hikashop_get('class.category');
				if(!empty($data->categories)) {
					foreach($data->categories as $category) {
						if(!is_object($category))
							$categories[$id]['originals'][$category] = $category;
						else
							$categories[$id]['originals'][$category->category_id] = $category->category_id;
					}
					$parents = $categoryClass->getParents($data->categories);
				} else {
					$productClass = hikashop_get('class.product');
					if(!isset($data->product_type)) {
						$prodData = $productClass->get($id);
						if(!empty($prodData->product_type)) {
							$data->product_type = $prodData->product_type;
							$data->product_parent_id = $prodData->product_parent_id;
						}
					}
					if(isset($data->product_type) && $data->product_type == 'variant')
						$loadedCategories = $productClass->getCategories($data->product_parent_id);
					else
						$loadedCategories = $productClass->getCategories($id);

					if(!empty($loadedCategories)) {
						foreach($loadedCategories as $cat) {
							$categories[$id]['originals'][$cat] = $cat;
						}
					}
					$parents = $categoryClass->getParents($loadedCategories);
				}
				if(!empty($parents) && is_array($parents)) {
					foreach($parents as $parent) {
						$categories[$id]['parents'][(int)$parent->category_id] = (int)$parent->category_id;
					}
					unset($parents);
				}
			} else if(!isset($categories[$id])) {
				$c = array(
					'originals' => array(),
					'parents' => array(),
					'products' => $ids
				);
				foreach($data as $d) {
					if(!empty($d->categories)) {
						foreach($d->categories as $category) {
							if(!is_object($category))
								$c['originals'][(int)$category] = (int)$category;
							else
								$c['originals'][(int)$category->category_id] = (int)$category->category_id;
						}
					} else {
					}
				}
				$categoryClass = hikashop_get('class.category');
				$parents = $categoryClass->getParents($c['originals']);

				$c['children'] = array();
				if(!empty($parents) && is_array($parents)) {
					foreach($parents as $parent) {
						$c['parents'][(int)$parent->category_id] = (int)$parent->category_id;

						if((int)$parent->category_parent_id > 0) {
							$c['children'][(int)$parent->category_id] = array( (int)$parent->category_parent_id );
							if(isset($c['children'][(int)$parent->category_parent_id]))
								$c['children'][(int)$parent->category_id] = array_merge($c['children'][(int)$parent->category_id], $c['children'][(int)$parent->category_parent_id]);
						} else {
							$c['children'][(int)$parent->category_id] = array();
						}
					}
					unset($parents);
				}

				$categories[$id] = $c;

				foreach($data as $d) {
					if(empty($d->product_id))
						continue;

					$p_c = array(
						'originals' => array(),
						'parents' => array(),
						'products' => array((int)$d->product_id)
					);
					if(!empty($d->categories)) {
						foreach($d->categories as $category) {
							if(!is_object($category))
								$cat_id = (int)$category;
							else
								$cat_id = (int)$category->category_id;

							$p_c['originals'][$cat_id] = $cat_id;
							$p_c['parents'] = array_combine($c['children'][$cat_id], $c['children'][$cat_id]);
						}
					}
					$categories[(int)$d->product_id] = $p_c;
				}
			}

			$allCat =& $categories[$id];
		}

		if($type == 'order' && !empty($data->products) && is_array($data->products) && count($data->products)) {
			$allCat = array(
				'originals' => array(),
				'parents' => array()
			);
			$categoryClass = hikashop_get('class.category');
			$productClass = hikashop_get('class.product');

			foreach($data->products as $k => $p) {
				if(!isset($p->product_type)) {
					$prodData = $productClass->get($p->product_id);
					if(!empty($prodData->product_type)){
						$data->products[$k]->product_type = $prodData->product_type;
						$data->products[$k]->product_parent_id = $prodData->product_parent_id;
					}
				}
			}

			$ids = array();
			foreach($data->products as $p) {
				if(isset($p->product_type) && $p->product_type == 'variant') {
					$ids[] = (int)$p->product_parent_id;
				} else {
					$ids[] = (int)$p->product_id;
				}
			}

			$allCat['products'] = $ids;
			$loadedCategories = $productClass->getCategories($ids);
			if(!empty($loadedCategories)) {
				foreach($loadedCategories as $cat) {
					$allCat['originals'][$cat]=$cat;
				}
			}
			$parents = $categoryClass->getParents($loadedCategories);

			if(!empty($parents) && is_array($parents)) {
				foreach($parents as $parent) {
					$allCat['parents'][$parent->category_id] = $parent->category_id;
				}
			}
		}

		if($type == 'category' && !empty($data->category_id)) {
			static $categories2 = array();
			if(!isset($categories2[$data->category_id])) {
				$categories2[$data->category_id]['originals'][$data->category_id] = $data->category_id;
				$categoryClass = hikashop_get('class.category');
				$parents = $categoryClass->getParents($data->category_id);
				if(!empty($parents)) {
					foreach($parents as $parent) {
						$categories2[$data->category_id]['parents'][$parent->category_id] = $parent->category_id;
					}
				}
			}
			$allCat =& $categories2[$data->category_id];
		}

		return $allCat;
	}

	function chart($table, $field, $order_status = '', $width = 0, $height = 0) {
		static $a = false;
		if(!$a){
			$a = true;
			if(!HIKASHOP_PHP5) {
				$doc =& JFactory::getDocument();
			} else {
				$doc = JFactory::getDocument();
			}
			$doc->addScript(((empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) != "on" ) ? 'http://' : 'https://')."www.google.com/jsapi");
		}
		if($table == 'contact')
			return;
		$namekey = hikashop_secureField($field->field_namekey);
		if(empty($order_status)){
			if($table=='item') $table ='order_product';
			$this->database->setQuery('SELECT COUNT(`'.$namekey.'`) as total,`'.$namekey.'` as name FROM '.$this->fieldTable($table).' WHERE `'.$namekey.'` IS NOT NULL AND `'.$namekey.'` != \'\' GROUP BY `'.$namekey.'` ORDER BY total DESC LIMIT 20');
		}elseif($table=='entry'){
			$this->database->setQuery('SELECT COUNT(a.`'.$namekey.'`) as total,a.`'.$namekey.'` as name FROM '.$this->fieldTable($table).' AS a LEFT JOIN '.hikashop_table('order').' AS b ON a.order_id=b.order_id WHERE b.order_status='.$this->database->Quote($order_status).' AND a.`'.$namekey.'` IS NOT NULL AND a.`'.$namekey.'` != \'\' GROUP BY a.`'.$namekey.'` ORDER BY total DESC LIMIT 20');
		}
		if(empty($width)){
			$width=600;
		}
		if(empty($height)){
			$height=400;
		}
		$results = $this->database->loadObjectList();
?>
		<script type="text/javascript">
		function drawChart<?php echo $namekey; ?>() {
			var dataTable = new google.visualization.DataTable();
			dataTable.addColumn('string');
			dataTable.addColumn('number');
			dataTable.addRows(<?php echo count($results); ?>);
<?php
foreach($results as $i => $oneResult){
	$name = isset($field->field_value[$oneResult->name]) ? $this->trans(@$field->field_value[$oneResult->name]->value) : $oneResult->name; ?>
			dataTable.setValue(<?php echo $i ?>, 0, '<?php echo addslashes($name).' ('.$oneResult->total.')'; ?>');
			dataTable.setValue(<?php echo $i ?>, 1, <?php echo intval($oneResult->total); ?>);
<?php } ?>

			var vis = new google.visualization.PieChart(document.getElementById('fieldchart<?php echo $namekey;?>'));
			var options = {
				title: '<?php echo addslashes($field->field_realname);?>',
				width: <?php echo $width;?>,
				height: <?php echo $height;?>,
				is3D:true,
				legendTextStyle: {color:'#333333'}
			};
			vis.draw(dataTable, options);
		}
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(drawChart<?php echo $namekey; ?>);
		</script>

		<div class="hikachart chart" style="width:<?php echo $width;?>px;height:<?php echo $height;?>px;" id="fieldchart<?php echo $namekey;?>"></div>
<?php
	}

	function prepareFields(&$fields, &$data, $type = 'user', $url = 'checkout&task=state', $test = false) {
		if(empty($fields))
			return;

		$id = $type.'_id';
		switch($type) {
			case 'address':
				$user_id = (int)@$data->address_user_id;
				break;
			case 'item':
				$order_id = (int)@$data->order_id;
				if($order_id > 0){
					$orderClass = hikashop_get('class.order');
					$order = $orderClass->get($order_id);
					$user_id = (int)@$order->order_user_id;
				} else {
					$user_id = 0;
				}
				break;
			case 'order':
				$user_id = (int)@$data->order_user_id;
				break;
			default:
				$user_id = 0;
				break;
		}

		$guest = true;
		if($user_id > 0) {
			$userClass = hikashop_get('class.user');
			$user = $userClass->get($user_id);
			$guest = !(bool)@$user->user_cms_id;
		}

		foreach($fields as $namekey => $field) {
			$fields[$namekey]->guest_mode = $guest;
			if(!empty($fields[$namekey]->field_options) && is_string($fields[$namekey]->field_options)) {
				$fields[$namekey]->field_options = unserialize($fields[$namekey]->field_options);
			}
			if(!empty($field->field_value) && is_string($fields[$namekey]->field_value)) {
				$fields[$namekey]->field_value = $this->explodeValues($fields[$namekey]->field_value);
			}
			if(is_object($data) && empty($data->$id) && !empty($namekey) && empty($data->$namekey)) {
				if($data == null || empty($data))
					$data = new stdClass();
				if(empty($fields[$namekey]->field_options['pleaseselect'])) {
					$data->$namekey = $field->field_default;
				} else {
					$data->$namekey = '';
				}
			} else if(is_array($data) && !empty($namekey)) {
				$v = (empty($fields[$namekey]->field_options['pleaseselect'])) ? $field->field_default : '';
				foreach($data as &$d) {
					if(!empty($d->$namekey) || !empty($data->$id))
						continue;
					$d->$namekey = $v;
				}
			}
			if(!empty($fields[$namekey]->field_options['zone_type']) && $fields[$namekey]->field_options['zone_type'] == 'country'){
				$baseUrl = JURI::base().'index.php?option=com_hikashop&ctrl='.$url.'&tmpl=component';
				$currentUrl = strtolower(hikashop_currentUrl());
				if(substr($currentUrl, 0, 8) == 'https://') {
					$domain = substr($currentUrl, 0, strpos($currentUrl, '/', 9));
				} else {
					$domain = substr($currentUrl, 0, strpos($currentUrl, '/', 8));
				}
				if(substr($baseUrl, 0, 8) == 'https://') {
					$baseUrl = $domain . substr($baseUrl, strpos($baseUrl, '/', 9));
				} else {
					$baseUrl = $domain . substr($baseUrl, strpos($baseUrl, '/', 8));
				}
				$fields[$namekey]->field_url = $baseUrl . '&';
			}
		}
		$this->handleZone($fields, $test, $data);
	}

	function handleZone(&$fields, $test = false, $data) {
		$types = array();
		foreach($fields as $k => $field){
			if($field->field_type != 'zone' || empty($field->field_options['zone_type']))
				continue;

			if($field->field_options['zone_type'] != 'state') {
				$types[$field->field_options['zone_type']] = $field->field_options['zone_type'];
				continue;
			}

			if(!empty($field->field_value))
				continue;

			$allFields = $this->getData('', $field->field_table, false);

			foreach($allFields as $i => $oneField) {
				if(!empty($oneField->field_options)&&is_string($oneField->field_options)) {
					$oneField->field_options = unserialize($oneField->field_options);
				}

				if($oneField->field_type != 'zone' || empty($oneField->field_options['zone_type']) || $oneField->field_options['zone_type'] != 'country')
					continue;

				$namekey = $oneField->field_namekey;
				if(!empty($data->$namekey)) {
					$oneField->field_default = $data->$namekey;
				} else {
					$zoneClass = hikashop_get('class.zone');

					$zone = $zoneClass->get($oneField->field_default);
					$ok = true;
					if(empty($zone) || !$zone->zone_published){
						$config =& hikashop_config();
						$zone_id = explode(',',$config->get('main_tax_zone',$zone->zone_id));
						if(count($zone_id))
							$zone_id = array_shift($zone_id);
						$ok = false;
						if($zone->zone_id != $zone_id) {
							$newZone = $zoneClass->get($zone_id);
							if($newZone->zone_published) {
								$allFields[$i]->field_default = $newZone->zone_namekey;
								$oneField->field_default = $newZone->zone_namekey;
								$oneField->field_options = serialize($oneField->field_options);
								$this->save($oneField);
								$ok = true;
							}
						}
					}
					if(!$ok) {
						$app = JFactory::getApplication();
						if(empty($zone)) {
							$app->enqueueMessage('In your custom zone field "'.$oneField->field_namekey.'", you have the zone "'.$oneField->field_default. '". However, that zone does not exist. Please change your custom field accordingly.', 'error');
						} else {
							$app->enqueueMessage('In your custom zone field "'.$oneField->field_namekey.'", you have the zone "'.$oneField->field_default. '". However, that zone is unpublished. Please change your custom field accordingly.', 'error');
						}
					}
				}
				$zoneType = hikashop_get('type.country');
				$zoneType->type = 'state';
				$zoneType->published = true;
				$zoneType->country_name = $oneField->field_default;
				$zones = $zoneType->load();
				$this->setValues($zones,$fields,$k,$field);

				break;
			}
		}

		if(!empty($types)) {
			$zoneType = hikashop_get('type.country');
			$zoneType->type = $types;
			$zoneType->published = true;
			$zones = $zoneType->load();

			if(!empty($zones)) {
				foreach($fields as $k => $field) {
					$this->setValues($zones,$fields,$k,$field);
				}
			}
		}
	}

	function handleZoneListing(&$fields,&$rows){
		if(empty($rows)) return;
		$values = array();
		foreach($fields as $k => $field){
			if($field->field_type=='zone'){
				$field_namekey = $field->field_namekey;
				foreach($rows as $row){
					if(!empty($row->$field_namekey)){
						$values[$row->$field_namekey]=$this->database->Quote($row->$field_namekey);
					}
				}
			}
		}
		if(!empty($values)){
			$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_namekey IN ('.implode(',',$values).') ORDER BY zone_name_english ASC';
			$this->database->setQuery($query);
			$zones = $this->database->loadObjectList('zone_namekey');
			foreach($fields as $k => $field){
				if($field->field_type!='zone')
					continue;
				$field_namekey = $field->field_namekey;
				foreach($rows as $k => $row){
					if(empty($row->$field_namekey))
						continue;
					foreach($zones as $zone){
						if($zone->zone_namekey!=$row->$field_namekey)
							continue;
						if(is_numeric($zone->zone_name_english)){
							$title = $zone->zone_name;
						}else{
							$title = $zone->zone_name_english;
							if($zone->zone_name_english != $zone->zone_name){
								$title.=' ('.$zone->zone_name.')';
							}
						}
						$rows[$k]->$field_namekey=$title;
						break;
					}
				}
			}
		}
	}

	function setValues(&$zones,&$fields,$k,&$field){
		foreach($zones as $zone){
			if($field->field_type=='zone' && !empty($field->field_options['zone_type']) && $field->field_options['zone_type']==$zone->zone_type){
				$title = $zone->zone_name_english;
				if($zone->zone_name_english != $zone->zone_name){
					$title.=' ('.$zone->zone_name.')';
				}
				$obj = new stdClass();
				$obj->value = $title;
				$obj->disabled = '0';
				$fields[$k]->field_value[$zone->zone_namekey]=$obj;
			}
		}
	}

	function getInput($type, &$oldData, $report = true, $varname = 'data', $force = false, $area = '') {
		$this->report = $report;
		$data = null;

		static $formDataCache = null;
		static $formDataName = null;

		if(is_string($varname) && ($force || $formDataCache === null || $formDataName != $varname)) {
			$formDataCache = JRequest::getVar($varname, array(), '', 'array');
			$formDataName = $varname;
			$formData =& $formDataCache;
		} else if(is_array($varname)) {
			$formData =& $varname;
		} else {
			$formData =& $formDataCache;
		}

		$dataType = $type;
		if(is_array($type)) {
			$dataType = $type[0];
			$type = $type[1];
		} elseif(substr($type, 0, 4) == 'plg.') {
			$this->_loadExternals();
			if(!empty($this->externalValues)) {
				foreach($this->externalValues as $name => $externalValue) {
					if($externalValue->value == $type && !empty($externalValue->datatype)) {
						$dataType = $externalValue->datatype;
						break;
					}
				}
			}
		}

		if(empty($formData[$dataType])) {
			if(is_string($varname)) {
				$formData[$dataType] = array();
			} else if(is_array($varname)) {
				unset($formData);
				$formData = array();
				$formData[$dataType] =& $varname;
			}
		}

		$app = JFactory::getApplication();
		if(empty($area))
			$area = ($app->isAdmin()) ? 'backend' : 'frontcomp';

		$allCat = $this->getCategories($type, $oldData);

		$fields =& $this->getData($area, $type, false, $allCat);

		if(!empty($fields)) {
			foreach($fields as $namekey => $field){
				if(!empty($fields[$namekey]->field_options) && is_string($fields[$namekey]->field_options)) {
					$fields[$namekey]->field_options = unserialize($fields[$namekey]->field_options);
				}
			}
		}

		if($type == 'entry' && $area == 'frontcomp') {
			$ok = true;
			$data = array();
			foreach($formData[$dataType] as $key => $form) {
				$data[$key] = new stdClass();
				if( !$this->_checkOneInput($fields, $formData[$dataType][$key], $data[$key], $type, $oldData) ) {
					$ok = false;
				}
			}
		} else {
			if(!isset($formData[$dataType]))
				$formData[$dataType] = '';

			$data = new stdClass();
			$ok = $this->_checkOneInput($fields, $formData[$dataType], $data, $type, $oldData);
		}

		if($data != null && !empty($data) && (!is_object($data) || count(get_object_vars($data)) > 0)) {
			$_SESSION['hikashop_'.$type.'_data'] = $data;
		} else {
			$_SESSION['hikashop_'.$type.'_data'] = null;
			unset($_SESSION['hikashop_'.$type.'_data']);
		}

		unset($formData);

		if(!$ok)
			return $ok;
		return $data;
	}

	function _checkOneInput(&$fields, &$formData, &$data,$type, &$oldData) {
		$ok = true;
		$this->error_fields = array();
		if(!empty($fields)) {
			foreach($fields as $k => $field) {
				$namekey = $field->field_namekey;
				if($field->field_type == "customtext") {
					if(isset($formData[$field->field_namekey]))
						unset($formData[$field->field_namekey]);
					continue;
				}

				if(!empty($field->field_options['limit_to_parent'])) {
					$parent = $field->field_options['limit_to_parent'];
					if(!isset($field->field_options['parent_value'])) {
						$field->field_options['parent_value'] = '';
					}
					$skip = false;
					foreach($fields as $otherField) {
						if($otherField->field_namekey==$parent) {
							if(!isset($formData[$parent]) || $field->field_options['parent_value']!=$formData[$parent]) {
								if(isset($formData[$namekey]))
									unset($formData[$namekey]);
								$skip = true;
							}
							break;
						}
					}

					if($skip && $field->field_required)
						continue;
				}

				$field_type = $field->field_type;
				if(substr($field->field_type, 0, 4) == 'plg.') {
					$field_type = substr($field->field_type, 4);
					JPluginHelper::importPlugin('hikashop', $field_type);
				}

				$classType = 'hikashop'.ucfirst($field_type);
				if(!class_exists($classType))
					continue;

				$class = new $classType($this);

				$val = @$formData[$namekey];
				if(!$class->check($fields[$k], $val, @$oldData->$namekey)) {
					$ok = false;
					$this->error_fields[] = $fields[$k];
				}
				$formData[$namekey] = $val;
			}
		}

		$this->checkFields($formData, $data, $type, $fields);
		return $ok;
	}

	function checkFields(&$data, &$object, $type, &$fields) {
		$app = JFactory::getApplication();
		static $safeHtmlFilter = null;
		if(is_null($object))
			$object = new stdClass();
		if($app->isAdmin() && is_null($safeHtmlFilter)) {
			jimport('joomla.filter.filterinput');
			$safeHtmlFilter = JFilterInput::getInstance(null, null, 1, 1);
		}
		$noFilter = array();
		if(!empty($fields)) {
			foreach($fields as $field) {
				if(isset($field->field_options['filtering']) && !$field->field_options['filtering']){
					$noFilter[] = $field->field_namekey;
				}
			}
		}
		if(empty($data) || !is_array($data))
			return;

		foreach($data as $column => $value){
			$column = trim(strtolower($column));
			if(!$this->allowed($column, $type))
				continue;

			hikashop_secureField($column);

			if(is_array($value)){
				$arrayColumn = false;
				if(substr($type, 0, 4) == 'plg.') {
					$this->_loadExternals();
					foreach($this->externalValues as $externalValue) {
						if($externalValue->value == $type && !empty($externalValue->arrayColumns)) {
							$arrayColumn = in_array($column, $externalValue->arrayColumns);
							break;
						}
					}
				}
				if( $arrayColumn || ($type == 'user' && $column == 'user_params') || ($type == 'order' && $app->isAdmin() && in_array($column,array('history','mail','product'))) ) {
					$object->$column = new stdClass();
					foreach($value as $c => $v){
						$c = trim(strtolower($c));
						if($this->allowed($c,$type)) {
							hikashop_secureField($c);
							$object->$column->$c = in_array($c, $noFilter) ? $v : strip_tags($v);
						}
					}
				} else {
					foreach($value as $c => $v){
						if(is_array($v) || is_object($v))
							$value[$c] = '';
					}
					$value = implode(',',$value);
					$object->$column = in_array($column, $noFilter) ? $value : strip_tags($value);
				}
			} elseif(is_null($safeHtmlFilter)) {
				$object->$column = in_array($column, $noFilter) ? $value : strip_tags($value);
			} else {
				$object->$column = in_array($column, $noFilter) ? $value : $safeHtmlFilter->clean($value, 'string');
			}
		}
	}

	function checkFieldsForJS(&$extraFields,&$requiredFields,&$validMessages,&$values){
		foreach($extraFields as $type => $oneType) {
			if(empty($oneType))
				continue;
			foreach($oneType as $k => $oneField) {
				if(!empty($oneField->field_js_added))
					continue;

				$field_type = $oneField->field_type;
				if(substr($oneField->field_type,0,4) == 'plg.') {
					$field_type = substr($oneField->field_type,4);
					JPluginHelper::importPlugin('hikashop', $field_type);
				}
				$classType = 'hikashop'.ucfirst($field_type);
				if(!class_exists($classType)) {
					continue;
				}
				$class = new $classType($this);
				$class->JSCheck($oneField,$requiredFields[$type],$validMessages[$type],$values[$type]);

				if(!empty($oneField->field_options['regex'])){
					$this->regexs[$type][$oneField->field_namekey] = str_replace("'","\'",$oneField->field_options['regex']);
				}
				$extraFields[$type][$k]->field_js_added = true;
			}
		}
	}

	function addJS( &$requiredFields, &$validMessages, $types = array() ) {
		static $done = false;
		$doc = JFactory::getDocument();

		if(!$done) {
			$js = "
window.hikashopFieldsJs = {
	'reqFieldsComp': {},
	'validFieldsComp': {},
	'regexFieldsComp': {},
	'regexValueFieldsComp': {}
};";
			$doc->addScriptDeclaration( "<!--".$js."\n//-->\n" );
			$done = true;
		}

		if(empty($types))
			return;

		$js = '';
		foreach($types as $type) {
			if(!empty($requiredFields[$type])) {
				$js .= "\nwindow.hikashopFieldsJs['reqFieldsComp']['".$type."'] = ['" . implode("','", $requiredFields[$type]) . "'];".
					"\nwindow.hikashopFieldsJs['validFieldsComp']['".$type."'] = ['" . implode("','", $validMessages[$type]) . "'];";
			}

			if(!empty($this->regexs[$type])) {
				$js .= "\nwindow.hikashopFieldsJs['regexFieldsComp']['".$type."'] = ['" . implode("','", array_keys($this->regexs[$type])) . "'];".
					"\nwindow.hikashopFieldsJs['regexValueFieldsComp']['".$type."'] = ['".implode("','", $this->regexs[$type]) . "'];";
			}

			if($type == 'register') {
				$js .= "\nwindow.hikashopFieldsJs['password_different'] = '".JText::_('PASSWORDS_DO_NOT_MATCH', true)."';".
					"\nwindow.hikashopFieldsJs['valid_email'] = '".JText::_('VALID_EMAIL', true)."';";
			} elseif($type == 'address') {
				$js .= "\nwindow.hikashopFieldsJs['valid_phone'] = '".JText::_('VALID_PHONE', true)."';";
			}
		}

		if(!empty($js))
			$doc->addScriptDeclaration( "<!--".$js."\n//-->\n" );
	}

	function jsToggle(&$fields, $data, $id = 1) {
		$doc = JFactory::getDocument();
		$js = '';
		static $done = false;

		if(!$done) {
			$js = '
function hikashopToggleFields(new_value, namekey, field_type, id, prefix) {
	if(!window.hikashop)
		return false;
	if(!window.hikashop.fields_data && window.hikashopFieldsJs)
		window.hikashop.fields_data = window.hikashopFieldsJs;
	return window.hikashop.toggleField(new_value, namekey, field_type, id, prefix);
}';

			$done = true;
		}
		$parents = $this->getParents($fields);

		if(empty($parents)) {
			if(!empty($js))
				$doc->addScriptDeclaration( "<!--".$js."\n//-->\n" );
			return false;
		}

		$first = reset($parents);
		$type = $first->type;

		if(substr($type, 0, 4) == 'plg.') {
			$this->_loadExternals();
			foreach($this->externalValues as $externalValue) {
				if($externalValue->value == $type && !empty($externalValue->datatype)) {
					$type = $externalValue->datatype;
					break;
				}
			}
		}

		$js .= "\nwindow.hikashopFieldsJs['".$type."'] = {};";
		foreach($parents as $namekey => $parent){
			$js .= "\nwindow.hikashopFieldsJs['".$type."']['".$namekey."'] = {};";
			foreach($parent->childs as $value => $childs){
				$js .= "\nwindow.hikashopFieldsJs['".$type."']['".$namekey."']['".$value."'] = {};";
				foreach($childs as $field){
					$js .= "\nwindow.hikashopFieldsJs['".$type."']['".$namekey."']['".$value."']['".$field->field_namekey."'] = '".$field->field_namekey."';";
				}
			}
		}

		$js .= $this->getLoadJSForToggle($parents, $data, $id);

		$doc->addScriptDeclaration( "<!--".$js."\n//-->\n" );
	}

	function getLoadJSForToggle(&$parents, &$data, $id = 1) {
		return "\nwindow.hikashop.ready(function(){\n" .
			$this->initJSToggle($parents, $data, $id) .
			"\n});";
	}

	function initJSToggle(&$parents, &$data, $id = 1) {
		$first = reset($parents);
		$type = $first->type;
		if(substr($type, 0, 4) == 'plg.') {
			$this->_loadExternals();
			foreach($this->externalValues as $externalValue) {
				if($externalValue->value == $type && !empty($externalValue->datatype)) {
					$type = $externalValue->datatype;
					if(!empty($externalValue->prefix))
						$id .= ',"'.$externalValue->prefix.'"';
					break;
				}
			}
		}
		$js = '';
		foreach($parents as $namekey => $parent) {
			if(empty($data)){
				$js .= "\nhikashopToggleFields('','" . $namekey . "','" . $type . "'," . $id . ");";
				continue;
			}
			if(is_object($data)) {
				$js .= "\nhikashopToggleFields('" . str_replace("'", "\'", @$data->$namekey) . "','" . $namekey . "','" . $type . "'," . $id . ");";
				continue;
			}
			foreach($data as $d) {
				$js .= "\nhikashopToggleFields('" . str_replace("'", "\'", @$d->$namekey) . "','" . $namekey . "','" . $type . "'," . $id . ");";
			}
		}
		return $js;
	}

	function getParents(&$fields){
		$parents = array();
		if(empty($fields))
			return false;

		foreach($fields as $k => $field){
			if(empty($field->field_options['limit_to_parent']))
				continue;

			$parent = $field->field_options['limit_to_parent'];

			if(!isset($parents[$parent])) {
				$obj = new stdClass();
				$obj->type = $field->field_table;
				$obj->childs = array();
				$parents[$parent] = $obj;
			}

			$parent_value = @$field->field_options['parent_value'];
			if(is_array($parent_value)) {
				foreach($parent_value as $value) {
					if(!isset($parents[$parent]->childs[$value]))
						$parents[$parent]->childs[$value] = array();
					$parents[$parent]->childs[$value][$field->field_namekey] = $field;
				}
			} else {
				if(!isset($parents[$parent]->childs[$parent_value]))
					$parents[$parent]->childs[$parent_value] = array();
				$parents[$parent]->childs[$parent_value][$field->field_namekey]=$field;
			}
		}
		return $parents;
	}

	function allowed($column, $type = 'user') {
		$restricted = array(
			'user'=>array('user_partner_price'=>1,'user_partner_paid'=>1,'user_created_ip'=>1,'user_partner_id'=>1,'user_partner_lead_fee'=>1,'user_partner_click_fee'=>1,'user_partner_percent_fee'=>1,'user_partner_flat_fee'=>1),
			'order'=>array('order_id'=>1,'order_billing_address_id'=>1,'order_shipping_address_id'=>1,'order_user_id'=>1,'order_status'=>1,'order_discount_code'=>1,'order_created'=>1,'order_ip'=>1,'order_currency_id'=>1,'order_status'=>1,'order_shipping_price'=>1,'order_discount_price'=>1,'order_shipping_id'=>1,'order_shipping_method'=>1,'order_payment_id'=>1,'order_payment_method'=>1,'order_full_price'=>1,'order_modified'=>1,'order_partner_id'=>1,'order_partner_price'=>1,'order_partner_paid'=>1,'order_type'=>1,'order_partner_currency_id'=>1)
		);
		if(substr($type, 0, 4) == 'plg.')
			$this->_loadExternals();

		if(isset($restricted[$type][$column])) {
			$app = JFactory::getApplication();
			if(!$app->isAdmin())
				return false;
		}
		return true;
	}

	function _loadExternals() {
		if($this->externalValues !== null)
			return;

		$this->externalValues = array();
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onTableFieldsLoad', array( &$this->externalValues ) );
		if(empty($this->externalValues))
			return;
		foreach($this->externalValues as &$externalValue) {
			if(!empty($externalValue->table) && substr($externalValue->value, 0, 4) != 'plg.')
				$externalValue->value = 'plg.' . $externalValue->value;
			unset($externalValue);
		}
	}

	function explodeValues($values){
		$allValues = explode("\n",$values);
		$returnedValues = array();

		foreach($allValues as $id => $oneVal){
			$line = explode('::',trim($oneVal));
			$var = $line[0];
			$val = $line[1];
			if(count($line)==2){
				$disable = '0';
			}else{
				$disable = $line[2];
			}
			if(strlen($val)>0){
				$obj = new stdClass();
				$obj->value = $val;
				$obj->disabled = $disable;
				$returnedValues[$var] = $obj;
			}
		}
		return $returnedValues;
	}

	function getFieldName($field,$requiredDisplay = false){
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return $this->trans($field->field_realname);
		$required = '';
		if($requiredDisplay && !empty($field->field_required))
			$required = '<span class="hikashop_field_required_label">*</span>';
		return '<label for="'.$this->prefix.$field->field_namekey.$this->suffix.'">'.$this->trans($field->field_realname).$required.'</label>';
	}

	function trans($name){
		$val = preg_replace('#[^a-z0-9]#i', '_', strtoupper($name));
		$app = JFactory::getApplication();
		if($app->isAdmin() && strcmp(JText::_($val), strip_tags(JText::_($val))) !== 0)
			$trans = $val;
		else
			$trans = JText::_($val);
		if($val == $trans)
			$trans = $name;
		return $trans;
	}

	function get($field_id,$default=null){
		$query = 'SELECT a.* FROM '.hikashop_table('field').' as a WHERE a.`field_id` = '.intval($field_id).' LIMIT 1';
		$this->database->setQuery($query);

		$field = $this->database->loadObject();
		if(!empty($field->field_options)) {
			$field->field_options = unserialize($field->field_options);
		}

		if(!empty($field->field_display)) {
			$display_values = explode(';', trim($field->field_display, ';'));
			$field->field_display = array();
			foreach($display_values as $display_value) {
				if(strpos($display_value, '=') === false)
					continue;
				list($k,$v) = explode('=', $display_value, 2);
				$field->field_display[$k] = (int)$v;
			}
		}

		if(!empty($field->field_value))
			$field->field_value = $this->explodeValues($field->field_value);

		return $field;
	}

	function saveForm() {
		$field = new stdClass();
		$field->field_id = hikashop_getCID('field_id');
		$field->field_products = '';

		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		foreach($formData['field'] as $column => $value) {
			hikashop_secureField($column);
			if($column == 'field_default')
				continue;
			if($column=='field_products'){
				JArrayHelper::toInteger($value);
				$value = ','.implode(',',$value).',';
			}elseif(is_array($value))
				$value = implode(',',$value);
			$field->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
		}

		$fields = array( &$field );
		if(isset($field->field_namekey))
			$namekey = $field->field_namekey;
		$field->field_namekey = 'field_default';

		$fieldOptions = JRequest::getVar('field_options', array(), '', 'array');
		foreach($fieldOptions as $column => $value) {
			if(is_array($value)) {
				foreach($value as $id => $val) {
					if($column != 'parent_value')
						hikashop_secureField($id);
					$fieldOptions[$column][$id] = $safeHtmlFilter->clean($val, 'string');
				}
			} else {
				$fieldOptions[$column] = $safeHtmlFilter->clean($value, 'string');
			}
		}

		$field_required = $field->field_required;
		$field->field_required = false;
		$data = null;
		$oldData = null;
		if($this->_checkOneInput($fields,$formData['field'], $data, '', $oldData)) {
			if(isset($formData['field']['field_default']) && is_array($formData['field']['field_default'])){
				$defaultValue = '';
				foreach($formData['field']['field_default'] as $value) {
					if(empty($defaultValue)) {
						$defaultValue .= $value;
					} else {
						$defaultValue .= ',' . $value;
					}
				}
			} else {
				$defaultValue = @$formData['field']['field_default'];
			}

			if(isset($fieldOptions['filtering']) && $fieldOptions['filtering']) {
				$field->field_default = strip_tags($defaultValue);
			} else {
				jimport('joomla.filter.filterinput');
				$safeHtmlFilter = JFilterInput::getInstance(null, null, 1, 1);
				$field->field_default = $safeHtmlFilter->clean($defaultValue,'string');
			}
		}
		$field->field_required = $field_required;
		unset($field->field_namekey);
		if(isset($namekey))
			$field->field_namekey = $namekey;

		$fieldtype = hikashop_get('type.fields');
		$fieldtype->load($field->field_table);
		if(!empty($fieldtype->externalOptions) && isset($fieldtype->allValues[$field->field_type])) {
			$linkedOptions = $fieldtype->allValues[$field->field_type]['options'];
			foreach($fieldtype->externalOptions as $key => $extraOption) {
				if(in_array($key, $linkedOptions)) {
					$o = is_array($extraOption) ? $extraOption['obj'] : $extraOption->obj;
					if(is_string($o))
						$o = new $o();

					if(method_exists($o, 'save'))
						$o->save($fieldOptions);
				}
			}
		}

		if($field->field_type == "customtext") {
			$fieldOptions['customtext'] = JRequest::getVar('fieldcustomtext','','','string',JREQUEST_ALLOWRAW);
			if(empty($field->field_id)) {
			 	$field->field_namekey = 'customtext_'.date('z_G_i_s');
			} else {
				$oldField = $this->get($field->field_id);
				if($oldField->field_core)
					$field->field_type = $oldField->field_type;
			}
		}

		$field->field_options = serialize($fieldOptions);

		$fieldDisplay = JRequest::getVar('field_display', array(), '', 'array');
		if(!empty($fieldDisplay)) {
			$field->field_display = ';';
			foreach($fieldDisplay as $k => $v) {
				$field->field_display .= $k . '=' . (int)$v . ';';
			}
		}

		$fieldValues = JRequest::getVar('field_values', array(), '', 'array' );
		if(!empty($fieldValues)) {
			$field->field_value = array();
			jimport('joomla.filter.filterinput');
			$safeHtmlFilter = JFilterInput::getInstance(null, null, 1, 1);

			foreach($fieldValues['title'] as $i => $title) {
				if(strlen($title) < 1 && strlen($fieldValues['value'][$i]) < 1)
					continue;

				$value = (strlen($fieldValues['value'][$i]) < 1) ? $title : $fieldValues['value'][$i];
				$disabled = (strlen($fieldValues['disabled'][$i]) < 1) ? '0' : $fieldValues['disabled'][$i];
				$field->field_value[] = $safeHtmlFilter->clean($title,'string'). '::' .  $safeHtmlFilter->clean($value,'string') . '::' .  $safeHtmlFilter->clean($disabled,'string');
			}
			$field->field_value = implode("\n", $field->field_value);
		}

		if(empty($field->field_id) && $field->field_type != 'customtext') {
			if(empty($field->field_namekey))
				$field->field_namekey = $field->field_realname;

			$field->field_namekey = preg_replace('#[^a-z0-9_]#i', '', strtolower($field->field_namekey));
			if(empty($field->field_namekey)) {
				$this->errors[] = 'Please specify a namekey';
				return false;
			}

			if($field->field_namekey > 50) {
				$this->errors[] = 'Please specify a shorter column name';
				return false;
			}

			if($field->field_table != 'contact'){
				if(in_array(strtoupper($field->field_namekey),array(
					'ACCESSIBLE', 'ADD', 'ALL', 'ALTER', 'ANALYZE', 'AND', 'AS', 'ASC', 'ASENSITIVE',
					'BEFORE', 'BETWEEN', 'BIGINT', 'BINARY', 'BLOB', 'BOTH', 'BY', 'CALL', 'CASCADE',
					'CASE', 'CHANGE', 'CHAR', 'CHARACTER', 'CHECK', 'COLLATE', 'COLUMN', 'CONDITION',
					'CONSTRAINT', 'CONTINUE', 'CONVERT', 'CREATE', 'CROSS', 'CURRENT_DATE', 'CURRENT_TIME',
					'CURRENT_TIMESTAMP', 'CURRENT_USER', 'CURSOR', 'DATABASE', 'DATABASES', 'DAY_HOUR',
					'DAY_MICROSECOND', 'DAY_MINUTE', 'DAY_SECOND', 'DEC', 'DECIMAL', 'DECLARE', 'DEFAULT',
					'DELAYED', 'DELETE', 'DESC', 'DESCRIBE', 'DETERMINISTIC', 'DISTINCT', 'DISTINCTROW',
					'DIV', 'DOUBLE', 'DROP', 'DUAL', 'EACH', 'ELSE', 'ELSEIF', 'ENCLOSED', 'ESCAPED',
					'EXISTS', 'EXIT', 'EXPLAIN', 'FALSE', 'FETCH', 'FLOAT', 'FLOAT4', 'FLOAT8', 'FOR',
					'FORCE', 'FOREIGN', 'FROM', 'FULLTEXT', 'GRANT', 'GROUP', 'HAVING', 'HIGH_PRIORITY',
					'HOUR_MICROSECOND', 'HOUR_MINUTE', 'HOUR_SECOND', 'IF', 'IGNORE', 'IN', 'INDEX',
					'INFILE', 'INNER', 'INOUT', 'INSENSITIVE', 'INSERT', 'INT', 'INT1', 'INT2', 'INT3',
					'INT4', 'INT8', 'INTEGER', 'INTERVAL', 'INTO', 'IS', 'ITERATE', 'JOIN', 'KEY', 'KEYS',
					'KILL', 'LEADING', 'LEAVE', 'LEFT', 'LIKE', 'LIMIT', 'LINEAR', 'LINES', 'LOAD', 'LOCALTIME',
					'LOCALTIMESTAMP', 'LOCK', 'LONG', 'LONGBLOB', 'LONGTEXT', 'LOOP', 'LOW_PRIORITY',
					'MASTER_SSL_VERIFY_SERVER_CERT', 'MATCH', 'MAXVALUE', 'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT',
					'MIDDLEINT', 'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'MOD', 'MODIFIES', 'NATURAL',
					'NOT', 'NO_WRITE_TO_BINLOG', 'NULL', 'NUMERIC', 'ON', 'OPTIMIZE', 'OPTION', 'OPTIONALLY',
					'OR', 'ORDER', 'OUT', 'OUTER', 'OUTFILE', 'PRECISION', 'PRIMARY', 'PROCEDURE', 'PURGE',
					'RANGE', 'READ', 'READS', 'READ_WRITE', 'REAL', 'REFERENCES', 'REGEXP', 'RELEASE',
					'RENAME', 'REPEAT', 'REPLACE','REQUIRE', 'RESIGNAL', 'RESTRICT', 'RETURN', 'REVOKE',
					'RIGHT', 'RLIKE', 'SCHEMA', 'SCHEMAS', 'SECOND_MICROSECOND', 'SELECT', 'SENSITIVE',
					'SEPARATOR', 'SET', 'SHOW', 'SIGNAL', 'SMALLINT', 'SPATIAL', 'SPECIFIC', 'SQL', 'SQLEXCEPTION',
					'SQLSTATE', 'SQLWARNING', 'SQL_BIG_RESULT', 'SQL_CALC_FOUND_ROWS', 'SQL_SMALL_RESULT',
					'SSL', 'STARTING', 'STRAIGHT_JOIN', 'TABLE', 'TERMINATED', 'THEN', 'TINYBLOB', 'TINYINT',
					'TINYTEXT', 'TO', 'TRAILING', 'TRIGGER', 'TRUE', 'UNDO', 'UNION', 'UNIQUE', 'UNLOCK',
					'UNSIGNED', 'UPDATE', 'USAGE', 'USE', 'USING', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP',
					'VALUES', 'VARBINARY', 'VARCHAR', 'VARCHARACTER', 'VARYING', 'WHEN', 'WHERE', 'WHILE',
					'WITH', 'WRITE', 'XOR', 'YEAR_MONTH', 'ZEROFILL', 'GENERAL', 'IGNORE_SERVER_IDS',
					'MASTER_HEARTBEAT_PERIOD', 'MAXVALUE', 'RESIGNAL', 'SIGNAL', 'SLOW', 'ALIAS', 'OPTIONS',
					'RELATED', 'IMAGES', 'FILES', 'CATEGORIES', 'PRICES', 'VARIANTS', 'CHARACTERISTICS')
				)) {
					$this->errors[] = 'The column name "'.$field->field_namekey.'" is reserved. Please use another one.';
					return false;
				}

				$tables = array($field->field_table);
				if($field->field_table == 'item')
					$tables = array('cart_product', 'order_product');

				foreach($tables as $table_name) {
					if(!HIKASHOP_J30) {
						$columnsTable = $this->database->getTableFields($this->fieldTable($table_name));
						$columns = reset($columnsTable);
					} else {
						$columns = $this->database->getTableColumns($this->fieldTable($table_name));
					}

					if(isset($columns[$field->field_namekey])) {
						$this->errors[] = 'The field "'.$field->field_namekey.'" already exists in the table "'.$table_name.'"';
						return false;
					}
				}

				foreach($tables as $table_name) {
					$query = 'ALTER TABLE '.$this->fieldTable($table_name).' ADD `'.$field->field_namekey.'` TEXT NULL';
					$this->database->setQuery($query);
					$this->database->query();
				}
			}
		}

		$categories = JRequest::getVar('category', array(), '', 'array');
		JArrayHelper::toInteger($categories);
		$cat = ',';
		foreach($categories as $category) {
			$cat .= $category . ',';
		}
		if($cat == ',')
			$cat = 'all';

		$field->field_categories = $cat;
		$field_id = $this->save($field);
		if(!$field_id)
			return false;

		if(empty($field->field_id)) {
			$orderClass = hikashop_get('helper.order');
			$orderClass->pkey = 'field_id';
			$orderClass->table = 'field';
			$orderClass->groupMap = 'field_table';
			$orderClass->groupVal = $field->field_table;
			$orderClass->orderingMap = 'field_ordering';
			$orderClass->reOrder();
		}
		JRequest::setVar('field_id', $field_id);
		return true;

	}

	function delete(&$elements){
		if(!is_array($elements))
			$elements = array($elements);

		foreach($elements as $key => $val) {
			$elements[$key] = hikashop_getEscaped($val);
		}

		if(empty($elements))
			return false;

		$this->database->setQuery('SELECT `field_namekey`,`field_id`,`field_table`,`field_type` FROM '.hikashop_table('field').'  WHERE `field_core` = 0 AND `field_id` IN ('.implode(',',$elements).')');
		$fieldsToDelete = $this->database->loadObjectList('field_id');

		if(empty($fieldsToDelete)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('CORE_FIELD_DELETE_ERROR'));
			return false;
		}

		$namekeys = array();
		foreach($fieldsToDelete as $oneField) {
			if($oneField->field_type == 'customtext')
				continue;
			if($oneField->field_table=='item') {
				$namekeys['cart_product'][] = $oneField->field_namekey;
				$namekeys['order_product'][] = $oneField->field_namekey;
			} elseif($oneField->field_table != 'contact') {
				$namekeys[$oneField->field_table][] = $oneField->field_namekey;
			}
		}
		foreach($namekeys as $table => $fields) {
			$this->database->setQuery('ALTER TABLE '.$this->fieldTable($table).' DROP `'.implode('`, DROP `',$fields).'`');
			$this->database->query();
		}

		$this->database->setQuery('DELETE FROM '.hikashop_table('field').' WHERE `field_id` IN ('.implode(',',array_keys($fieldsToDelete)).')');
		$result = $this->database->query();
		if(!$result)
			return false;

		$affectedRows = $this->database->getAffectedRows();

		foreach($namekeys as $table => $fields) {
			$orderClass = hikashop_get('helper.order');
			$orderClass->pkey = 'field_id';
			$orderClass->table = 'field';
			$orderClass->groupMap = 'field_table';
			$orderClass->groupVal = $table;
			$orderClass->orderingMap = 'field_ordering';
			$orderClass->reOrder();
		}

		return $affectedRows;

	}

	function display(&$field, $value, $map, $inside = false, $options = '', $test = false, $allFields = null, $allValues = null, $requiredDisplay = true) {
		$field_type = $field->field_type;
		if(substr($field->field_type,0,4) == 'plg.') {
			$field_type = substr($field->field_type,4);
			JPluginHelper::importPlugin('hikashop', $field_type);
		}
		$classType = 'hikashop'.ucfirst($field_type);
		if(!class_exists($classType))
			return 'Plugin '.$field_type.' missing or deactivated';

		$class = new $classType($this);
		if(is_string($value))
			$value = htmlspecialchars($value, ENT_COMPAT,'UTF-8');

		$html = $class->display($field,$value,$map,$inside,$options,$test,$allFields,$allValues);

		if($requiredDisplay && !empty($field->field_required))
			$html .=' <span class="hikashop_field_required">*</span>';
		return $html;
	}

	function show(&$field,$value,$className='') {
		$field_type = $field->field_type;
		if(substr($field->field_type,0,4) == 'plg.') {
			$field_type = substr($field->field_type,4);
			JPluginHelper::importPlugin('hikashop', $field_type);
		}
		$classType = 'hikashop'.ucfirst($field_type);
		if(!class_exists($classType))
			return $value;

		$class = new $classType($this);
		$html = $class->show($field,$value,$className);
		return $html;
	}

	function fieldTable($table_name) {
		if(substr($table_name, 0, 4) == 'plg.') {
			$this->_loadExternals();
			$table_name = substr($table_name, 4);
			foreach($this->externalValues as $name => $externalValue) {
				if($name == $table_name) {
					if(!empty($externalValue->table))
						return $externalValue->table;
					break;
				}
			}
		}
		return hikashop_table($table_name);
	}
}

class hikashopItem {
	var $prefix;
	var $suffix;
	var $excludeValue;
	var $report;
	var $parent;

	function __construct(&$obj) {
		$this->prefix = $obj->prefix;
		$this->suffix = $obj->suffix;
		$this->excludeValue =& $obj->excludeValue;
		$this->report = @$obj->report;
		$this->parent =& $obj;
	}

	function getFieldName($field) {
		$app = JFactory::getApplication();
		if($app->isAdmin()) return $this->trans($field->field_realname);
		return '<label for="'.$this->prefix.$field->field_namekey.$this->suffix.'">'.$this->trans($field->field_realname).'</label>';
	}

	function trans($name) {
		$val = preg_replace('#[^a-z0-9]#i','_',strtoupper($name));

		$app = JFactory::getApplication();
		if($app->isAdmin() && strcmp(JText::_($val), strip_tags(JText::_($val))) !== 0)
			$trans = $val;
		else
			$trans = JText::_($val);

		if($val == $trans)
			$trans = $name;
		return $trans;
	}

	function show(&$field, $value) {
		return $this->trans($value);
	}

	function JSCheck(&$oneField, &$requiredFields, &$validMessages, &$values) {
		if(empty($oneField->field_required))
			return;

		$requiredFields[] = $oneField->field_namekey;
		if(!empty($oneField->field_options['errormessage'])) {
			$validMessages[] = addslashes($this->trans($oneField->field_options['errormessage']));
		} else {
			$validMessages[] = addslashes(JText::sprintf('FIELD_VALID',$this->trans($oneField->field_realname)));
		}
	}

	function check(&$field,&$value,$oldvalue) {
		if(is_string($value))
			$value = trim($value);

		if(!$field->field_required || is_array($value) || strlen($value) || strlen($oldvalue))
			return true;

		if(!empty($this->report)) {
			if($this->report === true) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname)), 'error');
			} else {
				$this->messages[] = array(
					JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname)),
					'error'
				);
			}
		}
		return false;
	}

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) { return $value; }

	function showfield($viewObj, $namekey, $row)	{
		if( isset( $row->$namekey)) { return $row->$namekey; }
		return '';
	}
}

class hikashopCustomtext extends hikashopItem {
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {
		return $this->trans(@$field->field_options['customtext']);
	}
}

class hikashopText extends hikashopItem {
	var $type = 'text';
	var $class = 'inputbox';

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {

		$size = '';
		if(!empty($field->field_options['size']))
			$size .= ' size="'.intval($field->field_options['size']).'"';
		if(!empty($field->field_options['maxlength']))
			$size .= ' maxlength="'.intval($field->field_options['maxlength']).'"';
		if(!empty($field->field_options['readonly']))
			$size .= ' readonly="readonly"';
		if(!empty($field->field_options['placeholder']))
			$size .= ' placeholder="'.JText::_($field->field_options['placeholder']).'"';

		$js = '';
		if($inside && strlen($value) < 1) {
			$value = addslashes($this->trans($field->field_realname));
			$this->excludeValue[$field->field_namekey] = $value;
			$js = ' onfocus="if(this.value == \''.$value.'\') this.value = \'\';" onblur="if(this.value==\'\') this.value=\''.$value.'\';"';
		}

		if(!empty($field->field_required) && !empty($field->registration_page))
			$size .= ' aria-required="true" required="required"';

		return '<input class="'.$this->class.'" id="'.$this->prefix.@$field->field_namekey.$this->suffix.'"'.$size.$js.' '.$options.' type="'.$this->type.'" name="'.$map.'" value="'.$value.'" />';
	}

	function show(&$field, $value) {
		if(in_array($field->field_table,array('address','order','item')))
			return $value;
		return $this->trans($value);
	}
}

class hikashopLink extends hikashopText {
	function show(&$field,$value) {
		$target = '';
		if(isset($field->field_options['target_blank']) && $field->field_options['target_blank'] == '1')
			$target = ' target="_blank"';
		return '<a'.$target.' href="'.$this->trans($value).'">'.$this->trans($value).'</a>';
	}
}

class hikashopFile extends hikashopText {
	var $type = 'file';
	var $class = 'inputbox hikashop_custom_file_upload_field';

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {
		$html = '';
		if(!empty($value)) {
			$html .= $this->show($field,$value,'hikashop_custom_file_upload_link');
		}
		$map = str_replace('.', '_', $field->field_table) . '_' . $field->field_namekey;
		$html.= parent::display($field, $value, $map, $inside, $options, $test, $allFields, $allValues);
		$html.= '<span class="hikashop_custom_file_upload_message">' . JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')).'</span>';
		return $html;
	}

	function JSCheck(&$oneField, &$requiredFields, &$validMessages, &$values) {
		$namekey = $oneField->field_namekey;
		if(empty($values->$namekey))
			return parent::JSCheck($oneField, $requiredFields, $validMessages, $values);
		return true;
	}

	function show(&$field,$value,$class='hikashop_custom_file_link') {
		switch($class){
			case 'admin_email':
				return '<a target="_blank" class="'.$class.'" href="'.HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value)).'">'.$value.'</a>';
			case 'user_email':
				if(@$field->guest_mode)
					return $value;
				$app = JFactory::getApplication();
				if(!$app->isAdmin())
					return '<a target="_blank" class="'.$class.'" href="'.hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value))).'">'.$value.'</a>';
				return '<a target="_blank" class="'.$class.'" href="'.HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value)).'">'.$value.'</a>';
			default:
				break;
		}
		return '<a target="_blank" class="'.$class.'" href="'.hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value))).'">'.$value.'</a>';
	}

	function check(&$field, &$value, $oldvalue) {
		$class = hikashop_get('class.file');
		$map = str_replace('.', '_', $field->field_table) . '_' . $field->field_namekey;

		if(empty($field->field_options['file_type']))
			$field->field_options['file_type'] = 'file';

		$file = $class->saveFile($map, $field->field_options['file_type'], $this->allowedFiles());

		if(!empty($file)) {
			$value = $file;
		} else if(!empty($oldvalue)) {
			$value = $oldvalue;
		} else {
			$value = '';
		}

		return parent::check($field, $value, $oldvalue);
	}

	function allowedFiles() {
		$config =& hikashop_config();
		return $config->get('allowedfiles');
	}
}

class hikashopImage extends hikashopFile {
	function show(&$field, $value, $class='hikashop_custom_image_link') {
		if(in_array($class,array('admin_email', 'user_email')))
			return parent::show($field, $value, $class);

		if(empty($class))
			$class = 'hikashop_custom_image_link';
		return '<img class="'.$class.'" src="'.hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value))).'" alt="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'" />';
	}

	function allowedFiles() {
		$config =& hikashop_config();
		return $config->get('allowedimages');
	}
}

class hikashopAjaxfile extends hikashopItem {
	var $layoutName = 'upload';
	var $mode = 'file';
	var $viewName = 'file_entry';

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		$config = hikashop_config();
		$uploaderType = hikashop_get('type.uploader');

		$id = $this->prefix.@$field->field_namekey.$this->suffix;
		$options = array(
			'upload' => true,
			'gallery' => false,
			'text' => JText::_('HIKA_DEFAULT_IMAGE_EMPTY_UPLOAD'),
			'uploader' => array('order', $field->field_table.'-'.$field->field_namekey),
			'ajax' => true,
			'vars' => array(
				'field_map' => $map,
				'uploader_id' => $id
			)
		);

		$params = new stdClass();
		$params->file_name = $value;
		$params->file_path = $value;
		$params->field_name = $map.'[name]';
		$params->file_size = 0;
		$params->delete = empty($field->field_required);
		$params->uploader_id = $id;

		if(!empty($value)) {
			$path = JPath::clean(HIKASHOP_ROOT.DS.trim($config->get('uploadsecurefolder'), DS.' ').DS);
			$v = '';
			if(JFile::exists($path . $value)) {
				$v = md5_file($path . $value);
				$params->file_size = filesize($path . $value);

				$params->working_path = $path;

				$n = $map.'[sec]';

				$params->extra_fields = array(
					$n => $v
				);
			}
		}

		$params->origin_url = hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value)));

		if($this->mode == 'image' && !empty($value)) {
			$thumbnail_x = 100;
			$thumbnail_y = 100;
			$thumbnails_params = '&thumbnail_x='.$thumbnail_x.'&thumbnail_y='.$thumbnail_y;

			$params->thumbnail_url = hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value)).$thumbnails_params);
		}

		$js = '';
		$content = '';
		if(!empty($value))
			$content = hikashop_getLayout($this->layoutName, $this->viewName, $params, $js);

		if($this->mode == 'image')
			return $uploaderType->displayImageSingle($id, $content, $options);
		return $uploaderType->displayFileSingle($id, $content, $options);
	}

	function show(&$field, $value, $class = 'hikashop_custom_file_link') {
		if($class == 'admin_email')
			return '<a target="_blank" class="'.$class.'" href="'.HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value)).'">'.$value.'</a>';

		if($class == 'user_email') {
			if(@$field->guest_mode)
				return $value;

			$app = JFactory::getApplication();
			if(!$app->isAdmin())
				return '<a target="_blank" class="'.$class.'" href="'.hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value))).'">'.$value.'</a>';
			return '<a target="_blank" class="'.$class.'" href="'.HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value)).'">'.$value.'</a>';
		}

		hikashop_loadJslib('opload');
		if($this->mode == 'image') {
			$thumbnail_x = 100;
			$thumbnail_y = 100;
			$thumbnails_params = '&thumbnail_x='.$thumbnail_x.'&thumbnail_y='.$thumbnail_y;
			return '<img class="'.$class.'" src="'.hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value)).$thumbnails_params).'" alt="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'" />';
		}
		return '<a target="_blank" class="'.$class.'" href="'.hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value))).'">'.$value.'</a>';
	}

	function check(&$field,&$value,$oldvalue) {
		if(!empty($value) && !is_array($value))
			return false;

		if(is_array($value)) {
			$config = hikashop_config();
			$path = JPath::clean(HIKASHOP_ROOT.DS.trim($config->get('uploadsecurefolder'), DS.' ').DS);
			$hash = '';
			if(!empty($value['name']) && file_exists($path . $value['name']))
				$hash = md5_file($path . $value['name']);
			if(!empty($value['name']) && (empty($value['sec']) || $hash != $value['sec'])) {
				$value = $oldvalue;
				return false;
			}
			$value = $value['name'];
		} else if($value != $oldvalue) {
			$value = $oldvalue;
			return false;
		}

		return parent::check($field,$value,$oldvalue);
	}

	function _manageUpload($field, &$ret, $map, $uploadConfig, $caller) {
		if(empty($map) || empty($field))
			return;

		$config = hikashop_config();
		$path = JPath::clean(HIKASHOP_ROOT.DS.trim($config->get('uploadsecurefolder'), DS.' ').DS);

		$ret->params->file_name = $ret->params->file_path;
		$ret->params->field_name = $map.'[name]';
		if(!empty($ret->params->file_path)) {
			$v = md5_file($path . $ret->params->file_path);
			$ret->params->file_size = filesize($path . $ret->params->file_path);

			$n = $map.'[sec]';
			$ret->params->extra_fields = array(
				$n => $v
			);
		}

		$ret->params->origin_url = hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($ret->params->file_path)));

		if($this->mode == 'image') {
			$thumbnail_x = 100;
			$thumbnail_y = 100;
			$thumbnails_params = '&thumbnail_x='.$thumbnail_x.'&thumbnail_y='.$thumbnail_y;
			$ret->params->thumbnail_url = hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($ret->params->file_path)).$thumbnails_params);
		}
	}
}

class hikashopAjaximage extends hikashopAjaxfile {
	var $layoutName = 'upload';
	var $mode = 'image';
	var $viewName = 'image_entry';
}

class hikashopCoupon extends hikashopText {
	function check(&$field,&$value,$oldvalue){
		$status = parent::check($field,$value,$oldvalue);

		if($status){
			if($field->field_required && empty($value)){
				return true;
			}
			$zone_id = hikashop_getZone('shipping');
			$discount=hikashop_get('class.discount');
			$zoneClass = hikashop_get('class.zone');
			$zones = $zoneClass->getZoneParents($zone_id);
			$total = new stdClass();
			$price = new stdClass();
			$price->price_value_with_tax = 0;
			$price->price_value = 0;
			$price->price_currency_id = hikashop_getCurrency();
			$total->prices = array($price);
			if(empty($field->coupon)){
				$field->coupon=array();
			}
			$products = array();
			$field->coupon[$value] = $discount->loadAndCheck($value,$total,$zones,$products,true);

			if(empty($field->coupon[$value])){
				$app = JFactory::getApplication();
				$app->enqueueMessage(JRequest::getVar('coupon_error_message'),'notice');
				$status = false;
			}
			static $validCoupons = array();
			if(!isset($validCoupons[$value])){
				$validCoupons[$value] = 1;
			}else{
				$validCoupons[$value]++;
			}

			if($field->coupon[$value]->discount_quota>0){
				$left = ($field->coupon[$value]->discount_quota - $field->coupon[$value]->discount_used_times);
				if($left<$validCoupons[$value]){
					if($left>0){
						$app = JFactory::getApplication();
						$app->enqueueMessage('You cannot use the coupon '.$value.' more than '.$left.' times !');
					}
					$status = false;
				}
			}


		}
		return $status;
	}
}

class hikashopWysiwyg extends hikashopTextarea {
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {
		$editorHelper = hikashop_get('helper.editor');
		$editorHelper->name = $map;
		$editorHelper->content = $value;
		$editorHelper->id = $this->prefix.@$field->field_namekey.$this->suffix;
		$editorHelper->width = '100%';
		$editorHelper->cols = empty($field->field_options['cols']) ? 50 : intval($field->field_options['cols']);
		$editorHelper->rows = empty($field->field_options['rows']) ? 10 : intval($field->field_options['rows']);

		$ret = $editorHelper->display().
			'<div style="clear:both"></div>'.
			'<script type="text/javascript">'."\r\n".
			'if(window.Oby) window.Oby.registerAjax("syncWysiwygEditors", function(){ '.$editorHelper->jsCode().' });'."\r\n".
			'</script>';

		return $ret;
	}

	function show(&$field,$value) {
		return $this->trans($value);
	}
}

class hikashopTextarea extends hikashopItem {
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		$js = '';
		$html = '';
		if($inside && strlen($value) < 1){
			$value = addslashes($this->trans($field->field_realname));
			$this->excludeValue[$field->field_namekey] = $value;
			$js = 'onfocus="if(this.value == \''.$value.'\') this.value = \'\';" onblur="if(this.value==\'\') this.value=\''.$value.'\';"';
		}
		if(!empty($field->field_options['maxlength'])){
			static $done = false;
			if(!$done){
				$jsFunc='
				function hikashopTextCounter(textarea, counterID, maxLen) {
					cnt = document.getElementById(counterID);
					if (textarea.value.length > maxLen){
						textarea.value = textarea.value.substring(0,maxLen);
					}
					cnt.innerHTML = maxLen - textarea.value.length;
				}';
				if(!HIKASHOP_PHP5) {
					$doc =& JFactory::getDocument();
				} else {
					$doc = JFactory::getDocument();
				}
				$doc->addScriptDeclaration( "<!--\n".$jsFunc."\n//-->\n" );
				$html.= '<span class="hikashop_remaining_characters">'.JText::sprintf('X_CHARACTERS_REMAINING',$this->prefix.@$field->field_namekey.$this->suffix.'_count',(int)$field->field_options['maxlength']).'</span>';
			}
			$js .= ' onKeyUp="hikashopTextCounter(this,\''.$this->prefix.@$field->field_namekey.$this->suffix.'_count'.'\','.(int)$field->field_options['maxlength'].');" onBlur="hikashopTextCounter(this,\''.$this->prefix.@$field->field_namekey.$this->suffix.'_count'.'\','.(int)$field->field_options['maxlength'].');" ';
		}

		$cols = empty($field->field_options['cols']) ? '' : 'cols="'.intval($field->field_options['cols']).'"';
		$rows = empty($field->field_options['rows']) ? '' : 'rows="'.intval($field->field_options['rows']).'"';
		$options .= empty($field->field_options['readonly']) ? '' : ' readonly="readonly"';
		$options .= empty($field->field_options['placeholder']) ? '' : ' placeholder="'.JText::_($field->field_options['placeholder']).'"';
		return '<textarea class="inputbox" id="'.$this->prefix.@$field->field_namekey.$this->suffix.'" name="'.$map.'" '.$cols.' '.$rows.' '.$js.' '.$options.'>'.$value.'</textarea>'.$html;
	}

	function show(&$field,$value){
		return nl2br(parent::show($field,$value));
	}
}

class hikashopDropdown extends hikashopItem{
	var $type = '';
	function show(&$field,$value){
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		if(isset($field->field_value[$value])) $value = $field->field_value[$value]->value;
		return parent::show($field,$value);
	}

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		$string = '';
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		if(empty($field->field_value) || !count($field->field_value)){
			if(is_array($value))
				$value = reset($value);
			return '<input type="hidden" name="'.$map.'" value="'.$value.'" />';
		}
		if($this->type == "multiple"){
			$string.= '<input type="hidden" name="'.$map.'" value=" " />';
			$map.='[]';
			$arg = 'multiple="multiple"';
			if(!empty($field->field_options['size'])) $arg .= ' size="'.intval($field->field_options['size']).'"';
		}else{
			$arg = 'size="1"';
			if(is_string($value)&& empty($value) && !empty($field->field_value)){
				$found = false;
				$first = false;
				foreach($field->field_value as $oneValue => $title){
					if($first===false){
						$first=$oneValue;
					}
					if($oneValue==$value){
						$found = true;
						break;
					}
				}
				if(!$found){
					$value = $first;
				}
			}
		}
		if(strpos($options, 'class="') === false) {
			$options .= ' class="hikashop_field_dropdown"';
		} else {
			$options = str_replace('class="', 'class="hikashop_field_dropdown ', $options);
		}
		$string .= '<select id="'.$this->prefix.$field->field_namekey.$this->suffix.'" name="'.$map.'" '.$arg.$options.'>';
		if(empty($field->field_value))
			return $string.'</select>';

		$app = JFactory::getApplication();
		$admin = $app->isAdmin();

		$isValue = !empty($value) && !is_array($value) && isset($field->field_value[$value]);
		if(is_array($value)) {
			$keys = array_keys($field->field_value);
			$isValue = array_intersect($value, $keys);
			$isValue = !empty($isValue);
		}
		$selected = '';
		foreach($field->field_value as $oneValue => $title) {
			if(isset($field->field_default) && !$isValue) {
				if(array_key_exists($field->field_default, $field->field_value)){
					$defaultValueEqualToCurrentValue = (is_numeric($field->field_default) && is_numeric($oneValue) && $oneValue == $field->field_default) || (is_string($field->field_default) && $oneValue === $field->field_default);
					if($defaultValueEqualToCurrentValue){
						$selected = ($defaultValueEqualToCurrentValue || is_array($field->field_default) && in_array($oneValue,$field->field_default)) ? 'selected="selected" ' : '';
					}else{
						$selected = ((int)$title->disabled && !$admin) ? 'disabled="disabled" ' : '';
					}
				}
			} else {
				$selected = ((int)$title->disabled && !$admin) ? 'disabled="disabled" ' : '';
				$selected .= ((is_numeric($value) && is_numeric($oneValue) && $oneValue == $value) || (is_string($value) && $oneValue === $value) || is_array($value) && in_array($oneValue,$value)) ? 'selected="selected" ' : '';
			}
			$id = $this->prefix.$field->field_namekey.$this->suffix.'_'.$oneValue;
			$string .= '<option value="'.$oneValue.'" id="'.$id.'" '.$selected.'>'.$this->trans($title->value).'</option>';
		}
		$string .= '</select>';

		return $string;
	}
}

class hikashopSingledropdown extends hikashopDropdown{
	var $type = 'single';
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		return parent::display($field,$value,$map,$inside,$options,$test,$allFields,$allValues);
	}
}

class hikashopZone extends hikashopSingledropdown{
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		if($field->field_options['zone_type'] != 'country' || empty($field->field_options['pleaseselect'])) {
			$currentZoneId = hikashop_getZone() ? hikashop_getZone() : '';
			if(!empty($currentZoneId) && JFactory::getApplication()->isSite()) {
				$zoneClass = hikashop_get('class.zone');
				$currentZone = $zoneClass->getZoneParents($currentZoneId);
				foreach($currentZone as $currentZoneInfos){
					if(preg_match('/country/',$currentZoneInfos)){
						$defaultCountry = $currentZoneInfos;
					}
				}
			}
		}

		if($field->field_options['zone_type'] == 'country'){
			if(isset($defaultCountry)){
				$field->field_default = $defaultCountry;
			}

			if(!empty($field->field_options['pleaseselect'])){
				$PleaseSelect = new stdClass();
				$PleaseSelect->value = JText::_('PLEASE_SELECT_SOMETHING');
				$PleaseSelect->disabled = 0;
				$field->field_value = array_merge(array('' => $PleaseSelect), $field->field_value);
				$field->field_default = '';
			}
			$stateNamekey = str_replace('country','state',$field->field_namekey);
			if(!empty($allFields)) {
				foreach($allFields as &$f) {
					if($f->field_type == 'zone' && !empty($f->field_options['zone_type']) && $f->field_options['zone_type'] == 'state') {
						$stateNamekey = $f->field_namekey;
						break;
					}
				}
			}
			$stateId = str_replace(
				array('[',']',$field->field_namekey),
				array('_','',$stateNamekey),
				$map
			);
			$form_name = str_replace(array('data[',']['.$field->field_namekey.']'), '', $map);

			$changeJs = 'window.hikashop.changeState(this,\''.$stateId.'\',\''.$field->field_url.'field_type='.$form_name.'&field_id='.$stateId.'&field_namekey='.$stateNamekey.'&namekey=\'+this.value);';
			if(!empty($options) && stripos($options,'onchange="')!==false){
				$options = preg_replace('#onchange="#i','onchange="'.$changeJs,$options);
			}else{
				$options = ' onchange="'.$changeJs.'"';
			}
			if($allFields == null || $allValues == null) {
				$doc = JFactory::getDocument();
				$lang = JFactory::getLanguage();
				$locale = strtolower(substr($lang->get('tag'),0,2));
				$js = 'window.hikashop.ready( function() {
	var el = document.getElementById(\''.$this->prefix.$field->field_namekey.$this->suffix.'\');
	window.hikashop.changeState(el,\''.$stateId.'\',\''.$field->field_url.'lang='.$locale.'&field_type='.$form_name.'&field_id='.$stateId.'&field_namekey='.$stateNamekey.'&namekey=\'+el.value);
});';
				$doc->addScriptDeclaration($js);
			}
		} elseif($field->field_options['zone_type'] == 'state') {
			$stateId = str_replace(array('[',']'),array('_',''),$map);

			$dropdown = '';

			if($allFields != null) {
				$country = null;
				if(isset($defaultCountry)){
					$country = $defaultCountry;
				}
				foreach($allFields as $f) {
					if($f->field_type == 'zone' && !empty($f->field_options['zone_type']) && $f->field_options['zone_type'] == 'country') {
						$key = $f->field_namekey;
						if(!empty($allValues->$key)) {
							$country = $allValues->$key;
						} else {
							$country = $f->field_default;
						}
						break;
					}
				}
				if(empty($country)) {
					$address_country_field = $this->parent->get(14); //14 = id of country field
					if(!empty($address_country_field) && $address_country_field->field_type=='zone' && !empty($address_country_field->field_options['zone_type']) && $address_country_field->field_options['zone_type']=='country' && !empty($address_country_field->field_default)) {
						$country = $address_country_field->field_default;
					}
				}
				if(!empty($country)) {
					$countryType = hikashop_get('type.country');
					$countryType->field = $field;
					$dropdown = $countryType->displayStateDropDown($country, $stateId, $map, '', $value, $field->field_options);
				} else {
					$dropdown = '<span class="state_no_country">'.JText::_('PLEASE_SELECT_COUNTRY_FIRST').'</span>';
				}
			}

			return '<span id="'.$stateId.'_container">'.$dropdown.'</span>'.
				'<input type="hidden" id="'.$stateId.'_default_value" name="'.$stateId.'_default_value" value="'.$value.'"/>';
		}
		return parent::display($field,$value,$map,$inside,$options,$test,$allFields,$allValues);
	}
	function check(&$field,&$value,$oldvalue){
		if(is_string($value))
			$value = trim($value);
		if(!$field->field_required || is_array($value) || strlen($value) || strlen($oldvalue)){
			if($value == 'no_state_found')
				$value = '';
			return true;
		}
		if(!empty($this->report)) {
			if($this->report === true) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname)));
			} else {
				$this->messages[] = JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname));
			}
		}
		return false;
	}
}

class hikashopMultipledropdown extends hikashopDropdown{
	var $type = 'multiple';
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		$value = explode(',',$value);
		return parent::display($field,$value,$map,$inside,$options,$test,$allFields,$allValues);
	}
	function show(&$field,$value){
		if(!is_array($value)){
			$value = explode(',',$value);
		}
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		$results = array();
		foreach($value as $val){
			if(isset($field->field_value[$val])) $val = $field->field_value[$val]->value;
			$results[]= parent::show($field,$val);
		}
		return implode(', ',$results);
	}
}

class hikashopRadioCheck extends hikashopItem {
	var $radioType = 'checkbox';
	function show(&$field,$value) {
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		if(isset($field->field_value[$value])) $value = $field->field_value[$value]->value;
		return parent::show($field,$value);
	}

	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		$type = $this->radioType;
		$string = '';
		if($inside) $string = $this->trans($field->field_realname).' ';
		if($type == 'checkbox'){
			$string.= '<input type="hidden" name="'.$map.'" value=" "/>';
			$map.='[]';
		}
		if(empty($field->field_value)) return $string;
		$app = JFactory::getApplication();
		$admin = $app->isAdmin();

		if(is_array($value)) {
			foreach($value as &$v) {
				$v = (string)$v;
			}
			unset($v);
		}

		$app = JFactory::getApplication();
		$use_bootstrap = $app->isAdmin() ? HIKASHOP_BACK_RESPONSIVE : HIKASHOP_RESPONSIVE;

		foreach($field->field_value as $oneValue => $title){
			$checked = ((int)$title->disabled && !$admin) ? 'disabled="disabled" ' : '';

			$oneValue = (string)$oneValue;
			$oneValue = htmlentities($oneValue, ENT_COMPAT, 'UTF-8');
			$checked .= ((is_string($value) && $oneValue === $value) || is_array($value) && in_array($oneValue,$value)) ? 'checked="checked" ' : '';
			$id = $this->prefix.$field->field_namekey.$this->suffix.'_'.$oneValue;

			if(!$use_bootstrap)
				$string .= '<input type="'.$type.'" name="'.$map.'" value="'.$oneValue.'" id="'.$id.'" '.$checked.' '.$options.' /><label for="'.$id.'">'.$this->trans($title->value).'</label>';
			else
				$string .= '<label><input type="'.$type.'" name="'.$map.'" value="'.$oneValue.'" id="'.$id.'" '.$checked.' '.$options.' /> '.$this->trans($title->value).'</label>';
		}
		return $string;
	}
}

class hikashopRadio extends hikashopRadioCheck {
	var $radioType = 'radio';
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		return parent::display($field,$value,$map,$inside,$options,$test,$allFields,$allValues);
	}
}

class hikashopCheckbox extends hikashopRadioCheck {
	var $radioType = 'checkbox';
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		if(!is_array($value)){
			$value = explode(',',$value);
		}
		return parent::display($field,$value,$map,$inside,$options,$test,$allFields,$allValues);
	}
	function show(&$field,$value){
		if(!is_array($value)){
			$value = explode(',',$value);
		}
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		$results = array();
		foreach($value as $val){
			if(isset($field->field_value[$val]))
				$val = $field->field_value[$val]->value;
			$results[] = parent::show($field,$val);
		}
		return implode(', ',$results);
	}
}

class hikashopDate extends hikashopItem{
	function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null){
		if(empty($field->field_options['format'])) $field->field_options['format'] = "%Y-%m-%d";
		$format = $field->field_options['format'];
		$size = $options . empty($field->field_options['size']) ? '' : ' size="'.$field->field_options['size'].'"';

		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		else
			JHTML::_('behavior.framework');
		$processing='';
		$message='';
		$check = 'false';
		if(HIKASHOP_J30 && !empty($field->field_options['allow'])){
			switch($field->field_options['allow']){
				case 'future':
					$check = 'today>selectedDate';
					$message = JText::_('SELECT_DATE_IN_FUTURE',true);
					$format .= '",'. "\r\n" . 'disableFunc: function(date) { var today=new Date(); today.setHours(0);today.setMinutes(0);today.setSeconds(0);today.setMilliseconds(0); if(date < today) { return true; } return false; }, //';
					break;
				case 'past':
					$check = 'today<selectedDate';
					$message = JText::_('SELECT_DATE_IN_PAST',true);
					$format .= '",'. "\r\n" . 'disableFunc: function(date) { var today=new Date(); today.setHours(23);today.setMinutes(59);today.setSeconds(59);today.setMilliseconds(99); if(date > today) { return true; } return false; }, //';
					break;
			}
		}

		if(!empty($check)) {
			$conversion = '';
			if($field->field_options['format'] != "%Y-%m-%d") {
				$seps = preg_replace('#[a-z0-9%]#iU','',$field->field_options['format']);
				$seps = str_replace(array('.','-'),array('\.','\-'),$seps);
				$mConv = false; $yP = -1; $mP = -1; $dP = -1; $i = 0;
				foreach(preg_split('#['.$seps.']#', $field->field_options['format']) as $d) {
					switch($d) {
						case '%y':
						case '%Y':
							if($yP<0) $yP = $i;
							break;
						case '%b':
						case '%B':
							$mConv = true;
						case '%m':
							if($mP<0) $mP = $i;
							break;
						case '%d':
						case '%e':
							if($dP<0) $dP = $i;
							break;
					}
					$i++;
				}
				$conversion .= '
				var reg = new RegExp("['.$seps.']+", "g");
				var elems = d.split(reg);
				';

				if($mConv) {
					$conversion .= 'for(var j=0;j<12;++j){if(Calendar._MN[j].substr(0,elems['.$mP.'].length).toLowerCase()==elems['.$mP.'].toLowerCase()){elems['.$mP.']=(j+1);break;}};
				';
				}

				$conversion .= 'd = elems['.$yP.'] + "-" + elems['.$mP.'] + "-" + elems['.$dP.'];
				';
			}
			$js = 'function '.$this->prefix.$field->field_namekey.$this->suffix.'_checkDate(nohide)
			{
				var selObj = document.getElementById(\''.$this->prefix.$field->field_namekey.$this->suffix.'\');
				if( typeof('.$this->prefix.$field->field_namekey.$this->suffix.'_preCheckDate) == "function" ) {
					try {
						if(!'.$this->prefix.$field->field_namekey.$this->suffix.'_preCheckDate(selObj))
							return false;
					} catch(ex) {}
				}
				if(selObj.value==\'\'){
					return true;
				}
				var d = selObj.value;'.$conversion.'
				var timestamp=Date.parse(d);
				var today=new Date();
				today.setHours(0);today.setMinutes(0);today.setSeconds(0);today.setMilliseconds(0);
				if(isNaN(timestamp)!=false){
					selObj.value=\'\';
					alert(\''.JText::_('INCORRECT_DATE_FORMAT',true).'\');
					return false;
				}
				var selectedDate = new Date(timestamp);
				selectedDate.setHours(0);selectedDate.setMinutes(0);selectedDate.setSeconds(0);selectedDate.setMilliseconds(0);

				'.$processing.'
				if('.$check.'){
					selObj.value=\'\';
					alert(\''.$message.'\');
				}else{
					if(!nohide) this.hide();
				}
				if( typeof('.$this->prefix.$field->field_namekey.$this->suffix.'_postCheckDate) == "function" ) {
					try{ '.$this->prefix.$field->field_namekey.$this->suffix.'_postCheckDate(selObj, selectedDate); } catch(ex){}
				}
			}';
			if(HIKASHOP_PHP5) {
				$document = JFactory::getDocument();
			} else {
				$document =& JFactory::getDocument();
			}
			$document->addScriptDeclaration($js);
			$size .= ' onChange="'.$this->prefix.$field->field_namekey.$this->suffix.'_checkDate(1);"';
		}

		if(HIKASHOP_J25 && !empty($value) && $field->field_options['format'] != "%Y-%m-%d") {
			$seps = preg_replace('#[a-z0-9%]#iU','',$field->field_options['format']);
			$seps = str_replace(array('.','-'),array('\.','\-'),$seps);
			$mConv = false; $yP = -1; $mP = -1; $dP = -1; $i = 0;
			foreach(preg_split('#['.$seps.']#', $field->field_options['format']) as $d) {
				switch($d) {
					case '%y':
					case '%Y':
						if($yP<0) $yP = $i;
						break;
					case '%b':
					case '%B':
						$mConv = true;
					case '%m':
						if($mP<0) $mP = $i;
						break;
					case '%d':
					case '%e':
						if($dP<0) $dP = $i;
						break;
				}
				$i++;
			}
			$elems = preg_split('#['.$seps.']#', $value);
			$value = @$elems[$yP] . '-' . @$elems[$mP] . '-' . @$elems[$dP];
			$app = Jfactory::getApplication();
			if($app->isAdmin()) {
				$app->enqueueMessage('Since Joomla 2.5.24 it is not possible anymore to change the format of dates. If you need a different format, please use the advanced datepicker type of custom field.');
			}
			$format = "%Y-%m-%d";
			$field->field_options['format'] = $format;
		}
		if(HIKASHOP_J25 && !empty($value)) {
			try{
				JHTML::_('date', $value, null, null);
			}catch(Exception $e) {
				$value = '';
			}
		}

		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onFieldDateDisplay', array($field->field_namekey, $field, &$value, &$map, &$format, &$size));

		return JHTML::_('calendar', $value, $map,$this->prefix.$field->field_namekey.$this->suffix,$format,$size);
	}

	function showfield($viewObj, $namekey, $row) {
		if( isset( $row->$namekey)) {
			$date_format = !empty( $this->field_options['format']) ? $this->field_options['format'] : '%Y-%m-%d %H:%M:%S';
			return hikashop_getDate( $row->$namekey, $date_format);
		}
		return '';
	}
}

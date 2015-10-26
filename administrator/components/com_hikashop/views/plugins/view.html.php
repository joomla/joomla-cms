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
class PluginsViewPlugins extends hikashopView{
	var $type = '';
	var $ctrl = 'plugins';
	var $nameListing = 'PLUGINS';
	var $nameForm = 'PLUGINS';
	var $icon = 'plugin';
	var $triggerView = true;

	function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(!method_exists($this, $function) || $this->$function())
			parent::display($tpl);
	}

	function listing() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$config =& hikashop_config();
		$this->assignRef('config', $config);

		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggle);

		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyClass', $currencyClass);
		$zoneClass = hikashop_get('class.zone');
		$this->assignRef('zoneClass', $zoneClass);

		$manage = hikashop_isAllowed($config->get('acl_plugins_manage','all'));
		$this->assignRef('manage',$manage);

		$type = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.plugin_type', 'plugin_type', 'shipping');
		$this->assignRef('plugin_type',$type);

		if(HIKASHOP_J16){
			$query='SELECT * FROM '.hikashop_table('extensions',false).' WHERE type=\'plugin\' AND enabled = 1 AND access <> 1 AND (folder=\'hikashoppayment\' OR folder=\'hikashopshipping\') ORDER BY ordering ASC';
			$db->setQuery($query);
			$plugins = $db->loadObjectList();
			if (!empty($plugins))
			{
				$s = '(';
				foreach ($plugins as $p)
					$s .= $p->name.', ';
				$s = rtrim($s,', ').')';
				$app->enqueueMessage(JText::sprintf('PLUGIN_ACCESS_WARNING',$s),'warning');
			}
		}

		if(!in_array($type, array('shipping', 'payment', 'plugin'))) {
			hikashop_setTitle(JText::_($this->nameListing), $this->icon, $this->ctrl);
			return false;
		}

		hikashop_setTitle(JText::_($this->nameListing), $this->icon, $this->ctrl.'&plugin_type='.$type);

		$cfg = array(
			'table' => $type,
			'main_key' => $type.'_id',
			'order_sql_value' => 'plugin.'.$type.'_ordering'
		);
		$searchMap = array(
			'plugin.'.$type.'_name',
			'plugin.'.$type.'_type',
			'plugin.'.$type.'_id'
		);

		$pageInfo = $this->getPageInfo($cfg['order_sql_value']);

		$filters = array();
		$order = '';

		$this->processFilters($filters, $order, $searchMap);

		JPluginHelper::importPlugin('hikashop');
		if(in_array($type, array('shipping', 'payment')))
			JPluginHelper::importPlugin('hikashop'.$type);
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeHikaPluginConfigurationListing', array($type, &$filters, &$order, &$searchMap, &$extrafilters, &$this));

		$query = 'FROM '.hikashop_table($cfg['table']).' AS plugin '.$filters.$order;

		$this->getPageInfoTotal($query, '*');

		$db->setQuery('SELECT * '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

		$rows = $db->loadObjectList();
		if(!empty($pageInfo->search)) {
			$rows = hikashop_search($pageInfo->search, $rows, array($cfg['main_key'], $type.'_params', $type.'_type'));
		}
		$this->assignRef('rows', $rows);
		$pageInfo->elements->page = count($rows);

		$listing_columns = array();
		$pluginInterfaceClass = null;
		switch($type) {
			case 'payment':
				$pluginInterfaceClass = hikashop_get('class.payment');
				break;
			case 'shipping':
				$pluginInterfaceClass = hikashop_get('class.shipping');
				break;
			case 'plugin':
			default:
				$pluginInterfaceClass = hikashop_get('class.plugin');
				break;
		}
		if(!empty($pluginInterfaceClass) && method_exists($pluginInterfaceClass, 'fillListingColumns'))
			$pluginInterfaceClass->fillListingColumns($rows, $listing_columns, $this);

		$dispatcher->trigger('onAfterHikaPluginConfigurationListing', array($type, &$rows, &$listing_columns, &$this));

		$this->assignRef('listing_columns', $listing_columns);

		$this->getPagination();
		$this->getOrdering('plugin.'.$type.'_ordering', true);

		if(!HIKASHOP_J16) {
			$db->setQuery('SELECT id, published, name, element FROM '.hikashop_table('plugins',false).' WHERE `folder` = '.$db->Quote('hikashop'.$type));
		} else {
			$db->setQuery('SELECT extension_id as id, enabled as published, name, element FROM '.hikashop_table('extensions',false).' WHERE `folder` = '.$db->Quote('hikashop'.$type).' AND type=\'plugin\'');
		}
		$plugins = $db->loadObjectList('element');
		$this->assignRef('plugins', $plugins);

		$this->toolbar = array(
			'|',
			array('name' => 'custom', 'icon' => 'copy', 'task' => 'copy', 'alt' => JText::_('HIKA_COPY'),'display'=>$manage),
			array('name' => 'publishList', 'display' => $manage),
			array('name' => 'unpublishList', 'display' => $manage),
			array('name' => 'addNew', 'display' => $manage),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);


		return true;
	}

	function selectnew() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$config =& hikashop_config();
		$this->assignRef('config', $config);

		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggle);

		$manage = hikashop_isAllowed($config->get('acl_plugins_manage','all'));
		$this->assignRef('manage',$manage);

		$type = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.plugin_type', 'plugin_type', 'shipping');
		hikashop_setTitle(JText::_($this->nameListing), $this->icon, $this->ctrl.'&task=add&plugin_type='.$type);

		if($type == 'plugin')
			$group = 'hikashop';
		else
			$group = 'hikashop' . $type;
		if(!HIKASHOP_J16) {
			$db->setQuery('SELECT * FROM '.hikashop_table('plugins',false).' WHERE `folder` = '.$db->Quote($group).' ORDER BY published DESC, name ASC, ordering ASC');
		} else {
			$db->setQuery('SELECT extension_id as id, enabled as published,name,element FROM '.hikashop_table('extensions',false).' WHERE `folder` = '.$db->Quote($group).' AND type=\'plugin\' ORDER BY enabled DESC, name ASC, ordering ASC');
		}
		$plugins = $db->loadObjectList();

		if($type == 'plugin')
			JPluginHelper::importPlugin('hikashop');
		else
			JPluginHelper::importPlugin('hikashop'.$type);
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterHikaPluginConfigurationSelectionListing', array($type, &$plugins, &$this));

		$query = 'SELECT * FROM '.hikashop_table($type);
		$db->setQuery($query);
		$obj = $db->loadObject();
		if(empty($obj)) {
			$app->enqueueMessage(JText::_('EDIT_PLUGINS_BEFORE_DISPLAY'));
		}

		$currencies = null;
		if($type == 'payment') {
			$currencyClass = hikashop_get('class.currency');
			$mainCurrency = $config->get('main_currency',1);
			$currencyIds = $currencyClass->publishedCurrencies();
			if(!in_array($mainCurrency, $currencyIds))
				$currencyIds = array_merge(array($mainCurrency), $currencyIds);
			$null = null;
			$currencies = $currencyClass->getCurrencies($currencyIds, $null);

			foreach($plugins as &$plugin) {
				try{
					$p = hikashop_import('hikashoppayment', $plugin->element);
				} catch(Exception $e) { $p = null; }
				$plugin->accepted_currencies = array();
				if(isset($p->accepted_currencies))
					$plugin->accepted_currencies = $p->accepted_currencies;
				unset($plugin);
			}
		}
		$this->assignRef('plugins', $plugins);
		$this->assignRef('plugin_type',$type);
		$this->assignRef('currencies', $currencies);

		$this->toolbar = array(
			array('name' => 'link', 'alt' => 'HIKA_CANCEL', 'icon' => 'cancel', 'url' => hikashop_completeLink('plugins&plugin_type='.$type)),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		return true;
	}

	function form() {
		JHTML::_('behavior.modal');
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$task = JRequest::getVar('task');

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggle', $toggle);

		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);

		$this->content = '';
		$this->plugin_name = JRequest::getCmd('name', '');
		if(empty($this->plugin_name)) {
			hikashop_setTitle(JText::_($this->nameListing), $this->icon, $this->ctrl);
			return false;
		}

		$this->plugin_type = '';
		$type = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.plugin_type', 'plugin_type', 'shipping');
		if(in_array($type, array('shipping', 'payment', 'plugin'))) {
			if($type == 'plugin') {
				$plugin = hikashop_import('hikashop', $this->plugin_name);

				if(!is_subclass_of($plugin, 'hikashopPlugin')) {
					if(!HIKASHOP_J16) {
						$url = 'index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]=';
						$db->setQuery("SELECT id FROM `#__plugins` WHERE `folder` = 'hikashop' and element=".$db->Quote($this->plugin_name));
						$plugin_id = $db->loadResult();
					}else{
						$url = 'index.php?option=com_plugins&task=plugin.edit&extension_id=';
						$db->setQuery("SELECT extension_id as id FROM `#__extensions` WHERE `folder` = 'hikashop' AND `type`='plugin' AND element=".$db->Quote($this->plugin_name));
						$plugin_id = $db->loadResult();
					}
					$app->redirect($url.$plugin_id);
				}
			} else
				$plugin = hikashop_import('hikashop' . $type, $this->plugin_name);
			if(!$plugin) {
				hikashop_setTitle(JText::_($this->nameListing), $this->icon, $this->ctrl);
				return false;
			}
			$this->plugin_type = $type;
		} else {
			hikashop_setTitle(JText::_($this->nameListing), $this->icon, $this->ctrl);
			return false;
		}

		$multiple_plugin = false;
		$multiple_interface = false;
		if(method_exists($plugin, 'isMultiple')) {
			$multiple_interface = true;
			$multiple_plugin = $plugin->isMultiple();
		}

		$subtask = JRequest::getCmd('subtask', '');
		if($multiple_plugin && empty($subtask)) {
			$querySelect = array();
			$queryFrom = array();
			$queryWhere = array();
			$filters = array();

			JPluginHelper::importPlugin('hikashop');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onHikaPluginListing', array($type, &$querySelect, &$queryFrom, &$queryWhere, &$filters));

			if(!empty($querySelect)) $querySelect = ', ' . implode(',', $querySelect);
			else $querySelect = '';

			if(!empty($queryFrom)) $queryFrom = ', ' . implode(',', $queryFrom);
			else $queryFrom = '';

			if(!empty($queryWhere)) $queryWhere = ' AND (' . implode(') AND (', $queryWhere) . ') ';
			else $queryWhere = '';

			$this->assignRef('filters', $filters);
		} else {
			$querySelect = '';
			$queryFrom = '';
			$queryWhere = '';
		}

		$query = 'SELECT plugin.* ' . $querySelect .
			' FROM ' . hikashop_table($this->plugin_type) . ' as plugin ' . $queryFrom .
			' WHERE (plugin.' . $this->plugin_type . '_type = ' . $db->Quote($this->plugin_name) . ') ' . $queryWhere .
			' ORDER BY plugin.' . $this->plugin_type . '_ordering ASC';
		$db->setQuery($query);
		$elements = $db->loadObjectList($this->plugin_type.'_id');

		if(!empty($elements)){
			$params_name = $this->plugin_type.'_params';
			foreach($elements as $k => $el) {
				if(!empty($el->$params_name)) {
					$elements[$k]->$params_name = unserialize($el->$params_name);
				}
			}
		}

		$function = 'pluginConfiguration';
		$ctrl = '&plugin_type='.$this->plugin_type.'&task='.$task.'&name='.$this->plugin_name;
		if($multiple_plugin === true) {
			$subtask = JRequest::getCmd('subtask','');
			$ctrl .= '&subtask='.$subtask;
			if(empty($subtask)) {
				$function = 'pluginMultipleConfiguration';
			} else {
				$typeFunction = 'on' . ucfirst($this->plugin_type) . 'Configuration';
				if(method_exists($plugin, $typeFunction)) {
					$function = $typeFunction;
				}
			}
			$cid = hikashop_getCID($this->plugin_type.'_id');
			if(isset($elements[$cid])) {
				$this->assignRef('element', $elements[$cid]);
				$configParam =& $elements[$cid];
				$ctrl .= '&' . $this->plugin_type . '_id=' . $cid;
			} else {
				$configParam = new stdClass;
				$this->assignRef('element', $configParam);
			}
		} else {
			$configParam =& $elements;

			$element = null;
			if(!empty($elements)) {
				$element = reset($elements);
			}
			$this->assignRef('element', $element);
			$typeFunction = 'on' . ucfirst($this->plugin_type) . 'Configuration';
			if(method_exists($plugin, $typeFunction)) {
				$function = $typeFunction;
			}
		}
		$this->assignRef('elements', $elements);

		if($multiple_interface && !isset($subtask) || !empty($subtask)) {
			$extra_config = array();
			$extra_blocks = array();

			JPluginHelper::importPlugin('hikashop');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onHikaPluginConfiguration', array($type, &$plugin, &$this->element, &$extra_config, &$extra_blocks));

			$this->assignRef('extra_config', $extra_config);
			$this->assignRef('extra_blocks', $extra_blocks);
		}

		$setTitle = true;
		if(method_exists($plugin, $function)) {
			if(empty($plugin->title))
				$plugin->title = JText::_('HIKA_PLUGIN').' '.$this->plugin_name;
			ob_start();
			$plugin->$function($configParam);
			$this->content = ob_get_clean();
			$this->data = $plugin->getProperties();
			$setTitle = false;
		}

		if(isset($this->data['toolbar'])) {
			$this->toolbar = $this->data['toolbar'];
		} else {
			$this->toolbar = array(
				'save',
				'apply',
				'cancel',
				'|'
			);
		}

		$this->assignRef('name', $this->plugin_name);
		$this->assignRef('plugin', $plugin);
		$this->assignRef('multiple_plugin', $multiple_plugin);
		$this->assignRef('multiple_interface', $multiple_interface);
		$this->assignRef('content', $this->content);
		$this->assignRef('plugin_type', $this->plugin_type);

		$categoryType = hikashop_get('type.categorysub');
		$categoryType->type = 'tax';
		$categoryType->field = 'category_id';
		$this->assignRef('categoryType', $categoryType);

		if($this->plugin_type == 'shipping') {
			$warehouseType = hikashop_get('type.warehouse');
			$this->assignRef('warehouseType', $warehouseType);
			if(!empty($this->element->shipping_params->override_tax_zone)){
				$zoneClass = hikashop_get('class.zone');
				$this->element->shipping_params->override_tax_zone = $zoneClass->get($this->element->shipping_params->override_tax_zone);
			}
		}

		$this->_noForm($type, $elements);

		$currencies = hikashop_get('type.currency');
		$column_name = $type.'_currency';
		$this->element->$column_name = explode(',',trim(@$this->element->$column_name,','));
		$this->assignRef('currencies',$currencies);

		if($type == 'payment')
			$this->_loadPayment();

		if(empty($plugin->pluginView)) {
			$this->content .= $this->loadPluginTemplate(@$plugin->view, $type);
		}

		if($setTitle)
			hikashop_setTitle(JText::_('HIKA_PLUGIN').' '.$this->name, $this->icon, $this->ctrl. $ctrl);

		return true;
	}

	function _noForm($type, $elements) {
		$this->assignRef('noForm', $this->data['noForm']);
		if(!empty($this->data['noForm']))
			return;

		$element = $this->element;
		if(empty($element))
			$element = new stdClass();
		$id = 0;
		if(is_array($elements) && count($elements)) {
			$id_name = $type.'_id';
			$id = hikashop_getCID($id_name);
			if(isset($elements[$id])) {
				$element = $elements[$id];
				$id = @$element->$id_name;
			} elseif(!$this->multiple_plugin && empty($this->data->multiple_entries)) {
				$element = array_pop($elements);
				$id = @$element->$id_name;
			}
		}

		$plugin_zone_namekey = $type .'_zone_namekey';
		if(!empty($element->$plugin_zone_namekey)){
			$zoneClass = hikashop_get('class.zone');
			$zone = $zoneClass->get($element->$plugin_zone_namekey);
			if(!empty($zone)) {
				foreach(get_object_vars($zone) as $k => $v){
					$element->$k = $v;
				}
			}
		}

		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()) {
			$translation = true;
			$payment_id = $type.'_id';
			$transHelper->load('hikashop_'.$type, @$element->$payment_id, $element);
		}

		$config =& hikashop_config();
		$multilang_display = $config->get('multilang_display','tabs');
		if($multilang_display == 'popups')
			$multilang_display = 'tabs';

		$tabs = hikashop_get('helper.tabs');
		$editor = hikashop_get('helper.editor');
		$editor->name = $type.'_description';
		$name = $editor->name;
		$editor->content = @$element->$name;

		$this->assignRef('transHelper', $transHelper);
		$this->assignRef('tabs', $tabs);
		$this->assignRef('editor', $editor);
		$this->assignRef('translation', $translation);
		$this->assignRef('element', $element);
		$this->assignRef('id', $id);
	}

	function _loadPayment() {
		$shippingMethods = hikashop_get('type.plugins');
		$shippingMethods->type = 'shipping';
		$shippingMethods->manualOnly = true;

		if(!empty($this->element->payment_shipping_methods)) {
			$methods = explode("\n", $this->element->payment_shipping_methods);
			$this->element->payment_shipping_methods_id = array();
			$this->element->payment_shipping_methods_type = array();
			foreach($methods as $method) {
				list($shipping_type,$shipping_id) = explode('_', $method, 2);
				$this->element->payment_shipping_methods_id[] = $shipping_id;
				$this->element->payment_shipping_methods_type[] = $shipping_type;
			}

		} else {
			if(!isset($this->element))
				$this->element= new stdClass();
			$this->element->payment_shipping_methods_id = array();
			$this->element->payment_shipping_methods_type = array();
		}
		$this->assignRef('shippingMethods', $shippingMethods);
	}

	function edit_translation() {
		$language_id = JRequest::getInt('language_id',0);

		$type = JRequest::getString('type');
		$field = $type.'_id';
		$cid = hikashop_getCID($field);
		$class = hikashop_get('class.'.$type);
		$element = $class->get($cid);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()) {
			$translation = true;
			$transHelper->load('hikashop_'.$type, @$element->$field, $element, $language_id);
			$this->assignRef('transHelper', $transHelper);
		}
		$editor = hikashop_get('helper.editor');
		$desc = $type.'_description';
		$editor->name = $desc;
		$editor->content = @$element->$desc;
		$editor->height=300;
		$this->assignRef('editor',$editor);
		$this->assignRef('element',$element);
		$this->assignRef('plugin_type',$type);

		$tabs = hikashop_get('helper.tabs');
		$this->assignRef('tabs',$tabs);
		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);

		return true;
	}

	function selectimages(){
		$type = JRequest::getCmd('type','shipping');
		if(!in_array($type,array('shipping','payment'))){
			$type = 'shipping';
		}
		$path = HIKASHOP_MEDIA.'images'.DS.$type.DS;
		jimport('joomla.filesystem.folder');
		$images = JFolder::files($path);
		$rows = array();
		foreach($images as $image){
			$parts = explode('.',$image);
			$row = new stdClass();
			$row->ext = array_pop($parts);
			if(!in_array(strtolower($row->ext),array('gif','png','jpg','jpeg','svg'))) continue;
			$row->id = implode($parts);
			$row->name = str_replace('_',' ',$row->id);
			$row->file = $image;
			$row->full = HIKASHOP_IMAGES .$type.'/'. $row->file;
			$rows[]=$row;
		}

		$selectedImages = JRequest::getVar('values','','','string');

		if(strtolower($selectedImages) == 'all') {
			foreach($rows as $id => $oneRow) {
				$rows[$id]->selected = true;
			}
		} elseif(!empty($selectedImages)) {
			$selectedImages = explode(',',$selectedImages);
			foreach($rows as $id => $oneRow){
				if(in_array($oneRow->id,$selectedImages)){
					$rows[$id]->selected = true;
				}
			}
		}

		$this->assignRef('rows', $rows);
		$this->assignRef('selectedLists', $selectedImages);
		$this->assignRef('type', $type);

		return true;
	}

	function loadPluginTemplate($view = '', $type = '') {
		static $previousType = '';
		if(empty($type)) {
			$type = $previousType;
		} else {
			$previousType = $type;
		}

		$app = JFactory::getApplication();
		$this->subview = '';
		if(!empty($view)) {
			$this->subview = '_' . $view;
		}

		if(isset($this->data['pluginConfig'])) {
			$paramsType = $type.'_params';
			$html = '';
			foreach($this->data['pluginConfig'] as $key => $value){
				if(is_array($value[0])) {
					$a = array_shift($value[0]);
					$label = vsprintf(JText::_($a), $value[0]);
				} else {
					$label = JText::_($value[0]);
				}

				$html .= '<tr><td class="key"><label for="data['.$type.']['.$paramsType.']['.$key.']">'.$label.'</label></td><td>';

				switch ($value[1]) {
					case 'input':
						$html .= '<input type="text" name="data['.$type.']['.$paramsType.']['.$key.']" value="'.@$this->element->$paramsType->$key.'"/>';
						break;

					case 'textarea':
						$html .= '<textarea name="data['.$type.']['.$paramsType.']['.$key.']" rows="3">'.@$this->element->$paramsType->$key.'</textarea>';
						break;
					case 'big-textarea':
						$html .= '<textarea name="data['.$type.']['.$paramsType.']['.$key.']" rows="9" width="100%" style="width:100%;">'.@$this->element->$paramsType->$key.'</textarea>';
						break;

					case 'boolean':
						if(!isset($this->element->$paramsType->$key) && isset($value[2]))
							$this->element->$paramsType->$key = $value[2];
						if(!isset($this->element->$paramsType->$key))
							$this->element->$paramsType->$key=1;
						$html .= JHTML::_('hikaselect.booleanlist', 'data['.$type.']['.$paramsType.']['.$key.']' , '', @$this->element->$paramsType->$key);
						break;

					case 'checkbox':
						$i = 0;
						foreach($value[2] as $listKey => $listData){
							$checked = '';
							if(!empty($this->element->$paramsType->$key)){
								if(in_array($listKey, $this->element->$paramsType->$key))
									$checked = 'checked="checked"';
							}
							$html .= '<input id="data_'.$type.'_'.$paramsType.'_'.$key.'_'.$i.'" name="data['.$type.']['.$paramsType.']['.$key.'][]" type="checkbox" value="'.$listKey.'" '.$checked.' /><label for="data_'.$type.'_'.$paramsType.'_'.$key.'_'.$i.'">'.$listData.'</label><br/>';
							$i++;
						}
						break;

					case 'radio':
						$values = array();
						foreach($value[2] as $listKey => $listData){
							$values[] = JHTML::_('select.option', $listKey, JText::_($listData));
						}
						$html .= JHTML::_('hikaselect.radiolist',   $values, 'data['.$type.']['.$paramsType.']['.$key.']' , 'class="inputbox" size="1"', 'value', 'text', @$this->element->$paramsType->$key );
						break;

					case 'list':
						$values = array();
						foreach($value[2] as $listKey => $listData){
							$values[] = JHTML::_('select.option', $listKey,JText::_($listData));
						}
						$html .= JHTML::_('select.genericlist',   $values, 'data['.$type.']['.$paramsType.']['.$key.']' , 'class="inputbox" size="1"', 'value', 'text', @$this->element->$paramsType->$key );
						break;

					case 'orderstatus':
						$html .= $this->data['order_statuses']->display('data['.$type.']['.$paramsType.']['.$key.']',@$this->element->$paramsType->$key);
						break;

					case 'address':
						$addressType = hikashop_get('type.address');
						$html .= $addressType->display('data['.$type.']['.$paramsType.']['.$key.']',@$this->element->$paramsType->$key);
						break;

					case 'html':
						$html .= $value[2];
						break;
				}

				$html .= '</td></tr>';
			}

			return $html;
		}

		if($type == 'plugin')
			$type = '';

		$name = $this->name.'_configuration'.$this->subview.'.php';
		$path = JPATH_THEMES.DS.$app->getTemplate().DS.'hikashop'.$type.DS.$name;

		if(!file_exists($path)) {
			if(!HIKASHOP_J16) {
				$path = JPATH_PLUGINS.DS.'hikashop'.$type.DS.$name;
			} else {
				$path = JPATH_PLUGINS.DS.'hikashop'.$type.DS.$this->name.DS.$name;
			}
			if(!file_exists($path)) {
				return '';
			}
		}
		ob_start();
		require($path);
		return ob_get_clean();
	}
}

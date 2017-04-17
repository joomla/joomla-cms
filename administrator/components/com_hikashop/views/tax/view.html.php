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

class TaxViewTax extends hikashopView{
	var $ctrl= 'tax';
	var $nameListing = 'RATES';
	var $nameForm = 'RATE';
	var $icon = 'tax';
	var $triggerView = true;

	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->filter->filter_status = $app->getUserStateFromRequest($this->paramBase.".filter_status",'filter_status','','array');
		$pageInfo->filter->filter_end = $app->getUserStateFromRequest($this->paramBase.".filter_end",'filter_end','','string');
		$pageInfo->filter->filter_start = $app->getUserStateFromRequest($this->paramBase.".filter_start",'filter_start','','string');
		$database	= JFactory::getDBO();
		$searchMap = array('a.tax_namekey','a.tax_rate');

		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeTaxListing', array($this->paramBase, &$this->extrafilters, &$pageInfo, &$filters));

		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}
		$query = ' FROM '.hikashop_table('tax').' AS a '.$filters.$order;
		$database->setQuery('SELECT a.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList('tax_namekey');
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows);
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		$filters = array('order_type=\'sale\'');
		if(is_array($pageInfo->filter->filter_status) && count($pageInfo->filter->filter_status) == 1) {
			$pageInfo->filter->filter_status = reset($pageInfo->filter->filter_status);
		}
		switch($pageInfo->filter->filter_status){
			case '':
				break;
			default:
				if(!is_array($pageInfo->filter->filter_status)) {
					$filters[] = 'order_status = '.$database->Quote($pageInfo->filter->filter_status);
					break;
				}
				if(!count($pageInfo->filter->filter_status) || in_array('', $pageInfo->filter->filter_status))
					break;
				$statuses = array();
				foreach($pageInfo->filter->filter_status as $status){
					$statuses[] = $database->Quote($status);
				}
				$filters[]='order_status IN ('.implode(',',$statuses).')';
				break;
		}
		switch($pageInfo->filter->filter_start){
			case '':
				switch($pageInfo->filter->filter_end){
					case '':
						break;
					default:
						$filter_end=explode('-',$pageInfo->filter->filter_end);
						$noHourDay=explode(' ',$filter_end[2]);
						$filter_end[2]=$noHourDay[0];
						$filter_end= hikashop_getTime(mktime(23, 59, 59, $filter_end[1], $filter_end[2], $filter_end[0]));
						$filters[]='order_created <= '.(int)$filter_end;
						$pageInfo->filter->filter_end=(int)$filter_end;
						break;
				}
				break;
			default:
				$filter_start=explode('-',$pageInfo->filter->filter_start);
				$noHourDay=explode(' ',$filter_start[2]);
				$filter_start[2]=$noHourDay[0];
				$filter_start= hikashop_getTime(mktime(0, 0, 0, $filter_start[1], $filter_start[2], $filter_start[0]));
				switch($pageInfo->filter->filter_end){
					case '':
						$filters[]='order_created >= '.hikashop_getTime((int)$filter_start);
						$pageInfo->filter->filter_start=(int)$filter_start;
						break;
					default:
						$filter_end=explode('-',$pageInfo->filter->filter_end);
						$noHourDay=explode(' ',$filter_end[2]);
						$filter_end[2]=$noHourDay[0];
						$filter_end= hikashop_getTime(mktime(23, 59, 59, $filter_end[1], $filter_end[2], $filter_end[0]));
						$filters[]='order_created >= '.(int)$filter_start. ' AND order_created <= '.(int)$filter_end;
						$pageInfo->filter->filter_start=(int)$filter_start;
						$pageInfo->filter->filter_end=(int)$filter_end;
						break;
				}
				break;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}

		$database->setQuery('SELECT order_tax_info, order_currency_id FROM '.hikashop_table('order').$filters);
		$orders_taxes = $database->loadObjectList();

		$config = hikashop_config();
		$main_currency = $config->get('main_currency');
		$currencyClass = hikashop_get('class.currency');
		$currency_ids = array($main_currency=>$main_currency);
		$currencies = array();
		if(count($orders_taxes)){
			foreach($orders_taxes as $k => $v){
				$currency_ids[$v->order_currency_id] = $v->order_currency_id;
			}
		}
		$null = null;
		$currencies = $currencyClass->getCurrencies($currency_ids,$null);

		if(count($orders_taxes)){
			foreach($orders_taxes as $k => $v){
				$orders_taxes[$k]->order_tax_info = unserialize($v->order_tax_info);
				$info =& $orders_taxes[$k]->order_tax_info;
				if(!$info) continue;
				foreach($info as $k2 => $taxes_info){
					$tax_amount = $taxes_info->tax_amount + @$taxes_info->tax_amount_for_shipping + @$taxes_info->tax_amount_for_payment - @$taxes_info->tax_amount_for_coupon;
					if(!isset($taxes_info->tax_rate)) $taxes_info->tax_rate = $rows[$taxes_info->tax_namekey]->tax_rate;
					if($taxes_info->tax_rate != 0)
						$info[$k2]->amount = round($tax_amount/$taxes_info->tax_rate,$currencyClass->getRounding($v->order_currency_id));
					else
						$info[$k2]->amount = 0;
					$info[$k2]->tax_amount = round($tax_amount,$currencyClass->getRounding($v->order_currency_id));
					if($main_currency!=$v->order_currency_id){
						$info[$k2]->tax_amount_main_currency = $currencyClass->convertUniquePrice($info[$k2]->tax_amount,$v->order_currency_id,$main_currency);
						$info[$k2]->amount_main_currency = $currencyClass->convertUniquePrice($info[$k2]->amount,$v->order_currency_id,$main_currency);
					}else{
						$info[$k2]->tax_amount_main_currency = $info[$k2]->tax_amount;
						$info[$k2]->amount_main_currency = $info[$k2]->amount;
					}
				}

			}
		}

		if($pageInfo->elements->page){
			foreach($rows as $k => $tax){
				$tax_amounts = array();
				$amounts = array();
				foreach($currencies as $currency_id => $currency){
					$tax_amount = 0;
					$amount = 0;
					if(count($orders_taxes)){
						foreach($orders_taxes as $order_taxes){
							if($order_taxes->order_currency_id != $currency_id || !$order_taxes->order_tax_info) continue;
							foreach($order_taxes->order_tax_info as $order_tax){
								if($order_tax->tax_namekey != $tax->tax_namekey) continue;
								$tax_amount += $order_tax->tax_amount;
								$amount += $order_tax->amount;
							}
						}
					}
					$tax_amounts[$currency_id] = $tax_amount;
					$amounts[$currency_id] = $amount;
				}
				$rows[$k]->tax_amounts = $tax_amounts;
				$rows[$k]->amounts = $amounts;

				$tax_amount_main_currency = 0;
				$amount_main_currency = 0;
				if(count($orders_taxes)){
					foreach($orders_taxes as $order_taxes){
						if(!$order_taxes->order_tax_info) continue;
						foreach($order_taxes->order_tax_info as $order_tax){
							if($order_tax->tax_namekey != $tax->tax_namekey) continue;
							$tax_amount_main_currency += $order_tax->tax_amount_main_currency;
							$amount_main_currency += $order_tax->amount_main_currency;
						}
					}
				}
				$rows[$k]->tax_amount = $tax_amount_main_currency;
				$rows[$k]->amount = $amount_main_currency;
			}
		}
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('currencies',$currencies);
		$this->assignRef('main_currency',$main_currency);
		$this->assignRef('currencyHelper',$currencyClass);
		$category = hikashop_get('type.categorysub');
		$category->type = 'status';
		$this->assignRef('category',$category);

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$this->getPagination();

		$this->toolbar = array(
			'addNew',
			'editList',
			'deleteList'
		);
		$return = JRequest::getString('return','');
		if(!empty($return)){
			$this->toolbar[]='cancel';
		}
		$this->assignRef('return',$return);
		$this->toolbar[]='|';
		$this->toolbar[]=array('name' => 'pophelp', 'target' => $this->ctrl.'-listing');
		$this->toolbar[]='dashboard';

	}
	function form(){

		$tax_namekey = JRequest::getString('tax_namekey');
		if(empty($tax_namekey)){
			$id = JRequest::getVar( 'cid', array(), '', 'array' );
			if(is_array($id) && count($id)) $tax_namekey = reset($id);
			else $tax_namekey = $id;
		}

		$class = hikashop_get('class.tax');
		if(!empty($tax_namekey)){
			$element = $class->get($tax_namekey);
			$task='edit';
		}else{
			$element = new stdClass();
			$element->banner_url = HIKASHOP_LIVE;
			$task='add';
			$tax_namekey='';
		}
		$this->assignRef('element',$element);

		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&tax_namekey='.$tax_namekey);

		$this->toolbar = array(
			'save',
			array('name' => 'save2new', 'display' => version_compare(JVERSION,'1.7','>=')),
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$return = JRequest::getString('return','');
		$this->assignRef('return',$return);
	}
}

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
class CurrencyViewCurrency extends hikashopView
{
	var $type = '';
	var $ctrl= 'currency';
	var $nameListing = 'CURRENCIES';
	var $nameForm = 'CURRENCIES';
	var $icon = 'currency';
	function display($tpl = null)
	{
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
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.currency_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );

		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(JRequest::getVar('search')!=$app->getUserState($this->paramBase.".search")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$database	= JFactory::getDBO();
		$searchMap = array('a.currency_symbol','a.currency_code','a.currency_name','a.currency_id');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}

		$query = 'FROM '.hikashop_table('currency').' AS a';
		if(!empty($filters)){
			$query.= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$database->setQuery('SELECT a.* '.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList('currency_id');
		$currencyClass = hikashop_get('class.currency');
		$currencyClass->getCurrencies(null,$rows);
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'currency_id');
		}
		$database->setQuery('SELECT count(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_currency_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name' => 'confirm','check'=>false, 'msg'=> JText::_('UPDATE_RATES_WARNING'),'icon'=>'upload','alt'=>JText::_('UPDATE_RATES'), 'task' => 'update','display'=>$manage && hikashop_level(2)),
			array('name'=>'addNew','display'=>$manage),
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_currency_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('currency',$currencyClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
	}

	function form(){
		$currency_id = hikashop_getCID('currency_id',false);
		$class = hikashop_get('class.currency');
		if(!empty($currency_id)){
			$element = $class->get($currency_id);
			$task='edit';
		}else{
			$element = JRequest::getVar('fail');
			if(empty($element)){
				$element = new stdClass();
				$element->currency_published=1;
				$element->currency_format='%i';
				$element->currency_rate=1.00000;
				$element->currency_flat_fee=0;
				$element->currency_percent_fee=0;
				$class->checkLocale($element);
			}
			$task='add';
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&currency_id='.$currency_id);

		$this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$this->assignRef('element',$element);
		$signpos = hikashop_get('type.signpos');
		$this->assignRef('signpos',$signpos);

	}

}

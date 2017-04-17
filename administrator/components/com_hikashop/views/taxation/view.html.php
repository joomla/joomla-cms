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

class TaxationViewTaxation extends hikashopView{
	var $ctrl= 'taxation';
	var $nameListing = 'TAXATIONS';
	var $nameForm = 'TAXATION';
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
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.taxation_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );

		$pageInfo->filter->taxation_date_start=$app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.taxation_date_start','taxation_date_start','' ,'string');
		$pageInfo->filter->taxation_date_end=$app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.taxation_date_end','taxation_date_end','' ,'string');
		$pageInfo->filter->tax_namekey=$app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.tax_namekey','tax_namekey','' ,'string');
		$pageInfo->filter->taxation_type=$app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.taxation_type','taxation_type','' ,'string');
		$pageInfo->filter->taxation_site_id=$app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.taxation_site_id','taxation_site_id','' ,'string');
		$database = JFactory::getDBO();

		$filters = array();


		if ( !empty( $pageInfo->filter->taxation_date_start)) {
			$filters[] = '('
				. hikashop_getTime($pageInfo->filter->taxation_date_start).'<=a.taxation_date_start'
				. ' OR '. hikashop_getTime($pageInfo->filter->taxation_date_start).'<=a.taxation_date_end'
				. ' OR a.taxation_date_end=0'
				. ')';
		}
		if ( !empty( $pageInfo->filter->taxation_date_end)) {
			$filters[] = '('
				. 'a.taxation_date_start<='.hikashop_getTime($pageInfo->filter->taxation_date_end)
				. ' OR (a.taxation_date_start<=a.taxation_date_end AND a.taxation_date_end<='.hikashop_getTime($pageInfo->filter->taxation_date_end).')'
				. ' OR a.taxation_date_start=0'
				. ')';
		}

		if(!empty($pageInfo->filter->tax_namekey)){
			$filters[]='a.tax_namekey='.$database->Quote($pageInfo->filter->tax_namekey);
		}
		if(!empty($pageInfo->filter->taxation_type)){
			$filters[]='(a.taxation_type='.$database->Quote($pageInfo->filter->taxation_type).' OR taxation_type LIKE \'%'.hikashop_getEscaped($pageInfo->filter->taxation_type,true).'%\')';
		}
		if(!empty($pageInfo->filter->taxation_site_id) && $pageInfo->filter->taxation_site_id!='[unselected]'){
			$filters[]='a.taxation_site_id='.$database->Quote($pageInfo->filter->taxation_site_id);
		}

		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeTaxationListing', array($this->paramBase, &$this->extrafilters, &$pageInfo, &$filters));

		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}
		$query = ' FROM '.hikashop_table('taxation').' AS a LEFT JOIN '.hikashop_table('tax').' AS b ON a.tax_namekey=b.tax_namekey LEFT JOIN '.hikashop_table('category').' AS c ON a.category_namekey=c.category_namekey AND a.category_namekey!=\'\' AND c.category_type=\'tax\''.$filters.$order;
		$database->setQuery('SELECT b.*,c.*,a.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();

		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		$config =& hikashop_config();
		$this->assignRef('config',$config);

		if($pageInfo->elements->page){
			$zones = array();
			foreach($rows as $row){
				if(empty($row->zone_namekey)) continue;
				$tax_zones = explode(',',$row->zone_namekey);
				foreach($tax_zones as $zone){
					if(!empty($zone)) $zones[] = $database->Quote($zone);
				}
			}
			if(count($zones)){
				$database->setQuery('SELECT * FROM #__hikashop_zone WHERE zone_namekey IN ('.implode(',',$zones).');');
				$zones = $database->loadObjectList('zone_namekey');
				foreach($rows as $k => $row){
					if(empty($row->zone_namekey)) continue;
					$tax_zones = explode(',',$row->zone_namekey);
					$rows[$k]->zone_namekey = array();
					foreach($tax_zones as $zone){
						if(empty($zone)) continue;
						if(isset($zones[$zone])){
							$rows[$k]->zone_namekey[] = $zones[$zone];
						}else{
							$rows[$k]->zone_namekey[] = $zone;
						}
					}
				}
			}
			foreach($rows as $k => $row){
				$restrictions = array();
				if(!empty($row->zone_namekey)){
					$zones = array();
					foreach($row->zone_namekey as $zone){
						if(is_string($zone)){
							$zones[]=$zone;
							continue;
						}
						if(hikashop_isAllowed($config->get('acl_zone_manage','all'))){
							$zones[]= $zone->zone_name_english.'<a href="'.hikashop_completeLink('zone&task=edit&zone_id='.$zone->zone_id).'"><img class="hikashop_go" src="'.HIKASHOP_IMAGES.'go.png" alt="go" /></a>';
						}else{
							$zones[]=$zone->zone_name_english;
						}
					}
					if(count($zones)){
						$restrictions['ZONE'] = implode(', ',$zones);
					}
				}
				if(!empty($row->taxation_post_code)){
					$restrictions['POST_CODE'] = $row->taxation_post_code;
				}
				if(!empty($row->taxation_type)){
					$row->taxation_type = explode(',',strtoupper(trim($row->taxation_type,',')));
					$types = array();
					foreach($row->taxation_type as $type){
						$types[] = JText::_($type);
					}
					$restrictions['CUSTOMER_TYPE'] = implode(', ',$types);
				}
				if(!empty($row->taxation_date_start)){
					$restrictions['START_DATE'] = hikashop_getDate($row->taxation_date_start,'%Y-%m-%d %H:%M');
				}
				if(!empty($row->taxation_end_start)){
					$restrictions['END_DATE'] = hikashop_getDate($row->taxation_end_start,'%Y-%m-%d %H:%M');
				}

				$rows[$k]->restrictions = $restrictions;
			}
		}

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);

		$taxType = hikashop_get('type.tax');
		$this->assignRef('taxType',$taxType);
		$ratesType = hikashop_get('type.rates');
		$this->assignRef('ratesType',$ratesType);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$this->getPagination();


		$manage = hikashop_isAllowed($config->get('acl_taxation_manage','all'));
		$this->assignRef('manage',$manage);

		$this->toolbar = array(

			array('name' => 'link', 'icon'=>'edit','alt'=>JText::_('MANAGE_TAX_CATEGORIES'), 'url' =>hikashop_completeLink('category&filter_id=tax'),'display'=>$manage),
			array('name' => 'link', 'icon'=>'edit','alt'=>JText::_('MANAGE_RATES'), 'url' =>hikashop_completeLink('tax&return=taxation') ,'display'=>$manage),
			array('name'=>'|','display'=>$manage),
			array('name'=>'addNew','display'=>$manage),
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_taxation_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);



		JHTML::_('behavior.modal');
	}
	function form(){
		$taxation_id = hikashop_getCID('taxation_id');
		$class = hikashop_get('class.taxation');
		if(!empty($taxation_id)){
			$element = $class->get($taxation_id);
			$task='edit';
		}else{
			$element = new stdClass();
			$element->banner_url = HIKASHOP_LIVE;
			$task='add';
		}

		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&taxation_id='.$taxation_id);

		$this->toolbar = array(
			'save',
			array('name' => 'save2new', 'display' => version_compare(JVERSION,'1.7','>=')),
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);


		$this->assignRef('element',$element);

		$taxType = hikashop_get('type.tax');
		$this->assignRef('taxType',$taxType);
		$ratesType = hikashop_get('type.rates');
		$this->assignRef('ratesType',$ratesType);
		$category = hikashop_get('type.categorysub');
		$category->type = 'tax';
		$category->field = 'category_namekey';
		$this->assignRef('category',$category);
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);
		$nameboxType = hikashop_get('type.namebox');
		$this->assignRef('nameboxType', $nameboxType);
	}
}

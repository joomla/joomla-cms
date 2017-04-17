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
class VoteViewVote extends hikashopView{
	var $ctrl= 'vote';
	var $nameListing = 'VOTE';
	var $nameForm = 'VOTE';
	var $icon = 'vote';

	function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();
		parent::display($tpl);
	}

	function isEnabled() {
		$config = hikashop_config();
		switch($config->get('enable_status_vote', 0)) {
			case 'two':
			case 'both':
				return 3;
			case 'vote':
				return 1;
			case 'comment':
				return 2;
		}
		return 0;
	}

	function listing() {
		$config = hikashop_config();
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.vote_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$filters = array();
		$searchMap = array('a.vote_id','a.vote_rating','a.vote_ref_id','a.vote_pseudo','a.vote_comment','a.vote_email','a.vote_user_id','a.vote_ip','a.vote_date');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(JString::strtolower(trim($pageInfo->search)),true).'%\'';
			$filters[] =  implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE '. implode(' AND ',$filters);
		}else{
			$filters = '';
		}
		$query = ' FROM '.hikashop_table('vote').' AS a '.$filters.$order;
		$database->setQuery('SELECT a.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($rows)){
			foreach($rows as $k => $v){
				if(function_exists('mb_substr')){
					$rows[$k]->vote_comment_short=mb_substr($v->vote_comment,0,51);
				}else{
					$rows[$k]->vote_comment_short=substr($v->vote_comment,0,51);
				}
				if($rows[$k]->vote_type == 'vendor'){
					$query2='SELECT vendor_name FROM `#__hikamarket_vendor` WHERE vendor_id = '.(int)$rows[$k]->vote_ref_id.'';
				}else{
					$query2='SELECT product_name FROM `#__hikashop_product` WHERE product_id = '.(int)$rows[$k]->vote_ref_id.'';
				}
				$database->setQuery($query2);
				$rows[$k]->item_name  = $database->loadResult();

				if($rows[$k]->vote_pseudo == '0'){
					$userClass = hikashop_get('class.user');
					$userInfos = $userClass->get($rows[$k]->vote_user_id);
					if(!empty($userInfos)){
						$rows[$k]->username	= $userInfos->username;
						$rows[$k]->email	= $userInfos->email;
					}
				}
			}
		}
		$pageInfo->enabled = $this->isEnabled();
		$pageInfo->manageProduct = hikashop_isAllowed($config->get('acl_product_manage','all'));
		$pageInfo->manageUser = hikashop_isAllowed($config->get('acl_user_manage','all'));
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'vote_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
		$order = new stdClass();
		$order->ordering = true;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'a.vote_ordering'){
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}
		$this->assignRef('order',$order);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_vote_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name'=>'addNew','display'=>$manage),
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_vote_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

	}

	function form() {
		$vote_id = hikashop_getCID('currency_id',false);
		$item = new stdClass();
		$database	= JFactory::getDBO();

		if(!empty($vote_id)){
			$query = 'SELECT * FROM `#__hikashop_vote` WHERE vote_id = '.(int)$vote_id.'';
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			if(!empty($rows)){
				foreach($rows as $k => $v){
					$query2='SELECT product_name FROM `#__hikashop_product` WHERE product_id  = '.(int)$rows[$k]->vote_ref_id.'';
					$database->setQuery($query2);
					$product_names = $database->loadObjectList();
					foreach($product_names as $product_name){
						$rows[$k]->product_name = $product_name->product_name;
					}
					if($rows[$k]->vote_pseudo == '0'){
						$userClass = hikashop_get('class.user');
						$userInfos = $userClass->get($rows[$k]->vote_user_id);
						if(!empty($userInfos)){
							$rows[$k]->username	= $userInfos->username;
							$rows[$k]->email	= $userInfos->email;
						}
						else{
							$rows[$k]->username	= '';
							$rows[$k]->email	= '';
						}
					}
				}
			}
			$item->newItem = false;
		}
		else{
			$item->newItem = true;
		}
		$item->enabled = $this->isEnabled();
		$this->assignRef('rows',$rows);
		$this->assignRef('item',$item);

		$vote_id = hikashop_getCID('vote_id');
		$class = hikashop_get('class.vote');
		if(!empty($vote_id)){
			$element = $class->get($vote_id,true);
			$task='edit';
		}else{
			$element = new stdClass();
			$element->vote_url = HIKASHOP_LIVE;
			$element->vote_published = 1;
			$task='add';
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&vote_id='.$vote_id);
		$this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing')
		);

		$this->assignRef('element',$element);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_vote',@$element->vote_id,$element);
			$config =& hikashop_config();
			$multilang_display=$config->get('multilang_display','tabs');
			if($multilang_display=='popups') $multilang_display = 'tabs';
			$tabs = hikashop_get('helper.tabs');
			$this->assignRef('tabs',$tabs);
			$this->assignRef('transHelper',$transHelper);
		}
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$this->assignRef('translation',$translation);
		$nameboxType = hikashop_get('type.namebox');
		$this->assignRef('nameboxType', $nameboxType);
		$voteType = hikashop_get('type.vote');
		$this->assignRef('voteType',$voteType);
	}
}

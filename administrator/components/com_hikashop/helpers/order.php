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
class hikashopOrderHelper {
	var $table = '';
	var $pkey = '';
	var $groupMap = '';
	var $groupVal = '';
	var $orderingMap = '';

	function order($down = true, $useCID = true) {
		$database = JFactory::getDBO();

		if($down){
			$sign = '>';
			$dir = 'ASC';
		}else{
			$sign = '<';
			$dir = 'DESC';
		}
		$orders = JRequest::getVar( 'order', array(), '', 'array' );
		if($useCID) {
			$ids = JRequest::getVar( 'cid', array(), '', 'array' );
		} else {
			$ids = array_keys($orders);
		}
		$orderingMap = $this->orderingMap;
		$id = (int) $ids[0];
		$pkey = $this->pkey;
		if(!empty($this->main_pkey)){
			$main = $this->main_pkey;
		}else{
			$main = $pkey;
		}

		$query = 'SELECT a.'.$orderingMap.',a.'.$pkey.' FROM '.hikashop_table($this->table).' as b, '.hikashop_table($this->table).' as a';
		$query .= ' WHERE a.'.$orderingMap.' '.$sign.' b.'.$orderingMap.' AND b.'.$main.' = '.$id.$this->group(false,'a').$this->group(false,'b');
		$query .= ' ORDER BY a.'.$orderingMap.' '.$dir.' LIMIT 1';
		$database->setQuery($query);
		$secondElement = $database->loadObject();
		if(empty($secondElement)) return false;

		$firstElement = new stdClass();
		if($main==$pkey){
			$firstElement->$pkey = $id;
		}else{
			$database->setQuery('SELECT '.$pkey.' FROM '.hikashop_table($this->table).' WHERE '.$main.' = '.$id.$this->group(false));
			$firstElement->$pkey = (int)$database->loadResult();
		}
		$firstElement->$orderingMap = $secondElement->$orderingMap;
		if($down)$secondElement->$orderingMap--;
		else $secondElement->$orderingMap++;

		$status1 = $database->updateObject(hikashop_table($this->table),$firstElement,$pkey);
		$status2 = $database->updateObject(hikashop_table($this->table),$secondElement,$pkey);
		$status = $status1 && $status2;
		if($status){
			if (!HIKASHOP_PHP5) {
				$app =& JFactory::getApplication();
			}else{
				$app = JFactory::getApplication();
			}
			$app->enqueueMessage(JText::_( 'NEW_ORDERING_SAVED' ), 'message');
		}
		return $status;
	}

	function save($useCID = true) {
		$app = JFactory::getApplication();
		$pkey = $this->pkey;
		if(!empty($this->main_pkey)){
			$main = $this->main_pkey;
		}else{
			$main = $pkey;
		}
		$orderingMap = $this->orderingMap;

		$order = JRequest::getVar('order', array(), 'post', 'array');
		if($useCID) {
			$cid = JRequest::getVar('cid', array(), 'post', 'array');
			JArrayHelper::toInteger($cid);
		} else {
			$cid = array_keys($order);
		}

		if(empty($cid)) {
			$app->enqueueMessage(JText::_('ERROR_ORDERING'), 'error');
			return false;
		}

		$database = JFactory::getDBO();
		if(!empty($this->groupMap)){
			$query = 'SELECT `'.$main.'` FROM '.hikashop_table($this->table).' WHERE `'.$main.'` IN ('.implode(',',$cid).') '. $this->group();
			$database->setQuery($query);
			if(!HIKASHOP_J25){
				$results = $database->loadResultArray();
			} else {
				$results = $database->loadColumn();
			}

			$newcid = array();
			$neworder=array();
			foreach($cid as $key => $val){
				if(in_array($val,$results)){
					$newcid[] = $val;
					if($useCID) {
						$neworder[] = $order[$key];
					} else {
						$neworder[] = $order[$val];
					}
				}
			}

			$cid = $newcid;
			$order = $neworder;
			if($main!=$pkey){
				$query = 'SELECT `'.$main.'`,`'.$pkey.'` FROM '.hikashop_table($this->table).' WHERE `'.$main.'` IN ('.implode(',',$cid).') '. $this->group();
				$database->setQuery($query);
				$results = $database->loadObjectList($main);
				$newcid=array();
				foreach($cid as $id){
					$newcid[] = $results[$id]->$pkey;
				}
				$cid = $newcid;
			}
		}
		if(empty($cid)) {
			$app->enqueueMessage(JText::_( 'ERROR_ORDERING' ), 'error');
			return false;
		}
		$query = 'SELECT `'.$orderingMap.'`,`'.$pkey.'` FROM '.hikashop_table($this->table).' WHERE `'.$pkey.'` NOT IN ('.implode(',',$cid).') ' . $this->group();
		$query .= ' ORDER BY `'.$orderingMap.'` ASC';
		$database->setQuery($query);
		$results = $database->loadObjectList($pkey);
		$oldResults = $results;
		asort($order);
		$newOrder = array();
		while(!empty($order) || !empty($results)){
			$dbElement = reset($results);
			if(!empty($order) && empty($dbElement->$orderingMap) || (!empty($order) && reset($order) <= $dbElement->$orderingMap)){
				$newOrder[] = $cid[(int)key($order)];
				unset($order[key($order)]);
			}else{
				$newOrder[] = $dbElement->$pkey;
				unset($results[$dbElement->$pkey]);
			}
		}
		$i = 1;
		$status = true;
		$element = new stdClass();
		foreach($newOrder as $val){
			$element->$pkey = $val;
			$element->$orderingMap = $i;
			if(!isset($oldResults[$val]) || $oldResults[$val]->$orderingMap != $i){
				$status = $database->updateObject(hikashop_table($this->table),$element,$pkey) && $status;
			}
			$i++;
		}
		if($status){
			$app->enqueueMessage(JText::_( 'NEW_ORDERING_SAVED' ), 'message');
		}else{
			$app->enqueueMessage(JText::_( 'ERROR_ORDERING' ), 'error');
		}
		return $status;
	}

	function reOrder() {
		$db = JFactory::getDBO();
		$orderingMap = $this->orderingMap;
		$query = 'SELECT MAX(`'.$orderingMap.'`) FROM '.hikashop_table($this->table) . $this->group(true);
		$db->setQuery($query);
		$max = $db->loadResult();
		$max++;
		$query = 'UPDATE '.hikashop_table($this->table).' SET `'.$orderingMap.'` ='.$max.' WHERE `'.$orderingMap.'`=0' . $this->group();
		$db->setQuery($query);
		$db->query();
		$query = 'SELECT `'.$orderingMap.'`,`'.$this->pkey.'` FROM '.hikashop_table($this->table) . $this->group(true);
		$query .= ' ORDER BY `'.$orderingMap.'` ASC';
		$db->setQuery($query);
		$results = $db->loadObjectList();
		$i = 1;
		if(!empty($results)){
			foreach($results as $oneResult){
				if($oneResult->$orderingMap != $i){
					$oneResult->$orderingMap = $i;
					$db->updateObject( hikashop_table($this->table), $oneResult, $this->pkey);
				}
				$i++;
			}
		}
	}

	function group($addWhere = false,$table = '') {
		if(!empty($this->groupMap)){
			$db = JFactory::getDBO();
			if(is_array($this->groupMap)){
				$groups = array();
				foreach($this->groupMap as $k => $group){
					if(!empty($table)){
						$group = $table.'.'.$group;
					}
					$groups[]= $group.' = '.$db->Quote($this->groupVal[$k]);
				}
				$groups = ' ' . implode(' AND ',$groups);
			}else{
				$groups = ' ' .(!empty($table)?$table.'.':''). $this->groupMap.' = '.$db->Quote($this->groupVal);
			}
			if($addWhere){
				$groups = ' WHERE'.$groups;
			}else{
				$groups = ' AND'.$groups;
			}
		}else{
			$groups='';
		}
		return $groups;
	}
}

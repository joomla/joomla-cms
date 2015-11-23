<?php
/**
 * @version		$Id: queue.php 2013-07-29 11:37:09Z maverick $
 * @package		CoreJoomla.Cjlib
 * @subpackage	Components.models
 * @copyright	Copyright (C) 2009 - 2012 corejoomla.com, Inc. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport('joomla.application.component.modellist');

class CjLibModelQueue extends JModelList {

	public function __construct($config = array()){
	
		if (empty($config['filter_fields'])) {
				
			$config['filter_fields'] = array(
					'id', 'q.id', 'm.id',
					'asset_name', 'm.asset_name',
					'asset_id', 'm.asset_id',
					'subject', 'm.subject',
					'status', 'q.status',
					'to_addr', 'q.to_addr',
					'message_id', 'q.message_id'
			);
		}
	
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null) {
	
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
	
		if ($layout = $app->input->get('layout')) {
				
			$this->context .= '.'.$layout;
		}
	
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
	
		$authorId = $app->getUserStateFromRequest($this->context.'.filter.toaddr', 'filter_toaddr');
		$this->setState('filter.toaddr', $authorId);

		$messageid = $this->getUserStateFromRequest($this->context.'.filter.messageid', 'filter_messageid', '');
		$this->setState('filter.messageid', $messageid);

		$status = $this->getUserStateFromRequest($this->context.'.filter.status', 'filter_status', '');
		$this->setState('filter.status', $status);
		
		$categoryId = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_asset');
		$this->setState('filter.asset', $categoryId);
	
		// List state information.
		parent::populateState('q.created', 'desc');
	}
	
	protected function getStoreId($id = ''){
	
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.toaddr');
		$id	.= ':'.$this->getState('filter.messageid');
		$id	.= ':'.$this->getState('filter.status');
		$id	.= ':'.$this->getState('filter.asset');
	
		return parent::getStoreId($id);
	}
	
	protected function _buildQuery(){
	
		$db = JFactory::getDbo();
		$query = $db->getQuery(TRUE);
	
		$query->select('q.id, q.to_addr, q.status, q.html, q.processed, q.created');
		$query->from('#__corejoomla_messagequeue as q');
			
		$query->select('m.id as message_id, m.asset_id, m.asset_name, m.subject');
		$query->join('inner', '#__corejoomla_messages as m on m.id = q.message_id');
	
		return $query;
	}
	
	protected function _buildWhere(&$query) {
	
		$toaddr = $this->getState('filter.toaddr');
		if(!empty($toaddr)){
				
			$query->where('q.to_addr = ' . (int) $toaddr);
		}
	
		$messageid = $this->getState('filter.messageid');
		if (is_numeric($messageid)) {
				
			$query->where('q.message_id IN = '. (int) $messageid);
		}
		
		$status = $this->getState('filter.status');
		if(is_numeric($status) && $status >= 0){
	
			$query->where('q.status = '. (int) $status);
		}
	
		$search = $this->getState('filter.search');
		if (!empty($search)) {
				
			if (stripos($search, 'id:') === 0) {
	
				$query->where('m.id = '.(int) substr($search, 3));
			} elseif (stripos($search, 'asset:') === 0) {
	
				$search = $db->Quote('%'.$db->escape(substr($search, 6), true).'%');
				$query->where('(m.asset_name LIKE '.$search.')');
			} else {
	
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(m.sbject LIKE '.$search.')');
			}
		}
	}
	
	protected function getListQuery() {
	
		$db = JFactory::getDbo();
	
		$orderCol	= $this->state->get('list.ordering', 'q.created');
		$orderDirn	= $this->state->get('list.direction', 'desc');
		
		$query = $this->_buildQuery();
		$this->_buildWhere($query);
		$query->order($db->escape($orderCol.' '.$orderDirn));
	
		return $query;
	}
	
	public function delete_queue($cids){

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		JArrayHelper::toInteger($cids);
		
		$query->delete('#__corejoomla_messagequeue')->where('id in ('.implode(',', $cids).')');
		$db->setQuery($query);
		
		try{
			
			$db->execute();
		} catch(Exception $e){
			return false;
		}
		
		return true;
	}
	
	public function process_queue($cids){
		
		JArrayHelper::toInteger($cids);
		
		if(CJFunctions::send_messages_from_queue(count($cids), 0, false, $cids)){
			
			return true;
		}
		
		return false;
	}
}
?>
<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

// import Joomla table library
jimport('joomla.database.table');

class SppagebuilderTablePage extends JTable {

	function __construct(&$db) {
		parent::__construct('#__sppagebuilder', 'id', $db);
	}

	public function bind($array, $ignore = ''){
		if (isset($array['id'])) {
			$date = JFactory::getDate();
			$user = JFactory::getUser();
			if ($array['id']) {
				$array['modified'] = $date->toSql();
				$array['modified_by'] = $user->get('id');
			}else{
				if (!(int) $array['created_on']) {
					$array['created_on'] = $date->toSql();
				}
				if (empty($array['created_by'])) {
					$array['created_by'] = $user->get('id');
				}
			}
		}
		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules'])) {
			$rules = new JRules($array['rules']);
			$this->setRules($rules);
		}

		if($this->_db->getServerType() == 'postgresql'){
			if(empty($array['id'])){
				unset($array['id']);
			}
			if(empty($array['hits'])){
				unset($array['hits']);
			}
		}
		
		return parent::bind($array, $ignore);
	}
	protected function _getAssetTitle(){
		return $this->title;
	}
	/**
	 * Redefined asset name, as we support action control
	 */
  protected function _getAssetName() {
		$k = $this->_tbl_key;
		return 'com_sppagebuilder.page.'.(int) $this->$k;
  }
	
  /**
   * We provide our global ACL as parent
	 * @see JTable::_getAssetParentId()
   */
	protected function _getAssetParentId(JTable $table = NULL, $id = NULL){
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_sppagebuilder');
		return $asset->id;
	}
}

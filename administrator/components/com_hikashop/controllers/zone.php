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
class ZoneController extends hikashopController{
	var $type='zone';
	var $toggle = array('zone_published'=>'zone_id');
	var $modify = array('apply','save','save2new','store','orderdown','orderup','saveorder','savechild','toggle','copy');
	function __construct($config = array()){
		parent::__construct($config);
		$this->modify_views[] = 'addchild';
		$this->modify_views[] = 'unpublish';
		$this->modify_views[] = 'publish';
		$this->modify_views[] = 'selectchildlisting';
		$this->display[] = 'addchild';
		$this->display[] = 'getTree';
	}

	function copy(){
		$zones = JRequest::getVar( 'cid', array(), '', 'array' );
		$result = true;
		if(!empty($zones)){
			$zoneClass = hikashop_get('class.zone');
			foreach($zones as $zone){
				$data = $zoneClass->get($zone);
				if($data){
					$childs = $zoneClass->getChildren($data->zone_id);
					unset($data->zone_id);
					unset($data->zone_namekey);
					if(!$zoneClass->save($data)){
						$result=false;
					}elseif(!empty($childs)){
						$childNamekeys = array();
						foreach($childs as $child){
							$childNamekeys[]=$child->zone_namekey;
						}
						$zoneClass->addChilds($data->zone_namekey,$childNamekeys);
					}

				}
			}
		}
		if($result){
			$app = JFactory::getApplication();
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
			else
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));
		}
		return $this->listing();
	}

	function savechild(){
		$new_id = $this->store();
		$main_id = JRequest::getInt('main_id');
		if($main_id && $new_id){
			$zoneObject = hikashop_get('class.zone');
			$insertedNamekeys = $zoneObject->addChilds($main_id,array($new_id));
			JRequest::setVar('cid',$new_id);
			JRequest::setVar( 'layout', 'savechild'  );
			return parent::display();
		}else{
			$this->selectchildlisting();
		}
	}

	function selectchildlisting(){
		JRequest::setVar( 'task', 'selectchildlisting'  );
		JRequest::setVar( 'layout', 'selectchildlisting'  );
		return parent::display();
	}

	function addchild(){
		$type=JRequest::getWord('type');
		if(!in_array($type,array('discount','shipping','payment','config','tax'))){
			$childNamekeys = JRequest::getVar( 'cid', array(), '', 'array' );
			$mainNamekey = JRequest::getVar( 'main_id', 0, '', 'int' );
			$zoneObject = hikashop_get('class.zone');
			$insertedNamekeys = $zoneObject->addChilds($mainNamekey,$childNamekeys);
			JRequest::setVar( 'cid', $insertedNamekeys );
			JRequest::setVar( 'layout', 'newchild'  );
		}else{
			JRequest::setVar( 'layout', 'addchild'  );
		}
		return parent::display();
	}

	function newchild(){
		JRequest::setVar( 'layout', 'newchildform'  );
		return parent::display();
	}

	function getTree() {
		$zone_key = JRequest::getVar('zone_key', null);
		$displayFormat = JRequest::getVar('displayFormat', '');
		$search = JRequest::getVar('search', null);

		$nameboxType = hikashop_get('type.namebox');
		$options = array(
			'zone_key' => $zone_key,
			'displayFormat' => $displayFormat
		);

		$return_zonetype = JRequest::getVar('return_zonetype', null);
		if(!empty($return_zonetype))
			$options['type'] = $return_zonetype;

		$ret = $nameboxType->getValues($search, 'zone', $options);
		if(!empty($ret)) {
			echo json_encode($ret);
			exit;
		}
		echo '[]';
		exit;
	}
}

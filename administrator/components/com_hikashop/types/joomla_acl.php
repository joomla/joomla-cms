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
class hikashopJoomla_aclType {

	protected $groups = null;

	protected function load() {
		$this->groups = array();
		$db = JFactory::getDBO();
		if(version_compare(JVERSION,'1.6.0','<')) {

			$db->setQuery('SELECT a.* FROM `#__core_acl_aro_groups` AS a WHERE a.value = \'USERS\'');
			$userRoot = $db->loadObject();

			$db->setQuery('SELECT a.* FROM `#__core_acl_aro_groups` AS a WHERE a.lft > ' . (int)$userRoot->lft . ' AND a.lft < ' . (int)$userRoot->rgt . ' ORDER BY a.lft ASC');
			$groups = $db->loadObjectList('id');
			foreach($groups as &$group){
				if(isset($groups[$group->parent_id])){
					$group->level = intval(@$groups[$group->parent_id]->level) + 1;
				} else {
					$group->level = 0;
				}
				$group->text = JText::_( $group->name );
			}
			unset($group);
			foreach($groups as &$group) {
				$this->groups[] = $group;
			}
		} else {
			$db->setQuery('SELECT a.*, a.title as text, a.id as value FROM `#__usergroups` AS a ORDER BY a.lft ASC');
			$groups = $db->loadObjectList('id');
			foreach($groups as &$group){
				if(isset($groups[$group->parent_id])){
					$group->level = intval(@$groups[$group->parent_id]->level) + 1;
				} else {
					$group->level = 0;
				}
			}
			unset($group);
			foreach($groups as &$group) {
				$this->groups[] = $group;
			}
		}
	}

	public function getList() {
		if(empty($this->groups)) {
			$this->load();
		}
		return $this->groups;
	}

	public function display($map, $values, $allBtn = false, $min = false) {
		hikashop_loadJslib('otree');
		if(empty($this->groups)) {
			$this->load();
		}
		$map = str_replace('"','',$map);
		$id = str_replace(array('[',']',' '),array('_','','_'),$map);
		$cpt = count($this->groups)-1;

		$ret = '<div id="'.$id.'_otree" class="oTree"></div><input type="hidden" value="'.$values.'" name="'.$map.'" id="'.$id.'"/>
<script type="text/javascript">
var data_'.$id.' = ' . $this->getData($values, $allBtn, $min) . ';
'.$id.' = new window.oTree("'.$id.'",{rootImg:"'.HIKASHOP_IMAGES.'otree/", showLoading:false, useSelection:false, checkbox:true},null,data_'.$id.',true);
'.$id.'.callbackCheck = function(treeObj, id, value) {
	var node = treeObj.get(id), d = document, e = d.getElementById("'.$id.'");
	if(node.state == 5) {
		if(value === true) {
			treeObj.chks("*",false);
			e.value = "all";
		} else if(value === false) {
			treeObj.chks(false,false,true);
			e.value = "none";
		}
	} else {
		var v = treeObj.getChk();
		node = treeObj.get(0);
		if(v === false || v.length == 0) {
			e.value = "none";
			treeObj.chk(1,0,false,false);
		} else if( v.length > '.$cpt.') {
			e.value = "all";
			treeObj.chk(1,1,false,false);
		} else {
			e.value = "," + v.join(",") + ",";
			treeObj.config.tricheckbox = true;
			treeObj.chk(1,2,false,false);
			treeObj.config.tricheckbox = false;
		}
	}
};
</script>';
		return $ret;
	}

	public function displayButton($map, $values) {
		hikashop_loadJslib('otree');
		hikashop_loadJslib('jquery');
		$ret = '';
		if(empty($this->groups)) {
			$this->load();
		}
		$map = str_replace('"','',$map);

		if(empty($this->id)) {
			$this->id = 'hikamarket_joomlaacl';
			$cpt = count($this->groups)-1;

			$ret .= '<script type="text/javascript">
if(!window.aclMgr) window.aclMgr = {};
if(!window.aclMgr.trees) window.aclMgr.trees = {};
if(!window.aclMgr.data) window.aclMgr.data = {};
if(!window.aclMgr.popups) window.aclMgr.popups = {};
if(!window.aclMgr.cpt) window.aclMgr.cpt = {};
window.aclMgr.data["'.$this->id.'"] = ' . $this->getData($values) . ';
window.aclMgr.cpt["'.$this->id.'"] = ' . $cpt . ';
window.aclMgr.updateJoomlaAcl = function(el,id,tree_id) {
	var d = document, w = window, tree = d.getElementById(tree_id + "_otree"), e = d.getElementById(id), values = e.value;
	if(w.aclMgr.popups[id] && tree) {
		tree.style.display = "none";
		w.Oby.removeEvent(document, "click", w.aclMgr.popups[id]);
		w.aclMgr.popups[id] = false;
		return false;
	}
	if(!tree) {
		tree = d.createElement("div");
		tree.id = tree_id + "_otree";
		tree.style.position = "absolute";
		tree.style.display = "none";
		tree.className = "oTree acl-popup-content";
		d.body.appendChild(tree);
		w.aclMgr.trees[tree_id] = new w.oTree(tree_id,{rootImg:"'.HIKASHOP_IMAGES.'otree/", showLoading:false, useSelection:false, checkbox:true},null,w.aclMgr.data[tree_id],true);
	}
	switch(values) {
		case "all":
			treevalues = "*";
			break;
		case "none":
			treevalues = "";
			break;
		default:
			treevalues = values.split(",");
			break;
	}
	w.aclMgr.trees[tree_id].callbackCheck = null;
	w.aclMgr.trees[tree_id].chks(treevalues, null, false);
	var p = jQuery(el).offset();
	if(tree.style.display != "none" && tree.style.top != ((p.top + el.offsetHeight) + "px")) {
		setTimeout(function(){
			w.aclMgr.updateJoomlaAcl(el,id,tree_id);
		}, 100);
		return false;
	}
	tree.style.top = (p.top + el.offsetHeight + 5) + "px";
	tree.style.left = (p.left + el.offsetWidth - 200) + "px";

	var f = function(evt) {
		if (!evt) var evt = window.event;
		var trg = (window.event) ? evt.srcElement : evt.target;
		while(trg != null) {
			if(trg == tree || trg == el)
				return;
			trg = trg.parentNode;
		}
		tree.style.display = "none";
		w.Oby.removeEvent(document, "click", f);
		w.aclMgr.popups[id] = false;
	};
	w.Oby.addEvent(document, "click", f);
	w.aclMgr.popups[id] = f;

	w.aclMgr.trees[tree_id].callbackCheck = function(treeObj, id, value) {
		var v = treeObj.getChk();
		if(v === false || v.length == 0) {
			e.value = "none";
		} else if( v.length > w.aclMgr.cpt[tree_id]) {
			e.value = "all";
		} else {
			e.value = "," + v.join(",") + ",";
		}
	};

	tree.style.display = "";
	return false;
};
</script>';
		}

		$id = str_replace(array('[',']'),array('_',''),$map);

		$ret .= '<a href="#" onclick="return window.aclMgr.updateJoomlaAcl(this, \''.$id.'\', \''.$this->id.'\');">'.
			'<img src="'.HIKASHOP_IMAGES.'icons/icon-16-levels.png" title="'.JText::_('ACCESS_LEVEL').'" />'.
			'</a><input type="hidden" id="'.$id.'" name="'.$map.'" value="'.$values.'" />';

		return $ret;
	}

	public function displayList($map, $value, $empty = 'HIKA_ALL') {
		$ret = '';
		if(empty($this->groups))
			$this->load();
		$values = array(
			JHTML::_('select.option', '', JText::_($empty))
		);
		foreach($this->groups as $group) {
			$name = str_repeat('- ', $group->level) . $group->text;
			$values[] = JHTML::_('select.option', $group->value, $name);
		}
		return JHTML::_('select.genericlist', $values, $map, 'class="inputbox" size="1"', 'value', 'text', $value);
	}

	public function getChildrenList() {
		if(empty($this->groups))
			$this->load();

		$ret = array();
		$level = 0;
		foreach($this->groups as $group) {
			$ret[ $group->id ] = array(
				'parent' => (int)$group->parent_id,
				'children' => array(),
				'level' => $group->level
			);
			$level = ($level > $group->level) ? $level : $group->level;
		}
		for($k = $level; $k >= 0; $k--) {
			foreach($ret as $i => $group) {
				if($group['level'] == $k && !empty($group['parent'])) {
					$ret[ $group['parent'] ]['children'][] = $i;
					$ret[ $group['parent'] ]['children'] = array_merge($ret[ $group['parent'] ]['children'], $group['children']);
				}
			}
		}
		return $ret;
	}

	public function getParentList() {
		if(empty($this->groups))
			$this->load();

		$ret = array();
		$level = 0;
		foreach($this->groups as $group) {
			$ret[ $group->id ] = array(
				'parent' => (int)$group->parent_id,
				'parents' => array((int)$group->parent_id),
				'level' => $group->level
			);
			$level = ($level > $group->level) ? $level : $group->level;
		}
		for($k = 1; $k <= $level; $k++) {
			foreach($ret as $i => $group) {
				if($group['level'] == $k && !empty($group['parent']))
					$ret[ $i ]['parents'] = array_merge($ret[ $group['parent'] ]['parents'], $ret[ $i ]['parents']);
			}
		}
		return $ret;
	}

	private function getData($values, $allBtn = false, $min = false) {
		$cpt = count($this->groups)-1;
		$sep = '';
		$ret = '[';
		$rootDepth = 0;
		$arrValues = explode(',', $values);

		if($allBtn) {
			$ret .= '{"status":5,"name":"'.JText::_('HIKA_ALL').'","icon":"folder","value":""';
			if($values == 'all')
				$ret .= ',"checked":true';
			$ret .= '}';
			$sep = ',';
		}

		foreach($this->groups as $k => $group) {
			$next = null;
			if($k < $cpt)
				$next = $this->groups[$k+1];

			$status = 4;
			if(!empty($next) && $next->level > $group->level)
				$status = 2;

			if($min == true && $k == 0)
				$status = 3;

			$ret .= $sep.'{"status":'.$status.',"name":"'.str_replace('"','&quot;',$group->text).'"';
			$ret .= ',"value":'.$group->id;

			if($values == 'all' || in_array($group->id, $arrValues)) {
				$ret .= ',"checked":true';
			}

			$sep = '';
			if(!empty($next)) {
				if($next->level > $group->level) {
					$ret .= ',"data":[';
				} else if($next->level < $group->level) {
					$ret .= '}'.str_repeat(']}', $group->level - $next->level);
					$sep = ',';
				} else {
					$ret .= '}';
					$sep = ',';
				}
			} else {
				$ret .= '}'.str_repeat(']}', $group->level - $rootDepth);
			}
		}
		$ret .= ']';
		return $ret;
	}
}

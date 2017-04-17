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
class hikashopCategorysubType {
	var $type='tax';
	var $value='';
	var $multiple = false;

	function load($form = true) {
		static $data = array();
		if(!isset($data[$this->type])){
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE  category_parent_id=0 LIMIT 1';
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$parent = (int)$db->loadResult();
			$select = 'SELECT a.category_name,a.category_id,a.category_namekey';
			$table = ' FROM '.hikashop_table('category') . ' AS a';
			$app = JFactory::getApplication();
			$translationHelper = hikashop_get('helper.translation');

			if($app->isAdmin() && $translationHelper->isMulti()){
				$user = JFactory::getUser();
				$locale = $user->getParam('language');
				if(empty($locale)){
					$config = JFactory::getConfig();
					if(HIKASHOP_J30){
						$locale = $config->get('language');
					}else{
						$locale = $config->getValue('config.language');
					}
				}
				$lgid = $translationHelper->getId($locale);
				$select .= ',b.value';
				$trans_table = 'jf_content';
				if($translationHelper->falang){
					$trans_table = 'falang_content';
				}
				$table .=' LEFT JOIN '.hikashop_table($trans_table,false).' AS b ON a.category_id=b.reference_id AND b.reference_table=\'hikashop_category\' AND b.reference_field=\'category_name\' AND b.published=1 AND language_id='.$lgid;
			}

			static $multiTranslation = null;
			$app = JFactory::getApplication();
			if($multiTranslation === null && !$app->isAdmin()) {
				$translationHelper = hikashop_get('helper.translation');
				$multiTranslation = $translationHelper->isMulti(true);
			}

			$filters = array(
				'a.category_type = \''.$this->type.'\'',
				'a.category_parent_id != '.$parent
			);
			if($this->type == 'tax')
				$filters[] = 'a.category_published = 1';

			$query = $select.$table.' WHERE ('.implode(') AND (',$filters).') ORDER BY a.category_ordering ASC';
			$db->setQuery($query);
			if(!$app->isAdmin() && $multiTranslation && class_exists('JFalangDatabase')){
				$this->categories = $db->loadObjectList('','stdClass',false);
			}elseif(!$app->isAdmin() && $multiTranslation && (class_exists('JFDatabase')||class_exists('JDatabaseMySQLx'))){
				if(HIKASHOP_J25){
					$this->categories = $db->loadObjectList('','stdClass',false);
				}else{
					$this->categories = $db->loadObjectList('',false);
				}
			}else{
				$this->categories = $db->loadObjectList();
			}
			$data[$this->type] =& $this->categories;
		} else {
			$this->categories =& $data[$this->type];
		}

		$this->values = array();
		if($form) {
			if(in_array($this->type,array('status','tax'))){
				$this->values[] = JHTML::_('select.option', '', JText::_('HIKA_NONE') );
			}else{
				$this->values[] = JHTML::_('select.option', 0, JText::_('HIKA_NONE') );
			}
		} else {
			if($this->type=='status'){
				$this->values[] = JHTML::_('select.option', '', JText::_('ALL_STATUSES') );
			}else{
				$this->values[] = JHTML::_('select.option', 0, JText::_('ALL_'.strtoupper($this->type)) );
			}
		}

		if(!empty($this->categories)) {
			foreach($this->categories as $k => $category) {
				if(empty($category->value)){
					$val = str_replace(' ','_',strtoupper($category->category_name));
					$category->value = JText::_($val);
					if($val==$category->value){
						$category->value = $category->category_name;
					}
					$this->categories[$k]->value = $category->value;
				}

				if($this->type=='status'){
					$this->values[] = JHTML::_('select.option', $category->category_name, $category->value );
				}elseif($this->type=='tax'){
					$field = $this->field;
					$this->values[] = JHTML::_('select.option', $category->$field, $category->value );
				}else{
					$this->values[] = JHTML::_('select.option', (int)$category->category_id, $category->value );
				}
			}
		}
	}

	function trans($status) {
		foreach($this->categories as $value){
			if($value->category_name == $status){
				if($this->type == 'status' && $value->value == $status)
					return hikashop_orderStatus($status);
				return $value->value;
			}
		}
		foreach($this->categories as $value){
			if($value->category_namekey == $status){
				if($this->type == 'status' && $value->value == $status)
					return hikashop_orderStatus($status);
				return $value->value;
			}
		}
		if($this->type == 'status')
			return hikashop_orderStatus($status);
		return $status;
	}

	function get($val) {
		foreach($this->values as $value){
			if($value->value == $val) {
				return $value->text;
			}
		}
		return $val;
	}

	function display($map, $value, $form = true, $none = true, $id = '') {
		$this->value = $value;
		if(!is_bool($form)) {
			$attribute = ' '.$form;
			$form = $none;
		} elseif(!$form) {
			$attribute = ' onchange="document.adminForm.submit();"';
		} else {
			$attribute = '';
		}
		$this->load($form);

		if(!in_array($this->type,array('status','tax'))) {
			$value = (int)$value;
		}
		if(strpos($attribute,'size="') === false) {
			if($this->multiple && !HIKASHOP_J30) {
				$attribute.=' size="3"';
			} else {
				$attribute.=' size="1"';
			}
		}
		if($this->multiple){
			$attribute .= ' multiple="multiple"';
			$map .='[]';
		}
		if(!empty($id))
			return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox"'.$attribute, 'value', 'text', $value , $id);
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox"'.$attribute, 'value', 'text', $value );
	}

	public function displaySingle($map, $value, $type = '', $root = 0, $delete = false) {
		if(empty($this->nameboxType))
			$this->nameboxType = hikashop_get('type.namebox');

		return $this->nameboxType->display(
			$map,
			$value,
			hikashopNameboxType::NAMEBOX_SINGLE,
			'category',
			array(
				'delete' => $delete,
				'root' => $root,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
	}

	public function displayMultiple($map, $values, $type = '', $root = 0) {
		if(empty($this->nameboxType))
			$this->nameboxType = hikashop_get('type.namebox');

		$first_element = reset($values);
		if(is_object($first_element))
			$values = array_keys($values);

		return $this->nameboxType->display(
			$map,
			$values,
			hikashopNameboxType::NAMEBOX_MULTIPLE,
			'category',
			array(
				'delete' => true,
				'root' => $root,
				'sort' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
	}

	public function displayTree($id, $root = 0, $type = null, $displayRoot = false, $selectRoot = false, $openTo = null, $link = '') {
		hikashop_loadJslib('otree');
		if(empty($type))
			$type = array('product','manufacturer','vendor');
		$ret = '';

		$ret .= '<div id="'.$id.'_otree" class="oTree"></div>
<script type="text/javascript">
var options = {rootImg:"'.HIKASHOP_IMAGES.'", showLoading:false};
var data = '.$this->getData($type, $root, $displayRoot, $selectRoot, $openTo).';
var '.$id.' = new window.oTree("'.$id.'",options,callbackTree,data,false);
'.$id.'.addIcon("world","world.png");';
		if(!empty($link)) {
			$ret .= '
'.$id.'.callbackSelection = function(tree,id,previous) {
	var node = tree.get(id);
	if( !node.value || !node.name )
		return;
	if(node.value < 0) {
		var parentNode = tree.find(-node.value);
		tree.rem(node);
		callbackTree(tree, parentNode, null, previous);
		return;
	}';
			if(!is_null($openTo)) {
				$ret .= '
	if(node.value == '.(int)$openTo.')
		return;
';
			}
			$ret .= '
	window.location.href = \''.$link.'&filter_id=\' + node.value;
};';
		}

		$ret .= '
'.$id.'.render(true);';
		if(!is_null($openTo)) {
			$ret .= '
var otoNode = '.$id.'.find('.(int)$openTo.');
if(otoNode) {
	'.$id.'.oTo(otoNode);
	'.$id.'.sel(otoNode);
}
';
		}
		$ret .= '
function callbackTree(tree,node,ev,skip) {
	var o = window.Oby;
	o.xRequest(\''.hikashop_completeLink('category&task=getTree&category_id=HIKAVALUE',false,false,true).'\'.replace(\'HIKAVALUE\', node.value), null,
		function(xhr,params) {
			var json = o.evalJSON(xhr.responseText);
			if(json.length == 0)
				return tree.emptyDirectory(node);
			var s = json.length, n, values;
			if(skip !== undefined && skip) {
				values = {};
				for(var i = 0; i < node.children.length; i++) {
					var v = tree.get(node.children[i]);
					values[v.value] = true;
				}
			}
			for(var i = 0; i < s; i++) {
				n = json[i];
				if(skip == undefined || !values[n.value])
					tree.add(node.id, n.status, n.name, n.value, n.url, n.icon);
			}
			tree.update(node);
			if(tree.selectOnOpen) {
				n = tree.find(tree.selectOnOpen);
				if(n) tree.sel(n);
				tree.selectOnOpen = null;
			}
			if(skip !== undefined && skip) {
				delete values;
				tree.selectedNode = skip;
				tree.sel(skip);
			}
		},
		function(xhr, params) { tree.add(node.id, 0, "error"); tree.update(node); }
	);
	return false;
}
';

		$ret .= '
</script>';
		return $ret;
	}

	private function getData($type = 'product', $root = 0, $displayRoot = false, $selectRoot = false, $value = null) {
		$categoryClass = hikashop_get('class.category');
		if($root == 1)
			$root = 0;
		if(empty($root)){
			if(!is_array($type))
				$type = array($type);
			$type[]='root';
		}
		$typeConfig = array('params'=>array('category_type'=>$type), 'mode' => 'tree');
		$fullLoad = false;
		$options = array('depth' => 2, 'start' => $root);

		list($elements,$values) = $categoryClass->getNameboxData($typeConfig, $fullLoad, null, null, null, $options);

		$parents = $categoryClass->getParents($value);
		if(count($parents) > 3) {
			$parents = array_reverse($parents);
			$data = array();
			$first = true;
			foreach($parents as $p){
				$o = new stdClass();
				$o->status = 2;
				if($first){
					if($p->category_left+1==$p->category_right){
						$o->status = 4;
					}else{
						$o->status = 3;
					}
					$first = false;
				}

				$o->name = JText::_($p->category_name);
				$o->value = (int)$p->category_id;
				$o->data = $data;

				$s = new stdClass();
				$s->status = 4;
				$s->name = '...';
				$s->value = -(int)$p->category_parent_id;
				$s->data = array();
				$data = array($o,$s);
			}

			foreach($elements as $k => $el){
				if($el->value != $data[0]->data[0]->value ) continue;

				if(count($el->data)){
					$found = false;
					foreach($el->data as $j => $e){
						if($e->value ==  $data[0]->data[0]->data[0]->value){
							$elements[$k]->data[$j]->data = $data[0]->data[0]->data[0]->data;
							$elements[$k]->data[$j]->status = 2;
							$found = true;
						}
					}
					if(!$found){
						$elements[$k]->data[] = $data[0]->data[0]->data[0];
					}
				}else{
					$elements[$k]->data = $data[0]->data[0]->data;
					$elements[$k]->status = 2;
				}
				break;
			}
		}

		return json_encode($elements);
	}
}

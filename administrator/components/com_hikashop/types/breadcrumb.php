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
class hikashopBreadcrumbType{
	function display($map,$value,$type=''){
		$class = hikashop_get('class.category');
		$mainCategories = $class->getParents($value);
		$mainHTML = array();
		$ids = array();
		if(!empty($mainCategories)){
			foreach($mainCategories as $mainCategory){
				$ids[] = $mainCategory->category_id;
			}

			$where=array();
			if(!empty($type)){
				$where = array(' a.category_type IN ('.$class->database->Quote($type).',\'root\')');
			}
			$childs = $class->loadAllWithTrans($ids,false,$where,' ORDER BY a.category_name ASC');

			foreach($mainCategories as $k => $mainCategory){
				$values = array();
				$current = 0;
				$values[]= '<option value="'.$mainCategory->category_id.'">' . JText::_('HIKA_NONE') . '</option>';
				foreach($childs as $child){
					if($child->category_parent_id==$mainCategory->category_id){
						$values[]= '<option value="'. $child->category_id .'" '. (in_array($child->category_id,$ids)?'selected="selected" ':'') .'>' . $child->translation . '</option>';
					}
				}
				if(count($values)==1) continue;
				$mainHTML[]= '<select name="'. $map .'_chooser_'.$k.'" id="'. $map .'_chooser_'.$k.'" class="inputbox" size="1" onchange="document.getElementById(\''.$map.'\').value=this.value; document.adminForm.submit();">'."\n".implode("\n",$values)."\n".'</select>';
			}
		}
		return implode('',$mainHTML);
	}
}

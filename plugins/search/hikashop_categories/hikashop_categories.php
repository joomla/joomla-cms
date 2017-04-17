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
class plgSearchHikashop_categories extends JPlugin{
	function plgSearchHikashop_categories(&$subject, $config){
		$this->loadLanguage('plg_search_hikashop_categories');
		$this->loadLanguage('plg_search_hikashop_categories_override');
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('search', 'hikashop_categories');
			if(version_compare(JVERSION,'2.5','<')){
				jimport('joomla.html.parameter');
				$this->params = new JParameter($plugin->params);
			} else {
				$this->params = new JRegistry($plugin->params);
			}
		}
	}

	function onContentSearchAreas(){
		return $this->onSearchAreas();
	}

	function onContentSearch( $text, $phrase='', $ordering='', $areas=null ){
		return $this->onSearch( $text, $phrase, $ordering, $areas );
	}

	function &onSearchAreas(){
		$areas = array(
			'categories' => JText::_('PRODUCT_CATEGORIES')
		);
		if(1==(int)$this->params->get('manufacturers','1')){
			$areas['manufacturers']=JText::_('MANUFACTURERS');
		}
		return $areas;
	}

	function onSearch( $text, $phrase='', $ordering='', $areas=null ){
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return array();
		$db	= JFactory::getDBO();

		$types=array();
		if (!empty($areas) && is_array( $areas )) {
			$valid = array_keys( $this->onSearchAreas() );
			$use = array_intersect( $areas,  $valid);
			if (!$use) {
				return array();
			}
			if(in_array('categories',$valid)){
				$types[]='\'product\'';
			}
			if(in_array('manufacturers',$valid)){
				$types[]='\'manufacturer\'';
			}
		}else{
			$types[]='\'product\'';
			if(1==(int)$this->params->get('manufacturers','1')){
				$types[]='\'manufacturer\'';
			}
		}

		$limit = $this->params->def( 'search_limit', 50 );

		$text = trim( $text );
		if ( $text == '' ) {
			return array();
		}

		switch($ordering){
			case 'alpha':
				$order = 'a.category_name ASC';
				break;
			case 'newest':
				$order = 'a.category_modified DESC';
				break;
			case 'oldest':
				$order = 'a.category_created ASC';
				break;
			case 'category':
			case 'popular':
			default:
				$order = 'a.category_name DESC';
				break;
		}
		$trans=hikashop_get('helper.translation');
		$multi=$trans->isMulti();
		$trans_table = 'jf_content';
		if($trans->falang){
			$trans_table = 'falang_content';
		}
		$rows = array();

		$filters = array('a.category_published=1');
		if(count($types)){
			$filters[]='a.category_type IN ('.implode(',',$types).')';
		}else{
			$filters[]='a.category_type NOT IN (\'status\',\'tax\')';
		}
		hikashop_addACLFilters($filters,'category_access','a');
		$filters2 = array();
		if($multi){
			$registry = JFactory::getConfig();
			if(!HIKASHOP_J25){
				$code = $registry->getValue('config.jflang');
			}else{
				$code = $registry->get('language');
			}
			$myLang = $trans->getId($code);
			$filters2[] = "b.reference_table='hikashop_category'";
			$filters2[] = "b.published=1";
			$filters2[] = 'b.language_id='.$myLang;
		}
		switch($phrase){
			case 'exact':
				$text		= $db->Quote( '%'.hikashop_getEscaped( $text, true ).'%', false );
				$filters1 = array();
				$filters1[] = "a.category_name LIKE ".$text;
				$filters1[] = "a.category_description LIKE ".$text;
				if($multi){
					$filters2[] = "b.value LIKE ".$text;
				}
				break;
			case 'all':
			case 'any':
			default:
				$words = explode( ' ', $text );
				$wordFilters = array();
				$subWordFilters1 = array();
				$subWordFilters2 = array();
				$wordFilters2 = array();
				foreach ($words as $word) {
					$word		= $db->Quote( '%'.hikashop_getEscaped( $word, true ).'%', false );
					$subWordFilters1[] 	= "a.category_name LIKE ".$word;
					$subWordFilters2[] 	= "a.category_description LIKE ".$word;
					if($multi){
						$wordFilters2[] = "b.value LIKE ".$word;
					}
				}
				$wordFilters[0]= '((' .implode( ($phrase == 'all' ? ') AND (' : ') OR ('),$subWordFilters1). '))';
				$wordFilters[1]= '((' .implode( ($phrase == 'all' ? ') AND (' : ') OR ('),$subWordFilters2). '))';
				$filters[] = '((' . implode( ') OR (', $wordFilters ) . '))';
				if($multi){
					$filters2[] = '((' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wordFilters2 ) . '))';
				}
				break;
		}

		$new_page = (int)$this->params->get('new_page','1');
		$select = ' a.category_type AS section, a.category_id AS id, a.category_name AS title, a.category_created AS created , a.category_description AS text, "'.$new_page.'" AS browsernav, a.category_canonical, a.category_alias';
		$count = 0;
		if($multi && !empty($myLang)){
			$query = ' SELECT DISTINCT '.$select.' FROM '.hikashop_table($trans_table,false) . ' AS b LEFT JOIN '.hikashop_table('category').' AS a ON b.reference_id=a.category_id WHERE '.implode(' AND ',$filters2).' ORDER BY '.$order;
			$db->setQuery($query, 0, $limit);
			$rows = $db->loadObjectList("id");
			$count = count($rows);
			if($count){
				$limit = $limit-$count;

				$ids = array_keys($rows);
				JArrayHelper::toInteger($ids);
				$filters[]='a.category_id NOT IN ('.implode(',',$ids).')';
			}
		}

		if($limit){
			$filters = implode(' AND ',$filters);
			if(isset($filters1)){
				$filters = '('.$filters.') AND ('.implode(' OR ',$filters1).')';
			}
			$query = ' SELECT '.$select.' FROM '.hikashop_table('category') . ' AS a WHERE '.$filters.' ORDER BY '.$order;
			$db->setQuery( $query, 0, $limit );
			$mainRows = $db->loadObjectList("id");
			if(!empty($mainRows)){
				foreach($mainRows as $k => $main){
					$rows[$k]=$main;
				}
				$count = count( $rows );
			}
		}
		if($count){

			if($multi && !empty($myLang)){
				$query = ' SELECT * FROM '.hikashop_table($trans_table,false) . ' WHERE reference_table=\'hikashop_category\' AND language_id=\''.$myLang.'\' AND published=1 AND reference_id IN ('.implode(',',array_keys($rows)).')';
				$db->setQuery($query);
				$trans = $db->loadObjectList();
				foreach($trans as $item){
					foreach($rows as $key => $row){
						if($row->id==$item->reference_id){
							if($item->reference_field=='category_name'){
								$row->title=$item->value;
							}elseif($item->reference_field=='category_description'){
								$row->text=$item->value;
							}
							break;
						}
					}
				}
			}
			$item_id = $this->params->get('item_id','');
			$menuClass = hikashop_get('class.menus');
			if(!empty($item_id)){
				$item_id = $menuClass->loadAMenuItemId('category','listing',$item_id);
			}
			$Itemid="";
			if(!empty($item_id)){
				$Itemid='&Itemid='.$item_id;
			}
			$manu_item_id = $this->params->get('manu_item_id','');
			if(!empty($manu_item_id)){
				$manu_item_id = $menuClass->loadAMenuItemId('manufacturer','listing',$manu_item_id);
			}
			$manu_Itemid='';
			if(!empty($manu_item_id)){
				$manu_Itemid='&Itemid='.$manu_item_id;
			}
			$itemids=array();

			$categoryClass = hikashop_get('class.category');
			foreach ( $rows as $k => $row ) {

				$row->category_name = $row->title;
				$categoryClass->addAlias($row);

				if(!empty($row->category_canonical)){
					$rows[$k]->href = $row->category_canonical;
				}else{
					if(empty($manu_item_id) && !empty($row->id) && $row->section=='manufacturer'){
						if(!isset($itemids[$row->id])) $itemids[$row->id] = $menuClass->getItemidFromCategory($row->id,$row->section);
						$manu_item_id = $itemids[$row->id];
						if(!empty($manu_item_id)){
							$manu_Itemid='&Itemid='.$manu_item_id;
						}
						if(!$this->params->get('manu_item_id','')) $manu_item_id = '';
					}elseif(empty($item_id) && !empty($row->id) && $row->section!='manufacturer'){
						if(!isset($itemids[$row->id])) $itemids[$row->id] = $menuClass->getItemidFromCategory($row->id,'category');
						$item_id = $itemids[$row->id];
						if(!empty($item_id)){
							$Itemid='&Itemid='.$item_id;
						}
						if(!$this->params->get('item_id','')) $item_id = '';
					}

					if($row->section=='manufacturer'){
						$rows[$k]->href = 'index.php?option=com_hikashop&ctrl=category&task=listing&name='.$row->alias.'&cid='.$row->id.$manu_Itemid;
					}else{
						$rows[$k]->href = 'index.php?option=com_hikashop&ctrl=category&task=listing&name='.$row->alias.'&cid='.$row->id.$Itemid;
					}
				}

				if($row->section=='manufacturer'){
					$rows[$k]->section 	= JText::_( 'MANUFACTURERS' );
				}else{
					$rows[$k]->section 	= JText::_( 'PRODUCT_CATEGORY' );
				}
			}
		}
		return $rows;
	}
}

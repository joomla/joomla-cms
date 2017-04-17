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
class plgSearchHikashop_products extends JPlugin{
	function plgSearchHikashop_products(&$subject, $config){
		$this->loadLanguage('plg_search_hikashop_products');
		$this->loadLanguage('plg_search_hikashop_products_override');
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('search', 'hikashop_products');
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
			'products' => JText::_('PRODUCTS')
		);
		return $areas;
	}

	function onSearch( $text, $phrase='', $ordering='', $areas=null ){
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return array();
		$db		= JFactory::getDBO();
		if (is_array( $areas )) {
			if (!array_intersect( $areas, array_keys( $this->onSearchAreas() ) )) {
				return array();
			}
		}

		$limit = $this->params->def( 'search_limit', 50 );

		$text = trim( $text );
		if ( $text == '' ) {
			return array();
		}

		switch($ordering){
			case 'alpha':
				$order = 'a.product_name ASC';
				break;
			case 'newest':
				$order = 'a.product_modified DESC';
				break;
			case 'oldest':
				$order = 'a.product_created ASC';
				break;
			case 'popular':
				$order = 'a.product_hit DESC';
				break;
			case 'category':
			default:
				$order = 'a.product_name DESC';
				break;
		}
		$trans=hikashop_get('helper.translation');
		$multi=$trans->isMulti();
		$trans_table = 'jf_content';
		if($trans->falang){
			$trans_table = 'falang_content';
		}

		$rows = array();

		$filters = array('a.product_published=1');

		$variants = (int)$this->params->get('variants','0');
		if(!$variants){
			$filters[]='a.product_type=\'main\'';
		}
		$out_of_stock = (int)$this->params->get('out_of_stock_display','1');
		if(!$out_of_stock){
			$filters[]='a.product_quantity!=0';
		}

		hikashop_addACLFilters($filters,'product_access','a');
		$leftjoin='';

		$catFilters = array('category_published=1','category_type=\'product\'');
		hikashop_addACLFilters($catFilters,'category_access');
		$db->setQuery('SELECT category_id FROM '.hikashop_table('category').' WHERE '.implode(' AND ',$catFilters));
		if(!HIKASHOP_J25){
			$cats = $db->loadResultArray();
		} else {
			$cats = $db->loadColumn();
		}
		if(!empty($cats)){
			$filters[]='b.category_id IN ('.implode(',',$cats).')';
		}

		if($variants){
			$leftjoin=' INNER JOIN '.hikashop_table('product_category').' AS b ON a.product_parent_id=b.product_id OR a.product_id=b.product_id';
		}else{
			$leftjoin=' INNER JOIN '.hikashop_table('product_category').' AS b ON a.product_id=b.product_id';
		}

		$filters2 = array();

		if($multi){
			$registry = JFactory::getConfig();
			if(!HIKASHOP_J25){
				$code = $registry->getValue('config.jflang');
			}else{
				$code = $registry->get('language');
			}
			$lg = $trans->getId($code);
			$filters2[] = "b.reference_table='hikashop_product'";
			$filters2[] = "b.published=1";
			$filters2[] = 'b.language_id='.$lg;
		}

		$fields = $this->params->get('fields','');
		if(empty($fields)){
			$fields = array('product_name','product_description');
		}else{
			$fields = explode(',',$fields);
		}

		switch($phrase){
			case 'exact':
				$text		= $db->Quote( '%'.hikashop_getEscaped( $text, true ).'%', false );
				$filters1 = array();
				foreach($fields as $f){
					$filters1[] = "a.".$f." LIKE ".$text;
				}

				if($multi){
					$filters2[] = "b.value LIKE ".$text;
				}
				break;
			case 'all':
			case 'any':
			default:
				$words = explode( ' ', $text );
				$wordFilters = array();
				$subWordFiltersX = array();
				$wordFilters2 = array();
				foreach ($words as $word) {
					$word		= $db->Quote( '%'.hikashop_getEscaped( $word, true ).'%', false );
					foreach($fields as $i => $f){
						$subWordFiltersX[$i][] = "a.".$f." LIKE ".$word;
					}
					if($multi){
						$wordFilters2[] = "b.value LIKE ".$word;
					}
				}
				foreach($subWordFiltersX as $i => $subWordFilters){
					$wordFilters[$i]= '((' .implode( ($phrase == 'all' ? ') AND (' : ') OR ('),$subWordFilters). '))';
				}
				$filters[] = '((' . implode( ') OR (', $wordFilters ) . '))';
				if($multi){
					$filters2[] = '((' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wordFilters2 ) . '))';
				}
				break;
		}

		$new_page = (int)$this->params->get('new_page','1');

		$select = ' a.product_id AS id, a.product_name, a.product_alias, a.product_canonical, a.product_created AS created , a.product_description, "'.$new_page.'" AS browsernav';
		if($variants){
			$select.=', a.product_type, a.product_parent_id';
		}
		$count = 0;
		if($multi && !empty($lg)){
			$db->setQuery('SET SQL_BIG_SELECTS=1');
			$db->query();
			$query = ' SELECT DISTINCT '.$select.' FROM '.hikashop_table($trans_table,false) . ' AS b LEFT JOIN '.hikashop_table('product').' AS a ON b.reference_id=a.product_id WHERE '.implode(' AND ',$filters2).' ORDER BY '.$order;
			$db->setQuery($query, 0, $limit);
			$rows = $db->loadObjectList("id");
			$count = count($rows);
			if($count){
				$limit = $limit-$count;
				$filters[]='a.product_id NOT IN ('.implode(',',array_keys($rows)).')';
			}
		}

		if($limit){
			if(!empty($leftjoin)){
				$select.=', b.category_id as category_id';
			}
			$db->setQuery('SET SQL_BIG_SELECTS=1');
			$db->query();
			$filters = implode(' AND ',$filters);
			if(isset($filters1)){
				$filters = '('.$filters.') AND ('.implode(' OR ',$filters1).')';
			}
			$query = ' SELECT DISTINCT '.$select.' FROM '.hikashop_table('product') . ' AS a '.$leftjoin.' WHERE '.$filters.' GROUP BY (a.product_id) ORDER BY '.$order;
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

			if($multi && !empty($lg)){
				$query = ' SELECT * FROM '.hikashop_table($trans_table,false) . ' WHERE reference_table=\'hikashop_product\' AND language_id=\''.$lg.'\' AND published=1 AND reference_id IN ('.implode(',',array_keys($rows)).')';
				$db->setQuery($query);
				$trans = $db->loadObjectList();
				foreach($trans as $item){
					foreach($rows as $key => $row){
						if($row->id==$item->reference_id){
							if($item->reference_field=='product_name'){
								$row->product_name=$item->value;
							}elseif($item->reference_field=='product_description'){
								$row->product_description=$item->value;
							}else{
								$row->product_name=$item->value;
							}
							break;
						}
					}
				}
			}
			$parent = '';
			$item_id = $this->params->get('item_id','');
			$menuClass = hikashop_get('class.menus');
			$config =& hikashop_config();
			$pathway_sef_name = $config->get('pathway_sef_name','category_pathway');
			$menus=array();
			$Itemid='';
			if(!empty($item_id)){
				$Itemid='&Itemid='.$item_id;
				if($this->params->get('full_path',1)){
					$menuData = $menus[$item_id] = $menuClass->get($item_id);
					if(!empty($menuData->hikashop_params['selectparentlisting'])){
						$parent = '&'.$pathway_sef_name.'='.(int)$menuData->hikashop_params['selectparentlisting'];
					}
				}
			}
			$itemids=array();
			$app= JFactory::getApplication();
			$class = hikashop_get('class.product');
			$ids = array();
			foreach ( $rows as $k => $row ) {
				$ids[$row->id]=$row->id;
				if(!empty($row->category_id)){
					if(empty($item_id)){
						if(!isset($itemids[$row->category_id])) $itemids[$row->category_id] = $menuClass->getItemidFromCategory($row->category_id);
						$item_id = $itemids[$row->category_id];
					}
					if(!empty($item_id)){
						$Itemid='&Itemid='.$item_id;
					}
					if($this->params->get('full_path',1)){
						$parent = '&'.$pathway_sef_name.'='.(int)$row->category_id;
					}
					if(!$this->params->get('item_id','')) $item_id = '';
				}
				$class->addAlias($row);
				$row->title=$row->product_name;
				$row->text=$row->product_description;
				if($variants && $row->product_type=='variant'){
					$ids[$row->product_parent_id]=$row->product_parent_id;
					static $mains = array();
					if(!isset($mains[$row->product_parent_id])){
						$mains[$row->product_parent_id] = $class->get((int)$row->product_parent_id);
						$class->addAlias($mains[$row->product_parent_id]);
					}
					$db = JFactory::getDBO();
					$db->setQuery('SELECT * FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic') .' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id='.(int)$row->id.' ORDER BY a.ordering');
					$row->characteristics = $db->loadObjectList();
					$class->checkVariant($row,$mains[$row->product_parent_id]);
					if(empty($row->title)){
						$row->title = $row->product_name;
					}
					if(empty($row->text)){
						$row->text = $mains[$row->product_parent_id]->product_description;
					}
				}
				if(empty($row->product_canonical)){
					$rows[$k]->href = JRoute::_('index.php?option=com_hikashop&ctrl=product&task=show&name='.$row->alias.'&cid='.$row->id.$Itemid.$parent);
				}else{
					$rows[$k]->href = $row->product_canonical;
				}
				$rows[$k]->href = hikashop_cleanURL($rows[$k]->href);

				$rows[$k]->section 	= JText::_( 'PRODUCT' );
			}

			if(!empty($ids)){
				$imageHelper = hikashop_get('helper.image');
				$height = (int)$config->get('thumbnail_y','100');
				$width = (int)$config->get('thumbnail_x','100');
				$image_options = array('default' => true,'forcesize'=>$config->get('image_force_size',true),'scale'=>$config->get('image_scale_mode','inside'));
				$db = JFactory::getDBO();
				$queryImage = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type=\'product\' ORDER BY file_ref_id ASC, file_ordering DESC, file_id ASC';
				$db->setQuery($queryImage);
				$images = $db->loadObjectList('file_ref_id');
				foreach($rows as $k => $row){
					foreach($images as $k2 => $image){
						if($k==$k2){
							$result = $imageHelper->getThumbnail(@$image->file_path, array('width' => $width, 'height' => $height), $image_options);
							if($result->success){
								$rows[$k]->image = $result->url;
							}
							break;
						}
					}

					if(!empty($rows[$k]->image))
						continue;

					if(!$variants)
						continue;

					foreach($images as $k2 => $image){
						if($row->product_parent_id==$k2){
							$result = $imageHelper->getThumbnail(@$image->file_path, array('width' => $width, 'height' => $height), $image_options);
							if($result->success){
								$rows[$k]->image = $result->url;
							}
							break;
						}
					}
				}
			}

		}
		return $rows;
	}
}

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
class sef_hikashop {
		function create($string) {
		$string = str_replace("&amp;", "&", preg_replace('#(index\.php\??)#i','',$string));
		$query = array();
		$allValues = explode('&',$string);
		foreach($allValues as $oneValue){
			list($var,$val) = explode('=',$oneValue);
			$query[$var] = $val;
		}
		$segments = array();
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(function_exists('hikashop_config') || include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			$config =& hikashop_config();
			if($config->get('activate_sef',1)){
				$categorySef=$config->get('category_sef_name','category');
				$productSef=$config->get('product_sef_name','product');
				$checkoutSef=$config->get('checkout_sef_name','checkout');
				if(empty($categorySef)){
					$categorySef='';
				}
				if(empty($productSef)){
					$productSef='';
				}

				if(isset($query['ctrl']) && isset($query['task'])){
					if($query['ctrl']=='category' && $query['task']=='listing'){
						$segments[] = $categorySef;
						unset( $query['ctrl'] );
						unset( $query['task'] );
					}
					else if($query['ctrl']=='product' && $query['task']=='show'){
						$segments[] = $productSef;
						unset( $query['ctrl'] );
						unset( $query['task'] );
					}
				}
				else if(isset($query['view']) && isset($query['layout'])){
					if($query['view']=='category' && $query['layout']=='listing'){
						$segments[] = $categorySef;
						unset( $query['layout'] );
						unset( $query['view'] );
					}
					else if($query['view']=='product' && $query['layout']=='show'){
						$segments[] = $productSef;
						unset( $query['layout'] );
						unset( $query['view'] );
					}
				}
				if((isset($query['ctrl']) && $query['ctrl']=='checkout' || isset($query['view']) && $query['view']=='checkout') && !empty($query['Itemid'])){
					$menuClass = hikashop_get('class.menus');
					$menu = $menuClass->get($query['Itemid']);
					if($menu->link =='index.php?option=com_hikashop&view=checkout&layout=step'){
						if(isset($query['ctrl'])) unset($query['ctrl']);
						if(isset($query['view'])) unset($query['view']);
						if(!empty($checkoutSef)) $segments[] = $checkoutSef;
					}
				}
			}
			$pathway_sef_name = $config->get('pathway_sef_name','category_pathway');
			if(isset($query[$pathway_sef_name])&& (empty($query[$pathway_sef_name])) || $config->get('simplified_breadcrumbs',1)){
				unset( $query[$pathway_sef_name] );
			}
			if(isset($query[$pathway_sef_name])){
				$category_pathway = $config->get('category_pathway','category_pathway');
				if($category_pathway!='category_pathway' && !empty($category_pathway)){
					$query[$category_pathway]=$query[$pathway_sef_name];
					unset( $query[$pathway_sef_name] );
				}
			}
			$related_sef_name = $config->get('related_sef_name','related_product');
			if(isset($query[$related_sef_name])&& $config->get('simplified_breadcrumbs',1)){
				unset( $query[$related_sef_name] );
			}
		}
		if (isset($query['ctrl'])) {
			$segments[] = $query['ctrl'];
			unset( $query['ctrl'] );
			if (isset($query['task'])) {
				$segments[] = $query['task'];
				unset( $query['task'] );
			}
		}elseif(isset($query['view'])){
			$segments[] = $query['view'];
			unset( $query['view'] );
			if(isset($query['layout'])){
				$segments[] = $query['layout'];
				unset( $query['layout'] );
			}
		}

		if(isset($query['cid']) && isset($query['name'])){
			if($config->get('sef_remove_id',1)){
				$segments[] = $query['name'];
			}else{
				if(is_numeric($query['name'])){
					$query['name']=$query['name'].'-';
				}
				$segments[] = $query['cid'].':'.$query['name'];
			}
			unset($query['cid']);
			unset($query['name']);
		}
		unset($query['option']);
		if(isset($query['Itemid'])) unset($query['Itemid']);
		if(!empty($query)){
			foreach($query as $name => $value){
				$segments[] = $name.':'.$value;
			}
		}
		return implode('/',$segments);
	}
}

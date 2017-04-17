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
function HikashopBuildRoute( &$query )
{
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
			if( ( isset($query['ctrl']) && $query['ctrl']=='checkout' || isset($query['view']) && $query['view']=='checkout' ) && !empty($query['Itemid']) && ( !isset($query['task']) && !isset($query['layout']) || (isset($query['task']) && $query['task']=='step' ) || (isset($query['layout']) && $query['layout']=='step' )) ) {
				if(empty($checkoutSef)){
					$menuClass = hikashop_get('class.menus');
					$menu = $menuClass->get($query['Itemid']);
					if(!empty($menu) && !empty($menu->link) && $menu->link =='index.php?option=com_hikashop&view=checkout&layout=step'){
						if(isset($query['ctrl'])) unset($query['ctrl']);
						if(isset($query['view'])) unset($query['view']);
					}
				}else{
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
		unset( $query['view'] );
		if(isset($query['layout'])){
			unset( $query['layout'] );
		}
	}

	if(isset($query['product_id'])){
		$query['cid'] = $query['product_id'];
		unset($query['product_id']);
	}
	if(isset($query['cid']) && isset($query['name'])){
		if($config->get('sef_remove_id',0) && !empty($query['name'])){
			$int_at_the_beginning = (int)$query['name'];
			if($int_at_the_beginning){
				$query['name'] = $config->get('alias_prefix','p').$query['name'];
			}
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

	if(!empty($query)){
		foreach($query as $name => $value){
			if(!in_array($name,array('option','Itemid','start','format','limitstart','lang'))){
					if(is_array($value)) $value = implode('-',$value);
					$segments[] = $name.':'.$value;
				unset($query[$name]);
			}
		}
	}

	return $segments;
}

function HikashopParseRoute( $segments )
{
	$vars = array();
	$check=false;
	if(!empty($segments)){
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(function_exists('hikashop_config') || include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			$config =& hikashop_config();
			if($config->get('activate_sef',1)){
				$categorySef=$config->get('category_sef_name','category');
				$productSef=$config->get('product_sef_name','product');
				$checkoutSef=$config->get('checkout_sef_name','checkout');
				$skip=false;
				if(isset($segments[0])){
					$file = HIKASHOP_CONTROLLER.$segments[0].'.php';
					if(file_exists($file) && isset($segments[1])){
						if(!($segments[0]=='product'&&$segments[1]=='show' || $segments[0]=='category'&&$segments[1]=='listing' || $segments[0]=='checkout'&&$segments[1]=='notice')){
							$controller = hikashop_get('controller.'.$segments[0],array(),true);
							if($controller->isIn($segments[1],array('display','modify_views','add','modify','delete'))){
								$skip = true;
							}
						}
					}
				}

				if(!$skip){

					if(count($segments)==1){
						if(empty($categorySef)){
							$vars['ctrl']='category';
							$vars['task']='listing';
						}
						elseif(empty($productSef)){
							$vars['ctrl']='product';
							$vars['task']='listing';
						}
					}

					$i = 0;

					foreach($segments as $k => $name){
						if(strpos($name,':')){
							if(empty($productSef) && !$check){
								$vars['ctrl']='product';
								$vars['task']='show';
							}
							list($arg,$val) = explode(':',$name,2);
							if($arg=='task'&&$val=='step'){
								$vars['ctrl']='checkout';
							}
							if(is_numeric($arg) && !is_numeric($val)){
								$vars['cid'] = $arg;
								$vars['name'] = $val;
							}elseif(is_numeric($arg)){
								$vars['Itemid'] = $arg;
							}elseif(str_replace(':','-',$name)==$productSef){
								$vars['ctrl']='product';
								$vars['task']='show';
							}else if(str_replace(':','-',$name)==$categorySef){
								$vars['ctrl']='category';
								$vars['task']='listing';
								$check=true;
							}else{
								if(hikashop_retrieve_url_id($vars,$name)) continue;
								$vars[$arg] = $val;
							}
						}else if($name==$productSef){
							$vars['ctrl']='product';
							$vars['task']='show';
						}else if($name==$categorySef){
							$vars['ctrl']='category';
							$vars['task']='listing';
							$check=true;
						}else if($name==$checkoutSef && ( $name!= 'checkout' || !isset($segments[$k+1]) || $segments[$k+1] != 'notice' )){
							$vars['ctrl']='checkout';
							$vars['task']='step';
							$check=true;
						}else{
							if(hikashop_retrieve_url_id($vars,$name)) continue;
							$i++;
							if($i == 1){
								$vars['ctrl'] = $name;
								$vars['task'] = '';
							}elseif($i == 2)
								$vars['task'] = $name;
							$check=true;
						}
					}

					return $vars;
				}
				$i = 0;
				foreach($segments as $name){
					if(strpos($name,':')){
						list($arg,$val) = explode(':',$name,2);
						if(is_numeric($arg) && !is_numeric($val)){
							$vars['cid'] = $arg;
							$vars['name'] = $val;
						}elseif(is_numeric($arg)){
							if(hikashop_retrieve_url_id($vars,$name)) continue;
							$vars['Itemid'] = $arg;
						}else{
							if(hikashop_retrieve_url_id($vars,$name)) continue;
							$vars[$arg] = $val;
						}
					}else{
						if(hikashop_retrieve_url_id($vars,$name)) continue;
						$i++;
						if($i == 1) $vars['ctrl'] = $name;
						elseif($i == 2) $vars['task'] = $name;
					}
				}
				$category_pathway = $config->get('category_pathway','category_pathway');
				if($category_pathway!='category_pathway' && isset($vars[$category_pathway])){
					$vars['category_pathway']=$vars[$category_pathway];
				}
			}else{
				foreach($segments as $name){
					hikashop_retrieve_url_id($vars,$name);
				}
			}

		}
	}
	return $vars;
}

function hikashop_retrieve_url_id(&$vars,$name){
	$config =& hikashop_config();
	if($config->get('sef_remove_id',0) && isset($vars['ctrl']) && isset($vars['task'])){
		if($vars['ctrl']=='category' || ($vars['ctrl']=='product' && $vars['task']=='listing')){
			$type = 'category';
		}elseif($vars['ctrl']=='product' && $vars['task']=='show'){
			$type = 'product';
		}else{
			return false;
		}

		$db = JFactory::getDBO();
		$config =& hikashop_config();

		$class = hikashop_get('helper.translation');
		if($class->isMulti()){
			$trans_table = 'jf_content';
			if($class->falang){
				$trans_table = 'falang_content';
			}
			$db->setQuery('SELECT reference_id FROM '.hikashop_table($trans_table,false).' WHERE reference_table='.$db->Quote('hikashop_'.$type).' AND reference_field='.$db->Quote($type.'_alias').' AND value = '.$db->Quote(str_replace(':','-',$name)));
			$retrieved_id = $db->loadResult();
			if($retrieved_id){
				$vars['cid'] = $retrieved_id;
				$vars['name'] = $name;
				return true;
			}
		}
		$db->setQuery('SELECT '.$type.'_id FROM '.hikashop_table($type).' WHERE '.$type.'_alias = '.$db->Quote(str_replace(':','-',$name)));
		$retrieved_id = $db->loadResult();
		if($retrieved_id){
			$vars['cid'] = $retrieved_id;
			$vars['name'] = $name;
			return true;
		}


		$name_regex = '^ *p?'.str_replace(array('-',':'),'.+',$name).' *$';
		$class = hikashop_get('helper.translation');
		if($class->isMulti()){
			$trans_table = 'jf_content';
			if($class->falang){
				$trans_table = 'falang_content';
			}
			$db->setQuery('SELECT reference_id FROM '.hikashop_table($trans_table,false).' WHERE reference_table='.$db->Quote('hikashop_'.$type).' AND ((reference_field='.$db->Quote($type.'_alias').' AND (value = '.$db->Quote(str_replace(':','-',$name)).' OR value REGEXP '.$db->Quote($name_regex).')) OR (reference_field='.$db->Quote($type.'_name').' AND value REGEXP '.$db->Quote($name_regex).'))');
			$retrieved_id = $db->loadResult();
			if($retrieved_id){
				$vars['cid'] = $retrieved_id;
				$vars['name'] = $name;
				return true;
			}
		}

		$db->setQuery('SELECT * FROM '.hikashop_table($type).' WHERE '.$type.'_alias REGEXP '.$db->Quote($name_regex).' OR '.$type.'_name REGEXP '.$db->Quote($name_regex));
		$retrieved = $db->loadObject();

		if($retrieved){
			$type_id = $type.'_id';
			$vars['cid'] = $retrieved->$type_id;
			$vars['name'] = $name;
			if($config->get('alias_auto_fill',1)){
				$type_alias = $type.'_alias';
				if(empty($retrieved->$type_alias)){
					$class = hikashop_get('class.'.$type);
					$class->addAlias($retrieved);

					if($config->get('sef_remove_id',0)){
						$int_at_the_beginning = (int)$retrieved->alias;
						if($int_at_the_beginning){
							$retrieved->alias = $config->get('alias_prefix','p').$retrieved->alias;
						}
					}

					$element = new stdClass();
					$element->$type_id = $retrieved->$type_id;
					$element->$type_alias = $retrieved->alias;

					$class->save($element);
				}
			}
			return true;
		}
	}
	return false;
}

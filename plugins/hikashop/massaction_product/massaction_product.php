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
class plgHikashopMassaction_product extends JPlugin
{
	var $message = '';

	function onMassactionTableLoad(&$externalValues){
		$obj = new stdClass();
		$obj->table ='product';
		$obj->value ='product';
		$obj->text =JText::_('PRODUCT');
		$externalValues[] = $obj;
	}

	function plgHikashopMassaction_product(&$subject, $config){
		parent::__construct($subject, $config);
		$this->massaction = hikashop_get('class.massaction');
		$this->massaction->datecolumns = array( 'product_created',
												'product_sale_start',
												'product_sale_end',
												'product_modified',
												'product_last_seen_date'
											);
		$this->product = hikashop_get('class.product');
	}

	function onProcessProductMassFilterlimit(&$elements, &$query,$filter,$num){
		$query->start = (int)$filter['start'];
		$query->value = (int)$filter['value'];
	}

	function onProcessProductMassFilterordering(&$elements, &$query,$filter,$num){
		if(!empty($filter['value'])){
			if(isset($query->ordering['default']))
				unset($query->ordering['default']);
			$query->ordering[] = $filter['value'];
		}
	}

	function onProcessProductMassFilterdirection(&$elements, &$query,$filter,$num){
		if(empty($query->ordering))
			$query->ordering['default'] = 'product_id';
		$query->direction = $filter['value'];
	}

	function onProcessProductMassFilterproductColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		if(count($elements)){
			foreach($elements as $k => $element){
				if(isset($element->$filter['type'])){
					$in = $this->massaction->checkInElement($element, $filter);
					if(!$in) unset($elements[$k]);
				}
			}
		}else{
			$db = JFactory::getDBO();
			if($filter['value'] == 0 || !empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				$query->where[] = $this->massaction->getRequest($filter,'hk_product');
			}
		}
	}
	function onCountProductMassFilterproductColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessProductMassFilterproductColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_product.product_id'));
	}
	function onProcessProductMassFilterpriceColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		if(count($elements)){
			foreach($elements as $k => $element){
				$del = true;
				foreach($element->prices as $price){
					$in = $this->massaction->checkInElement($price, $filter);
					if($in) $del = false;
				}
				if($del) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			if(!empty($filter['value']) || $filter['value'] == '0'){
				$query->leftjoin[] = hikashop_table('price').' AS hk_price ON hk_price.price_product_id = hk_product.product_id';
				if($filter['type'] == 'price_currency_id' && !is_int($filter['value'])){
					$nquery = 'SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_symbol = '.$db->quote($filter['value']).' OR currency_code = '.$db->quote($filter['value']).' OR currency_name = '.$db->quote($filter['value']);
					$db->setQuery($nquery);
					$result = $db->loadResult();
					$query->where[] = 'hk_price.'.$filter['type'].' = '.(int)$result;
				}else{
					if($filter['type'] == 'price_value' && ($filter['operator'] == ('IS NULL') || $filter['value'] == '0')){
						$nquery = 'SELECT price_product_id FROM '.hikashop_table('price').' WHERE 1';
						$db->setQuery($nquery);
						if(!HIKASHOP_J25){
							$result = $db->loadResultArray();
						} else {
							$result = $db->loadColumn();
						}
						$query->where[] = 'hk_product.product_id NOT IN ('.implode(',',$result).') ';
					}else{
						$query->where[] = 'hk_price.'.$filter['type'].' '.$filter['operator'].' '.$db->quote($filter['value']);
					}
					if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
						$query->where[] = $this->massaction->getRequest($filter,'hk_price');
					}
				}
			}
		}
	}
	function onCountProductMassFilterpriceColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessProductMassFilterpriceColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_product.product_id'));
	}
	function onProcessProductMassFiltercategoryColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		$db = JFactory::getDBO();
		if(count($elements)){
			foreach($elements as $k => $element){
				if(!empty($element->categories)){
					if(!is_array($element->categories)) $element->categories = array($element->categories);
					JArrayHelper::toInteger($element->categories);
					$db->setQuery('SELECT * FROM '.hikashop_table('category').' WHERE category_id IN('.implode(',',$element->categories).')');
					$categories = $db->loadObjectList();
					$del = true;
					foreach($categories as $category){
						$in = $this->massaction->checkInElement($category, $filter);
						if($in) $del = false;
					}
					if($del) unset($elements[$k]);
				}
			}
		}else{
			if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				$query->leftjoin['product_category'] = hikashop_table('product_category').' AS hk_product_category ON hk_product_category.product_id = hk_product.product_id';
				$query->leftjoin['category'] = hikashop_table('category').' AS hk_category ON hk_category.category_id = hk_product_category.category_id';
				$query->where[] = $this->massaction->getRequest($filter,'hk_category');

				$tQuery = '';
				if(!empty($query->select)) $tQuery .= ' SELECT '.$query->select;
				if(!empty($query->from)) $tQuery .= ' FROM '.$query->from;
				if(!empty($query->join)) $tQuery .= ' JOIN '.implode(' JOIN ',$query->join);
				if(!empty($query->leftjoin)) $tQuery .= ' LEFT JOIN '.implode(' LEFT JOIN ',$query->leftjoin);
				if(!empty($query->where)) $tQuery .= ' WHERE ('.implode(') AND (',$query->where).')';
				if(!empty($query->ordering)) $tQuery .= ' ORDER BY '.implode(',',$query->ordering);
				if(!empty($query->direction) && is_string($query->direction)) $tQuery .= ' '.$query->direction;
				if(!empty($query->group) && is_string($query->group)) $tQuery .= ' GROUP BY '.$query->group;

				$db->setQuery($tQuery);
				$mainProducts = $db->loadObjectList();
				$ids = array();
				foreach($mainProducts as $mainProduct){
					$ids[] = $mainProduct->product_id;
				}

				$query->join = array();
				$query->leftjoin = array();
				$query->where = array('product_id IN ('.implode(',',$ids).') OR product_parent_id IN ('.implode(',',$ids).')');
			}
		 }
	}
	function onCountProductMassFiltercategoryColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessProductMassFiltercategoryColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_product.product_id'));
	}
	function onProcessProductMassFiltercharacteristicColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		if(count($elements)){
			foreach($elements as $k => $element){
				$filter['type'] = strtolower($filter['type']);
				if(!isset($element->$filter['type'])){
					$in = false;
				}else{
					$in = $this->massaction->checkInElement($element, $filter);
				}
				if(!$in) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			if(!empty($filter['value'])){
				$query->leftjoin['variant'.$num] = hikashop_table('variant').' AS hk_variant'.$num.' ON hk_variant'.$num.'.variant_product_id = hk_product.product_id';
				$query->leftjoin['characteristic'.$num] = hikashop_table('characteristic').' AS hk_characteristic'.$num.' ON hk_characteristic'.$num.'.characteristic_id = hk_variant'.$num.'.variant_characteristic_id';
				$query->leftjoin['characteristic_parent'.$num] = hikashop_table('characteristic').' AS hk_characteristic_parent'.$num.' ON hk_characteristic'.$num.'.characteristic_parent_id = hk_characteristic_parent'.$num.'.characteristic_id';
				if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
					$rquery = str_replace($filter['type'],'',$this->massaction->getRequest($filter));
					$query->where[] = 'hk_characteristic'.$num.'.characteristic_value '.$rquery.' AND hk_characteristic_parent'.$num.'.characteristic_value = '.$db->quote($filter['type']).'';
				}
			}
		 }
	}
	function onCountProductMassFiltercharacteristicColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessProductMassFiltercharacteristicColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_product.product_id'));
	}
	function onProcessProductMassFilterproduct_relatedColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		$db = JFactory::getDBO();
		if(count($elements)){
			foreach($elements as $k => $element){
				if(empty($element->related)){
					unset($elements[$k]);
					continue;
				}
				$db->setQuery('SELECT * FROM '.hikashop_table('product_related').' as hk_product_related LEFT JOIN '.hikashop_table('product').' AS hk_product ON hk_product.product_id = hk_product_related.product_related_id WHERE product_related_type = \'related\' product_related_id IN('.implode(',',$element->related).')');
				$relateds = $db->loadObjectList();
				$del = true;
				foreach($relateds as $related){
					$in = $this->massaction->checkInElement($related, $filter);
					if($in) $del = false;
				}
				if($del) unset($elements[$k]);
			}
		}else{
			if(!empty($filter['value'])){
				$nquery = 'SELECT hk_product_related.product_related_id FROM '.hikashop_table('product_related').' AS hk_product_related LEFT JOIN '.hikashop_table('product').' AS hk_product ON hk_product_related.product_id = hk_product.product_id ';
				if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
					$nquery .= 'WHERE '.$this->massaction->getRequest($filter,'hk_product');
				}
				$nquery .= ' AND hk_product_related.product_related_type = '.$db->quote('related');
				$nquery .= ' AND hk_product.product_type = '.$db->quote('main');

				$db->setQuery($nquery);
				$relatedIds = $db->loadResultArray();

				if(empty($relatedIds)) $relatedIds = array('0');
				JArrayHelper::toInteger($relatedIds);
				$query->where[] = 'hk_product.product_id IN('.implode(',',$relatedIds).')';
			}
		}
	}
	function onCountProductMassFilterproduct_relatedColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessProductMassFilterproduct_relatedColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_product.product_id'));
	}
	function onProcessProductMassFilterproduct_optionColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		$db = JFactory::getDBO();
		if(count($elements)){
			foreach($elements as $k => $element){
				if(empty($element->options)){
					unset($elements[$k]);
					continue;
				}
				$db->setQuery('SELECT * FROM '.hikashop_table('product_related').' as hk_product_related LEFT JOIN '.hikashop_table('product').' AS hk_product ON hk_product.product_id = hk_product_related.product_related_id WHERE product_related_type = \'options\' product_related_id IN('.implode(',',$element->options).')');
				$options = $db->loadObjectList();
				$del = true;
				foreach($options as $option){
					$in = $this->massaction->checkInElement($option, $filter);
					if($in) $del = false;
				}
				if($del) unset($elements[$k]);
			}
		}else{
			if(!empty($filter['value'])){

				$nquery = 'SELECT hk_product_related.product_related_id FROM '.hikashop_table('product_related').' AS hk_product_related LEFT JOIN '.hikashop_table('product').' AS hk_product ON hk_product_related.product_id = hk_product.product_id ';
				if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
					$nquery .= 'WHERE '.$this->massaction->getRequest($filter,'hk_product');
				}
				$nquery .= ' AND hk_product_related.product_related_type = '.$db->quote('option');
				$nquery .= ' AND hk_product.product_type = '.$db->quote('main');

				$db->setQuery($nquery);
				$relatedIds = $db->loadResultArray();

				if(empty($relatedIds)) $relatedIds = array('0');
				JArrayHelper::toInteger($relatedIds);
				$query->where[] = 'hk_product.product_id IN('.implode(',',$relatedIds).')';
			}
		 }
	}
	function onCountProductMassFilterproduct_optionColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessProductMassFilterproduct_optionColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_product.product_id'));
	}
	function onProcessProductMassFiltercsvImport(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		if(count($elements)){
			foreach($elements as $k => $element){
				if($element->$filter['type']!=$filter['value']) unset($elements[$k]);
			}
		}else{
			$data = $this->massaction->getFromFile($filter);
			if(empty($data->ids) || is_null($data->ids) || isset($data->error)){
				$query->where[] = '1=2';
				return false;
			}

			JArrayHelper::toInteger($data->ids);
			$db = JFactory::getDBO();

			$productClass = hikashop_get('class.product');
			$productClass->getProducts($data->ids);

			$pool = array();
			foreach($data->ids as $i => $id){
				if(!isset($data->elements[$id]->product_id) || !isset($data->elements[$id]->product_code) || !isset($data->elements[$id]->product_parent_id)) continue;
				$pool[$data->elements[$id]->product_code] = array();
				$pool[$data->elements[$id]->product_code]['id'] = $data->elements[$id]->product_id;
				$pool[$data->elements[$id]->product_code]['parent_id'] = $data->elements[$id]->product_parent_id;
			}
			$toGet = array();
			foreach($data->ids as $i => $id){
				if(isset($data->elements[$id]->product_parent_id) && $data->elements[$id]->product_parent_id != '' && (int)$data->elements[$id]->product_parent_id == 0 && !in_array($data->elements[$id]->product_parent_id,$pool) && !in_array($data->elements[$id]->product_parent_id,$toGet)){
					$toGet[] = $data->elements[$id]->product_parent_id;
				}
			}
			$db->setQuery('SELECT * FROM '.hikashop_table('product').' WHERE product_code IN (\''.implode('\',\'',$toGet).'\')');
			$gets = $db->loadObjectList();
			foreach($gets as $get){
				$pool[$get->product_code]['id'] = $get->product_id;
				$pool[$get->product_code]['parent_id'] = $get->product_parent_id;
			}

			if(!is_array($data->ids)) $data->ids = array($data->ids);

			foreach($data->ids as $i => $id){
				if(!isset($data->elements[$id])) continue;
				$data->elements[$id]->product_id = (int)$data->elements[$id]->product_id;

				if(isset($data->elements[$id]->product_type) && $data->elements[$id]->product_type == 'variant' && (int)$data->elements[$id]->product_parent_id == 0){
					if(array_key_exists($data->elements[$id]->product_parent_id,$pool)){
						$data->elements[$id]->product_parent_id = $pool[$data->elements[$id]->product_parent_id]['id'];
					}else{
						continue;
					}
				}

				$oldElement = $productClass->all_products[$id];

				if(!empty($data->elements[$id]->price_value_with_tax)){
					$currencyHelper = hikashop_get('class.currency');
					if(empty($data->elements[$id]->product_tax_id)){
						if(!empty($oldElement->product_tax_id)){
							$data->elements[$id]->product_tax_id = $oldElement->product_tax_id;
						}else{
							$data->elements[$id]->product_tax_id = $currencyHelper->getTaxCategory();
						}
					}

					if($data->elements[$id]->product_tax_id){
						if(strpos($data->elements[$id]->price_value_with_tax,'|')===false){
							$data->elements[$id]->price_value = $currencyHelper->getUntaxedPrice(hikashop_toFloat($data->elements[$id]->price_value_with_tax),hikashop_getZone(),$data->elements[$id]->product_tax_id);
						}else{
							$price_value = explode('|',$data->elements[$id]->price_value_with_tax);
							foreach($price_value as $k => $price_value_one){
								$price_value[$k] = $currencyHelper->getUntaxedPrice($price_value_one,hikashop_getZone(),$data->elements[$id]->product_tax_id);
							}
							$data->elements[$id]->price_value = implode('|',$price_value);
						}
					}
					unset($data->elements[$id]->price_value_with_tax);
				}
				if(!empty($data->elements[$id]->price_value)){
					$data->elements[$id]->prices = array();
					$price_values = explode('|',$data->elements[$id]->price_value);
					$price_currencies = explode('|',$data->elements[$id]->price_currency_id);
					$price_min_quantities = explode('|',$data->elements[$id]->price_min_quantity);
					$price_accesses = explode('|',$data->elements[$id]->price_access);
					foreach($price_values as $k => $price_value){
						$data->elements[$id]->prices[$k] = new stdClass();
						$data->elements[$id]->prices[$k]->price_value = $price_value;
					}
					foreach($price_currencies as $k => $price_currency){
						if(!is_int($price_currency)){
							$db->setQuery('SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_code LIKE '.$db->quote($price_currency));
							$price_currency_id = $db->loadResult();
						}
						$data->elements[$id]->prices[$k]->price_currency_id = $price_currency_id;
					}
					foreach($price_min_quantities as $k => $price_min_quantity){
						$data->elements[$id]->prices[$k]->price_min_quantity = $price_min_quantity;
					}
					foreach($price_accesses as $k => $price_access){
						$data->elements[$id]->prices[$k]->price_access = $price_access;
					}
				}
				unset($data->elements[$id]->price_value);
				unset($data->elements[$id]->price_currency_id);
				unset($data->elements[$id]->price_min_quantity);
				unset($data->elements[$id]->price_access);

				if(!empty($data->elements[$id]->files)){
					$newFiles = explode(';',$data->elements[$id]->files);
					$data->elements[$id]->files = array();
					foreach($newFiles as $k => $newFile){
						$same = 0;
						foreach($oldElement->files as $oldFile){
							if($oldFile->file_path == $newFile){
								$data->elements[$id]->files[] = $oldFile->file_id;
								$same = 1;
							}
						}
						if(!empty($newFile) && $same == 0){
							$db->setQuery('SELECT file_id FROM '.hikashop_table('file').' WHERE file_path LIKE '.$db->quote($newFile));
							$newFileId = $db->loadResult();
							if(!empty($newFileId))
								$data->elements[$id]->files[] = $newFileId;
						}
					}
				}
				if(!empty($data->elements[$id]->images)){
					$newImages = explode(';',$data->elements[$id]->images);
					$data->elements[$id]->images = array();
					foreach($newImages as $k => $newImage){
						$same = 0;
						foreach($oldElement->images as $oldImage){
							if(!empty($newImage) && $oldImage->file_path == $newImage){
								$data->elements[$id]->images[] = $oldImage->file_id;
								$same = 1;
							}
						}
						if(!empty($newImage) && $same == 0){
							$db->setQuery('SELECT file_id FROM '.hikashop_table('file').' WHERE file_path LIKE '.$db->quote($newImage));
							$newImageId = $db->loadResult();
							if(!empty($newImageId))
								$data->elements[$id]->images[] = $newImageId;
						}
					}
				}
				if(!empty($data->elements[$id]->categories)){
					$categories = explode(';',$data->elements[$id]->categories);

					$data->elements[$id]->categories = array();
					foreach($categories as $category){
						$db->setQuery('SELECT category_id FROM '.hikashop_table('category').' WHERE category_name LIKE '.$db->quote($category));
						$data->elements[$id]->categories[] = $db->loadResult();
					}
				}
				if(!empty($data->elements[$id]->categories_ordering)){
					$data->elements[$id]->categories_ordering = explode(';',$data->elements[$id]->categories_ordering);
					unset($oldElement->categories_ordering);
				}

				if(!empty($data->elements[$id]->related)){
					$relateds = explode(';',$data->elements[$id]->related);
					$data->elements[$id]->related = array();
					foreach($relateds as $k => $related){
						$db->setQuery('SELECT product_id FROM '.hikashop_table('product').' WHERE product_code LIKE '.$db->quote($related));
						$data->elements[$id]->related[$k]->product_related_id = $db->loadResult();
						$data->elements[$id]->related[$k]->product_id = $id;
						$data->elements[$id]->related[$k]->product_related_ordering = $k;
						$data->elements[$id]->related[$k]->product_related_type = 'related';
					}
				}
				if(!empty($data->elements[$id]->options)){
					$options = explode(';',$data->elements[$id]->options);
					$data->elements[$id]->options = array();
					foreach($options as $option){
						$db->setQuery('SELECT product_id FROM '.hikashop_table('product').' WHERE product_code LIKE '.$db->quote($option));
						$data->elements[$id]->options[$k]->product_related_id = $db->loadResult();
						$data->elements[$id]->options[$k]->product_id = $id;
						$data->elements[$id]->options[$k]->product_related_ordering = $k;
						$data->elements[$id]->options[$k]->product_related_type = 'related';
					}
				}
				unset($oldElement->images);
				unset($oldElement->files);
				unset($oldElement->related);
				unset($oldElement->options);

				foreach($oldElement as $k => $old){
					foreach($data->elements[$id] as $l => $new){
						if($k == $l){
							$oldElement->$k = $new;
						}
					}

				}
				$data->elements[$id] = $oldElement;

				if(isset($filter['save'])){
					if(isset($data->elements[$id]->prices)){
						$productClass->updatePrices($data->elements[$id],$id);
					}
					if($data->elements[$id]->product_type!='variant')
						$productClass->updateCategories($data->elements[$id],$id);
					if(!empty($data->elements[$id]->files))
						$productClass->updateFiles($data->elements[$id],$id,'files');
					if(!empty($data->elements[$id]->images))
						$productClass->updateFiles($data->elements[$id],$id,'images');
					if(!empty($data->elements[$id]->related))
						$productClass->updateRelated($data->elements[$id],$id,'related');
					if(!empty($data->elements[$id]->options))
						$productClass->updateRelated($data->elements[$id],$id,'options');

					unset($oldElement->categories);

					$db->setQuery('SHOW columns FROM '.hikashop_table('product'));
					$cols = $db->loadObjectList();
					$fields = array();
					foreach($cols as $col){
						$fields[] = $col->Field;
					}
					foreach($data->elements[$id] as $l => $content){
						if(!in_array($l,$fields)){
							unset($data->elements[$id]->$l);
						}
					}
					if(isset($data->elements[$id]->product_created) && (int)$data->elements[$id]->product_created == 0){
						unset($data->elements[$id]->product_created);
					}
					$data->elements[$id]->product_modified = time();
					$data->elements[$id]->product_width = str_replace($data->elements[$id]->product_dimension_unit,'',$data->elements[$id]->product_width);
					$data->elements[$id]->product_length = str_replace($data->elements[$id]->product_dimension_unit,'',$data->elements[$id]->product_length);
					$data->elements[$id]->product_height = str_replace($data->elements[$id]->product_dimension_unit,'',$data->elements[$id]->product_height);

					$elements = $data->elements;

					$productClass->save($elements[$id]);
				}
			}

			$elements = $data->elements;

			if($filter['type'] == 'out'){
				JArrayHelper::toInteger($data->ids);
				$db->setQuery('SELECT product_id FROM '.hikashop_table('product').' WHERE product_id NOT IN ('.implode(',',$data->ids).')');
				$ids = $db->loadResultArray();
				$productClass = hikashop_get('class.product');
				$elements = array();
				foreach($ids as $id){
					$elements[] = $productClass->get($id);
				}
			}
		}
		return 'onProcessProductMassFiltercsvImport called';
	}
	function onCountProductMassFiltercsvImport(&$query,$filter,$num){
		return '';
	}
	function onProcessProductMassFilterproductType(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(count($elements)){
			foreach($elements as $k => $element){
				if($element->product_type!=$filter['type']) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			$query->where[] = 'hk_product.product_type='.$db->Quote($filter['type']);
		 }
	}
	function onCountProductMassFilterproductType(&$query,$filter,$num){
		$elements = array();
		$this->onProcessProductMassFilterproductType($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_product.product_id'));
	}
	function onProcessProductMassFilteraccessLevel(&$elements,&$query,$filter,$num){
		$this->massaction->_onProcessMassFilteraccessLevel($elements,$query,$filter,$num,'product');
	}
	function onCountProductMassFilteraccessLevel(&$query,$filter,$num){
		$elements = array();
		$this->onProcessProductMassFilteraccessLevel($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_product.product_id'));
	}

	function onProcessProductMassActiondisplayResults(&$elements,&$action,$k){
		$params = $this->massaction->_displayResults('product',$elements,$action,$k);
		$params->action_id = $k;
		$js = '';
		$app = JFactory::getApplication();
		if($app->isAdmin() && JRequest::getVar('ctrl','massaction') == 'massaction'){
			echo hikashop_getLayout('massaction','results',$params,$js);
		}
	}
	function onProcessProductMassActionexportCsv(&$elements,&$action,$k){
		$formatExport = $action['formatExport']['format'];
		$path = $action['formatExport']['path'];
		$email = $action['formatExport']['email'];
		if(!empty($path)){
			$url = $this->massaction->setExportPaths($path);
		}else{
			$url = array('server'=>'','web'=>'');
			ob_get_clean();
		}
		$app = JFactory::getApplication();
		if($app->isAdmin() || (!$app->isAdmin() && !empty($path))){
			$action['product']['product_id'] = 'product_id';
			unset($action['formatExport']);
			$params = $this->massaction->_displayResults('product',$elements,$action,$k);
			$params->formatExport = $formatExport;
			$params->path = $url['server'];
			$params = $this->massaction->sortResult($params->table,$params);
			$this->massaction->_exportCSV($params);
		}

		if(!empty($email) && !empty($path)){
			$config = hikashop_config();
			$mailClass = hikashop_get('class.mail');
			$content = array('type' => 'csv_export');
			$mail = $mailClass->get('massaction_notification',$content);
			$mail->subject = JText::_('MASS_CSV_EMAIL_SUBJECT');
			$mail->html = '1';
			$csv = new stdClass();
			$csv->name = basename($path);
			$csv->filename = basename($path);
			$csv->url = $url['web'];
			$mail->attachments = array($csv);
			$mail->dst_name = '';
			$mail->dst_email = explode(',',$email);
			$mailClass->sendMail($mail);
		}
	}
	function onProcessProductMassActionupdateValues(&$elements,&$action,$k){
		$current = 'product';
		$current_id = $current.'_id';
		$ids = array();
		foreach($elements as $element){
			$ids[] = $element->$current_id;
			if(isset($element->$action['type']))
				$element->$action['type'] = $action['value'];

		}

		$action['type'] = strip_tags($action['type']);
		$alias = explode('_',$action['type']);
		$queryTables = array($current);
		$possibleTables = array($current, 'price');

		$value = $this->massaction->updateValuesSecure($action,$possibleTables,$queryTables);

		if(!empty($queryTables)){
			$query = 'UPDATE '.hikashop_table($current).' AS hk_'.$current.' ';
			$queryTables = array_unique($queryTables);
			foreach($queryTables as $queryTable){
				switch($queryTable){
					case 'price':
					case 'hk_price':
						$query .= 'LEFT JOIN '.hikashop_table('price').' AS hk_price ON hk_price.price_product_id = hk_product.product_id ';
						break;
				}
			}
			if(!in_array($alias[0],array('product','price'))){
				$hk = 'product';
			}else{
				$hk = $alias[0];
			}
			$db = JFactory::getDBO();
			JArrayHelper::toInteger($ids);

			$max = 500;
			if(count($ids) > $max){
				$c = ceil((int)count($ids) / $max);
				$mainQuery = $query;
				for($i = 0; $i < $c; $i++){
					$offset = $max * $i;
					$id = array_slice($ids, $offset, $max);
					$query = $mainQuery.' SET hk_'.$hk.'.'.$action['type'].' = '.$value.' ';
					$query .= 'WHERE hk_'.$current.'.'.$current.'_id IN ('.implode(',',$id).')';
					$db->setQuery($query);
					$db->query();
				}
			}else{
				$query .= 'SET hk_'.$hk.'.'.$action['type'].' = '.$value.' ';
				$query .= 'WHERE hk_'.$current.'.'.$current.'_id IN ('.implode(',',$ids).')';
				$db->setQuery($query);
				$db->query();
			}
			if($hk == 'price'){
				$db->setQuery('SELECT price_product_id FROM '.hikashop_table('price').' WHERE `price_product_id` IN ('.implode(',',$ids).')');
				$existingIds = $db->loadObjectList();
				foreach($existingIds as $k => $existingId){
					$existingIds[$k] = $existingId->price_product_id;
				}
				if(count($existingIds) < count($ids)){
					if(!is_array($existingIds)) $existingIds = array($existingIds);
					$missingInsert = array_diff($ids,$existingIds);
					if(!empty($missingInsert)){
						$values = array();
						foreach($missingInsert as $id){
							$data = new stdClass();
							$data->price_currency_id = hikashop_getCurrency();
							$data->price_product_id = $id;
							$data->price_value = 0;
							$data->price_min_quantity = 0;
							$data->price_access = 'all';
							$data->$action['type'] = $action['value'];
							$values[] = (int)$data->price_currency_id.','.(int)$data->price_product_id.','.(float)$data->price_value.','.(int)$data->price_min_quantity.','.$db->quote($data->price_access);
						}
						$query = 'INSERT INTO '.hikashop_table('price').' (price_currency_id,price_product_id,price_value,price_min_quantity,price_access) VALUES ('.implode('),(',$values).')';
						$db->setQuery($query);
						$db->query();
					}
				}
			}
		}
	}
	function onProcessProductMassActiondeleteElements(&$elements,&$action,$k, $start = 0){
		$ids = array();
		$i = 0;
		$productClass = hikashop_get('class.product');


		foreach($elements as $element){
			$nb = $i - $start;
			if($nb >= 0 && $nb < 500){
				$ids[] = $element->product_id;
				$i++;
			}elseif($nb >= 500){
				$result = $productClass->delete($ids);
				$start = $start + 500;
				$this->onProcessProductMassActiondeleteElements($elements,$action,$k, $start);
			}
		}
		$productClass->delete($ids);
	}
	function onProcessProductMassActionupdateCategories(&$elements,&$action,$k){
		$db = JFactory::getDBO();
		$ids = array();
		foreach($elements as $element){
			if(isset($element->categories)){
				if($action['type'] == 'add'){
					$element->categories = array_unique(array_merge($element->categories,$action['value']));
				}else{
					$element->categories = $action['value'];
				}
			}
			$ids[]=(int)$element->product_id;
		}
		$max = 500;
		if($action['type'] == 'remove'){
			$deleteQuery = 'DELETE FROM '.hikashop_table('product_category').' WHERE category_id IN('.implode(',',$action['value']).') AND product_id IN(';
			if(count($ids) > $max){
				$c = ceil((int)count($ids) / $max);
				for($i = 0; $i < $c; $i++){
					$offset = $max * $i;
					$id = array_slice($ids, $offset, $max);
					$deleteQuery = $deleteQuery . implode(',',$id) .')';
					$db->setQuery($deleteQuery);
					$db->query();
				}
			}else{
				$deleteQuery = $deleteQuery . implode(',',$ids) .')';
				$db->setQuery($deleteQuery);
				$db->query();
			}
		}else{
			if(count($ids) > $max){
				$c = ceil((int)count($ids) / $max);
				$existings = array();
				$existing = array();
				for($i = 0; $i < $c; $i++){
					$offset = $max * $i;
					$id = array_slice($ids, $offset, $max);
					$db->setQuery('SELECT * FROM '.hikashop_table('product_category').' WHERE product_id IN('.implode(',',$id).')');
					$existings[] = $db->loadObjectList();
				}
				foreach($existings as $children){
					$existing = array_merge($existing, $children);
				}
			}else{
				$db->setQuery('SELECT * FROM '.hikashop_table('product_category').' WHERE product_id IN('.implode(',',$ids).')');
				$existing = $db->loadObjectList();
			}

			$deleteQuery = 'DELETE FROM '.hikashop_table('product_category').' WHERE product_id IN(';
			$deleteIds = array();
			$insertQuery = 'INSERT INTO '.hikashop_table('product_category').' (category_id, product_id) VALUES';
			$insertValues = array();
			foreach($elements as $element){
				if($element->product_type != 'main') continue;
				if($action['type'] == 'replace'){
					$deleteIds[] = (int)$element->product_id;
				}
				if(!isset($action['value'])) continue;
				foreach($action['value'] as $k => $category){
					$insert = true;
					foreach($existing as $existingEntry){
						if($action['type'] == 'add' && $existingEntry->category_id == $category && $existingEntry->product_id == $element->product_id){
							$insert = false;
						}
					}
					if($insert){
						 $insertValues[] = '('.(int)$category.','.(int)$element->product_id.')';
					}
				}
			}
			if(!empty($deleteIds)){
				if(count($deleteIds) > $max){
					$c = ceil((int)count($deleteIds) / $max);
					for($i = 0; $i < $c; $i++){
						$offset = $max * $i;
						$id = array_slice($deleteIds, $offset, $max);
						$deleteQuery = $deleteQuery . implode(',',$id) .')';
						$db->setQuery($deleteQuery);
						$db->query();
					}
				}else{
					$deleteQuery = $deleteQuery . implode(',',$deleteIds) .')';
					$db->setQuery($deleteQuery);
					$db->query();
				}
			}
			if(!empty($insertValues)){
				if(count($insertValues) > $max){
					$c = ceil((int)count($insertValues) / $max);
					for($i = 0; $i < $c; $i++){
						$offset = $max * $i;
						$id = array_slice($insertValues, $offset, $max);
						$insertQuery = $insertQuery . implode(',',$id);
						$db->setQuery($insertQuery);
						$db->query();
					}
				}else{
					$insertQuery = $insertQuery . implode(',',$insertValues);
					$db->setQuery($insertQuery);
					$db->query();
				}
			}
		}
	}

	function onProcessProductMassActionupdateRelateds(&$elements,&$action,$k){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM '.hikashop_table('product_related').' WHERE  product_related_type ='.$db->quote('related'));
		$existing = $db->loadObjectList();

		$deleteQuery = 'DELETE FROM '.hikashop_table('product_related').' WHERE product_related_type = '.$db->quote('related').' AND product_id IN (';
		$deleteIds = array();

		$insertQuery = 'INSERT INTO '.hikashop_table('product_related').' (product_id, product_related_id, product_related_type) VALUES ';
		$insertValues = array();

		foreach($elements as $element){
			if($element->product_type != 'main') continue;
			if($action['type'] == 'replace'){
				$deleteIds[] = (int)$element->product_id;
			}
			if(!isset($action['value'])) continue;
			foreach($action['value'] as $related){
				$insert = true;
				foreach($existing as $existingEntry){
					if($action['type'] == 'add' && $existingEntry->product_related_id == $related && $existingEntry->product_id == $element->product_id){
						$insert = false;
					}
				}
				if($insert){
					$insertValues[] = '('.(int)$element->product_id.','.(int)$related.','.$db->quote('related').')';
				}
			}
		}
		$max = 500;
		if(!empty($deleteIds)){
			JArrayHelper::toInteger($deleteIds);
			if(count($deleteIds) > $max){
				$c = ceil((int)count($deleteIds) / $max);
				for($i = 0; $i < $c; $i++){
					$offset = $max * $i;
					$id = array_slice($deleteIds, $offset, $max);
				$deleteQuery = $deleteQuery . implode(',',$id) .')';
				$db->setQuery($deleteQuery);
				$db->query();
				}
			}else{
				$deleteQuery = $deleteQuery . implode(',',$deleteIds) .')';
				$db->setQuery($deleteQuery);
				$db->query();
			}
		}
		if(!empty($insertValues)){
			if(count($insertValues) > $max){
				$c = ceil((int)count($insertValues) / $max);
				for($i = 0; $i < $c; $i++){
					$offset = $max * $i;
					$id = array_slice($insertValues, $offset, $max);
					$insertQuery = $insertQuery . implode(',',$id);
					$db->setQuery($insertQuery);
					$db->query();
				}
			}else{
				$insertQuery = $insertQuery . implode(',',$insertValues);
				$db->setQuery($insertQuery);
				$db->query();
			}
		}
	}

	function onProcessProductMassActionupdateOptions(&$elements,&$action,$k){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM '.hikashop_table('product_related').' WHERE product_related_type = '.$db->quote('options'));
		$existing = $db->loadObjectList();

		$deleteQuery = 'DELETE FROM '.hikashop_table('product_related').' WHERE product_related_type = '.$db->quote('options').' AND product_id IN (';
		$deleteIds = array();

		$insertQuery = 'INSERT INTO '.hikashop_table('product_related').' (product_id, product_related_id, product_related_type) VALUES ';
		$insertValues = array();

		foreach($elements as $element){
			if($element->product_type != 'main') continue;
			if($action['type'] == 'replace'){
				$deleteIds[] = (int)$element->product_id;
			}
			if(!isset($action['value'])) continue;
			foreach($action['value'] as $option){
				$insert = true;
				foreach($existing as $existingEntry){
					if($action['type'] == 'add' && $existingEntry->product_related_id == $option && $existingEntry->product_id == $element->product_id){
						$insert = false;
					}
				}
				if($insert){
					$insertValues[] = '('.(int)$element->product_id.','.(int)$option.','.$db->quote('options').')';
				}
			}
		}
		$max = 500;
		if(!empty($deleteIds)){
			JArrayHelper::toInteger($deleteIds);
			if(count($deleteIds) > $max){
				$c = ceil((int)count($deleteIds) / $max);
				for($i = 0; $i < $c; $i++){
					$offset = $max * $i;
					$id = array_slice($deleteIds, $offset, $max);
				$deleteQuery = $deleteQuery . implode(',',$id) .')';
				$db->setQuery($deleteQuery);
				$db->query();
				}
			}else{
				$deleteQuery = $deleteQuery . implode(',',$deleteIds) .')';
				$db->setQuery($deleteQuery);
				$db->query();
			}
		}
		if(!empty($insertValues)){
			if(count($insertValues) > $max){
				$c = ceil((int)count($insertValues) / $max);
				for($i = 0; $i < $c; $i++){
					$offset = $max * $i;
					$id = array_slice($insertValues, $offset, $max);
					$insertQuery = $insertQuery . implode(',',$id);
					$db->setQuery($insertQuery);
					$db->query();
				}
			}else{
				$insertQuery = $insertQuery . implode(',',$insertValues);
				$db->setQuery($insertQuery);
				$db->query();
			}
		}
	}
	function onProcessProductMassActionupdateCharacteristics(&$elements,&$action,$k){
		$enqueueMessage = '';
		$characteristics = array();
		$i = 0;
		$db = JFactory::getDBO();
		if($action['type'] == 'add'){
			foreach($action['value'] as $value){
				$db->setQuery('SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id = '.$value);
				$childrens = $db->loadObjectList();
				if(!isset($childrens[0])) continue;

				$characteristics[$i] = new stdClass();
				$characteristics[$i]->characteristic_id = (int)$value;
				$characteristics[$i]->ordering = 0;
				$characteristics[$i]->default_id = (int)$childrens[0]->characteristic_id;
				$characteristics[$i]->values = array();

				foreach($childrens as $children){
					$characteristics[$i]->values[$children->characteristic_id] = $children->characteristic_value;
				}
				$i++;
			}
		}else{
			foreach($action['value'] as $value){
				$characteristics[$i] = $value;
				$i++;
			}
		}
		$j = 0;
		$productClass = hikashop_get('class.product');
		foreach($elements as $element){
			if($element->product_type != 'main'){
				$enqueueMessage = 'ADD_CHARACTERISTICS_TO_MAIN_PRODUCTS_ONLY';
				continue;
			}
			$db->setQuery('SELECT b.characteristic_id FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id = b.characteristic_id WHERE b.characteristic_parent_id = 0 AND a.variant_product_id ='.$element->product_id);
			$currents = $db->loadObjectList();
			$oldCharacteristics = array();
			foreach($currents as $current){
				$db->setQuery('SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id = '.$current->characteristic_id);
				$childrens = $db->loadObjectList();
				if(!isset($childrens[0])) continue;

				$oldCharacteristics[$j] = new stdClass();
				$oldCharacteristics[$j]->characteristic_id = (int)$current->characteristic_id;
				$oldCharacteristics[$j]->ordering = 0;
				$oldCharacteristics[$j]->default_id = (int)$childrens[0]->characteristic_id;
				$oldCharacteristics[$j]->values = array();
				foreach($childrens as $children){
					$oldCharacteristics[$j]->values[$children->characteristic_id] = $children->characteristic_value;
				}
				$j++;
			}

			if($action['type'] == 'add'){
				if($characteristics == $oldCharacteristics) continue;
				if(!isset($element->characteristics)) $element->characteristics = array();
				$element->characteristics = array_merge($element->characteristics, $characteristics);
				$element->characteristics = array_merge($element->characteristics, $oldCharacteristics);
				$element->oldCharacteristics = array();
				$keys = array();
				$values = array();
				foreach($element->characteristics as $characteristic){
					$keys[] = $characteristic->characteristic_id;
					$values[] = $characteristic;
				}
				$element->characteristics = array_combine($keys,$values);
			}else{
				foreach($oldCharacteristics as $l => $oldCharacteristic){
					if(in_array($oldCharacteristic->characteristic_id,$characteristics)){
						unset($oldCharacteristics[$l]);
					}
				}
				$element->oldCharacteristics = $characteristics;
				$element->characteristics = $oldCharacteristics;
			}

			$productClass->updateCharacteristics($element, $element->product_id);
			unset($element);
		}
		if(!empty($enqueueMessage)){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_($enqueueMessage));
		}
	}

	function onProcessProductMassActionsendEmail(&$elements,&$action,$k){
		if(!empty($action['emailAddress'])){
			$config = hikashop_config();
			$mailClass = hikashop_get('class.mail');
			$content = array('elements' => $elements, 'action' => $action, 'type' => 'product_notification');
			$mail = $mailClass->get('massaction_notification',$content);
			$mail->subject = !empty($action['emailSubject'])?JText::_($action['emailSubject']):JText::_('MASS_NOTIFICATION_EMAIL_SUBJECT');
			$mail->body = $action['bodyData'];
			$mail->html = '1';
			$mail->dst_name = '';
			if(!empty($action['emailAddress']))
				$mail->dst_email = explode(',',$action['emailAddress']);
			else
				$mail->dst_email = $config->get('from_email');
			$mailClass->sendMail($mail);
		}
	}

	function onBeforeProductCreate(&$element,&$do){
		$elements = array($element);
		$this->massaction->trigger('onBeforeProductCreate',$elements);
	}

	function onAfterProductCreate(&$element){
		$elements = array($element);
		$this->massaction->trigger('onAfterProductCreate',$elements);
	}

	function onBeforeProductUpdate(&$element,&$do){
		$getProduct = $this->product->get($element->product_id);

		$product = clone $element;
		if(!empty($getProduct) && is_object($getProduct)){
			foreach(get_object_vars($getProduct) as $key => $value){
				if(!isset($product->$key) || $product->$key != $value)
					$product->$key = $value;
			}

			foreach(get_object_vars($element) as $key => $value){
				if($product->$key != $value)
					$product->$key = $value;
			}
		}

		$products = array($product);
		$this->massaction->trigger('onBeforeProductUpdate',$products);
	}

	function onAfterProductUpdate(&$element){
		$getProduct = $this->product->get($element->product_id);

		$product = clone $element;
		if(!empty($getProduct) && is_object($getProduct)){
			foreach(get_object_vars($getProduct) as $key => $value){
				if(!isset($product->$key) || $product->$key != $value)
					$product->$key = $value;
			}

			foreach(get_object_vars($element) as $key => $value){
				if($product->$key != $value)
					$product->$key = $value;
			}
		}

		$products = array($product);
		$this->massaction->trigger('onAfterProductUpdate',$products);
	}

	function onBeforeProductDelete(&$ids,&$do){
		$products = array();
		if(!is_array($ids)) $clone = array($ids);
		else $clone = $ids;
		foreach($clone as $id){
			$products[$id] = $this->product->get($id);
		}
		$this->deletedProducts =& $products;
		$this->massaction->trigger('onBeforeProductDelete',$products);
	}

	function onAfterProductDelete(&$ids){
		$this->massaction->trigger('onAfterProductDelete',$this->deletedProducts);
	}

	function onBeforeProductCopy(&$template,&$product,&$do){
		$products = array($product);
		$this->massaction->trigger('onBeforeProductCopy',$products);
	}

	function onAfterProductCopy(&$template,&$product){
		$products = array($product);
		$this->massaction->trigger('onAfterProductCopy',$products);
	}
}

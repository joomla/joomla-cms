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
jimport( 'joomla.application.component.view');
class ProductViewProduct  extends HikaShopView
{
	function display($tpl = null)
		{

		global $mainframe;
		global $Itemid;

		$db			= JFactory::getDBO();
		$app = JFactory::getApplication();
		$this->params = $app->getParams();
		$doc	= JFactory::getDocument();
		$menus	= $app->getMenu();
		$menu	= $menus->getActive();
		$config =& hikashop_config();
		if(empty($menu) && !empty($Itemid)){
			$menus->setActive($Itemid);
			$menu	= $menus->getItem($Itemid);
		}
		$myItem = empty($Itemid) ? '' : '&Itemid='.$Itemid;
		if (is_object( $menu )) {
			jimport('joomla.html.parameter');
			$menuparams = new HikaParameter( $menu->params );
		}
		$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_access=\'all\' AND product_published=1 AND product_type=\'main\' ';
		if(!$config->get('show_out_of_stock',1)){
			$query.=' AND product_quantity!=0 ';
		}
			$query .= ' ORDER BY '.$config->get('hikarss_order','product_id').' DESC';
		$query .= ' LIMIT '.$config->get('hikarss_element','10');
			$db->setQuery($query);
			$products = $db->loadObjectList();
			if(!empty($products)){
					$ids = array();
					$productClass = hikashop_get('class.product');
					foreach($products as $key => $row){
						$ids[]=$row->product_id;
						$productClass->addAlias($products[$key]);
					}
					$queryCategoryId='SELECT * FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',',$ids).')';
					$db->setQuery($queryCategoryId);
					$categoriesId = $db->loadObjectList();
					foreach($products as $k=>$row){
						foreach($categoriesId as $catId){
							if($row->product_id==$catId->product_id){
								$products[$k]->categories_id[0]=$catId->category_id;
							}
						}
					}
					$queryImage = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type=\'product\' ORDER BY file_ref_id ASC, file_ordering ASC, file_id ASC';
					$db->setQuery($queryImage);
					$images = $db->loadObjectList();
					foreach($products as $k => $row){
						foreach($images as $image){
							if($row->product_id == $image->file_ref_id){
								foreach(get_object_vars($image) as $key => $name){
									if(empty($products[$k]->images))
										$products[$k]->images = array();
									if(empty($products[$k]->images[0]))
										$products[$k]->images[0] = new stdClass();
									$products[$k]->images[0]->$key = $name;
								}
								break;
							}
						}
					}
					$db->setQuery('SELECT * FROM '.hikashop_table('variant').' WHERE variant_product_id IN ('.implode(',',$ids).')');
					$variants = $db->loadObjectList();
					if(!empty($variants)){
						foreach($products as $k => $product){
							foreach($variants as $variant){
								if($product->product_id==$variant->variant_product_id){
									$products[$k]->has_options = true;
									break;
								}
							}
						}
					}
			}
			else{
					return true;
			}
			$zone_id=hikashop_getZone();
			$currencyClass = hikashop_get('class.currency');
			$config =& hikashop_config();
			$main_currency = (int)$config->get('main_currency',1);
			$currencyClass->getListingPrices($products,$zone_id,$main_currency,'cheapest');

			$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder'))),DS);
			$uploadFolder = rtrim($uploadFolder,DS).DS;
			$this->uploadFolder_url = str_replace(DS,'/',$uploadFolder);
			$this->uploadFolder = JPATH_ROOT.DS.$uploadFolder;
			$app = JFactory::getApplication();
			$this->thumbnail = $config->get('thumbnail',1);
			$this->thumbnail_y = $config->get('product_image_y',$config->get('thumbnail_y'));
			$this->thumbnail_x = $config->get('product_image_x',$config->get('thumbnail_x'));
			$this->main_thumbnail_x=$this->thumbnail_x;
			$this->main_thumbnail_y=$this->thumbnail_y;
			$this->main_uploadFolder_url = $this->uploadFolder_url;
			$this->main_uploadFolder = $this->uploadFolder;

		$doc_description = $config->get('hikarss_description','');
		$doc_title = $config->get('hikarss_name','');
		if(!empty($doc_title)){
			$doc->title = $doc_title;
		}
		if(!empty($doc_description)){
			$doc->description = $doc_description;
		}


		$imageHelper = hikashop_get('helper.image');
		foreach ( $products as $product )
		{
			$title = $this->escape( $product->product_name );
			$title = html_entity_decode( $title );
			$pathway_sef_name = $config->get('pathway_sef_name','category_pathway');
			$link = JURI::base().'index.php?option=com_hikashop&amp;ctrl=product&amp;task=show&amp;cid='.$product->product_id.'&amp;name='.$product->alias.'&amp;Itemid='.$Itemid.'&amp;'.$pathway_sef_name.'='.@$product->category_id;

				if(!empty($product->prices) && $product->prices[0]->price_value_with_tax != 0 ){
					$desc = $product->product_description.JText::_('CART_PRODUCT_PRICE').' : '.$currencyClass->format($product->prices[0]->price_value_with_tax,$product->prices[0]->price_currency_id);
				}
				else{
					$desc= $product->product_description.JText::_('FREE_PRICE');
				}
				$desc = preg_replace('#<hr *id="system-readmore" */>#i','',$desc);

				$image_options = array('default' => true);
				$img = $imageHelper->getThumbnail(@$product->images[0]->file_path, array('width' => $imageHelper->main_thumbnail_x, 'height' => $imageHelper->main_thumbnail_y), $image_options);
				if(substr($img->url, 0, 3) == '../')
					$image = str_replace('../', HIKASHOP_LIVE, $img->url);
				else
					$image = substr(HIKASHOP_LIVE, 0, strpos(HIKASHOP_LIVE, '/', 9)) . $img->url;

				$description = '<table><tr><td><img src="'.$image.'"/></td><td>'.$desc.'</td></tr></table>';
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $product->product_created;
			$item->category   	= @$product->category_id;

			$doc->addItem( $item );
		}

	}
}

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
if(version_compare(JVERSION,'2.5','<')){
	jimport('joomla.html.parameter');
	$params = new JParameter('');
} else {
	$params = new JRegistry('');
}

$doc = JFactory::getDocument();
$app = JFactory::getApplication();
$js = '';
global $Itemid;
$config =& hikashop_config();
$custom_itemid = $this->params->get('itemid');
if($this->quantityfield == 1){
	$params->set('show_quantity_field', 1);
}
$productClass = hikashop_get('class.product');

$thumbnail_x = $config->get('thumbnail_x',100);
$thumbnail_y = $config->get('thumbnail_y',100);
foreach($products as $product) {
	if(in_array($product->product_id,$id)){
		echo'<div class="hikashop_inserted_product" style="text-align:center;">';
		$_SESSION['hikashop_product']=$product;
		if($this->border == 1 ) echo '<div class="hikashop_subcontainer hikashop_subcontainer_border">';
		$productClass->addAlias($product);
		$url_itemid = '';
		if(!empty($custom_itemid)){
			$url_itemid = '&Itemid='.(int)$custom_itemid;
		}
		elseif(!empty($Itemid)){
			$url_itemid = '&Itemid='.(int)$Itemid;
		}
		elseif($this->menuid != 0){
			$url_itemid = '&Itemid='.(int)$this->menuid;
		}
		$link = hikashop_contentLink('product&task=show&cid='.$product->product_id.'&name='.$product->alias.$url_itemid,$product);
		if(!empty($product->product_canonical)){
			$link = hikashop_cleanURL($product->product_canonical);
		}
		if($this->picture == 1) {
?>
			<!-- PRODUCT IMG -->
			<div style="position:relative;text-align:center;clear:both;width:200px;margin: auto;" class="hikashop_product_image">
				<?php if($this->link == 1){
				?>
						<a href="<?php echo $link;?>" title="<?php echo $this->escape($product->product_name); ?>">
				<?php }
					if(!empty($product->images)){
						$image =reset($product->images);
						$image_options = array('default' => true,'forcesize'=>$config->get('image_force_size',true),'scale'=>$config->get('image_scale_mode','inside'));
						$img = $this->image->getThumbnail(@$image->file_path, array('width' => $thumbnail_x, 'height' => $thumbnail_y), $image_options);
						if($img->success) {
							echo '<img class="hikashop_product_tag_image" title="'.$this->escape(@$image->file_description).'" alt="'.$this->escape(@$image->file_name).'" src="'.$img->url.'"/>';
						}
						if($this->badge == 1){
							if(!empty($product->badges))
								$this->classbadge->placeBadges($this->image, $product->badges, '0', '0');
						}
					}
				if($this->link == 1){ ?>
					</a>
				<?php } ?>
			</div>
			<!-- EO PRODUCT IMG -->
<?php
		}
		if($this->pricedis != 0 || $this->pricetax != 0 || $this->price !=0) {
?>
			<!-- PRODUCT PRICE -->
<?php
				if ($this->pricedis == 3){
					$default_params = $config->get('default_params');
					$pricediscount = @$default_params['show_discount'];
				}else $pricediscount = $this->pricedis;
				if($pricediscount == 1) $params->set('show_discount',1);
				if($pricediscount == 2) $params->set('show_discount',2);
				if($this->pricetax == 1) $params->set('price_with_tax',3);
				if($this->pricetax == 2) $params->set('price_with_tax',2);
				if($this->price == 1) $params->set('price_with_tax',3);
				$price = hikashop_getLayout('product','listing_price',$params,$js);
				echo $price;
?>
			<!-- EO PRODUCT PRICE -->
<?php
		}

		if($this->name == 1) {
?>
			<!-- PRODUCT NAME -->
			<span class="hikashop_product_name">
				<?php if($this->link == 1){ ?>
					<a href="<?php echo $link;?>">
				<?php }
					echo $product->product_name;
				if($this->link == 1){ ?>
					</a>
				<?php } ?>
			</span>
			<!-- EO PRODUCT NAME -->
<?php
		}

		if($this->description == 1) {
?>
			<!-- PRODUCT DESCRIPTION -->
			<span class="hikashop_product_description"><?php
				echo $product->product_description;
			?></span>
			<!-- EO PRODUCT DESCRIPTION -->
<?php
		}

		if($this->cart == 1) {
?>
			<!-- ADD TO CART BUTTON AREA -->
			<style type="text/css">
			.hikashop_inserted_product span.hikashop_add_to_cart table { margin: 0 auto; }
			.hikashop_inserted_product .hikashop_product_stock {
				text-align: center;
				display:block;
				margin-bottom:5px;
			}
			.hikashop_inserted_product span.hikashop_add_to_cart{
				text-align: center;
				display:block;
				margin-bottom:5px;
			}
			.hikashop_inserted_product .hikashop_product_quantity_field{
				float: none !important;
				width: 25px !important;
			}
			</style>
			<span class="hikashop_add_to_cart">
			<?php
				$params->set('price_with_tax',$config->get('price_with_tax',1));
				$params->set('add_to_cart',1);
				$add_to_cart = hikashop_getLayout('product','add_to_cart_listing',$params,$js);
				echo $add_to_cart;
			?>
			</span>
			<!-- EO ADD TO CART BUTTON AREA --><?php
		}

		if($this->border == 1 ) echo '</div>';
		echo'</div>';
	}
}

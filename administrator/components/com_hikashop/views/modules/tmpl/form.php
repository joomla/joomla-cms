<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<?php
	$arr = array(
		JHTML::_('select.option', '-1', JText::_('HIKA_INHERIT')),
		JHTML::_('select.option', '1', JText::_('HIKASHOP_YES')),
		JHTML::_('select.option', '0', JText::_('HIKASHOP_NO')),
	);
	$noForm = $this->element->hikashop_params['no_form'];
	$this->controlid = str_replace( array('[',']') , array('','') , $this->control );

if (!$noForm) {
?>
<form action="index.php" method="post"  name="adminForm" id="adminForm">
<?php }
if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-modules">
	<table style="width:100%">
		<tr>
			<td valign="top" width="50%">
<?php } else { ?>
<div id="page-modules" class="row-fluid">
	<div class="span6">
<?php } ?>
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'HIKA_DETAILS' ); ?></legend>

					<table class="admintable table" cellspacing="1">
						<tr>
							<td class="key" valign="top">
									<?php echo JText::_( 'HIKA_TITLE' ); ?>
							</td>
							<td>
								<input class="text_area" type="text" name="module<?php echo $this->control; ?>[title]" id="title" size="35" value="<?php echo $this->escape(@$this->element->title); ?>" />
							</td>
						</tr>
						<tr>
							<td width="100" class="key">
								<?php echo JText::_( 'SHOW_TITLE' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist',  'module'.$this->control.'[showtitle]', 'class="inputbox"', @$this->element->showtitle ); ?>
							</td>
						</tr>
						<tr>
							<td valign="top" class="key">
								<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist',  'module'.$this->control.'[published]', 'class="inputbox"', @$this->element->published ); ?>
							</td>
						</tr>
						<?php if($this->element->module=='mod_hikashop'){ ?>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('TYPE_OF_CONTENT');?>
							</td>
							<td>
								<?php
									$html = $this->contentType->display($this->control.'[content_type]',@$this->element->hikashop_params['content_type'],$this->js,true,'_'.$this->controlid,true);
									if($this->include_module){ //Never done
										echo @$this->element->hikashop_params['content_type'];
										?><input name="<?php echo $this->control; ?>[content_type]" type="hidden" value="<?php echo @$this->element->hikashop_params['content_type'];?>" /><?php
									}else{
										echo $html;
									}
								?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('TYPE_OF_LAYOUT');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['layout_type'])) $this->element->hikashop_params['layout_type'] = 'inherit';
								echo $this->layoutType->display($this->control.'[layout_type]',@$this->element->hikashop_params['layout_type'],$this->js,true,'','_'.$this->controlid,true);?>
							</td>
						</tr>
						<tr id="<?php echo 'number_of_columns_'.$this->controlid ?>">
							<td class="key" valign="top">
								<?php echo JText::_('NUMBER_OF_COLUMNS');?>
							</td>
							<td>
								<input name="<?php echo $this->control; ?>[columns]" type="text" value="<?php echo @$this->element->hikashop_params['columns'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('NUMBER_OF_ITEMS');?>
							</td>
							<td>
								<input name="<?php echo $this->control; ?>[limit]" type="text" value="<?php echo @$this->element->hikashop_params['limit'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('RANDOM_ITEMS');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['random'])) $this->element->hikashop_params['random'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[random]' , '', 'value', 'text', @$this->element->hikashop_params['random']);

								?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ORDERING_DIRECTION');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['order_dir'])) $this->element->hikashop_params['order_dir'] = 'inherit';
								echo $this->orderdirType->display($this->control.'[order_dir]',@$this->element->hikashop_params['order_dir']);?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('SUB_ELEMENTS_FILTER');?>
							</td>
							<td><?php
								if(!isset($this->element->hikashop_params['filter_type'])) $this->element->hikashop_params['filter_type'] = 2;
								echo $this->childdisplayType->display($this->control.'[filter_type]', @$this->element->hikashop_params['filter_type'], true, true, true);
							?></td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ASSOCIATED_CATEGORY');?>
							</td>
							<td>
								<span id="<?php echo str_replace(array('[',']'),'_',$this->control); ?>changeParent">
									<?php echo @$this->element->category->category_id.' '.htmlspecialchars(@$this->element->category->category_name, ENT_COMPAT, 'UTF-8');?>
								</span>
								<input class="inputbox" id="<?php echo str_replace(array('[',']'),'_',$this->control); ?>paramsselectparentlisting" name="<?php echo $this->control;?>[selectparentlisting]" type="hidden" size="20" value="<?php echo @$this->element->hikashop_params['selectparentlisting'];?>">
								<?php
								echo $this->popup->display(
										JText::_('SELECT'),
										'SELECT_A_CATEGORY',
										'\''.hikashop_completeLink('category&task=selectparentlisting&control='.str_replace(array('[',']'),'_',$this->control).'params&id='.str_replace(array('[',']'),'_',$this->control).'changeParent&filter_id=product',true).'&values=\'+document.getElementById(\''.str_replace(array('[',']'),'_',$this->control).'paramsselectparentlisting\').value',
										'linkparamsselectparentlisting',
										860, 480, '', '', 'button',true
									);
								?>
								<a href="#" onclick="document.getElementById('<?php echo str_replace(array('[',']'),'_',$this->control); ?>changeParent').innerHTML='';document.getElementById('<?php echo str_replace(array('[',']'),'_',$this->control); ?>paramsselectparentlisting').value=0;return false;">
									<img src="<?php echo HIKASHOP_IMAGES?>delete.png"/>
								</a>
							</td>
						</tr>
						<?php }else{
						$document= JFactory::getDocument();
						$js = "window.hikashop.ready( function() { hikashopToggleCart(".(int)@$this->element->hikashop_params['small_cart'].");});";
						$document->addScriptDeclaration($js);
							?>
						<tr>
							<td class="key">
								<?php echo JText::sprintf('MINI_CART',$this->type); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', $this->control.'[small_cart]','',@$this->element->hikashop_params['small_cart']);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('IMAGE_IN_CART'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', $this->control.'[image_in_cart]','',@$this->element->hikashop_params['image_in_cart']);?>
							</td>
						</tr>
						<tr id="cart_proceed">
							<td class="key">
								<?php echo JText::_('SHOW_CART_PROCEED'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', $this->control.'[show_cart_proceed]','',@$this->element->hikashop_params['show_cart_proceed']);?>
							</td>
						</tr>
						<tr id="cart_prod_name">
							<td class="key">
								<?php echo JText::_('SHOW_CART_PRODUCT_NAME'); ?>
							</td>
							<td>
								<?php if(!isset($this->element->hikashop_params['show_cart_product_name'])) $this->element->hikashop_params['show_cart_product_name'] = 1;
								echo JHTML::_('hikaselect.booleanlist', $this->control.'[show_cart_product_name]','',$this->element->hikashop_params['show_cart_product_name']);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('SHOW_CART_QUANTITY'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', $this->control.'[show_cart_quantity]','',@$this->element->hikashop_params['show_cart_quantity']);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('SHOW_CART_DELETE'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', $this->control.'[show_cart_delete]','',@$this->element->hikashop_params['show_cart_delete']);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('SHOW_CART_COUPON'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', $this->control.'[show_coupon]','',@$this->element->hikashop_params['show_coupon']);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('SHOW_CART_SHIPPING'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', $this->control.'[show_shipping]','',@$this->element->hikashop_params['show_shipping']);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('EMPTY_CART_MESSAGE_OVERRIDE'); ?>
							</td>
							<td>
								<input name="<?php echo $this->control;?>[msg]" type="text" value="<?php echo @$this->element->hikashop_params['msg'];?>" />
							</td>
						</tr>
						<?php
							if($this->element->module == 'mod_hikashop_wishlist'){
						?>
						<tr>
							<td class="key">
								<?php echo JText::_('CART_MODULE_ITEMID'); ?>
							</td>
							<td>
								<input name="<?php echo $this->control;?>[cart_itemid]" type="text" value="<?php echo @$this->element->hikashop_params['cart_itemid'];?>" />
							</td>
						</tr>
						<?php }
						}
								if($this->element->module=='mod_hikashop'){ ?>
						 <tr>
							<td class="key" valign="top">
								<?php echo JText::_('SYNCHRO_WITH_ITEM');?>
							</td>
							<td>
								<?php
									echo JHTML::_('hikaselect.booleanlist', $this->control.'[content_synchronize]' , '',@$this->element->hikashop_params['content_synchronize']);
								?>
							</td>
						</tr>
						<tr>
							<td class="key" >
							<?php echo JText::_('MENU'); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.genericlist', $this->hikashop_menu, $this->control.'[itemid]' , 'size="1"', 'value', 'text', @$this->element->hikashop_params['itemid']); ?>
							</td>
						</tr>

						<?php } ?>
					</table>
				</fieldset>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td valign="top" width="50%">
<?php } else { ?>
	</div>
	<div class="span6">
<?php } ?>
				<fieldset id="<?php echo 'content_product_'.$this->controlid ?>" class="adminform">
					<legend><?php echo JText::_('PARAMS_FOR_PRODUCTS'); ?></legend>
					<table class="admintable table" cellspacing="1" width="100%">
						<?php if($this->element->module=='mod_hikashop'){ ?>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ORDERING_FIELD');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['product_order'])) $this->element->hikashop_params['product_order'] = 'inherit';
								echo $this->orderType->display($this->control.'[product_order]',@$this->element->hikashop_params['product_order'],'product');?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('CONTENT_ON_PRODUCT_PAGE');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['product_synchronize'])) $this->element->hikashop_params['product_synchronize'] = 4;
								echo $this->productSyncType->display($this->control.'[product_synchronize]' , @$this->element->hikashop_params['product_synchronize']); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('RECENTLY_VIEWED');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['recently_viewed'])) $this->element->hikashop_params['recently_viewed'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[recently_viewed]' , '', 'value', 'text', @$this->element->hikashop_params['recently_viewed']); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ADD_TO_CART_BUTTON');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['add_to_cart'])) $this->element->hikashop_params['add_to_cart'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[add_to_cart]' , '', 'value', 'text', @$this->element->hikashop_params['add_to_cart']); ?>
							</td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('ADD_TO_CART_QUANTITY');?></td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['show_quantity_field'])) $this->element->hikashop_params['show_quantity_field'] = '0';
								echo JHTML::_('hikaselect.booleanlist',  $this->control.'[show_quantity_field]', 'class="inputbox"', @$this->element->hikashop_params['show_quantity_field'] );
								?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ADD_TO_WISHLIST_BUTTON');?>
							</td>
							<td>
								<?php if(hikashop_level(1)){
								if(!isset($this->element->hikashop_params['add_to_wishlist'])) $this->element->hikashop_params['add_to_wishlist'] = '-1';
									echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[add_to_wishlist]' , '', 'value', 'text', @$this->element->hikashop_params['add_to_wishlist']);
								}else{
									$this->element->hikashop_params['add_to_wishlist'] = 0;
									echo hikashop_getUpgradeLink('essential');
								} ?>							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('LINK_TO_PRODUCT_PAGE');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['link_to_product_page'])) $this->element->hikashop_params['link_to_product_page'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[link_to_product_page]' , '', 'value', 'text', @$this->element->hikashop_params['link_to_product_page']); ?>
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('DISPLAY_VOTE');?>
							</td>
							<td>
								<?php
									if(!isset($this->element->hikashop_params['show_vote_product'])) $this->element->hikashop_params['show_vote_product'] = '-1';
									echo JHTML::_('hikaselect.radiolist',  $arr, $this->control.'[show_vote_product]', '', 'value', 'text', @$this->element->hikashop_params['show_vote_product']);
								?>
							</td>
						</tr>
						<tr <?php if($this->element->module!='mod_hikashop'){ ?> id="cart_price"<?php } ?>>
							<td class="key" valign="top">
								<?php echo JText::_('DISPLAY_PRICE');?>
							</td>
							<td>
								<?php
									if(!isset($this->element->hikashop_params['show_price'])) $this->element->hikashop_params['show_price'] = '-1';
									echo JHTML::_('hikaselect.radiolist',  $arr, $this->control.'[show_price]', 'onchange="switchDisplay(this.value,\'show_taxed_price_line\',\'1\',\'_'.$this->controlid.'\');switchDisplay(this.value,\'show_original_price_line\',\'1\',\'_'.$this->controlid.'\');switchDisplay(this.value,\'show_discount_line\',\'1\',\'_'.$this->controlid.'\');switchDisplay(this.value,\'price_display_type_line\',\'1\',\'_'.$this->controlid.'\');"', 'value', 'text', @$this->element->hikashop_params['show_price']);
									if(!@$this->element->hikashop_params['show_price']) $this->js .='switchDisplay(0,\'show_taxed_price_line\',\'1\',\'_'.$this->controlid.'\');switchDisplay(\'0\',\'price_display_type_line\',\'1\',\'_'.$this->controlid.'\');switchDisplay(\'0\',\'show_original_price_line\',\'1\',\'_'.$this->controlid.'\');switchDisplay(\'0\',\'show_discount_line\',\'1\',\'_'.$this->controlid.'\');';
								?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('DISPLAY_OUT_OF_STOCK_PRODUCTS');?>
							</td>
							<td>
								<?php
									if(!isset($this->element->hikashop_params['show_out_of_stock'])) $this->element->hikashop_params['show_out_of_stock'] = '-1';
									echo JHTML::_('hikaselect.radiolist',  $arr, $this->control.'[show_out_of_stock]', '', 'value', 'text', @$this->element->hikashop_params['show_out_of_stock']);
								?>
							</td>
						</tr>
						<tr id="<?php echo 'show_taxed_price_line_'.$this->controlid ?>">
							<td class="key">
								<?php echo JText::_('SHOW_TAXED_PRICES');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['price_with_tax'])) $this->element->hikashop_params['price_with_tax'] = 3;
								echo $this->pricetaxType->display($this->control.'[price_with_tax]' , $this->element->hikashop_params['price_with_tax'],true); ?>
							</td>
						</tr>
						<tr id="<?php echo 'show_original_price_line_'.$this->controlid ?>">
							<td class="key" valign="top">
								<?php echo JText::_('ORIGINAL_CURRENCY_PRICE');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['show_original_price'])) $this->element->hikashop_params['show_original_price'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[show_original_price]' , '', 'value', 'text', @$this->element->hikashop_params['show_original_price']); ?>
							</td>
						</tr>
						<tr id="<?php echo 'show_discount_line_'.$this->controlid ?>">
							<td class="key" valign="top">
								<?php echo JText::_('SHOW_DISCOUNTED_PRICE');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['show_discount'])) $this->element->hikashop_params['show_discount'] = 3;
								echo $this->discountDisplayType->display( $this->control.'[show_discount]' ,@$this->element->hikashop_params['show_discount']); ?>
							</td>
						</tr>
						<tr id="<?php echo 'price_display_type_line_'.$this->controlid ?>">
							<td class="key">
								<?php echo JText::_('PRICE_DISPLAY_METHOD');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['price_display_type'])) $this->element->hikashop_params['price_display_type'] = 'inherit';
								echo $this->priceDisplayType->display( $this->control.'[price_display_type]',@$this->element->hikashop_params['price_display_type']); ?>
							</td>
						</tr>
						<?php
						if(hikashop_level(2) && $this->element->module=='mod_hikashop'){ ?>
							<tr>
								<td class="key">
									<?php echo JText::_('DISPLAY_CUSTOM_ITEM_FIELDS');?>
								</td>
								<td>
									<?php
									if(!isset($this->element->hikashop_params['display_custom_item_fields'])) $this->element->hikashop_params['display_custom_item_fields'] = '-1';
									echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[display_custom_item_fields]' , '', 'value', 'text', @$this->element->hikashop_params['display_custom_item_fields']);
									?>
								</td>
							</tr>
						<?php } ?>
						<?php if(hikashop_level(2)){ ?>
							<tr>
								<td class="key">
									<?php echo JText::_('DISPLAY_FILTERS');?>
								</td>
								<td>
									<?php
									if(!isset($this->element->hikashop_params['display_filters'])) $this->element->hikashop_params['display_filters'] = '-1';
									echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[display_filters]' , '', 'value', 'text', @$this->element->hikashop_params['display_filters']);
									?>
								</td>
							</tr>
						<?php } ?>
						<tr>
							<td class="key">
								<?php echo JText::_('DISPLAY_BADGE');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['display_badges'])) $this->element->hikashop_params['display_badges'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[display_badges]' , '', 'value', 'text', @$this->element->hikashop_params['display_badges']); ?>
							</td>
						</tr>
<?php
if(!empty($this->extra_blocks['products'])) {
	foreach($this->extra_blocks['products'] as $r) {
		if(is_string($r))
			echo $r;
		if(is_array($r)) {
			if(!isset($r['name']) && isset($r[0]))
				$r['name'] = $r[0];
			if(!isset($r['value']) && isset($r[1]))
				$r['value'] = $r[1];
?>
						<tr>
							<td class="key"><?php echo JText::_(@$r['name']); ?></td>
							<td><?php echo @$r['value']; ?></td>
						</tr>
<?php
		}
	}
}
?>
						<?php if($this->element->module=='mod_hikashop'){ ?>
					</table>
				</fieldset>
				<fieldset id="<?php echo 'content_category_'.$this->controlid ?>" class="adminform">
					<legend><?php echo JText::_('PARAMS_FOR_CATEGORIES'); ?></legend>
					<table class="admintable table" cellspacing="1" width="100%">
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ORDERING_FIELD');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['category_order'])) $this->element->hikashop_params['category_order'] = 'inherit';
								echo $this->orderType->display($this->control.'[category_order]',@$this->element->hikashop_params['category_order'],'category');?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('SHOW_SUB_CATEGORIES');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['child_display_type'])) $this->element->hikashop_params['child_display_type'] = 'inherit';
								echo $this->listType->display($this->control.'[child_display_type]',@$this->element->hikashop_params['child_display_type']);?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('NUMBER_OF_SUB_CATEGORIES');?>
							</td>
							<td>
								<input name="<?php echo $this->control; ?>[child_limit]" type="text" value="<?php echo @$this->element->hikashop_params['child_limit'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('LINK_ON_MAIN_CATEGORIES');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['links_on_main_categories'])) $this->element->hikashop_params['links_on_main_categories'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[links_on_main_categories]' , '', 'value', 'text', @$this->element->hikashop_params['links_on_main_categories']);
								?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('SHOW_NUMBER_OF_PRODUCTS');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['number_of_products'])) $this->element->hikashop_params['number_of_products'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[number_of_products]' , '', 'value', 'text', @$this->element->hikashop_params['number_of_products']);
								?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ONLY_DISPLAY_CATEGORIES_WITH_PRODUCTS');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['only_if_products'])) $this->element->hikashop_params['only_if_products'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[only_if_products]' , '', 'value', 'text', @$this->element->hikashop_params['only_if_products']);
								?>
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset id="<?php echo 'layout_div_'.$this->controlid ?>" class="adminform">
					<legend><?php echo JText::_('PARAMS_FOR_DIV'); ?></legend>
					<?php
						$displayCarouselType="";
						$slideDirection="";
						$transitionEffect="";
						$carouselEffectDuration="";
						$productBySlide="";
						$slideOneByOne="";
						$autoSlide="";
						$autoSlideDuration="";
						$slidePagination="";
						$paginationWidth="";
						$paginationHeight="";
						$paginationPosition="";
						$displayButton="";
						$productEffect="";
						$productEffectDuration="";
						$paneHeight="";
						if(@!$this->element->hikashop_params['enable_carousel']){
							$displayCarouselType='style="display:none"';
							$slideDirection='style="display:none"';
							$transitionEffect='style="display:none"';
							$carouselEffectDuration='style="display:none"';
							$productBySlide='style="display:none"';
							$slideOneByOne='style="display:none"';
							$autoSlide='style="display:none"';
							$autoSlideDuration='style="display:none"';
							$slidePagination='style="display:none"';
							$paginationWidth='style="display:none"';
							$paginationHeight='style="display:none"';
							$paginationPosition='style="display:none"';
							$displayButton='style="display:none"';
						}
						if(@$this->element->hikashop_params['carousel_effect']=="fade"){
							$transitionEffect='style="display:none"';
							$slideOneByOne='style="display:none"';
						}
						if(@$this->element->hikashop_params['div_item_layout_type']=='fade'){
							$productEffect='style="display:none"';
						}else if(@$this->element->hikashop_params['div_item_layout_type']=='img_pane'){
							$productEffect='style="display:none"';
							$productEffectDuration='style="display:none"';
						}else if(@$this->element->hikashop_params['div_item_layout_type']!='slider_horizontal' && @$this->element->hikashop_params['div_item_layout_type']!='slider_vertical'){
							$productEffect='style="display:none"';
							$productEffectDuration='style="display:none"';
						}
						if(!@$this->element->hikashop_params['auto_slide']){
							$autoSlideDuration='style="display:none"';
						}
						if(@$this->element->hikashop_params['pagination_type']=="no_pagination"){
							$paginationWidth='style="display:none"';
							$paginationHeight='style="display:none"';
							$paginationPosition='style="display:none"';
						}else{
							if(@$this->element->hikashop_params['pagination_type']!="thumbnails" ){
								$paginationHeight='style="display:none"';
								$paginationWidth='style="display:none"';
							}
						}
					?>
					<table class="admintable table" cellspacing="1" width="100%">
						<?php if(hikashop_level(2)){ ?>
							<tr>
								<td class="key" valign="top">
									<?php echo JText::_('ENABLE_CAROUSEL');?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', $this->control.'[enable_carousel]' , 'onclick="setVisible(this.value,\''.$this->controlid.'\');"',@$this->element->hikashop_params['enable_carousel']); ?>
								</td>
							</tr>
							<tr id="<?php echo 'carousel_type_'.$this->controlid.'" '.$displayCarouselType; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('TYPE_OF_CAROUSEL_EFFECT');?>
								</td>
								<td>
									<?php echo $this->effectType->display($this->control.'[carousel_effect]',@$this->element->hikashop_params['carousel_effect'] , 'onchange="setVisibleEffect(this.value,\''.$this->controlid.'\');"');?>
								</td>
							</tr>
							<tr id="<?php echo 'slide_direction_'.$this->controlid.'" '.$slideDirection; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('SLIDE_DIRECTION');?>
								</td>
								<td>
									<?php echo $this->directionType->display($this->control.'[slide_direction]',@$this->element->hikashop_params['slide_direction']);?>
								</td>
							</tr>
							<tr id="<?php echo 'transition_effect_'.$this->controlid.'" '.$transitionEffect; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('TRANSITION_EFFECT');?>
								</td>
								<td>
									<?php echo $this->transition_effectType->display($this->control.'[transition_effect]',@$this->element->hikashop_params['transition_effect']);?>
								</td>
							</tr>
							<tr id="<?php echo 'carousel_effect_duration_'.$this->controlid.'" '.$carouselEffectDuration; ?>>
								<td class="key">
									<?php echo JText::_('CAROUSEL_EFFECT_DURATION');?>
								</td>
								<td>
									<input size=12 name="<?php echo $this->control;?>[carousel_effect_duration]" type="text" value="<?php echo @$this->element->hikashop_params['carousel_effect_duration'];?>" /> ms
								</td>
							</tr>
							<tr id="<?php echo 'product_by_slide_'.$this->controlid.'" '.$productBySlide; ?>>
								<td class="key">
									<?php echo JText::_('PRODUCTS_BY_SLIDE');?>
								</td>
								<td>
									<input size=9 name="<?php echo $this->control;?>[item_by_slide]" type="text" value="<?php echo @$this->element->hikashop_params['item_by_slide'];?>" />
								</td>
							</tr>
							<tr id="<?php echo 'slide_one_by_one_'.$this->controlid.'" '.$slideOneByOne; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('SLIDE_ONE_BY_ONE');?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', $this->control.'[one_by_one]' , '',@$this->element->hikashop_params['one_by_one']); ?>
								</td>
							</tr>
							<tr id="<?php echo 'auto_slide_'.$this->controlid.'" '.$autoSlide; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('AUTO_SLIDE');?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', $this->control.'[auto_slide]' , 'onclick="setVisibleAutoSlide(this.value,\''.$this->controlid.'\');"',@$this->element->hikashop_params['auto_slide']); ?>
								</td>
							</tr>
							<tr id="<?php echo 'auto_slide_duration_'.$this->controlid.'" '.$autoSlideDuration; ?>>
								<td class="key">
									<?php echo JText::_('AUTO_SLIDE_DURATION');?>
								</td>
								<td>
									<input size=12 name="<?php echo $this->control;?>[auto_slide_duration]" type="text" value="<?php echo @$this->element->hikashop_params['auto_slide_duration'];?>" /> ms
								</td>
							</tr>
							<tr id="<?php echo 'slide_pagination_'.$this->controlid.'" '.$slidePagination; ?>>
								<td class="key">
									<?php echo JText::_('SLIDE_PAGINATION_TYPE');?>
								</td>
								<td>
									<?php echo $this->slide_paginationType->display($this->control.'[pagination_type]',@$this->element->hikashop_params['pagination_type'], 'onchange="setVisiblePagination(this.value,\''.$this->controlid.'\');"');?>
								</td>
							</tr>
							<tr id="<?php echo 'pagination_width_'.$this->controlid.'" '.$paginationWidth; ?>>
								<td class="key">
									<?php echo JText::_('PAGINATION_IMAGE_WIDTH');?>
								</td>
								<td>
									<input size=12 name="<?php echo $this->control;?>[pagination_image_width]" type="text" value="<?php echo @$this->element->hikashop_params['pagination_image_width'];?>" /> px
								</td>
							</tr>
							<tr id="<?php echo 'pagination_height_'.$this->controlid.'" '.$paginationHeight; ?>>
								<td class="key">
									<?php echo JText::_('PAGINATION_IMAGE_HEIGHT');?>
								</td>
								<td>
									<input size=12 name="<?php echo $this->control;?>[pagination_image_height]" type="text" value="<?php echo @$this->element->hikashop_params['pagination_image_height'];?>" /> px
								</td>
							</tr>
							<tr id="<?php echo 'pagination_position_'.$this->controlid.'" '.$paginationPosition; ?>>
								<td class="key">
									<?php echo JText::_('HIKA_PAGINATION');?>
								</td>
								<td>
									<?php echo $this->positionType->display($this->control.'[pagination_position]',@$this->element->hikashop_params['pagination_position']);?>
								</td>
							</tr>
							<tr id="<?php echo 'display_button_'.$this->controlid.'" '.$displayButton; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('DISPLAY_BUTTONS');?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', $this->control.'[display_button]' , '',@$this->element->hikashop_params['display_button']); ?>
								</td>
							</tr>
						<?php }else{ ?>
							<tr>
								<td class="key" valign="top">
									<?php echo JText::_('ENABLE_CAROUSEL');?>
								</td>
								<td>
									<?php echo hikashop_getUpgradeLink('business'); ?>
								</td>
							</tr>
						<?php } ?>
						<tr>
							<td class="key">
								<?php echo JText::_('IMAGE_X');?>
							</td>
							<td>
								<input size=12 name="<?php echo $this->control;?>[image_width]" type="text" value="<?php echo @$this->element->hikashop_params['image_width'];?>" /> px
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('IMAGE_Y');?>
							</td>
							<td>
								<input size=12 name="<?php echo $this->control;?>[image_height]" type="text" value="<?php echo @$this->element->hikashop_params['image_height'];?>" /> px
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('TYPE_OF_ITEM_LAYOUT');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['div_item_layout_type'])) $this->element->hikashop_params['div_item_layout_type'] = 'inherit';
								echo $this->itemType->display($this->control.'[div_item_layout_type]',@$this->element->hikashop_params['div_item_layout_type'],$this->js, 'onchange="setVisibleLayoutEffect(this.value,\''.$this->controlid.'\');"');?>
							</td>
						</tr>
						<?php if(hikashop_level(2)){ ?>
							<tr id="<?php echo 'product_effect_'.$this->controlid.'" '.$productEffect; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('PRODUCT_TRANSITION_EFFECT');?>
								</td>
								<td>
									<?php echo $this->transition_effectType->display($this->control.'[product_transition_effect]',@$this->element->hikashop_params['product_transition_effect']);?>
								</td>
							</tr>
							<tr id="<?php echo 'product_effect_duration_'.$this->controlid.'" '.$productEffectDuration; ?>>
								<td class="key">
									<?php echo JText::_('PRODUCT_EFFECT_DURATION');?>
								</td>
								<td>
									<input size=12 name="<?php echo $this->control;?>[product_effect_duration]" type="text" value="<?php echo @$this->element->hikashop_params['product_effect_duration'];?>" /> ms
								</td>
							</tr>
						<?php } ?>
						<tr id="<?php echo 'pane_height_'.$this->controlid.'" '.$paneHeight; ?>>
							<td class="key">
								<?php echo JText::_('PANE_HEIGHT');?>
							</td>
							<td>
								<input size=12 name="<?php echo $this->control;?>[pane_height]" type="text" value="<?php echo @$this->element->hikashop_params['pane_height'];?>" />px
							</td>
						</tr>
					<?php } ?>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ITEM_BOX_COLOR');?>
							</td>
							<td>
								<?php echo $this->colorType->displayAll('',$this->control.'[background_color]',@$this->element->hikashop_params['background_color']); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ITEM_BOX_MARGIN');?>
							</td>
							<td>
								<input name="<?php echo $this->control;?>[margin]" type="text" value="<?php echo @$this->element->hikashop_params['margin'];?>" />px
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ITEM_BOX_BORDER');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['border_visible'])) $this->element->hikashop_params['border_visible'] = '-1';
								$arr2 = $arr;
								$arr2[] = JHTML::_('select.option', 2, JText::_('THUMBNAIL'));
								echo JHTML::_('hikaselect.radiolist', $arr2, $this->control.'[border_visible]' , '', 'value', 'text', @$this->element->hikashop_params['border_visible']);
								?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ITEM_BOX_ROUND_CORNER');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['rounded_corners'])) $this->element->hikashop_params['rounded_corners'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[rounded_corners]' , '', 'value', 'text', @$this->element->hikashop_params['rounded_corners']);
								?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('TEXT_CENTERED');?>
							</td>
							<td>
								<?php
								if(!isset($this->element->hikashop_params['text_center'])) $this->element->hikashop_params['text_center'] = '-1';
								echo JHTML::_('hikaselect.radiolist', $arr, $this->control.'[text_center]' , '', 'value', 'text', @$this->element->hikashop_params['text_center']);
								?>
							</td>
						</tr>
					</table>
				</fieldset>
				<?php if($this->element->module=='mod_hikashop'){ ?>
				<fieldset id="<?php echo 'layout_list_'.$this->controlid ?>" class="adminform">
					<legend><?php echo JText::_('PARAMS_FOR_LIST'); ?></legend>
					<table class="admintable table" cellspacing="1" width="100%">
						<tr>
							<td class="key">
								<?php echo JText::_('UL_CLASS_NAME');?>
							</td>
							<td>
								<input name="<?php echo $this->control;?>[ul_class_name]" type="text" value="<?php echo @$this->element->hikashop_params['ul_class_name'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key"><?php
								echo JText::_('UL_DISPLAY_SIMPLELIST');
							?></td>
							<td><?php
								echo JHTML::_('hikaselect.booleanlist', $this->control.'[ul_display_simplelist]' , '', @$this->element->hikashop_params['ul_display_simplelist']);
							?></td>
						</tr>
					</table>
				</fieldset>
				<?php } ?>
<?php
if(!empty($this->extra_blocks['layouts'])) {
	foreach($this->extra_blocks['layouts'] as $key => $r) {
		if(is_string($r))
			echo $r;
		if(is_array($r)) {
			if(!isset($r['name']) && isset($r[0]))
				$r['name'] = $r[0];
			if(!isset($r['value']) && isset($r[1]))
				$r['value'] = $r[1];
?>
				<fieldset id="layout_<?php echo $key; ?>" class="adminform">
					<legend><?php echo JText::_(@$r['name']); ?></legend>
					<table class="admintable table" cellspacing="1" width="100%">
<?php
			if(is_array($r['value'])) {
				foreach($r['value'] as $k => $v) {
?>
						<tr>
							<td class="key"><?php echo JText::_($k); ?></td>
							<td><?php echo $v; ?></td>
						</tr>
<?php
				}
			} else {
				echo $r['value'];
			}
?>
					</table>
				</fieldset>
<?php
		}
	}
}
?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
	<div class="clr"></div>
<?php if (!$noForm) { ?>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="module[id]" value="<?php echo (int)@$this->element->id; ?>" />
	<input type="hidden" name="module[module]" value="<?php echo $this->element->module; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getVar('ctrl');?>" />
	<input type="hidden" name="return" value="<?php echo JRequest::getString('return');?>" />
	<input type="hidden" name="client" value="0" />
	<?php echo JHTML::_( 'form.token' );	?>
</form>
<?php }
$this->js = "window.hikashop.ready(function() {
		".$this->js."
});";
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($this->js);
?>

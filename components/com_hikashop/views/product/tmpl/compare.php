<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_compare_page">
<?php if(empty($this->elements)){
	$app = JFactory::getApplication();
	$app->enqueueMessage(JText::_('PRODUCT_NOT_FOUND'));
?>
</div>
<?php
	return;
}

$history = -1;
?>
<div id="hikashop_compare_back_btn" class="hikashop_compare_back_btn">
	<?php
	$empty='';
	$params = new HikaParameter($empty);
	echo $this->cart->displayButton(JText::_('HIKA_BACK'),'go_back',$params,'javascript:history.go('.$history.')','history.go('.$history.');return false;'); ?>
</div>
<table class="hikashop_compare_table">
	<tr id="hikashop_compare_tr_head">
		<td><!-- "Move and Remove Product from compare list" links --></td>
		<?php
		$ids = array();
		$url_ids = '';
		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid)){
			$url_itemid = '&Itemid='.$url_itemid;
		}
		foreach($this->elements as $element) {
			$ids[$element->product_id] = $element->product_id;
			$url_ids .= '&cid[]=' . $element->product_id;
		}
		foreach($ids as $k => $v) {
		?>
			<td></td>
		<?php } ?>
	</tr>
	<tr id="hikashop_compare_tr_name">
		<td class="hikashop_compare_title_first_column"></td>
		<?php foreach($this->elements as $element) {
			if(!isset($element->alias)) $element->alias = '';
			$link = hikashop_contentLink('product&task=show&cid='.$element->product_id.'&name='.$element->alias.$url_itemid,$element); ?>
			<td class="hikashop_compare_title_prod_column">
				<h2>
					<a href="<?php echo $link; ?>" title="<?php echo $this->escape($element->product_name); ?>">
						<span id="hikashop_product_<?php echo $element->product_id; ?>_name_main" class="hikashop_product_name_main"><?php echo $element->product_name; ?></span>
						<?php if ($this->config->get('show_code')) { ?><span id="hikashop_product_<?php echo $element->product_id; ?>_code_main" class="hikashop_product_code_main"><?php echo $element->product_code; ?></span><?php } ?>
					</a>
				</h2>
			</td>
		<?php } ?>
	</tr>
	<tr id="hikashop_compare_tr_image">
		<td class="hikashop_compare_img_first_column"></td>
		<?php foreach($this->elements as $element) { ?>
			<td class="hikashop_compare_img_prod_column">
				<div id="hikashop_product_<?php echo $element->product_id; ?>_image_main" >
					<div class="hikashop_main_image_div">
					<?php
					if(!empty($element->images)){
						$image = reset($element->images);
						if(!$this->config->get('thumbnail')){
							echo '<img src="'.$this->image->uploadFolder_url.$image->file_path.'" alt="'.$image->file_name.'" id="hikashop_main_image" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle" />';
						}else{
							$height = $this->config->get('thumbnail_y');
							$width = $this->config->get('thumbnail_x');
							$style='';
							if(count($element->images)>1){
								if(!empty($height)){
									$style=' style="height:'.($height+5).'px;"';
								}
							} ?>
							<div class="hikashop_product_main_image_thumb" id="hikashop_main_image_thumb_div" <?php echo $style;?> >
							<?php
							$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
							$img = $this->image->getThumbnail(@$image->file_path, array('width' => $width, 'height' => $height), $image_options);
							if($img->success) {
								echo '<img class="hikashop_product_compare_image" title="'.$this->escape(@$image->file_description).'" alt="'.$this->escape(@$image->file_name).'" src="'.$img->url.'"/>';
							}
							?>
							</div>
					<?php }
					}
					?>
					</div>
				</div>
			</td>
		<?php } ?>
	</tr>
	<tr id="hikashop_compare_tr_price">
		<td class="hikashop_compare_details_first_column"></td>
	<?php
	foreach($this->elements as $k => $element) { ?>
		<td class="hikashop_compare_details_prod_column">
			<?php
			if($this->params->get('show_price','-1')=='-1'){
				$config =& hikashop_config();
				$defaultParams = $config->get('default_params');
				$this->params->set('show_price',$defaultParams['show_price']);
			}
			if($this->params->get('show_price')) { ?>
			<span id="hikashop_product_<?php echo $element->product_id; ?>_price_main" class="hikashop_product_price_main">
				<?php
				$this->row =& $element;
				$this->setLayout('listing_price');
				echo $this->loadTemplate();
				?>
			</span>
			<?php } ?>
			<?php if(isset($element->product_weight) && bccomp($element->product_weight,0,3)){ ?>
			<span id="hikashop_product_weight_main" class="hikashop_product_weight_main">
				<?php echo JText::_('PRODUCT_WEIGHT').': '.rtrim(rtrim($element->product_weight,'0'),',.').' '.JText::_($element->product_weight_unit); ?><br />
			</span>
			<?php
			}
			if($this->config->get('dimensions_display',0) && bccomp($element->product_width,0,3)){ ?>
			<span id="hikashop_product_width_main" class="hikashop_product_width_main">
				<?php echo JText::_('PRODUCT_WIDTH').': '.rtrim(rtrim($element->product_width,'0'),',.').' '.JText::_($element->product_dimension_unit); ?><br />
			</span>
			<?php }
			if($this->config->get('dimensions_display',0) && bccomp($element->product_length,0,3)){ ?>
			<span id="hikashop_product_length_main" class="hikashop_product_length_main">
				<?php echo JText::_('PRODUCT_LENGTH').': '.rtrim(rtrim($element->product_length,'0'),',.').' '.JText::_($element->product_dimension_unit); ?><br />
			</span>
			<?php }
			if($this->config->get('dimensions_display',0) && bccomp($element->product_height,0,3)){ ?>
			<span id="hikashop_product_height_main" class="hikashop_product_height_main">
				<?php echo JText::_('PRODUCT_HEIGHT').': '.rtrim(rtrim($element->product_height,'0'),',.').' '.JText::_($element->product_dimension_unit); ?><br />
			</span>
			<?php } ?>
		</td>
	<?php } ?>
	</tr>
	<tr id="hikashop_compare_tr_cart">
		<td class="hikashop_compare_cart_first_column"></td>
		<?php
		$form = '';
		if(!$this->config->get('ajax_add_to_cart',0)){
			$form = ',\'hikashop_product_form\'';
		}

		if($this->params->get('add_to_cart')){
			foreach($this->elements as $element) {
				$this->row =& $element;
			?>
			<td class="hikashop_compare_cart_prod_column">
				<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form_<?php echo $this->row->product_id.'_'.$this->params->get('main_div_name'); ?>"><?php
					$this->ajax='';
					if(!$this->config->get('ajax_add_to_cart',0)){
						$this->ajax = 'return hikashopModifyQuantity(\''.$this->row->product_id.'\',field,1,\'hikashop_product_form_'.$this->row->product_id.'_'.$this->params->get('main_div_name').'\',\'cart\');';
					}
					$this->setLayout('quantity');
					echo $this->loadTemplate();
					if(!empty($this->ajax ) && $this->config->get('redirect_url_after_add_cart','stay_if_cart')=='ask_user'){ ?>
						<input type="hidden" name="popup" value="1"/>
					<?php } ?>
					<input type="hidden" name="product_id" value="<?php echo $this->row->product_id; ?>" />
					<input type="hidden" name="add" value="1"/>
					<input type="hidden" name="ctrl" value="product"/>
					<input type="hidden" name="task" value="updatecart"/>
					<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
				</form>
			</td><?php
			}
		}?>
	</tr>
	<?php
	foreach( $this->fields[0] as $fieldName => $oneExtraField ) {
		if($oneExtraField->field_type != "customtext") {
			$display = false;
			foreach($this->elements as $element) {
				if(!empty($element->$fieldName) || $element->$fieldName === '0') {
					$display = true;
				}
			}
			if(!$display) continue;
	?>
	<tr id="hikashop_compare_tr_cf_<?php echo $oneExtraField->field_id;?>">
		<td class="hikashop_compare_custom_first_column">
			<span id="hikashop_product_custom_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_name">
				<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
			</span>
		</td>
		<?php foreach($this->elements as $element) { ?>
		<td class="hikashop_compare_custom_prod_column">
		<?php
			if(!empty($element->$fieldName)) {
		?>
			<span id="hikashop_product_<?php echo $element->product_id; ?>_custom_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_value">
				<?php echo $this->fieldsClass->show($oneExtraField,$element->$fieldName); ?>
			</span>
		<?php } else {
				$t = JText::_('COMPARE_EMPTY');
				if( $t != 'COMPARE_EMPTY' ) { echo $t; }
			}
		} ?>
		</td>
	</tr>
	<?php } else { ?>
	<tr id="hikashop_compare_tr_cf_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_compare_custom_separator">
		<td class="hikashop_compare_separator_first_column">
			<span id="hikashop_product_custom_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_name">
				<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
			</span>
		</td>
		<?php foreach($this->elements as $element) { ?>
		<td class="hikashop_compare_separator_prod_column">
			<?php if( $this->params->get('compare_show_name_separator')) { ?>
			<span id="hikashop_product_<?php echo $element->product_id; ?>_custom_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_value">
				<?php echo $element->product_name; ?>
			</span>
			<?php } ?>
		</td>
		<?php } ?>
	</tr>
	<?php }
	}
	if(hikashop_level(1) && (($this->config->get('enable_wishlist'))) && $this->config->get('compare_to_wishlist',0) != 0 && (($this->config->get('hide_wishlist_guest', 1) && hikashop_loadUser() != null) || !$this->config->get('hide_wishlist_guest', 1))){
		global $Itemid;
		$Itemid = '&Itemid='.$Itemid;
		?><form method="POST" id="hikashop_compare_add_wishlist_form" name="hikashop_compare_add_wishlist_form" action="<?php echo hikashop_completeLink('cart&task=addtocart'.$Itemid);?>">
	<tr>
		<td></td>
		<?php foreach($this->elements as $j => $element){ ?>
		<td style="text-align: center;">
			<?php
				$quantityField = $j + 2;
			?>
			<input type="checkbox" name="data[products][<?php echo $element->product_id;?>][checked]" value="1" onclick="document.getElementById('product_quantity_<?php echo $quantityField; ?>').value = document.getElementById('hikashop_product_quantity_field_<?php echo $quantityField; ?>').value;" />
			<input type="hidden" id="product_quantity_<?php echo $quantityField; ?>" name="data[products][<?php echo $element->product_id;?>][quantity]" value="1"/>
		</td>
		<?php } ?>
	</tr>
	<tr>
		<td style="text-align:center;" colspan="<?php echo $k+2; ?>">
			<div id="hikashop_compare_wishlist_btn" class="hikashop_compare_wishlist_btn">
				<?php
				$cart_id = JRequest::getInt('cart_id','');
				echo $this->cart->displayButton(JText::_('ADD_TO_WISHLIST'),'wishlist',$this->params,hikashop_completeLink('cart&task=convert&cart_type=cart&cart_id='.$cart_id.'&Itemid='.$Itemid),'document.forms[\'hikashop_compare_add_wishlist_form\'].submit(); return false;');
				?>
			</div>
			<input type="hidden" name="add" value="1"/>
			<input type="hidden" name="ctrl" value="cart"/>
			<input type="hidden" name="task" value="addtocart"/>
			<input type="hidden" name="cart_type" value="cart"/>
			<input type="hidden" name="cart_id" value="compare"/>
			<input type="hidden" name="action" value="<?php echo JURI::getInstance()->toString(); ?>"/>
		</form>
		</td>
	</tr>
	<?php } ?>
</table>
</div>

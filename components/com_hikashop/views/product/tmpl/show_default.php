<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_product_top_part" class="hikashop_product_top_part">
<?php if(!empty($this->element->extraData->topBegin)) { echo implode("\r\n",$this->element->extraData->topBegin); } ?>
	<h1>
		<span id="hikashop_product_name_main" class="hikashop_product_name_main">
			<?php
			if (hikashop_getCID('product_id')!=$this->element->product_id && isset ($this->element->main->product_name))
				echo $this->element->main->product_name;
			else
				echo $this->element->product_name;
			?>
		</span>
		<?php if ($this->config->get('show_code')) { ?>
		<span id="hikashop_product_code_main" class="hikashop_product_code_main">
			<?php
			echo $this->element->product_code;
			?>
		</span>
		<?php } ?>
	</h1>
<?php if(!empty($this->element->extraData->topEnd)) { echo implode("\r\n",$this->element->extraData->topEnd); } ?>
<?php
	$this->setLayout('show_block_social');
	echo $this->loadTemplate();
?>
</div>
<?php if(HIKASHOP_RESPONSIVE){ ?>
	<div class="<?php echo HK_GRID_ROW; ?>">
<?php } ?>
<div id="hikashop_product_left_part" class="hikashop_product_left_part <?php echo HK_GRID_COL_6; ?>">
	<?php
	if(!empty($this->element->extraData->leftBegin)) { echo implode("\r\n",$this->element->extraData->leftBegin); }

	$this->row = & $this->element;
	$this->setLayout('show_block_img');
	echo $this->loadTemplate();

	if(!empty($this->element->extraData->leftEnd)) { echo implode("\r\n",$this->element->extraData->leftEnd); }
	?>
</div>

<div id="hikashop_product_right_part" class="hikashop_product_right_part <?php echo HK_GRID_COL_6; ?>">
	<?php
	if(!empty($this->element->extraData->rightBegin))
		echo implode("\r\n",$this->element->extraData->rightBegin);
	?>
	<div id="hikashop_product_vote_mini" class="hikashop_product_vote_mini">
		<?php
		$config =& hikashop_config();
		if($this->params->get('show_vote_product') == '-1'){
			$this->params->set('show_vote_product',$config->get('show_vote_product'));
		}
		if($this->params->get('show_vote_product')){
			$js = '';
			$this->params->set('vote_type','product');
			if(isset($this->element->main)){
				$product_id = $this->element->main->product_id;
			}else{
				$product_id = $this->element->product_id;
			}
			$this->params->set('vote_ref_id',$product_id);
			echo hikashop_getLayout('vote', 'mini', $this->params, $js);
		}
		?>
	</div>
	<span id="hikashop_product_price_main" class="hikashop_product_price_main">
		<?php
		if ($this->params->get('show_price')) {
			$this->row = & $this->element;
			$this->setLayout('listing_price');
			echo $this->loadTemplate();
		}
		?>
	</span>
	<?php
	if(!empty($this->element->extraData->rightMiddle))
		echo implode("\r\n",$this->element->extraData->rightMiddle);
	?>
	<?php
		$this->setLayout('show_block_dimensions');
		echo $this->loadTemplate();
	?><br />
	<?php
	if($this->params->get('characteristic_display') != 'list') {
		$this->setLayout('show_block_characteristic');
		echo $this->loadTemplate();
		?>
		<br />
	<?php
	}

	$form = ',0';
	if (!$this->config->get('ajax_add_to_cart', 1)) {
		$form = ',\'hikashop_product_form\'';
	}
	if (hikashop_level(1) && !empty ($this->element->options)) {
	?>
		<div id="hikashop_product_options" class="hikashop_product_options">
			<?php
			$this->setLayout('option');
			echo $this->loadTemplate();
			?>
		</div>
		<br />
		<?php
		$form = ',\'hikashop_product_form\'';
		if ($this->config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'ask_user') {
		?>
			<input type="hidden" name="popup" value="1"/>
		<?php
		}
	}
	if (!$this->params->get('catalogue') && ($this->config->get('display_add_to_cart_for_free_products') || ($this->config->get('display_add_to_wishlist_for_free_products', 1) && hikashop_level(1) && $this->params->get('add_to_wishlist') && $config->get('enable_wishlist', 1)) || !empty($this->element->prices))) {
		if (!empty ($this->itemFields)) {
			$form = ',\'hikashop_product_form\'';
			if ($this->config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'ask_user') {
			?>
				<input type="hidden" name="popup" value="1"/>
			<?php
			}
			$this->setLayout('show_block_custom_item');
			echo $this->loadTemplate();
		}
	}
	$this->formName = $form;
	if($this->params->get('show_price')){ ?>
		<span id="hikashop_product_price_with_options_main" class="hikashop_product_price_with_options_main">
		</span>
	<?php }
	if(empty ($this->element->characteristics) || $this->params->get('characteristic_display')!='list'){ ?>
		<div id="hikashop_product_quantity_main" class="hikashop_product_quantity_main">
			<?php
			$this->row = & $this->element;
			$this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form\')){ return hikashopModifyQuantity(\'' . $this->row->product_id . '\',field,1' . $form . ',\'cart\'); } else { return false; }';
			$this->setLayout('quantity');
			echo $this->loadTemplate();
			?>
		</div>
	<?php } ?>
	<div id="hikashop_product_contact_main" class="hikashop_product_contact_main">
		<?php
		$contact = $this->config->get('product_contact',0);
		if (hikashop_level(1) && ($contact == 2 || ($contact == 1 && !empty ($this->element->product_contact)))) {
			$empty = '';
			$params = new HikaParameter($empty);
			global $Itemid;
			$url_itemid='';
			if(!empty($Itemid)){
				$url_itemid='&Itemid='.$Itemid;
			}
			echo $this->cart->displayButton(JText :: _('CONTACT_US_FOR_INFO'), 'contact_us', $params, hikashop_completeLink('product&task=contact&cid=' . $this->element->product_id.$url_itemid), 'window.location=\'' . hikashop_completeLink('product&task=contact&cid=' . $this->element->product_id.$url_itemid) . '\';return false;');
		}
		?>
	</div>
	<?php
	if(!empty($this->fields)){
		$this->setLayout('show_block_custom_main');
		echo $this->loadTemplate();
	}

	if(HIKASHOP_J30) {
		$this->setLayout('show_block_tags');
		echo $this->loadTemplate();
	}

	?>
	<span id="hikashop_product_id_main" class="hikashop_product_id_main">
		<input type="hidden" name="product_id" value="<?php echo $this->element->product_id; ?>" />
	</span>
	<?php
	if(!empty($this->element->extraData->rightEnd))
		echo implode("\r\n",$this->element->extraData->rightEnd);
	?>
</div>
<?php if(HIKASHOP_RESPONSIVE){ ?>
	</div>
<?php } ?>
<div id="hikashop_product_bottom_part" class="hikashop_product_bottom_part">
	<?php
	if(!empty($this->element->extraData->bottomBegin))
		echo implode("\r\n",$this->element->extraData->bottomBegin);
	?>
	<div id="hikashop_product_description_main" class="hikashop_product_description_main">
		<?php
		echo JHTML::_('content.prepare',preg_replace('#<hr *id="system-readmore" */>#i','',$this->element->product_description));
		?>
	</div>
	<span id="hikashop_product_url_main" class="hikashop_product_url_main">
		<?php
		if (!empty ($this->element->product_url)) {
			echo JText :: sprintf('MANUFACTURER_URL', '<a href="' . $this->element->product_url . '" target="_blank">' . $this->element->product_url . '</a>');
		}
		?>
	</span>
	<?php
	$this->setLayout('show_block_product_files');
	echo $this->loadTemplate();
	?>
	<?php
	if(!empty($this->element->extraData->bottomMiddle))
		echo implode("\r\n",$this->element->extraData->bottomMiddle);
	?>
	<?php
	if(!empty($this->element->extraData->bottomEnd))
		echo implode("\r\n",$this->element->extraData->bottomEnd);
	?>
</div>

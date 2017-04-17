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
if(!empty($this->canonical)) {
	$doc = JFactory::getDocument();
	$doc->addCustomTag('<link rel="canonical" href="'.hikashop_cleanURL($this->canonical).'" />');
}
$classes = array();
if(!empty($this->categories)){
	foreach($this->categories as $category){
		$classes[] = 'hikashop_product_of_category_'.$category->category_id;
	}
}
?>
<div id="hikashop_product_<?php echo preg_replace('#[^a-z0-9]#i','_',@$this->element->product_code); ?>_page" class="hikashop_product_page <?php echo implode(' ',$classes);?>">
<?php
$app = JFactory::getApplication();
if(empty($this->element)) {
	$app->enqueueMessage(JText::_('PRODUCT_NOT_FOUND'));
	hikashop_setPageTitle(JText::_('PRODUCT_NOT_FOUND'));
	echo '</div>';
	return;
}

if(!empty($this->links->previous))
	echo "<a title='".JText :: _('PREVIOUS_PRODUCT')."' href='".$this->links->previous."'><span class='hikashop_previous_product'></span></a>";
if(!empty($this->links->next))
	echo "<a title='".JText :: _('NEXT_PRODUCT')."' href='".$this->links->next."'><span class='hikashop_next_product'></span></a>";
?>
	<div class='clear_both'></div>
	<form action="<?php echo hikashop_completeLink('product&task=updatecart'); ?>" method="post" name="hikashop_product_form" onsubmit="return hikashop_product_form_check();" enctype="multipart/form-data">
	<script type="text/javascript">
	function hikashop_product_form_check(){
		var add_to_cart_main_div = document.getElementById('hikashop_product_quantity_main');
		if(!add_to_cart_main_div)
			return true;
		var main_div_inputs = add_to_cart_main_div.getElementsByTagName('input');
		if(!main_div_inputs.length){
			var main_div_links = add_to_cart_main_div.getElementsByTagName('a');
			if(!main_div_links.length)
				return false;
		}
		return true;
	}
	</script>
<?php
$this->variant_name ='';
if(!empty($this->element->variants)&&$this->config->get('variant_increase_perf',1)&&!empty($this->element->main)){
	foreach (get_object_vars($this->element->main) as $name=>$value) {
		if(!is_array($name)&&!is_object($name)){
			if(empty($this->element->$name)){
				if($name=='product_quantity' && $this->element->$name==0){
					continue;
				}
				$this->element->$name=$this->element->main->$name;
				continue;
			}

		}
		if($this->params->get('characteristic_display')=='list' && !empty ($this->element->characteristics) && !empty($this->element->main->characteristics)){
			$this->element->$name = $this->element->main->$name;
		}
	}
}
$this->setLayout($this->productlayout);
echo $this->loadTemplate();

if($this->productlayout != 'show_tabular') {
?>
		<input type="hidden" name="cart_type" id="type" value="cart"/>
		<input type="hidden" name="add" value="1"/>
		<input type="hidden" name="ctrl" value="product"/>
		<input type="hidden" name="task" value="updatecart"/>
		<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
	</form>
<?php
}

if($this->params->get('characteristic_display') == 'list') {
	$this->setLayout('show_block_characteristic');
	echo $this->loadTemplate();
}

if($this->productlayout != 'show_tabular') {
	$config =& hikashop_config();
	if($config->get('enable_status_vote') == "comment" || $config->get('enable_status_vote') == "two" || $config->get('enable_status_vote') == "both" ) {
?>
	<form action="<?php echo hikashop_currentURL() ?>" method="post" name="hikashop_comment_form" id="hikashop_comment_form">
		<div id="hikashop_product_vote_listing" class="hikashop_product_vote_listing">
<?php
		if($this->params->get('show_vote_product')){
			$js = '';
			if(isset($this->element->main)){
				$product_id = $this->element->main->product_id;
			}else{
				$product_id = $this->element->product_id;
			}
			$this->params->set('product_id',$product_id);
			echo hikashop_getLayout('vote', 'listing', $this->params, $js);
?>
		</div>
		<div id="hikashop_product_vote_form" class="hikashop_product_vote_form">
<?php
			$js = '';
			if(isset($this->element->main)){
				$product_id = $this->element->main->product_id;
			}else{
				$product_id = $this->element->product_id;
			}
			$this->params->set('product_id',$product_id);
			echo hikashop_getLayout('vote', 'form', $this->params, $js);
		}
?>
		</div>
		<input type="hidden" name="add" value="1"/>
		<input type="hidden" name="ctrl" value="product"/>
		<input type="hidden" name="task" value="show"/>
		<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
	</form>
<?php
	}
}

$contact = $this->config->get('product_contact',0);

if(empty($this->element->variants) || $this->params->get('characteristic_display')=='list'){
	if(hikashop_level(1) && !empty($this->element->options)){
		$priceUsed = 0;
		if(!empty($this->row->prices)){
			foreach($this->row->prices as $price){
				if(isset($price->price_min_quantity) && empty($this->cart_product_price)){
					if($price->price_min_quantity<=1){
						if($this->params->get('price_with_tax')){
							$priceUsed = $price->price_value_with_tax;
						} else {
							$priceUsed = $price->price_value;
						}
					}
				}
			}
		}
?>
	<input type="hidden" name="hikashop_price_product" value="<?php echo $this->element->product_id; ?>" />
	<input type="hidden" id="hikashop_price_product_<?php echo $this->element->product_id; ?>" value="<?php echo $priceUsed; ?>" />
	<input type="hidden" id="hikashop_price_product_with_options_<?php echo $this->element->product_id; ?>" value="<?php echo $priceUsed; ?>" />
<?php
	}
} else {
	$productClass = hikashop_get('class.product');
	$productClass->generateVariantData($this->element);

	$main_images =& $this->element->main->images;

	foreach($this->element->variants as $variant) {
		$this->row = & $variant;
		$variant_name = array ();
		if(!empty($variant->characteristics)) {
			foreach($variant->characteristics as $k => $ch) {
				$variant_name[] = $ch->characteristic_id;
			}
		}
		$this->element->images =& $main_images;
		if(!empty($variant->images))
			$this->element->images =& $variant->images;

		$variant_name = implode('_', $variant_name);
		$this->variant_name = '_' . $variant_name;
		$this->setLayout('show_block_img');
		echo $this->loadTemplate();

		if(!empty($variant->product_name)) {
?>
	<div id="hikashop_product_name_<?php echo $variant_name;?>" style="display:none;"><?php
		echo $variant->product_name;
	?></div>
<?php
		}

		if($this->config->get('show_code') && !empty($variant->product_code)) {
?>
	<div id="hikashop_product_code_<?php echo $variant_name;?>" style="display:none;"><?php
		echo $variant->product_code;
	?></div>
<?php
		}
?>
	<div id="hikashop_product_price_<?php echo $variant_name;?>" style="display:none;"><?php
		if($this->params->get('show_price','-1')=='-1'){
			$config =& hikashop_config();
			$this->params->set('show_price',$config->get('show_price'));
		}
		if ($this->params->get('show_price')) {
			$this->setLayout('listing_price');
			echo $this->loadTemplate();
		}
	?></div>
<?php
		if(hikashop_level(1) && !empty($this->element->options)) {
			$priceUsed = 0;
			if(!empty($this->row->prices)) {
				foreach($this->row->prices as $price) {
					if(isset($price->price_min_quantity) && empty($this->cart_product_price) && $price->price_min_quantity <= 1)
						$priceUsed = ($this->params->get('price_with_tax')) ? $price->price_value_with_tax : $price->price_value;
				}
			}
?>
	<input type="hidden" name="hikashop_price_product" value="<?php echo $this->row->product_id; ?>" />
	<input type="hidden" id="hikashop_price_product_<?php echo $this->row->product_id; ?>" value="<?php echo $priceUsed; ?>" />
	<input type="hidden" id="hikashop_price_product_with_options_<?php echo $this->row->product_id; ?>" value="<?php echo $priceUsed; ?>" />
<?php
		}
?>
	<div id="hikashop_product_quantity_<?php echo $variant_name;?>" style="display:none;"><?php
		$this->row = & $variant;
		if(empty($this->formName)) {
			$this->formName = ',0';
			if (!$this->config->get('ajax_add_to_cart', 1)) {
				$this->formName = ',\'hikashop_product_form\'';
			}
		}
		$this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form\')){ return hikashopModifyQuantity(\'' . $this->row->product_id . '\',field,1' . $this->formName . ',\'cart\'); } else { return false; }';
		$this->setLayout('quantity');
		echo $this->loadTemplate();
	?></div>
	<div id="hikashop_product_contact_<?php echo $variant_name;?>" style="display:none;"><?php
		if(hikashop_level(1) && ($contact == 2 || ($contact == 1 && !empty ($this->element->main->product_contact)))) {
			$params = @$this->params;
			global $Itemid;
			$url_itemid='';
			if(!empty($Itemid)){
				$url_itemid='&Itemid='.$Itemid;
			}
			echo $this->cart->displayButton(
				JText::_('CONTACT_US_FOR_INFO'),
				'contact_us',
				$params,
				''.hikashop_completeLink('product&task=contact&cid=' . $variant->product_id.$url_itemid),
				'window.location=\'' . hikashop_completeLink('product&task=contact&cid=' . $variant->product_id.$url_itemid) . '\';return false;'
			);
		}
	?></div>
<?php
		if(!empty($variant->product_description)) {
?>
		<div id="hikashop_product_description_<?php echo $variant_name;?>" style="display:none;"><?php
			echo JHTML::_('content.prepare',preg_replace('#<hr *id="system-readmore" */>#i','',$variant->product_description));
		?></div>
<?php
		}

		if ($this->config->get('weight_display', 0)) {
			if(!empty($variant->product_weight) && bccomp($variant->product_weight,0,3)){
?>
		<div id="hikashop_product_weight_<?php echo $variant_name;?>" style="display:none;"><?php
			echo JText::_('PRODUCT_WEIGHT').': '.rtrim(rtrim($variant->product_weight,'0'),',.').' '.JText::_($variant->product_weight_unit);
		?><br /></div>
<?php
			}
		}

		if($this->config->get('dimensions_display', 0)) {
			if(!empty ($variant->product_width) && bccomp($variant->product_width, 0, 3)) {
?>
		<div id="hikashop_product_width_<?php echo $variant_name;?>" style="display:none;"><?php
			echo JText::_('PRODUCT_WIDTH').': '.rtrim(rtrim($variant->product_width,'0'),',.').' '.JText::_($variant->product_dimension_unit);
		?><br /></div>
<?php
			}

			if(!empty($variant->product_length) && bccomp($variant->product_length,0,3)){
?>
		<div id="hikashop_product_length_<?php echo $variant_name;?>" style="display:none;"><?php
			echo JText::_('PRODUCT_LENGTH').': '.rtrim(rtrim($variant->product_length,'0'),',.').' '.JText::_($variant->product_dimension_unit);
		?><br /></div>
<?php
			}

			if(!empty($variant->product_height) && bccomp($variant->product_height,0,3)){
?>
		<div id="hikashop_product_height_<?php echo $variant_name;?>" style="display:none;"><?php
			echo JText::_('PRODUCT_HEIGHT').': '.rtrim(rtrim($variant->product_height,'0'),',.').' '.JText::_($variant->product_dimension_unit);
		?><br /></div>
<?php
			}
		}

		if(!empty($variant->product_url)) {
?>
		<span id="hikashop_product_url_<?php echo $variant_name;?>" style="display:none;"><?php
			if(!empty ($variant->product_url))
				echo JText::sprintf('MANUFACTURER_URL', '<a href="' . $variant->product_url . '" target="_blank">' . $variant->product_url . '</a>');
		?></span>
<?php
		}
?>
		<span id="hikashop_product_id_<?php echo $variant_name;?>">
			<input type="hidden" name="product_id" value="<?php echo $variant->product_id; ?>" />
		</span>
<?php
		if(!empty($this->fields)) {
?>
	<div id="hikashop_product_custom_info_<?php echo $variant_name;?>" style="display:none;">
		<h4><?php echo JText::_('SPECIFICATIONS');?></h4>
		<table width="100%">
<?php

			$this->fieldsClass->prefix = '';
			foreach ($this->fields as $fieldName => $oneExtraField) {
				if(empty($variant->$fieldName) && !empty($this->element->main->$fieldName)){
					$variant->$fieldName = $this->element->main->$fieldName;
				}
				if(!empty($variant->$fieldName))
					$variant->$fieldName = trim($variant->$fieldName);
				if(!empty($variant->$fieldName) || (isset($variant->$fieldName) && $variant->$fieldName === '0')) {
?>
			<tr class="hikashop_product_custom_<?php echo $oneExtraField->field_namekey;?>_line">
				<td class="key">
					<span id="hikashop_product_custom_name_<?php echo $oneExtraField->field_id;?>_<?php echo $variant_name;?>" class="hikashop_product_custom_name">
						<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
					</span>
				</td>
				<td>
					<span id="hikashop_product_custom_value_<?php echo $oneExtraField->field_id;?>_<?php echo $variant_name;?>" class="hikashop_product_custom_value">
						<?php echo $this->fieldsClass->show($oneExtraField,$variant->$fieldName); ?>
					</span>
				</td>
			</tr>
<?php
				}
			}
?>
		</table>
	</div>
<?php
		}

		if(!empty ($variant->files)) {
			$skip = true;
			foreach ($variant->files as $file) {
				if ($file->file_free_download)
					$skip = false;
			}
			if (!$skip) {
?>
	<div id="hikashop_product_files_<?php echo $variant_name;?>" style="display:none;">
		<fieldset class="hikashop_product_files_fieldset"><?php

				$html = array();
				echo '<legend>' . JText :: _('DOWNLOADS') . '</legend>';
				foreach ($variant->files as $file) {
					if(empty ($file->file_name)) {
						$file->file_name = $file->file_path;
					}
					$fileHtml = '';
					if(!empty ($file->file_free_download)) {
						$fileHtml = '<a class="hikashop_product_file_link" href="' . hikashop_completeLink('product&task=download&file_id=' . $file->file_id) . '">' . $file->file_name . '</a><br/>';
					}
					$html[] = $fileHtml;
				}
				echo implode('<br/>', $html);
		?></fieldset>
	</div>
<?php
			}
		}
	}
}

$this->params->set('show_price_weight', 0);
$this->product = $this->element;

?>
	<div class="hikashop_submodules" id="hikashop_submodules" style="clear:both">
<?php
	if(!empty ($this->modules) && is_array($this->modules)) {
		jimport('joomla.application.module.helper');
		foreach($this->modules as $module) {
			echo JModuleHelper::renderModule($module);
		}
	}
?>
	</div>
	<div class="hikashop_external_comments" id="hikashop_external_comments" style="clear:both">
<?php
$config =& hikashop_config();
if($config->get('comments_feature') == 'jcomments') {
	$comments = HIKASHOP_ROOT . 'components' . DS . 'com_jcomments' . DS . 'jcomments.php';
	if(file_exists($comments)) {
		require_once ($comments);
		if(hikashop_getCID('product_id') != $this->product->product_id && isset($this->product->main->product_name)) {
			$product_id = $this->product->main->product_id;
			$product_name = $this->product->main->product_name;
		} else {
			$product_id = $this->product->product_id;
			$product_name = $this->product->product_name;
		}
		echo JComments::showComments($product_id, 'com_hikashop', $product_name);
	}
} elseif($config->get('comments_feature') == 'jomcomment') {
	$comments = HIKASHOP_ROOT . 'plugins' . DS . 'content' . DS . 'jom_comment_bot.php';
	if(file_exists($comments)) {
		require_once ($comments);
		if(hikashop_getCID('product_id') != $this->product->product_id && isset($this->product->main->product_name))
			$product_id = $this->product->main->product_id;
		else
			$product_id = $this->product->product_id;
		echo jomcomment($product_id, 'com_hikashop');
	}
}
?>
	</div>
</div>

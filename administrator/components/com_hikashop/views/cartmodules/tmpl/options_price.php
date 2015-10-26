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
$style = '';
if(@$this->element['small_cart'])
		$style='display: none;';
?>
<div class="hkc-xl-6 hikashop_module_subblock hikashop_module_edit_product" style="<?php echo $style; ?>" data-part="mini_cart">
	<div class="hikashop_module_subblock_content">
<?php
$style = '';
if(!@$this->element['show_price'])
		$style='display: none;';
?>
		<div class="hikashop_menu_subblock_title hikashop_module_edit_display_settings_div_title"><?php echo JText::_('HIKA_PRICE_DISPLAY'); ?></div>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<?php echo JText::_('DISPLAY_PRICE');?>
			</dt>
			<dd class="hikashop_option_value">
				<?php
					if(!isset($this->element['show_price'])) $this->element['show_price'] = '-1';
					foreach($this->arr as $v){
						if($v->value == $this->default_params['show_price'])
							$v->default = true;
					}
					echo JHTML::_('hikaselect.radiolist',  $this->arr, $this->name.'[show_price]', 'data-control="price"', 'value', 'text', @$this->element['show_price']);
				?>
			</dd>
		</dl>
		<dl class="hika_options" id="price_display_type_line" style="<?php echo $style; ?>" data-part="price">
			<dt class="hikashop_option_name">
				<?php echo JText::_('HIKA_PRICE_TYPE');?>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['price_display_type'])) $this->element['price_display_type'] = 'inherit';
				echo $this->priceDisplayType->display( $this->name.'[price_display_type]',@$this->element['price_display_type']); ?>
			</dd>
		</dl>
		<dl class="hika_options" id="show_taxed_price_line" style="<?php echo $style; ?>" data-part="price">
			<dt class="hikashop_option_name">
				<?php echo JText::_('SHOW_TAXED_PRICES');?>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['price_with_tax'])) $this->element['price_with_tax'] = 3;
				echo $this->pricetaxType->display($this->name.'[price_with_tax]' , $this->element['price_with_tax'],true); ?>
			</dd>
		</dl>
		<dl class="hika_options" id="show_original_price_line" style="<?php echo $style; ?>" data-part="price">
			<dt class="hikashop_option_name">
				<?php echo JText::_('HIKA_ORIGINAL_CURRENCY');?>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['show_original_price'])) $this->element['show_original_price'] = '-1';
				foreach($this->arr as $v){
					if($v->value == $this->default_params['show_original_price'])
						$v->default = true;
				}
				echo JHTML::_('hikaselect.radiolist', $this->arr, $this->name.'[show_original_price]' , '', 'value', 'text', @$this->element['show_original_price']); ?>
			</dd>
		</dl>
		<dl class="hika_options" id="show_discount_line" style="<?php echo $style; ?>" data-part="price">
			<dt class="hikashop_option_name">
				<?php echo JText::_('HIKA_DISCOUNT_DISPLAY');?>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['show_discount'])) $this->element['show_discount'] = 3;
				echo $this->discountDisplayType->display( $this->name.'[show_discount]' ,@$this->element['show_discount']); ?>
			</dd>
		</dl>
	</div>
</div>
<?php
$js = "
window.hikashop.ready(function(){
	hkjQuery('[data-control=\'price\']').change(function(){
		if(hkjQuery(this).val() == '1' || (hkjQuery(this).val() == '-1' && '".@$this->default_params['show_price']."' == '1'))
			hkjQuery('[data-part=\'price\']').show();
		else
			hkjQuery('[data-part=\'price\']').hide();
	});
});
";
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($js);

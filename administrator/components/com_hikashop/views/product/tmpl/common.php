<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>				<tr class="hikashop_product_quantity_row">
					<td class="key">
							<?php echo JText::_( 'PRODUCT_QUANTITY' ); ?>
					</td>
					<td>
						<input type="text" name="data[product][product_quantity]" <?php echo is_numeric(@$this->element->product_max_per_order)? '' : 'onfocus="if(isNaN(parseInt(this.value))) this.value=\'\';"'; ?> value="<?php echo @$this->element->product_quantity; ?>" />
					</td>
				</tr>
				<?php
				if($this->config->get('show_quantity_field')==-2){ ?>
				<tr class="hikashop_product_display_quantity_field_row">
					<td class="key">
							<?php echo JText::_('QUANTITY_FIELD'); ?>
					</td>
					<td>
						<?php echo $this->quantity->display('data[product][product_display_quantity_field]',@$this->element->product_display_quantity_field,false);?>
					</td>
				</tr>
				<?php } ?>
				<tr class="hikashop_product_min_per_order_row">
					<td class="key">
							<?php echo JText::_( 'PRODUCT_MIN_QUANTITY_PER_ORDER' ); ?>
					</td>
					<td>
						<input type="text" name="data[product][product_min_per_order]" value="<?php echo (int)@$this->element->product_min_per_order; ?>" />
					</td>
				</tr>
				<tr class="hikashop_product_max_per_order_row">
					<td class="key">
							<?php echo JText::_( 'PRODUCT_MAX_QUANTITY_PER_ORDER' ); ?>
					</td>
					<td>
						<input type="text" name="data[product][product_max_per_order]" <?php echo is_numeric(@$this->element->product_max_per_order)? '' : 'onfocus="if(isNaN(parseInt(this.value))) this.value=\'\';"'; ?> value="<?php echo @$this->element->product_max_per_order; ?>" />
					</td>
				</tr>
				<tr class="hikashop_product_sale_start_row">
					<td class="key">
							<?php echo JText::_( 'PRODUCT_SALE_START' ); ?>
					</td>
					<td>
						<?php echo JHTML::_('calendar', hikashop_getDate((@$this->element->product_sale_start?@$this->element->product_sale_start:''),'%Y-%m-%d %H:%M'), 'data[product][product_sale_start]','product_sale_start','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
					</td>
				</tr>
				<tr class="hikashop_product_sale_end_row">
					<td class="key">
							<?php echo JText::_( 'PRODUCT_SALE_END' ); ?>
					</td>
					<td>
						<?php echo JHTML::_('calendar', hikashop_getDate((@$this->element->product_sale_end?@$this->element->product_sale_end:''),'%Y-%m-%d %H:%M'), 'data[product][product_sale_end]','product_sale_end','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
					</td>
				</tr>
				<tr class="hikashop_product_msrp_row">
					<td class="key">
							<?php echo JText::_( 'PRODUCT_MSRP' ); ?>
					</td>
					<?php $currencyClass = hikashop_get('class.currency'); $curr = ''; $mainCurr = $currencyClass->mainCurrency(); $mainCurr = $currencyClass->getCurrencies(@$mainCurr,$curr); ?>
					<td>
						<input type="text" name="data[product][product_msrp]" value="<?php echo @$this->element->product_msrp; ?>"/><?php echo ' '.@$mainCurr[1]->currency_symbol.' '.@$mainCurr[1]->currency_code; ?>
					</td>
				</tr>
				<tr class="hikashop_product_warehouse_id_row">
					<td class="key">
						<?php echo JText::_( 'WAREHOUSE' ); ?>
					</td>
					<td><?php
						echo $this->warehouseType->display('data[product][product_warehouse_id]', @$this->element->product_warehouse_id, true);
					?></td>
				</tr>
				<tr class="hikashop_product_type_row">
					<td class="key">
							<?php echo JText::_( 'WIZARD_PRODUCT_TYPE' ); ?>
					</td>
					<td>
						<?php if(empty($this->product_type)) {if(!empty($this->element->product_weight) && hikashop_toFloat($this->element->product_weight) != 0.0){$this->product_type='shippable';}else{$this->product_type='virtual';} }
						$arr = array(
							JHTML::_('select.option', 'shippable', JText::_('WIZARD_REAL')),
							JHTML::_('select.option', 'virtual', JText::_('WIZARD_VIRTUAL'))
						);
						echo JHTML::_('hikaselect.genericlist', $arr, "product_type" , ' onchange="hikashopSetShippable(this.value);"','value', 'text', @$this->product_type); ?>
						<script type="text/javascript">
						function hikashopSetShippable(value){
							var display = false, fields = ['hikashop_product_weight_row', 'hikashop_product_volume_row'];
							if(value == 'shippable') display = true;
							window.hikashop.setArrayDisplay(fields, display);
						}
						window.hikashop.ready(function(){ hikashopSetShippable('<?php echo $this->product_type; ?>'); });
						</script>
					</td>
				</tr>
				<tr id="hikashop_product_weight_row" class="hikashop_product_weight_row">
					<td class="key">
							<?php echo JText::_( 'PRODUCT_WEIGHT' ); ?>
					</td>
					<td>
						<input type="text" name="data[product][product_weight]" value="<?php echo @$this->element->product_weight; ?>"/><?php echo $this->weight->display('data[product][product_weight_unit]',@$this->element->product_weight_unit); ?>
					</td>
				</tr>
				<tr id="hikashop_product_volume_row" class="hikashop_product_volume_row">
					<td class="key">
						<?php echo JText::_( 'PRODUCT_VOLUME' ); ?>
					</td>
					<td>
						<div class="input-prepend">
							<span class="add-on"><i class="icon-14-length"></i></span>
							<input size="10" type="text" name="data[product][product_length]" value="<?php echo @$this->element->product_length; ?>"/>
						</div>
						<div class="input-prepend">
							<span class="add-on"><i class="icon-14-width"></i></span>
							<input size="10" type="text" name="data[product][product_width]" value="<?php echo @$this->element->product_width; ?>"/>
						</div>
						<div class="input-prepend">
							<span class="add-on"><i class="icon-14-height"></i></span>
							<input size="10" type="text" name="data[product][product_height]" value="<?php echo @$this->element->product_height; ?>"/>
						</div>
						<?php echo $this->volume->display('data[product][product_dimension_unit]',@$this->element->product_dimension_unit); ?>
					</td>
				</tr>
				<tr class="hikashop_product_published_row">
					<td class="key">
							<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
					</td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', "data[product][product_published]" , '',@$this->element->product_published	); ?>
					</td>
				</tr>
<?php
	JPluginHelper::importPlugin( 'hikashop' );
	$dispatcher = JDispatcher::getInstance();
	$html = array();
	$dispatcher->trigger( 'onProductFormDisplay', array( & $this->element, & $html ) );
	if(!empty($html)){
		foreach($html as $h){
			echo $h;
		}
	}
?>
				<tr class="hikashop_product_access_row">
					<td colspan="2">
						<fieldset class="adminform">
						<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
						<?php
						if(hikashop_level(2)){
							$acltype = hikashop_get('type.acl');
							echo $acltype->display('product_access',@$this->element->product_access,'product');
						}else{
							echo hikashop_getUpgradeLink('business');
						} ?>
						</fieldset>
					</td>
				</tr>
				<tr class="hikashop_product_custom_html_row">
					<td colspan="2">
<?php
	$html = array();
	$dispatcher->trigger( 'onProductDisplay', array( & $this->element, & $html ) );
	if(!empty($html)){
		foreach($html as $h){
			echo $h;
		}
	}
?>
					</td>
				</tr>

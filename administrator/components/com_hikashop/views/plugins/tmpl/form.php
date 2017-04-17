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
<div>
	<form action="index.php?option=<?php echo HIKASHOP_COMPONENT; ?>&amp;ctrl=plugins" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
<?php
if(!empty($this->plugin->pluginView)) {
	$this->setLayout($this->plugin->pluginView);
	echo $this->loadTemplate();
} else if(!empty($this->plugin->noForm)) {
	echo $this->content;
} else {
	if(empty($this->plugin_type)) $this->plugin_type= '';

	$type = $this->plugin_type;
	$upType = strtoupper($type);
	$plugin_published = $type . '_published';
	$plugin_images = $type . '_images';
	$plugin_name = $type . '_name';
	$plugin_name_input = $plugin_name . '_input';

	if(!HIKASHOP_BACK_RESPONSIVE) {
?>
<div id="page-plugins">
	<table style="width:100%">
	<tr>
		<td valign="top" width="50%">
<?php
	} else {
?>
<div id="page-plugins" class="row-fluid">
	<div class="span6">
<?php
	}
?>
		<fieldset class="adminform" id="htmlfieldset">
			<legend><?php echo JText::_( 'MAIN_INFORMATION' ); ?></legend>
<?php
	$this->$plugin_name_input = 'data['.$type.']['.$plugin_name.']';
	if($this->translation) {
		$this->setLayout('translation');
	} else {
		$this->setLayout('normal');
	}
	echo $this->loadTemplate();
?>
		</fieldset>
<?php
	if(!HIKASHOP_BACK_RESPONSIVE) {
?>
		</td>
		<td valign="top" width="50%">
<?php
	} else {
?>
	</div>
	<div class="span6 hikaspanleft">
<?php
	}
?>
		<fieldset class="adminform">
			<legend><?php echo JText::_('PLUGIN_GENERIC_CONFIGURATION'); ?></legend>
			<table class="admintable table">
<?php
	if($this->multiple_plugin) {
?>
				<tr>
					<td class="key"><?php
						echo JText::_('HIKA_PUBLISHED');
					?></td>
					<td><?php
						echo JHTML::_('hikaselect.booleanlist', 'data['. $type.']['.$type.'_published]', '', @$this->element->$plugin_published);
					?></td>
				</tr>
<?php
	}

	if($this->plugin_type == 'payment' || $this->plugin_type == 'shipping') {
?>
				<tr>
					<td class="key"><?php
						echo JText::_( 'HIKA_IMAGES' );
					?></td>
					<td>
						<input type="text" id="plugin_images" name="data[<?php echo $type;?>][<?php echo $type;?>_images]" value="<?php echo @$this->element->$plugin_images; ?>" /><?php
						echo $this->popup->display(
							'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('HIKA_EDIT').'"/>',
							'HIKA_IMAGES',
							'\''.hikashop_completeLink('plugins&task=selectimages&type='.$type,true).'&values=\'+document.getElementById(\'plugin_images\').value',
							'plugin_images_link',
							760, 480, '', '', 'link',true
						);
					?></td>
				</tr>
<?php
	}

	if($this->plugin_type == 'payment') {
?>
				<tr>
					<td class="key"><?php
						echo JText::_('PRICE');
					?></td>
					<td>
						<input type="text" name="data[payment][payment_price]" value="<?php echo @$this->element->payment_price; ?>" /><?php echo $this->currencies->display('data[payment][payment_params][payment_currency]',@$this->element->payment_params->payment_currency); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_percentage]"><?php
							echo JText::_('DISCOUNT_PERCENT_AMOUNT');
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_percentage]" value="<?php echo (float)@$this->element->payment_params->payment_percentage; ?>" />%
					</td>
				</tr>
<!--jms2win_begin -->
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_tax_id]"><?php
							echo JText::_( 'PRODUCT_TAXATION_CATEGORY' );
						?></label>
					</td>
					<td><?php
						$categoryType = hikashop_get('type.categorysub');
						$categoryType->type='tax';
						$categoryType->field='category_id';

						echo $categoryType->display('data[payment][payment_params][payment_tax_id]', @$this->element->payment_params->payment_tax_id, 'tax');
					?></td>
				</tr>
				<tr style="display:none;">
					<td class="key">
						<label for="data[payment][payment_params][payment_algorithm]"><?php
							echo JText::_( 'Payment algorithm' );
						?></label>
					</td>
					<td><?php
						$values = array(
							JHTML::_('select.option', '0', JText::_('Default')),
							JHTML::_('select.option', 'realcost', JText::_('Real cost')),
						);

						echo JHTML::_('select.genericlist', $values, "data[payment][payment_params][payment_algorithm]" , 'onchange="hika_payment_algorithm(this);"', 'value', 'text', @$this->element->payment_params->payment_algorithm );
					?>
<script type="text/javascript">
function hika_payment_algorithm(el) {
	var t = document.getElementById('hika_payment_algorithm_text');
	if(!t) return;
	t.style.display = (el.value == 3 || el.value == 4) ? '' : 'none';
}
</script>
					</td>
				</tr>
<!-- jms2win_end -->
<?php
	}
	if($this->plugin_type == 'shipping' && $this->multiple_interface) {
?>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_price]"><?php
							echo JText::_('PRICE');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_price]" value="<?php echo @$this->element->shipping_price; ?>" /><?php echo $this->data['currency']->display('data[shipping][shipping_currency_id]',@$this->element->shipping_currency_id); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_price][shipping_percentage]"><?php
							echo JText::_('DISCOUNT_PERCENT_AMOUNT');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_percentage]" value="<?php echo (float)@$this->element->shipping_params->shipping_percentage; ?>" />%
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="shipping_tax_id"><?php
							echo JText::_( 'TAXATION_CATEGORY' );
						?></label>
					</td>
					<td><?php
						echo $this->categoryType->display('data[shipping][shipping_tax_id]',@$this->element->shipping_tax_id,true);
					?></td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_per_product]"><?php
							echo JText::_('USE_PRICE_PER_PRODUCT');
						?></label>
					</td>
					<td><?php
						if(!isset($this->element->shipping_params->shipping_per_product))
							$this->element->shipping_params->shipping_per_product = false;
						echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][shipping_per_product]" , ' onchange="hikashop_switch_tr(this,\'hikashop_shipping_per_product_\',2)"', @$this->element->shipping_params->shipping_per_product);
					?></td>
				</tr>
				<tr id="hikashop_shipping_per_product_1"<?php if($this->element->shipping_params->shipping_per_product == false) { echo ' style="display:none;"';}?>>
					<td class="key">
						<label for="data[shipping][shipping_price_per_product]"><?php
							echo JText::_( 'PRICE_PER_PRODUCT' );
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_price_per_product]" value="<?php echo @$this->element->shipping_params->shipping_price_per_product; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_override_address]"><?php
							echo JText::_( 'OVERRIDE_SHIPPING_ADDRESS' );
						?></label>
					</td>
					<td><?php
						$values = array(
							JHTML::_('select.option', '0', JText::_('HIKASHOP_NO')),
							JHTML::_('select.option', '1', JText::_('STORE_ADDRESS')),
							JHTML::_('select.option', '2', JText::_('HIKA_HIDE')),
							JHTML::_('select.option', '3', JText::_('TEXT_VERSION')),
							JHTML::_('select.option', '4', JText::_('HTML_VERSION'))
						);

						echo JHTML::_('select.genericlist', $values, "data[shipping][shipping_params][shipping_override_address]" , 'onchange="hika_shipping_override(this);"', 'value', 'text', @$this->element->shipping_params->shipping_override_address );
					?>
						<script type="text/javascript">
						function hika_shipping_override(el) {
							var t = document.getElementById('hikashop_shipping_override_text');
							if(!t) return;
							if(el.value == 3 || el.value == 4) {
								t.style.display = '';
							} else {
								t.style.display = 'none';
							}
						}
						</script>
					</td>
				</tr>
				<tr id="hikashop_shipping_override_text" style="<?php
						$override = (int)@$this->element->shipping_params->shipping_override_address;
						if( $override != 3 && $override != 4 ) { echo 'display:none;'; }
					?>">
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_override_address_text]"><?php
							echo JText::_( 'OVERRIDE_SHIPPING_ADDRESS_TEXT' );
						?></label>
					</td>
					<td>
						<textarea name="data[shipping][shipping_params][shipping_override_address_text]"><?php
							echo @$this->element->shipping_params->shipping_override_address_text;
						?></textarea>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][override_tax_zone]"><?php
							echo JText::_('OVERRIDE_TAX_ZONE');
						?></label>
					</td>
					<td>
						<span id="override_tax_zone_id"><?php
							echo @$this->element->shipping_params->override_tax_zone->zone_id.' '.@$this->element->shipping_params->override_tax_zone->zone_name_english;
						?><input type="hidden" name="data[shipping][shipping_params][override_tax_zone]" value="<?php echo @$this->element->shipping_params->override_tax_zone->zone_id; ?>" />
						</span><?php
							echo $this->popup->display(
								'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('HIKA_EDIT').'"/>',
								'OVERRIDE_TAX_ZONE',
								 hikashop_completeLink("zone&task=selectchildlisting&type=".$type."&subtype=override_tax_zone_id&column=zone_id&map=data[shipping][shipping_params][override_tax_zone]",true ),
								'override_tax_zone_id_link',
								760, 480, '', '', 'link'
							);
						?><a href="#" onclick="document.getElementById('override_tax_zone_id').innerHTML='<input type=\'hidden\' name=\'data[shipping][shipping_params][override_tax_zone]\' value=\'\' />';return false;" >
							<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
						</a>
					</td>
				</tr>
<?php
	}

	if(!empty($this->extra_config)) {
		echo implode("\r\n", $this->extra_config);
	}
?>
			</table>
		</fieldset>
<?php
	if(!empty($this->content)) {
?>
		<fieldset class="adminform">
			<legend><?php echo JText::_('PLUGIN_SPECIFIC_CONFIGURATION'); ?></legend>
			<table class="admintable table"><?php
				echo $this->content;
			?></table>
		</fieldset>
<?php
	}

	if(!empty($this->extra_blocks)) {
		echo implode("\r\n", $this->extra_blocks);
	}

	if($this->plugin_type == 'payment' || $this->plugin_type == 'shipping') {
?>
		<fieldset class="adminform">
<?php
		$restriction_fields = array(
			'zone_id',
			'payment_shipping_methods_id',
			'payment_currency',
			'shipping_currency',
			array('shipping_params','shipping_warehouse_filter'),
			array('shipping_params','shipping_min_price'),
			array('shipping_params','shipping_max_price'),
			array('shipping_params','shipping_min_weight'),
			array('shipping_params','shipping_max_weight'),
			array('shipping_params','shipping_min_volume'),
			array('shipping_params','shipping_max_volume'),
			array('shipping_params','shipping_zip_prefix'),
			array('shipping_params','shipping_min_zip'),
			array('shipping_params','shipping_max_zip'),
			array('shipping_params','shipping_zip_suffix'),
		);

		$field_style = 'style="display:none;"';
		$checked = '';
		$is_restriction = false;
		foreach($restriction_fields as $f) {
			$e = $this->element;
			if(is_array($f)) {
				$g = $f[0];
				$f = $f[1];
				if(!empty($g)) {
					if(isset($this->element->$g))
						$e = $this->element->$g;
					else
						continue;
				}
			}
			if(!empty($e->$f)) {
				if(is_array($e->$f)) {
					if(count($e->$f) > 1 || (count($e->$f) == 1 && reset($e->$f) != '')) {
						$is_restriction = true;
						break;
					}
				} else {
					$is_restriction = true;
					break;
				}
			}
		}
		if($is_restriction) {
			$field_style = '';
			$checked='checked';
		}
?>
			<legend><input type="checkbox" id="restrictions_checkbox" name="restrictions_checkbox" onchange="var display_fieldset ='none'; if(this.checked){ display_fieldset = 'block'; } document.getElementById('restrictions').style.display=display_fieldset;" <?php echo $checked;?> /><label style="cursor:pointer;" for="restrictions_checkbox"><?php echo JText::_('HIKA_RESTRICTIONS'); ?></label></legend>
			<div id="restrictions" <?php echo $field_style; ?>>
				<table class="admintable table">
					<tr>
						<td class="key"><?php echo JText::_('ZONE'); ?></td>
						<td>
							<span id="zone_id"><?php
								echo @$this->element->zone_id.' '.@$this->element->zone_name_english;
								$plugin_zone_namekey = $type.'_zone_namekey';
							?><input type="hidden" name="data[<?php echo $type;?>][<?php echo $type;?>_zone_namekey]" value="<?php echo @$this->element->$plugin_zone_namekey; ?>" />
							</span><?php
								echo $this->popup->display(
									'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('HIKA_EDIT').'"/>',
									'ZONE',
									 hikashop_completeLink("zone&task=selectchildlisting&type=".$type,true ),
									'zone_id_link',
									760, 480, '', '', 'link'
								);
							?><a href="#" onclick="document.getElementById('zone_id').innerHTML='<input type=\'hidden\' name=\'data[<?php echo $type;?>][<?php echo $type;?>_zone_namekey]\' value=\'\' />';return false;" >
								<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
							</a>
						</td>
					</tr>
<?php
		if($this->plugin_type == 'payment') {
?>
					<tr>
						<td class="key"><?php echo JText::_('HIKASHOP_SHIPPING_METHOD'); ?></td>
						<td><?php
							echo $this->shippingMethods->display('data[payment][payment_shipping_methods][]',@$this->element->payment_shipping_methods_type,@$this->element->payment_shipping_methods_id,true,'multiple="multiple" size="3"');
			if(!HIKASHOP_BACK_RESPONSIVE) {
						?><br/><a href="javascript:void(0)" onclick="selectNone('datapaymentpayment_shipping_methods');"><?php echo JText::_('HIKA_NO_RESCTION'); ?></a>
<?php
			}
?>
						</td>
					</tr>
<?php
		}
?>
					<tr>
						<td class="key"><?php
							echo JText::_('CURRENCY');
						?></td>
						<td><?php $name = $this->plugin_type.'_currency';
							echo $this->currencies->display('data['.$this->plugin_type.']['.$name.'][]', @$this->element->$name, 'multiple="multiple" size="3"');
			if(!HIKASHOP_BACK_RESPONSIVE) {
						?><br/><a href="javascript:void(0)" onclick="selectNone('data<?php echo $this->plugin_type.$name; ?>');"><?php echo JText::_('HIKA_NO_RESCTION'); ?></a>
<?php
			}
?>
						</td>
					</tr>
<?php

		if($this->plugin_type == 'shipping' && $this->multiple_interface) {
?>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_warehouse_filter]"><?php
							echo JText::_('WAREHOUSE');
						?></label>
					</td>
					<td>
						<?php echo $this->warehouseType->display('data[shipping][shipping_params][shipping_warehouse_filter]', @$this->element->shipping_params->shipping_warehouse_filter, true) ;?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_min_price]"><?php
							echo JText::_('SHIPPING_MIN_PRICE');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_min_price]" value="<?php echo @$this->element->shipping_params->shipping_min_price; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_max_price]"><?php
							echo JText::_( 'SHIPPING_MAX_PRICE' );
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_max_price]" value="<?php echo @$this->element->shipping_params->shipping_max_price; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_virtual_included]"><?php echo JText::_( 'INCLUDE_VIRTUAL_PRODUCTS_PRICE' ); ?></label>
					</td>
					<td><?php
						if(!isset($this->element->shipping_params->shipping_virtual_included)){
							$config = hikashop_config();
							$this->element->shipping_params->shipping_virtual_included = $config->get('force_shipping',1);
						}
						echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][shipping_virtual_included]" , '',$this->element->shipping_params->shipping_virtual_included);
					?></td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_price_use_tax]"><?php
							echo JText::_('WITH_TAX');
						?></label>
					</td>
					<td>
						<?php
						if(!isset($this->element->shipping_params->shipping_price_use_tax)) $this->element->shipping_params->shipping_price_use_tax=1;
						echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][shipping_price_use_tax]" , '', $this->element->shipping_params->shipping_price_use_tax); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_min_quantity]"><?php
							echo JText::_('SHIPPING_MIN_QUANTITY');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_min_quantity]" value="<?php echo @$this->element->shipping_params->shipping_min_quantity; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_max_quantity]"><?php
							echo JText::_('SHIPPING_MAX_QUANTITY');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_max_quantity]" value="<?php echo @$this->element->shipping_params->shipping_max_quantity; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_min_weight]"><?php
							echo JText::_('SHIPPING_MIN_WEIGHT');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_min_weight]" value="<?php echo @$this->element->shipping_params->shipping_min_weight; ?>"/>
						<?php
							echo $this->data['weight']->display('data[shipping][shipping_params][shipping_weight_unit]',@$this->element->shipping_params->shipping_weight_unit);
						?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_max_weight]"><?php
							echo JText::_('SHIPPING_MAX_WEIGHT');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_max_weight]" value="<?php echo @$this->element->shipping_params->shipping_max_weight; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_min_volume]"><?php
							echo JText::_('SHIPPING_MIN_VOLUME');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_min_volume]" value="<?php echo @$this->element->shipping_params->shipping_min_volume; ?>"/>
						<?php
							echo $this->data['volume']->display('data[shipping][shipping_params][shipping_size_unit]',@$this->element->shipping_params->shipping_size_unit);
						?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_max_volume]"><?php
							echo JText::_('SHIPPING_MAX_VOLUME');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_max_volume]" value="<?php echo @$this->element->shipping_params->shipping_max_volume; ?>"/>
					</td>
				</tr>
<?php
		}

		if($this->plugin_type == 'payment' && $this->multiple_interface) {
?>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_min_price]"><?php
							echo JText::_('SHIPPING_MIN_PRICE');
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_min_price]" value="<?php echo @$this->element->payment_params->payment_min_price; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_max_price]"><?php
							echo JText::_( 'SHIPPING_MAX_PRICE' );
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_max_price]" value="<?php echo @$this->element->payment_params->payment_max_price; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_price_use_tax]"><?php
							echo JText::_('WITH_TAX');
						?></label>
					</td>
					<td>
						<?php
						if(!isset($this->element->payment_params->payment_price_use_tax)) $this->element->payment_params->payment_price_use_tax=1;
						echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][payment_price_use_tax]" , '', $this->element->payment_params->payment_price_use_tax); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_min_quantity]"><?php
							echo JText::_('SHIPPING_MIN_QUANTITY');
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_min_quantity]" value="<?php echo @$this->element->payment_params->payment_min_quantity; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_max_quantity]"><?php
							echo JText::_('SHIPPING_MAX_QUANTITY');
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_max_quantity]" value="<?php echo @$this->element->payment_params->payment_max_quantity; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_min_weight]"><?php
							echo JText::_('SHIPPING_MIN_WEIGHT');
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_min_weight]" value="<?php echo @$this->element->payment_params->payment_min_weight; ?>"/>
						<?php
							echo $this->data['weight']->display('data[payment][payment_params][payment_weight_unit]',@$this->element->payment_params->payment_weight_unit);
						?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_max_weight]"><?php
							echo JText::_('SHIPPING_MAX_WEIGHT');
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_max_weight]" value="<?php echo @$this->element->payment_params->payment_max_weight; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_min_volume]"><?php
							echo JText::_('SHIPPING_MIN_VOLUME');
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_min_volume]" value="<?php echo @$this->element->payment_params->payment_min_volume; ?>"/>
						<?php
							echo $this->data['volume']->display('data[payment][payment_params][payment_size_unit]',@$this->element->payment_params->payment_size_unit);
						?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_max_volume]"><?php
							echo JText::_('SHIPPING_MAX_VOLUME');
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_max_volume]" value="<?php echo @$this->element->payment_params->payment_max_volume; ?>"/>
					</td>
				</tr>
<?php
		}


		if($this->plugin_type == 'shipping') {
?>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_zip_prefix]"><?php
							echo JText::_('SHIPPING_PREFIX');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_zip_prefix]" value="<?php echo @$this->element->shipping_params->shipping_zip_prefix; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_min_zip]"><?php
							echo JText::_('SHIPPING_MIN_ZIP');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_min_zip]" value="<?php echo @$this->element->shipping_params->shipping_min_zip; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_max_zip]"><?php
							echo JText::_('SHIPPING_MAX_ZIP');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_max_zip]" value="<?php echo @$this->element->shipping_params->shipping_max_zip; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[shipping][shipping_params][shipping_zip_suffix]"><?php
							echo JText::_('SHIPPING_SUFFIX');
						?></label>
					</td>
					<td>
						<input type="text" name="data[shipping][shipping_params][shipping_zip_suffix]" value="<?php echo @$this->element->shipping_params->shipping_zip_suffix; ?>"/>
					</td>
				</tr>
<?php
		}
		if($this->plugin_type == 'payment') {
?>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_zip_prefix]"><?php
							echo JText::_('SHIPPING_PREFIX');
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_zip_prefix]" value="<?php echo @$this->element->payment_params->payment_zip_prefix; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_min_zip]"><?php
							echo JText::_('SHIPPING_MIN_ZIP');
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_min_zip]" value="<?php echo @$this->element->payment_params->payment_min_zip; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_max_zip]"><?php
							echo JText::_('SHIPPING_MAX_ZIP');
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_max_zip]" value="<?php echo @$this->element->payment_params->payment_max_zip; ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[payment][payment_params][payment_zip_suffix]"><?php
							echo JText::_('SHIPPING_SUFFIX');
						?></label>
					</td>
					<td>
						<input type="text" name="data[payment][payment_params][payment_zip_suffix]" value="<?php echo @$this->element->payment_params->payment_zip_suffix; ?>"/>
					</td>
				</tr>
<?php
		}
?>
				</table>
			</div>
		</fieldset>
<?php
	}
?>
		<fieldset class="adminform">
			<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
<?php
	if(hikashop_level(2)) {
		$acltype = hikashop_get('type.acl');
		$access = $type.'_access';
		echo $acltype->display($access, @$this->element->$access, $type);
	} else {
		echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
	}
?>
		</fieldset>
<?php
	if(!HIKASHOP_BACK_RESPONSIVE) {
?>
		</td>
	</tr>
	</table>
</div>
<?php
	} else {
?>
	</div>
</div>
<?php
	}
?>
		<input type="hidden" name="data[<?php echo $type;?>][<?php echo $type;?>_id]" value="<?php echo $this->id;?>"/>
		<input type="hidden" name="data[<?php echo $type;?>][<?php echo $type;?>_type]" value="<?php echo $this->name;?>"/>
		<input type="hidden" name="task" value="save"/>
<?php
}
?>
		<input type="hidden" name="name" value="<?php echo $this->name;?>"/>
		<input type="hidden" name="subtask" value="<?php echo JRequest::getVar('subtask', '');?>"/>
		<input type="hidden" name="ctrl" value="plugins" />
		<input type="hidden" name="plugin_type" value="<?php echo $this->plugin_type;?>" />
		<input type="hidden" name="<?php echo $this->plugin_type; ?>_plugin_type" value="<?php echo $this->name; ?>"/>
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
<script type="text/javascript">
function selectNone(name) {
	var el = document.getElementById(name);
	if(!el) return false;
	for (var i = 0; i < el.options.length; i++) {
		el.options[i].selected = false;
	}
}
function hikashop_switch_tr(el, name, num) {
	var d = document, s = (el.value == '1');
	if(!el.checked) { s = !s; }
	if(num === undefined) {
		var e = d.getElementById(name);
		if(!e) return;
		e.style.display = (s?'':'none');
		return;
	}
	var e = null;
	for(var i = num; i >= 0; i--) {
		var e = d.getElementById(name + i);
		if(e) {
			e.style.display = (s?'':'none');
		}
	}
}
</script>

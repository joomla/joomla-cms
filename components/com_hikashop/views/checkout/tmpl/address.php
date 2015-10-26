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
if($this->identified){
	$config = hikashop_config();
	$address_selector = (int)$config->get('checkout_address_selector', 0);

	$mainId = 'hikashop_checkout_address_billing_only';
	$leftId = 'hikashop_checkout_billing_address';
	$mainClass = 'hikashop_checkout_address_billing_only';
	$leftClass = 'hikashop_checkout_billing_address';
	if($this->has_shipping) {
		$mainId = 'hikashop_checkout_address';
		$leftId = 'hikashop_checkout_address_left_part';
		$mainClass = 'hikashop_checkout_address';
		$leftClass = 'hikashop_checkout_address_left_part';
	}
	if(HIKASHOP_RESPONSIVE) {
		$mainClass .= ' '.HK_GRID_ROW;
		$leftClass .= ' '.HK_GRID_COL_6;
	}
?>
<div id="<?php echo $mainId; ?>" class="<?php echo $mainClass; ?>">
	<div id="<?php echo $leftId; ?>" class="<?php echo $leftClass; ?>">
		<fieldset class="hika_address_field" id="hikashop_checkout_billing_address">
			<legend><?php echo JText::_('HIKASHOP_BILLING_ADDRESS'); ?></legend>
<?php
	if(empty($address_selector) || $address_selector == 0) {
		$this->type = 'billing';
		echo $this->loadTemplate('view');
	} else {
		$this->type = 'billing';
		echo $this->loadTemplate('select');
	}

	if($this->has_shipping) {
?>
		</fieldset>
	</div>
	<div id="hikashop_checkout_address_right_part" class="hikashop_checkout_address_right_part<?php if(HIKASHOP_RESPONSIVE){ echo ' '.HK_GRID_COL_6;} ?>">
		<fieldset class="hika_address_field" id="hikashop_checkout_shipping_address">
			<legend><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS'); ?></legend>
<?php
		$checked = '';
		$style = '';

		$override = false;
		foreach($this->currentShipping as $selectedMethod){
			if(!empty($selectedMethod) && method_exists($selectedMethod, 'getShippingAddress')) {
				$override = $selectedMethod->getShippingAddress();
			}
		}

		if(!empty($override)) {
?>				<span class="hikashop_checkout_shipping_address_info"><?php
					echo $override;
				?></span>
<?php
		} else {
			if($config->get('shipping_address_same_checkbox', 1)) {
				$onclick = 'return hikashopSameAddress(this.checked);';
				if($this->shipping_address==$this->billing_address){
					$checked = 'checked="checked" ';
					$style = ' style="display:none"';
					$nb_addresses = count(@$this->addresses);
					if($nb_addresses==1){
						$address = reset($this->addresses);
						if(!empty($address_selector)) {
							$onclick='if(!this.checked) { window.localPage.switchAddr(0, \'shipping\'); } '.$onclick;
						}else{
							$onclick='if(!this.checked) { hikashopEditAddress(document.getElementById(\'hikashop_checkout_shipping_address_edit_'.$address->address_id.'\'),1,false); } '.$onclick;
						}
					}
				}
?>
				<label for="same_address">
					<input class="hikashop_checkout_shipping_same_address inputbox" <?php echo $checked; ?>type="checkbox" id="same_address" name="same_address" value="yes" alt="Same address" onclick="<?php echo $onclick; ?>" />
					<?php echo JText::_('SAME_AS_BILLING');?>
				</label>
<?php
			} else {
				$style = '';
			}
?>
				<div class="hikashop_checkout_shipping_div" id="hikashop_checkout_shipping_div" <?php echo $style;?>>
<?php
			$this->type = 'shipping';
			if(!empty($address_selector)) {
				echo $this->loadTemplate('select');
			} else {
				echo $this->loadTemplate('view');
			}
?>
				</div>
<?php
		}
	}
?>
		</fieldset>
	</div>
</div>
<div style="clear:both"></div>
<?php
}else{
}

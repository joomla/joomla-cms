<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><tr>
	<td class="key">
		<label for="data[payment][payment_params][url]"><?php
			echo JText::_( 'URL' );
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][url]" value="<?php echo $this->escape(@$this->element->payment_params->url); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][email]"><?php
			echo JText::_( 'HIKA_EMAIL' );
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][email]" value="<?php echo $this->escape(@$this->element->payment_params->email); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][address_type]"><?php
			echo JText::_( 'PAYPAL_ADDRESS_TYPE' );
		?></label>
	</td>
	<td><?php
		echo $this->data['address']->display('data[payment][payment_params][address_type]', @$this->element->payment_params->address_type);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][address_override]"><?php
			echo JText::_( 'ADDRESS_OVERRIDE' );
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][address_override]" , '', @$this->element->payment_params->address_override);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][no_shipping]"><?php
			echo JText::_( 'NO_SHIPPING' );
		?></label>
	</td>
	<td><?php
		$arr = array(
			JHTML::_('select.option', '2', '2 '.JText::_('REQUIRED') ),
			JHTML::_('select.option', '1', '1 '.JText::_('HIKASHOP_YES') ),
			JHTML::_('select.option', '0', '0 '.JText::_('HIKASHOP_NO') ),
		);
		echo JHTML::_('hikaselect.radiolist',  $arr, "data[payment][payment_params][no_shipping]", '', 'value', 'text', @$this->element->payment_params->no_shipping);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][notification]"><?php
			echo JText::sprintf('ALLOW_NOTIFICATIONS_FROM_X', $this->element->payment_name);
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][notification]" , '', @$this->element->payment_params->notification);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][details]"><?php
			echo JText::_('SEND_DETAILS_OF_ORDER');
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][details]" , '', @$this->element->payment_params->details);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][notes]"><?php
			echo JText::_('FEDEX_SHOW_NOTES');
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][notes]" , '', @$this->element->payment_params->notes);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][enable_validation]"><?php
			echo JText::_('ENABLE_VALIDATION');
		?></label>
	</td>
	<td><?php
		if(!isset($this->element->payment_params->validation) && isset($this->element->payment_params->enable_validation))
			$this->element->payment_params->validation = !$this->element->payment_params->enable_validation;
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][validation]" , '', @$this->element->payment_params->validation);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][debug]"><?php
			echo JText::_('DEBUG');
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][debug]" , '', @$this->element->payment_params->debug);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][cancel_url]"><?php
			echo JText::_('CANCEL_URL');
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][cancel_url]" value="<?php echo $this->escape(@$this->element->payment_params->cancel_url); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][return_url]"><?php
			echo JText::_('RETURN_URL');
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][return_url]" value="<?php echo $this->escape(@$this->element->payment_params->return_url); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][cpp_header_image]"><?php
			echo JText::_('HEADER_IMAGE');
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][cpp_header_image]" value="<?php echo $this->escape(@$this->element->payment_params->cpp_header_image); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][ips]"><?php
			echo JText::_('IPS');
		?></label>
	</td>
	<td>
		<textarea id="paypal_ips" name="data[payment][payment_params][ips]" ><?php echo (!empty($this->element->payment_params->ips) && is_array($this->element->payment_params->ips)?trim(implode(',',$this->element->payment_params->ips)):''); ?></textarea>
		<br/>
		<a href="#" onclick="return paypal_refreshIps();"><?php echo JText::_('REFRESH_IPS');?></a>
<script type="text/javascript">
function paypal_refreshIps() {
	var w = window, d = document, o = w.Oby;
	o.xRequest('<?php echo hikashop_completeLink('plugins&plugin_type=payment&task=edit&name='.$this->data['name'].'&subtask=ips',true,true);?>', null, function(xhr) {
		d.getElementById('paypal_ips').value = xhr.responseText;
	});
	return false;
}
</script>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][invalid_status]"><?php
			echo JText::_('INVALID_STATUS');
		?></label>
	</td>
	<td><?php
		echo $this->data['order_statuses']->display("data[payment][payment_params][invalid_status]", @$this->element->payment_params->invalid_status);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][pending_status]"><?php
			echo JText::_('PENDING_STATUS');
		?></label>
	</td>
	<td><?php
		echo $this->data['order_statuses']->display("data[payment][payment_params][pending_status]", @$this->element->payment_params->pending_status);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][verified_status]"><?php
			echo JText::_('VERIFIED_STATUS');
		?></label>
	</td>
	<td><?php
		echo $this->data['order_statuses']->display("data[payment][payment_params][verified_status]", @$this->element->payment_params->verified_status);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][rm]"><?php
			echo 'Return method';
		?></label>
	</td>
	<td><?php
		if(!isset($this->element->payment_params->rm))
			$this->element->payment_params->rm = 1;
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][rm]" , '', $this->element->payment_params->rm);
	?></td>
</tr>

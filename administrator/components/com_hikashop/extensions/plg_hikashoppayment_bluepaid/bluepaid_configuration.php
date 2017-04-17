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
		<label for="data[payment][payment_params][url]">
			<?php echo JText::_( 'URL' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][url]" value="<?php echo @$this->element->payment_params->url; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][shop_id]">
			<?php echo JText::_( 'BLUEPAID_SHOP_ID' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][shop_id]" value="<?php echo @$this->element->payment_params->shop_id; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][status_url]">
			<?php echo JText::sprintf( 'STATUS_URL',$this->element->payment_name ); ?>
		</label>
	</td>
	<td>
		<input type="hidden" name="data[payment][payment_params][secure_key]" value="<?php echo @$this->element->payment_params->secure_key; ?>" />
		<?php echo str_replace( '&', '&amp;',@$this->element->payment_params->status_url.'&secure_key='.@$this->element->payment_params->secure_key); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][notification]">
			<?php echo JText::sprintf( 'ALLOW_NOTIFICATIONS_FROM_X', $this->element->payment_name);  ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][notification]" , '',@$this->element->payment_params->notification	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][debug]">
			<?php echo JText::_( 'DEBUG' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][debug]" , '',@$this->element->payment_params->debug	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][ips]">
			<?php echo JText::_( 'IPS' ); ?>
		</label>
	</td>
	<td>
		<textarea id="bluepaid_ips" name="data[payment][payment_params][ips]" ><?php echo (!empty($this->element->payment_params->ips) && is_array($this->element->payment_params->ips)?trim(implode(',',$this->element->payment_params->ips)):''); ?></textarea>
		<br/>
		<a href="#" onclick="return refresh_ips();"><?php echo JText::_('REFRESH_IPS');?></a>
		<script type="text/javascript">
		function refresh_ips() {
			var w = window, d = document, o = w.Oby;
			o.xRequest(
				'<?php echo hikashop_completeLink('plugins&plugin_type=payment&task=edit&name='.$this->name.'&subtask=ips',true,true);?>',
				null,
				function(xhr) {
					d.getElementById('bluepaid_ips').value = xhr.responseText;
				}
			);
			return false;
		}
		</script>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][invalid_status]">
			<?php echo JText::_( 'INVALID_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['order_statuses']->display("data[payment][payment_params][invalid_status]",@$this->element->payment_params->invalid_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][pending_status]">
			<?php echo JText::_( 'PENDING_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['order_statuses']->display("data[payment][payment_params][pending_status]",@$this->element->payment_params->pending_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][verified_status]">
			<?php echo JText::_( 'VERIFIED_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['order_statuses']->display("data[payment][payment_params][verified_status]",@$this->element->payment_params->verified_status); ?>
	</td>
</tr>

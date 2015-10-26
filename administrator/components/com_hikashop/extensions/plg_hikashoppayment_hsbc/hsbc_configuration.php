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
		<label for="data[payment][payment_params][merchantid]">
			<?php echo JText::_( 'MERCHANT_NUMBER' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][merchantid]" value="<?php echo @$this->element->payment_params->merchantid; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][cpihash]">
			<?php echo JText::_( 'CPI_STORE_KEY' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][cpihash]" value="<?php echo @$this->element->payment_params->cpihash; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][instant_capture]">
			<?php echo JText::_( 'INSTANTCAPTURE' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][instant_capture]" , '',@$this->element->payment_params->instant_capture	); ?>
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
		<label for="data[payment][payment_params][cancel_url]">
			<?php echo JText::_( 'CANCEL_URL' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][cancel_url]" value="<?php echo @$this->element->payment_params->cancel_url; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][return_url]">
			<?php echo JText::_( 'RETURN_URL' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][return_url]" value="<?php echo @$this->element->payment_params->return_url; ?>" />
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
		<label for="data[payment][payment_params][verified_status]">
			<?php echo JText::_( 'VERIFIED_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['order_statuses']->display("data[payment][payment_params][verified_status]",@$this->element->payment_params->verified_status); ?>
	</td>
</tr>

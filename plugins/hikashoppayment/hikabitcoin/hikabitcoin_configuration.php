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
defined('_JEXEC') or die('Restricted access');
?>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][apiKey]">
			<?php echo JText::_( 'API Key)' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][apiKey]" value="<?php echo @$this->element->payment_params->apiKey; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][notificationEmail]">
			<?php echo JText::_( 'Notification Email' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][notificationEmail]" value="<?php echo @$this->element->payment_params->notificationEmail; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][test]"><?php
			echo JText::_('DEBUG');
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][test]" , '', @$this->element->payment_params->test);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][notification]"><?php
			echo JText::sprintf('ALLOW_NOTIFICATIONS_FROM_X', @$this->element->payment_name);
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][notification]" , '', @$this->element->payment_params->notification);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][transactionSpeed]">
			<?php echo JText::_( 'Transaction Speed' ); ?>
		</label>
	</td>
	<td><?php
		$arr = array(
			JHTML::_('select.option',  'high', JText::_( 'High' ) ),
			JHTML::_('select.option',  'medium', JText::_( 'Medium' ) ),
			JHTML::_('select.option',  'low', JText::_( 'Low' ) ),
		);
		echo JHTML::_('hikaselect.radiolist',  $arr, "data[payment][payment_params][transactionSpeed]", '', 'value', 'text',@$this->element->payment_params->transactionSpeed);?>	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][address_type]">
			<?php echo JText::_( 'Customer address' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['address']->display('data[payment][payment_params][address_type]',@$this->element->payment_params->address_type); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][address_override]">
			<?php echo JText::_( 'ADDRESS_OVERRIDE' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][address_override]" , '',@$this->element->payment_params->address_override	); ?>
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
		<label for="data[payment][payment_params][paid_status]">
			<?php echo JText::_( 'Paid Status' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][paid_status]",@$this->element->payment_params->paid_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][confirmed_status]">
			<?php echo JText::_( 'Confirmed Status' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][confirmed_status]",@$this->element->payment_params->confirmed_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][complete_status]">
			<?php echo JText::_( 'Complete Status' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][complete_status]",@$this->element->payment_params->complete_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][invalid_status]"><?php
			echo JText::_('INVALID_STATUS');
		?></label>
	</td>
	<td><?php
		echo $this->data['category']->display("data[payment][payment_params][invalid_status]", @$this->element->payment_params->invalid_status);
	?></td>
</tr>

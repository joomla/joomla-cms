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
		<label for="data[payment][payment_params][pspid]">
			<?php echo JText::_('PSPID'); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][pspid]" value="<?php echo @$this->element->payment_params->pspid; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][shain_passphrase]">
			SHA-IN PASSPHRASE
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][shain_passphrase]" value="<?php echo @$this->element->payment_params->shain_passphrase; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][shaout_passphrase]">
			SHA-OUT PASSPHRASE
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][shaout_passphrase]" value="<?php echo @$this->element->payment_params->shaout_passphrase; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][hash_method]">
			<?php echo JText::_( 'HASH_METHOD' ); ?>
		</label>
	</td>
	<td>
		<?php
		$values = array();
		if( function_exists('hash') || function_exists('sha1') ) {
			$values[] = JHTML::_('select.option', 'sha1',JText::_('SHA1'));
		} else {
			$values[] = JHTML::_('select.option', 'sha1',JText::_('SHA1').' '.JText::_('not present'), 'value', 'text', true);
		}
		if( function_exists('hash')){
			$values[] = JHTML::_('select.option', 'sha256',JText::_('SHA256'));
			$values[] = JHTML::_('select.option', 'sha512',JText::_('SHA512'));
		}else{
			$values[] = JHTML::_('select.option', 'sha256',JText::_('SHA256').' '.JText::_('not present'), 'value', 'text', true);
			$values[] = JHTML::_('select.option', 'sha512',JText::_('SHA512').' '.JText::_('not present'), 'value', 'text', true);
		}

		echo JHTML::_('select.genericlist',   $values, "data[payment][payment_params][hash_method]" , 'class="inputbox" size="1"', 'value', 'text', @$this->element->payment_params->hash_method ); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][environnement]">
			<?php echo JText::_( 'ENVIRONNEMENT' ); ?>
		</label>
	</td>
	<td>
		<?php
		$values = array();
		$values[] = JHTML::_('select.option', 'production', JText::_('HIKA_PRODUCTION'));
		$values[] = JHTML::_('select.option', 'test', JText::_('HIKA_TEST'));

		echo JHTML::_('select.genericlist',   $values, "data[payment][payment_params][environnement]" , 'class="inputbox" size="1"', 'value', 'text', @$this->element->payment_params->environnement ); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label>
			After payment URL
		</label>
	</td>
	<td>
		<?php echo htmlentities(@$this->element->payment_params->status_url); ?>
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

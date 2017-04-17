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
		<label for="data[payment][payment_params][status_url]">
			<?php echo JText::sprintf( 'STATUS_URL',$this->element->payment_name ); ?>
		</label>
	</td>
	<td>

		<?php echo str_replace( '&', '&amp;',@$this->element->payment_params->status_url); ?>
	</td>
</tr>



<tr>
	<td class="key">
		<label for="data[payment][payment_params][verified_status]">
			<?php echo JText::_( 'VERIFIED_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][verified_status]",@$this->element->payment_params->verified_status); ?>
	</td>
</tr>

<tr>
	<td class="key">
		<label for="data[payment][payment_params][merchant_id]">
			<?php echo JText::_( 'ATOS_MERCHANT_ID' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][merchant_id]" value="<?php echo @$this->element->payment_params->merchant_id; ?>" />
	</td>
</tr>

<tr>
	<td class="key">
		<label for="data[payment][payment_params][acquirer_bin]">
			Acquirer bin
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][acquirer_bin]" value="<?php echo @$this->element->payment_params->acquirer_bin; ?>" />
	</td>
</tr>

<tr>
	<td class="key">
		<label for="data[payment][payment_params][terminal_id]">
			Terminal ID
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][terminal_id]" value="<?php echo @$this->element->payment_params->terminal_id; ?>" />
	</td>
</tr>

<tr>
	<td class="key">
		<label for="data[payment][payment_params][clave_encryp]">
			<?php echo JText::_( 'ENCRYPTION_KEY' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][clave_encryp]" value="<?php echo @$this->element->payment_params->clave_encryp; ?>" />
	</td>
</tr>



<tr>
	<td class="key">
		<label for="data[payment][payment_params][debug]">
			<?php echo JText::_( 'TEST_MODE' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][debug]" , '',@$this->element->payment_params->debug	); ?>
	</td>
</tr>

<tr>
	<td class="key">
		<label for="data[payment][payment_params][comunicacion_online_ok]">
			<?php echo JText::_( 'ONLINE_COMMUNICATION' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][comunicacion_online_ok]" , '',@$this->element->payment_params->comunicacion_online_ok	); ?>
	</td>
</tr>

<tr>
	<td class="key">
		<label for="data[payment][payment_params][respuesta_requerida]">
			<?php echo JText::_( 'RESPONSE_REQUIRED' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][respuesta_requerida]" , '',@$this->element->payment_params->respuesta_requerida	); ?>
	</td>
</tr>





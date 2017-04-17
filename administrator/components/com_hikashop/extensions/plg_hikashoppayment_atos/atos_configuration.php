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
		<label for="data[payment][payment_params][payment_means]">
			<?php echo JText::_( 'ATOS_PAYMENT_MEANS' ); ?>
		</label>
	</td>
	<td>
		<input type="text" id="plugin_cards" name="data[payment][payment_params][payment_means]" value="<?php echo @$this->element->payment_params->payment_means; ?>">
		<a class="modal" id="plugin_cards_link" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("plugins&task=edit&name=atos&plugin_type=payment&subtask=logos&values=".@$this->element->payment_params->payment_means."",true);?>">
		<img src="../media/com_hikashop/images/edit.png" alt="edit"/></a>

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
		<label for="data[payment][payment_params][merchant_country]">
			<?php echo JText::_( 'ATOS_MERCHANT_COUNTRY' ); ?>
		</label>
	</td>
		<td>
		<select name="data[payment][payment_params][merchant_country]">
			<option <?php if($this->element->payment_params->merchant_country == 'fr') echo "selected=\"selected\""; ?> value="fr">France</option>
			<option <?php if($this->element->payment_params->merchant_country == 'en') echo "selected=\"selected\""; ?> value="en">United-Kingdom</option>
			<option <?php if($this->element->payment_params->merchant_country == 'de') echo "selected=\"selected\""; ?> value="de">Germany</option>
			<option <?php if($this->element->payment_params->merchant_country == 'be') echo "selected=\"selected\""; ?> value="be">Belgium</option>
			<option <?php if($this->element->payment_params->merchant_country == 'es') echo "selected=\"selected\""; ?> value="es">Spain</option>
			<option <?php if($this->element->payment_params->merchant_country == 'it') echo "selected=\"selected\""; ?> value="it">Italy</option>
		</select>
	</td>
</tr>
<?php $safe_mode = ini_get('safe_mode') == 1 || !strcasecmp(ini_get('safe_mode'), 'On');
if(!$safe_mode){ ?>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][upload_folder]">
			<?php echo JText::_( 'ATOS_UPLOAD_FOLDER' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][upload_folder]" value="<?php echo @$this->element->payment_params->upload_folder; ?>" />
	</td>
</tr>
<?php }else{ ?>
	<tr>
		<td class="key">
			<label for="data[payment][payment_params][binaries_folder]">
				<?php echo JText::_( 'ATOS_BINARIES_FOLDER' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[payment][payment_params][binaries_folder]" value="<?php echo @$this->element->payment_params->binaries_folder; ?>" />
		</td>
	</tr>
<?php } ?>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][logo_folder]">
			<?php echo JText::_( 'ATOS_LOGO_FOLDER' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][logo_folder]" value="<?php echo @$this->element->payment_params->logo_folder; ?>" />
	</td>
</tr>
<?php $safe_mode = ini_get('safe_mode') == 1 || !strcasecmp(ini_get('safe_mode'), 'On');
if(!$safe_mode){ ?>
	<tr>
		<td class="key">
			<label for="request">
				<?php echo JText::_( 'ATOS_UPLOAD_REQUEST' ); ?>
			</label>
		</td>
		<td>
			<?php if(!empty($this->element->payment_params->request_exist) && $this->element->payment_params->request_exist){echo $this->element->payment_params->upload_folder_relative.'b'.DS.'<br/>';}?>
			<input type="file" name="request" value="<?php echo @$this->request; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="response">
				<?php echo JText::_( 'ATOS_UPLOAD_RESPONSE' ); ?>
			</label>
		</td>
		<td>
			<?php if(!empty($this->element->payment_params->response_exist) && $this->element->payment_params->response_exist){echo $this->element->payment_params->upload_folder_relative.'b'.DS.'<br/>';}?>
			<input type="file" name="response" value="<?php echo @$this->response; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="certificate">
				<?php echo JText::_( 'ATOS_CERTIFICATE' ); ?>
			</label>
		</td>
		<td>
			<?php if(!empty($this->element->payment_params->certif_exist) && $this->element->payment_params->certif_exist){echo $this->element->payment_params->upload_folder_relative.'b'.DS.'<br/>';}?>
			<input type="file" name="certificate" value="<?php echo @$this->certificate; ?>" />
		</td>
	</tr>
<?php }else{ ?>
	<tr>
		<td class="key">
			<label for="data[payment][payment_params][param_folder]">
				<?php echo JText::_( 'ATOS_PARAM_FOLDER' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[payment][payment_params][param_folder]" value="<?php echo @$this->element->payment_params->param_folder; ?>" />
		</td>
	</tr>
<?php } ?>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][delay]">
			<?php echo JText::_( 'ATOS_DELAY' ); ?>
		</label>
	</td>
	<td>
		<input size="7" type="text" name="data[payment][payment_params][delay]" value="<?php echo @$this->element->payment_params->delay; ?>" />
		<?php echo JText::_( 'DAYS' ); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][enable_validation]">
			<?php echo JText::sprintf( 'ENABLE_VALIDATION' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][enable_validation]" , '',@$this->element->payment_params->enable_validation	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][instalments]">
			<?php echo JText::_( 'ATOS_INSTALLMENTS' ); ?>
		</label>
	</td>
	<td>
		<input size="3" type="text" name="data[payment][payment_params][instalments]" value="<?php echo @$this->element->payment_params->instalments; ?>" />
		<?php echo JText::sprintf( 'INSTALLMENTS_SEPARATED_BY' ); ?>
		<input size="2" type="text" name="data[payment][payment_params][period]" value="<?php echo @$this->element->payment_params->period; ?>" />
		<?php echo JText::_( 'DAYS' ); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][force_instalments]">
			<?php echo JText::sprintf( 'FORCE_MULTIPLE_PAYMENTS' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][force_instalments]" , '',@$this->element->payment_params->force_instalments	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][template]">
			<?php echo JText::_( 'TEMPLATE' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][template]" value="<?php echo @$this->element->payment_params->template; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][information]">
			HTML à afficher sur la page du reçu
		</label>
	</td>
	<td>
		<textarea name="data[payment][payment_params][information]" rows="9" width="100%" style="width:100%;"><?php echo @$this->element->payment_params->information; ?></textarea>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][notification]">
			<?php echo JText::sprintf( 'ALLOW_NOTIFICATIONS_FROM_X', $this->element->payment_name); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][notification]" , 'yes',@$this->element->payment_params->notification	); ?>
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

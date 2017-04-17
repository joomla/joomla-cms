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
		<label for="data[payment][payment_params][api]">
			<?php echo JText::_( 'API' ); ?>
		</label>
	</td>
	<td>
		<?php
		$values = array();
		$values[] = JHTML::_('select.option', 'direct',JText::_('Direct'));
		if (extension_loaded('soap')) {
			$values[] = JHTML::_('select.option', 'hosted',JText::_('Hosted'));
		} else {
			$values[] = JHTML::_('select.option', 'hosted',JText::_('Hosted (SOAP not present)'), 'value', 'text', true);
		}
		echo JHTML::_('select.genericlist',   $values, "data[payment][payment_params][api]" , 'class="inputbox" size="1"', 'value', 'text', @$this->element->payment_params->api ); ?>
	</td>
</tr>
<tr>
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
		<label for="data[payment][payment_params][password]">
			<?php echo JText::_( 'HIKA_PASSWORD' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][password]" value="<?php echo @$this->element->payment_params->password; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][sharedkey]">
			<?php echo JText::_( 'SHARED_KEY' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][sharedkey]" value="<?php echo @$this->element->payment_params->sharedkey; ?>" />
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
		if( function_exists('mhash') || function_exists('sha1') ) {
			$values[] = JHTML::_('select.option', 'sha1',JText::_('SHA1'));
			$values[] = JHTML::_('select.option', 'hmacsha1',JText::_('HMAC SHA1'));
		} else {
			$values[] = JHTML::_('select.option', 'sha1',JText::_('SHA1').' '.JText::_('not present'), 'value', 'text', true);
			$values[] = JHTML::_('select.option', 'hmacsha1',JText::_('HMAC SHA1').' '.JText::_('not present'), 'value', 'text', true);
		}

		if( function_exists('mhash') || function_exists('md5') ) {
			$values[] = JHTML::_('select.option', 'md5',JText::_('MD5'));
			$values[] = JHTML::_('select.option', 'hmacmd5',JText::_('HMAC MD5'));
		} else {
			$values[] = JHTML::_('select.option', 'md5',JText::_('MD5').' '.JText::_('not present'), 'value', 'text', true);
			$values[] = JHTML::_('select.option', 'hmacmd5',JText::_('HMAC MD5').' '.JText::_('not present'), 'value', 'text', true);
		}

		echo JHTML::_('select.genericlist',   $values, "data[payment][payment_params][hash_method]" , 'class="inputbox" size="1"', 'value', 'text', @$this->element->payment_params->hash_method ); ?>
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
		<label for="data[payment][payment_params][ask_ccv]">
			<?php echo JText::_( 'CARD_VALIDATION_CODE' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][ask_ccv]" , '',@$this->element->payment_params->ask_ccv	); ?>
	</td>
</tr>
<!-- MANDATORY PART -->
<tr>
	<td class="key">
		<label for="data[payment][payment_params][cv2mandatory]">
			<?php echo JText::_( 'CV2_MANDATORY' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][cv2mandatory]" , '',@$this->element->payment_params->cv2mandatory	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][address1mandatory]">
			<?php echo JText::_( 'ADDRESS1_MANDATORY' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][address1mandatory]" , '',@$this->element->payment_params->address1mandatory	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][citymandatory]">
			<?php echo JText::_( 'CITY_MANDATORY' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][citymandatory]" , '',@$this->element->payment_params->citymandatory	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][postcodemandatory]">
			<?php echo JText::_( 'POSTCODE_MANDATORY' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][postcodemandatory]" , '',@$this->element->payment_params->postcodemandatory	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][statemandatory]">
			<?php echo JText::_( 'STATE_MANDATORY' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][statemandatory]" , '',@$this->element->payment_params->statemandatory	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][countrymandatory]">
			<?php echo JText::_( 'COUNTRY_MANDATORY' ); ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][countrymandatory]" , '',@$this->element->payment_params->countrymandatory	); ?>
	</td>
</tr>
<!-- END OF MANDATORY PART -->

<tr>
	<td class="key">
		<label for="data[payment][payment_params][gw_entrypoint]">
			<?php echo JText::_( 'GATEWAY_DOMAIN' ); ?>
		</label>
	</td>
	<td>
		https://gwX.<input type="text" name="data[payment][payment_params][gw_entrypoint]" value="<?php echo @$this->element->payment_params->gw_entrypoint; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][gw_port]">
			<?php echo JText::_( 'GATEWAY_PORT' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][gw_port]" value="<?php echo @$this->element->payment_params->gw_port; ?>" />
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

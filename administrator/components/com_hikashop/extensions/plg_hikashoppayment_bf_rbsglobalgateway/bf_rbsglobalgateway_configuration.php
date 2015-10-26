<?php
/**
 * @package		 HikaShop for Joomla!
 * @subpackage Payment Plug-in for Worldpay Global Gateway using XML Redirect.
 * @version		 0.0.1
 * @author		 brainforge.co.uk
 * @copyright	 (C) 2011 Brainforge derived from Paypal plug-in by HIKARI SOFTWARE. All rights reserved.
 * @license		 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * See: http://www.worldpay.com/support/kb/gg/submittingtransactionsredirect/rxml.html
 * 
 * In order to configure and use this plug-in you must have a RBS Worldpay Global Gateway account.
 * Worldpay Global Gateway is sometimes referred to as 'BiBit'.
 */
defined('_JEXEC') or die('Restricted access');
if(!function_exists('curl_init')){ ?>
<tr><td colspan="2" style="color:red;">This plugin needs the CURL library installed but it is not available on your server.<br />Please contact your web hosting to set it up.</td></tr>
<?php }
if(!class_exists('SimpleXMLElement')){ ?>
<tr><td colspan="2" style="color:red;">This plugin needs the SimpleXML library of PHP5 installed but it is not available on your server.<br />Please contact your web hosting to set it up.</td></tr>
<?php }
?>
<tr><td colspan="2"><hr /></td></tr>
<tr id="instid"><td colspan="2">Required for <a href="http://www.worldpay.com/support/kb/gg/payment-pages-pilot/Redirectamends.pdf" target="wpkbggpppr">Hosted Payment Pages</a>.</td></tr>
<tr title="Only required for hosted payment pages.">
	<td class="key">
		<label for="data[payment][payment_params][instid]">
			<?php echo 'Installation ID'; ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][instid]" value="<?php echo @$this->element->payment_params->instid; ?>" />
	</td>
</tr>
<tr><td colspan="2"><hr /></td></tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][adminCode]">Admin Code</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][adminCode]" size="40" value="<?php echo htmlspecialchars(@$this->element->payment_params->adminCode); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][merchantCode]">Merchant Code</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][merchantCode]" size="40" value="<?php echo htmlspecialchars(@$this->element->payment_params->merchantCode); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][macSecret]">MAC Secret</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][macSecret]" size="40" value="<?php echo htmlspecialchars(@$this->element->payment_params->macSecret); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][xmlurl]">XML Order URL</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][xmlurl]" size="60" value="<?php echo htmlspecialchars(@$this->element->payment_params->xmlurl); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][paymentRefField]">WorldPay Reference Order Field<br /><div style="font-size:70%;">(Commercial version only)</div></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][paymentRefField]" size="40" value="<?php echo htmlspecialchars(@$this->element->payment_params->paymentRefField); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][responseRefField]">WorldPay Enquiry Response Field<div style="font-size:70%;">(Commercial version only)</div></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][responseRefField]" size="40" value="<?php echo htmlspecialchars(@$this->element->payment_params->responseRefField); ?>" />
	</td>
</tr>
<tr><td colspan="2"><hr /></td></tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][redirect_button]">Redirect to WorldPay Button</label>
	</td>
	<td>
		<textarea name="data[payment][payment_params][redirect_button]" rows="5" cols="40"><?php echo htmlspecialchars($this->element->payment_params->redirect_button); ?></textarea>
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
		<label for="data[payment][payment_params][showVars]">
			<?php echo 'Show Parameters'; ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][showVars]" , '',@$this->element->payment_params->showVars	); ?>
	</td>
</tr>
<tr><td colspan="2"><hr /></td></tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][htmllayout]">
			<?php echo 'Order Content HTML Layout'; ?>
		</label>
	</td>
	<td>
		<?php
		jimport('joomla.filesystem.folder');
		$files = JFolder::files(dirname(__FILE__),'bf_rbsglobalgateway_template_.*');
			sort($files);
			$values = array();
			foreach ($files as $file) {
				$name = basename($file, '.php');
				if ($name != $file) $values[] = JHTML::_('select.option', $file, $name );
			}
			 echo JHTML::_('select.genericlist', $values, 'data[payment][payment_params][htmllayout]', 'class="inputbox" size="1"', 'value', 'text', @$this->element->payment_params->htmllayout);
		?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][show_tax_amount]">Show Tax Amount</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][show_tax_amount]" , '',@$this->element->payment_params->show_tax_amount	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][description]">Order Description</label>
	</td>
	<td>
		<textarea name="data[payment][payment_params][description]" rows="2" cols="40"><?php echo htmlspecialchars($this->element->payment_params->description); ?></textarea>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][paymentMethodMask]">Payment Method Mask</label>
	</td>
	<td>
		<textarea id="paymentMethodMask" name="data[payment][payment_params][paymentMethodMask]" rows="3" cols="40"><?php echo $this->element->payment_params->paymentMethodMask; ?></textarea>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][address_type]">
			<?php echo 'Customer Address'; ?>
		</label>
	</td>
	<td>
		<?php
			$values = array();
			$values[] = JHTML::_('select.option', '',JText::_('NO_ADDRESS') );
			$values[] = JHTML::_('select.option', 'billing',JText::_('HIKASHOP_BILLING_ADDRESS'));
			$values[] = JHTML::_('select.option', 'shipping',JText::_('HIKASHOP_SHIPPING_ADDRESS'));
			$values[] = JHTML::_('select.option', 'billing,shipping','Both addresses');
			 echo JHTML::_('select.genericlist', $values, 'data[payment][payment_params][address_type]', 'class="inputbox" size="1"', 'value', 'text', @$this->element->payment_params->address_type);
		?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][houseNoField]">House Number Field</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][houseNoField]" size="40" value="<?php echo htmlspecialchars(@$this->element->payment_params->houseNoField); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][houseNoField]">House Name Field</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][houseNameField]" size="40" value="<?php echo htmlspecialchars(@$this->element->payment_params->houseNameField); ?>" />
	</td>
</tr>
<tr><td colspan="2"><hr /></td></tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][notification]">
			<?php echo JText::sprintf( 'ALLOW_NOTIFICATIONS_FROM_X', '<br/>' . $this->element->payment_name);  ?>
		</label>
	</td>
	<td>
		<?php echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][notification]" , '',@$this->element->payment_params->notification	); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][invalid_status]">
			<?php echo JText::_( 'INVALID_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][invalid_status]",@$this->element->payment_params->invalid_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][pending_status]">
			<?php echo JText::_( 'PENDING_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][pending_status]",@$this->element->payment_params->pending_status); ?>
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
		<label for="data[payment][payment_params][cancelled_status]">Cancelled Status</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][cancelled_status]",@$this->element->payment_params->cancelled_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][invalidURL]">Customer Invalid URL</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][invalidURL]" size="60" value="<?php echo htmlspecialchars(@$this->element->payment_params->invalidURL); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][pendingURL]">Customer Pending URL</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][pendingURL]" size="60" value="<?php echo htmlspecialchars(@$this->element->payment_params->pendingURL); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][verifiedURL]">Customer Confirmed URL</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][verifiedURL]" size="60" value="<?php echo htmlspecialchars(@$this->element->payment_params->verifiedURL); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][cancelledURL]">Customer Cancelled URL</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][cancelledURL]" size="60" value="<?php echo htmlspecialchars(@$this->element->payment_params->cancelledURL); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][invalidMessage]">Invalid Notification Message</label>
	</td>
	<td>
		<textarea name="data[payment][payment_params][invalidMessage]" rows="3" cols="40"><?php echo htmlspecialchars($this->element->payment_params->invalidMessage); ?></textarea>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][pendingMessage]">Pending Notification Message</label>
	</td>
	<td>
		<textarea name="data[payment][payment_params][pendingMessage]" rows="3" cols="40"><?php echo htmlspecialchars($this->element->payment_params->pendingMessage); ?></textarea>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][verifiedMessage]">Verified Notification Message</label>
	</td>
	<td>
		<textarea name="data[payment][payment_params][verifiedMessage]" rows="3" cols="40"><?php echo htmlspecialchars($this->element->payment_params->verifiedMessage); ?></textarea>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][cancelledMessage]">Cancelled Notification Message</label>
	</td>
	<td>
		<textarea name="data[payment][payment_params][cancelledMessage]" rows="3" cols="40"><?php echo htmlspecialchars($this->element->payment_params->cancelledMessage); ?></textarea>
	</td>
</tr>
<tr><td colspan="2"><hr /></td></tr>
<tr><td colspan="2">These fields are not used if you are using <a href="http://www.worldpay.com/support/kb/gg/payment-pages-pilot/Redirectamends.pdf" target="wpkbggpppr">Hosted Payment Pages</a> and have specified an <a href="#instid">Installation ID</a>.</td></tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][bodyAttr]">Body Attributes (bodyAttr Parameter)</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][bodyAttr]" size="60" value="<?php echo htmlspecialchars(@$this->element->payment_params->bodyAttr); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][fontAttr]">Font Attributes (fontAttr Parameter)</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][fontAttr]" size="60" value="<?php echo htmlspecialchars(@$this->element->payment_params->fontAttr); ?>" />
	</td>
</tr>
<tr title="Custom CSS which is inserted into the XML Order Content field and used by the selected layout.">
	<td class="key">
		<label for="data[payment][payment_params][orderContentCSS]">Order Content CSS</label>
	</td>
	<td>
		<textarea name="data[payment][payment_params][orderContentCSS]" rows="5" cols="40"><?php echo htmlspecialchars($this->element->payment_params->orderContentCSS); ?></textarea>
	</td>
</tr>
<tr title="Site logo available for use by the selected layout.">
	<td class="key">
		<label for="data[payment][payment_params][siteLogo]">Site Logo</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][siteLogo]" size="60" value="<?php echo htmlspecialchars(@$this->element->payment_params->siteLogo); ?>" />
	</td>
</tr>
<tr title="Information available for use by the selected layout.">
	<td class="key">
		<label for="data[payment][payment_params][contactInformation]">Contact Information</label>
	</td>
	<td>
		<textarea id="contactInformation" name="data[payment][payment_params][contactInformation]" rows="3" cols="40"><?php echo htmlspecialchars($this->element->payment_params->contactInformation); ?></textarea>
	</td>
</tr>
<tr title="Information available for use by the selected layout.">
	<td class="key">
		<label for="data[payment][payment_params][billingNotice]">Billing Notice</label>
	</td>
	<td>
		<textarea id="billingNotice" name="data[payment][payment_params][billingNotice]" rows="3" cols="40"><?php echo htmlspecialchars($this->element->payment_params->billingNotice); ?></textarea>
	</td>
</tr>
<tr><td colspan="2"><hr /></td></tr>

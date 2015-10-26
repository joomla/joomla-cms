<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="features_affiliate">
<fieldset class="adminform">
	<legend><?php echo JText::_( 'AFFILIATE' ); ?></legend>
	<table class="admintable" cellspacing="1">
		<tr>
			<td class="key" >
				<?php echo JText::_('PARTNER_KEY'); ?>
			</td>
			<td>
				<input class="inputbox" type="text" name="params[system][hikashopaffiliate][partner_key_name]" value="<?php echo @$this->escape($this->affiliate_params['partner_key_name']); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('PARTNER_CLICK_FEE'); ?>
			</td>
			<td>
				<input class="inputbox" size="5" type="text" name="config[partner_click_fee]" value="<?php echo $this->config->get('partner_click_fee'); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('PARTNER_LEAD_FEE'); ?>
			</td>
			<td>
				<input class="inputbox" size="5" type="text" name="config[partner_lead_fee]" value="<?php echo $this->config->get('partner_lead_fee'); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('PARTNER_ORDER_PERCENT_FEE'); ?>
			</td>
			<td>
				<input class="inputbox" size="5" type="text" name="config[partner_percent_fee]" value="<?php echo $this->config->get('partner_percent_fee'); ?>" />%
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('PARTNER_ORDER_FLAT_FEE'); ?>
			</td>
			<td>
				<input class="inputbox" size="5" type="text" name="config[partner_flat_fee]" value="<?php echo $this->config->get('partner_flat_fee'); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('PERCENTAGE_FEE_EXCLUDING_SHIPPING'); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "config[affiliate_fee_exclude_shipping]" , '', $this->config->get('affiliate_fee_exclude_shipping',0)); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('VALID_ORDER_STATUS'); ?>
			</td>
			<td>
				<?php
					echo $this->nameboxType->display(
						'config[partner_valid_status]',
						explode(',',$this->config->get('partner_valid_status')),
						hikashopNameboxType::NAMEBOX_MULTIPLE,
						'order_status',
						array(
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
						)
					);
				?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('VALIDITY_PERIOD'); ?>
			</td>
			<td>
				<?php echo $this->delayTypeAffiliate->display('config[click_validity_period]', $this->config->get('click_validity_period',2592000),3); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('CLICK_MINIMUM_DELAY'); ?>
			</td>
			<td>
				<?php echo $this->delayTypeClick->display('config[click_min_delay]', $this->config->get('click_min_delay',86400)); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('NO_AFFILIATION_IF_CART_PRESENT'); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "config[no_affiliation_if_cart_present]" , '', $this->config->get('no_affiliation_if_cart_present',0)); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('AFFILIATE_PAYMENT_DELAY'); ?>
			</td>
			<td>
				<?php echo $this->delayTypeOrder->display('config[affiliate_payment_delay]', $this->config->get('affiliate_payment_delay',0),3); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('ADD_PARTNER_TO_USER_ACCOUNT_DURING_REGISTRATION'); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "config[add_partner_to_user_account]" , '', $this->config->get('add_partner_to_user_account',0)); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('AFFILIATE_TERMS'); ?>
			</td>
			<td>
				<input class="inputbox" id="affiliate_terms" name="config[affiliate_terms]" type="text" size="20" value="<?php echo $this->config->get('affiliate_terms'); ?>">
				<?php
				if(!HIKASHOP_J16){
					$link = 'index.php?option=com_content&amp;task=element&amp;tmpl=component&amp;object=affiliate';
				}else{
					$link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;object=content&amp;function=jSelectArticle_terms';
					$js = "
					function jSelectArticle_terms(id, title, catid, object) {
						document.getElementById('affiliate_terms').value = id;
						hikashop.closeBox();
					}";
					if (!HIKASHOP_PHP5) {
						$doc =& JFactory::getDocument();
					}else{
						$doc = JFactory::getDocument();
					}
					$doc->addScriptDeclaration($js);
				}
				echo $this->popup->display(
					JText::_('Select'),
					'Select one article which will be displayed for the affiliate program Terms & Conditions',
					$link,
					'affiliate_terms_link',
					760, 480, '', '', 'button'
				);
				?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('BECOME_PARTNER_QUESTION_REGISTRATION'); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "config[affiliate_registration]" , '', $this->config->get('affiliate_registration',0)); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('QUESTION_REGISTRATION_DEFAULT'); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "config[affiliate_registration_default]" , '', $this->config->get('affiliate_registration_default',0)); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('PARTNER_CURRENCY'); ?>
			</td>
			<td>
				<?php echo $this->currency->display('config[partner_currency]', $this->config->get('partner_currency')); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('ALLOW_CURRENCY_SELECTION'); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "config[allow_currency_selection]" , '', $this->config->get('allow_currency_selection')); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('AFFILIATE_ADVANCED_STATS'); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "config[affiliate_advanced_stats]" , '', $this->config->get('affiliate_advanced_stats')); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_('AFFILIATE_NO_SELF_AFFILIATION'); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "config[no_self_affiliation]" , '', $this->config->get('no_self_affiliation', 0)); ?>
			</td>
		</tr>
	</table>
</fieldset>
</div>

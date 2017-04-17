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
if( isset($this->display_form) && $this->display_form ) {
	global $Itemid;
	$order_id = JRequest::getInt('order_id',0);
?><form action="<?php echo hikashop_completeLink('order'); ?>" name="payForm_<?php echo $order_id; ?>" method="post">
<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
<input type="hidden" name="ctrl" value="order"/>
<input type="hidden" name="task" value="pay"/>
<input type="hidden" name="order_id" value="<?php echo $order_id; ?>"/>
<input type="hidden" name="new_payment_method" value="<?php echo JRequest::getVar('new_payment_method','');?>"/>
<?php
	echo JHTML::_( 'form.token' );
}
?><div id="hikashop_credit_card_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>" class="hikashop_credit_card">
<?php
	if(!empty($this->method->ask_cc)){
		$app = JFactory::getApplication();
		$cc_number = $app->getUserState( HIKASHOP_COMPONENT.'.cc_number');
		$cc_month = $app->getUserState( HIKASHOP_COMPONENT.'.cc_month');
		$cc_year = $app->getUserState( HIKASHOP_COMPONENT.'.cc_year');
		$cc_CCV = $app->getUserState( HIKASHOP_COMPONENT.'.cc_CCV');
		$cc_type = $app->getUserState( HIKASHOP_COMPONENT.'.cc_type');
		$cc_owner = $app->getUserState( HIKASHOP_COMPONENT.'.cc_owner');
		if(!empty($cc_number) && !empty($cc_month) && !empty($cc_year) && (!empty($cc_CCV)|| empty($this->method->ask_ccv)) && (!empty($cc_owner)|| empty($this->method->ask_owner)) && (!empty($cc_type)|| empty($this->method->ask_cctype))){
			$cc_number = base64_decode($cc_number);
			$cc_month = base64_decode($cc_month);
			$cc_year = base64_decode($cc_year);
			$cc_owner = base64_decode($cc_owner);
			$cc_CCV = base64_decode($cc_CCV);
			$cc_type = base64_decode($cc_type);
?>
	<table width="100%">
		<?php if(!empty($this->method->ask_owner)){ ?>
		<tr>
			<td style="text-align:right"><label for="hikashop_credit_card_CCV_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>"><?php echo JText::_('CREDIT_CARD_OWNER'); ?></label></td>
			<td><span class="hikashop_credit_ccv"><?php echo $cc_owner; ?></span></td>
		</tr>
		<?php } ?>
		<?php if(!empty($this->method->ask_cctype)){ ?>
		<tr>
			<td style="text-align:right"><label for="hikashop_credit_card_type_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>"><?php echo JText::_('CARD_TYPE'); ?></label></td>
			<td><span class="hikashop_credit_card_type"><?php echo $this->method->ask_cctype[$cc_type];?></span></td>
		</tr>
		<?php } ?>
		<tr>
			<td style="text-align:right"><label for="hikashop_credit_card_number_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>"><?php echo JText::_('CREDIT_CARD_NUMBER'); ?></label></td>
			<td><span class="hikashop_credit_card_number"><?php echo str_repeat("X", strlen($cc_number)-4) . substr($cc_number,strlen($cc_number)-5);?></span></td>
		</tr>
		<tr>
			<td style="text-align:right"><label for="hikashop_credit_card_month_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>"><?php echo JText::_('EXPIRATION_DATE'); ?></label></td>
			<td><span class="hikashop_credit_card_date"><?php echo $cc_month."/".$cc_year;?></span></td>
		</tr>
		<?php if(!empty($this->method->ask_ccv)){ ?>
		<tr>
			<td style="text-align:right"><label for="hikashop_credit_card_CCV_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>"><?php echo JHTML::tooltip(JText::_('CVC_TOOLTIP_TEXT'), JText::_('CVC_TOOLTIP_TITLE'),
			'', JText::_('CARD_VALIDATION_CODE')); ?></label></td>
			<td><span class="hikashop_credit_ccv"><?php echo str_repeat("X", strlen($cc_CCV));?></span></td>
		</tr>
		<?php } ?>
	</table>
	<?php }else{
		static $done = false;
		if(!$done){
			$done = true;
			if(!HIKASHOP_PHP5) {
				$doc =& JFactory::getDocument();
			} else {
				$doc = JFactory::getDocument();
			}
			$doc->addScript(HIKASHOP_JS.'creditcard.js');
		} ?>
	<table width="100%">
		<?php if(!empty($this->method->ask_owner)){ ?>
		<tr>
			<td style="text-align:right"><label for="hikashop_credit_card_owner_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>"><?php echo JText::_('CREDIT_CARD_OWNER'); ?></label></td>
			<td><input type="text" autocomplete="off" style="text-align: center;" id="hikashop_credit_card_owner_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>" name="hikashop_credit_card_owner[<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>]" value="" /></td>
		</tr>
		<?php } ?>
		<?php if(!empty($this->method->ask_cctype)){ ?>
		<tr>
			<td style="text-align:right"><label for="hikashop_credit_card_type<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>"><?php echo JText::_('CARD_TYPE'); ?></label></td>
			<td><?php
				$values = array();
				foreach($this->method->ask_cctype as $k => $v){
					$values[] = JHTML::_('select.option', $k, $v);
				}
				echo JHTML::_('select.genericlist', $values, "hikashop_credit_card_type[".$this->method->payment_type.'_'.$this->method->payment_id.']', '', 'value', 'text', $cc_type );
			?></td>
		</tr>
		<?php } ?>
		<tr>
			<td style="text-align:right"><label for="hikashop_credit_card_number_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>"><?php echo JText::_('CREDIT_CARD_NUMBER'); ?></label></td>
			<td><input type="text" autocomplete="off" name="hikashop_credit_card_number[<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>]" id="hikashop_credit_card_number_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>" value="" onchange="if(!hikashopCheckCreditCard(this.value)){ this.value='';}"/></td>
		</tr>
		<tr>
			<td style="text-align:right"><label for="hikashop_credit_card_month_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>"><?php echo JText::_('EXPIRATION_DATE'); ?></label></td>
			<?php $mm = JText::_('CC_MM'); if($mm=='CC_MM')$mm = JText::_('MM'); ?>
			<td><input style="text-align: center;" autocomplete="off" type="text" id="hikashop_credit_card_month_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>" name="hikashop_credit_card_month[<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>]" onkeyup="moveOnMax(this,'hikashop_credit_card_year_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>');" onfocus="this.value='';" maxlength="2" size="2" value="<?php echo $mm;?>" /> / <input style="text-align: center;" autocomplete="off" type="text" id="hikashop_credit_card_year_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>" name="hikashop_credit_card_year[<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>]" onfocus="this.value='';" maxlength="2" size="2" value="<?php echo JText::_('YY');?>" onchange="var month = document.getElementById('hikashop_credit_card_month_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>'); if(!hikashopValidateExpDate(month.value,this.value)){this.value='';month.value='';}" /></td>
		</tr>
		<?php if(!empty($this->method->ask_ccv)){ ?>
		<tr>
			<td style="text-align:right"><label for="hikashop_credit_card_CCV_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>"><?php echo JHTML::tooltip(JText::_('CVC_TOOLTIP_TEXT'), JText::_('CVC_TOOLTIP_TITLE'),
			'', JText::_('CARD_VALIDATION_CODE')); ?></label></td>
			<td><input type="text" autocomplete="off" style="text-align: center;" id="hikashop_credit_card_CCV_<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>" name="hikashop_credit_card_CCV[<?php echo $this->method->payment_type.'_'.$this->method->payment_id;?>]" maxlength="4" size="4" value="" /></td>
		</tr>
		<?php } ?>
	</table>
	<?php
		}
	}elseif(!empty($this->method->custom_html)){
		echo $this->method->custom_html;
	}
	?>
</div>
<?php
if( isset($this->display_form) && $this->display_form ) {
	echo $this->cart->displayButton(JText::_('PAY_NOW'),'pay',$this->params,hikashop_completeLink('order&task=pay&order_id='.$order_id),'document.payForm_'.$order_id.'.submit();return false;','class="hikashop_order_pay_button"');
?>
</form>
<?php
}

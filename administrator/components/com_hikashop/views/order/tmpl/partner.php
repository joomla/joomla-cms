<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="submitbutton('savepartner');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order',true); ?>" method="post"  name="adminForm" id="adminForm">
	<table width="100%" class="admintable table">
		<tr>
			<td class="key">
				<label for="data[order][order_partner_id]">
					<?php echo JText::_( 'PARTNER' ); ?>
				</label>
			</td>
			<td>
				<?php echo $this->partners->display("data[order][order_partner_id]",@$this->element->order_partner_id); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[order][order_partner_price]">
					<?php echo JText::_( 'PARTNER_FEE' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="data[order][order_partner_price]" value="<?php echo @$this->element->order_partner_price; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[order][order_partner_currency_id]">
					<?php echo JText::_( 'CURRENCY' ); ?>
				</label>
			</td>
			<td>
				<?php echo $this->currencyType->display("data[order][order_partner_currency_id]", @$this->element->order_partner_currency_id); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[order][order_partner_paid]">
					<?php echo JText::_( 'PAID' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[order][order_partner_paid]" , '',@$this->element->order_partner_paid	); ?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="data[order][order_id]" value="<?php echo @$this->element->order_id;?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

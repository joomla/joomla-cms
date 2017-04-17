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
		<button class="btn" type="button" onclick="submitbutton('savechangeplugin');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order',true); ?>" method="post"  name="adminForm" id="adminForm">
	<table width="100%" class="admintable">
		<tr>
			<td class="key">
				<label for="data[order][order_discount_code]">
					<?php echo JText::_( 'NEW_COUPON_CODE' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="data[order][order_discount_code]" value="<?php echo $this->escape(@$this->element->order_discount_code); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[order][order_discount_price]">
					<?php echo JText::_( 'NEW_COUPON_VALUE' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="data[order][order_discount_price]" value="<?php echo @$this->element->order_discount_price; ?>" />

			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[order][order_discount_tax]">
					<?php echo JText::_( 'VAT' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="data[order][order_discount_tax]" value="<?php echo @$this->element->order_discount_tax; ?>" />
				<?php echo $this->ratesType->display( "data[order][order_discount_tax_namekey]" , @$this->element->order_discount_tax_namekey ); ?>
			</td>
		</tr>
		<?php $this->setLayout('notification'); echo $this->loadTemplate();?>
	</table>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="data[order][order_id]" value="<?php echo @$this->element->order_id;?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

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
		<button class="btn" type="button" onclick="submitbutton('saveproduct');"><img style="vertical-align:middle;" src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order',true); ?>" method="post"  name="adminForm" id="adminForm">
	<table width="100%" class="admintable table">
		<tr>
			<td class="key">
				<label><?php
					echo JText::_( 'REMOVE_ORDER_ITEM' );
				?></label>
			</td>
			<td><?php
				echo @$this->element->order_product_name.' '.@$this->element->order_product_code;
			?></td>
		</tr>
		<?php $this->setLayout('notification'); echo $this->loadTemplate();?>
	</table>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="data[order][order_id]" value="<?php echo @$this->element->order_id;?>" />
	<input type="hidden" name="data[order][product][product_id]" value="<?php echo @$this->element->product_id;?>" />
	<input type="hidden" name="data[order][product][order_product_id]" value="<?php echo @$this->element->order_product_id;?>" />
	<input type="hidden" name="data[order][product][order_product_quantity]" value="0" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="order" />

	<input type="hidden" name="cart_id" value="<?php echo JRequest::getInt('cart_id','0');?>" />
	<input type="hidden" name="product_id" value="<?php echo JRequest::getInt('product_id','0');?>" />
	<input type="hidden" name="cart_type" value="<?php echo JRequest::getString('cart_type','cart');?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

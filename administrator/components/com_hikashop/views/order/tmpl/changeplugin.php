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
<?php $type = $this->type; ?>
<form action="<?php echo hikashop_completeLink('order',true); ?>" method="post"  name="adminForm" id="adminForm">
	<table width="100%" class="admintable table">
		<tr>
			<td class="key">
				<label>
					<?php echo JText::_( 'NEW_'.strtoupper($type) ); ?>
				</label>
			</td>
			<td>
				<?php echo $this->$type->getName($this->method,$this->id); ?>
			</td>
		</tr>
		<?php
			if($type=='shipping'){
				?>
				<tr>
					<td class="key">
						<label for="data[order][order_shipping_price]">
							<?php echo JText::_( 'NEW_PRICE' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[order][order_shipping_price]" value="<?php echo $this->element->order_shipping_price; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[order][order_shipping_tax]">
							<?php echo JText::_( 'VAT' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[order][order_shipping_tax]" value="<?php echo @$this->element->order_shipping_tax; ?>" />
						<?php echo $this->ratesType->display( "data[order][order_shipping_tax_namekey]" , @$this->element->order_shipping_tax_namekey ); ?>
					</td>
				</tr>
				<?php
			}
			if($type=='payment'){
				?>
				<tr>
					<td class="key">
						<label for="data[order][order_payment_price]">
							<?php echo JText::_( 'NEW_PRICE' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[order][order_payment_price]" value="<?php echo $this->element->order_payment_price; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[order][order_payment_tax]">
							<?php echo JText::_( 'VAT' ); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[order][order_payment_tax]" value="<?php echo @$this->element->order_payment_tax; ?>" />
						<?php echo $this->ratesType->display( "data[order][order_payment_tax_namekey]" , @$this->element->order_payment_tax_namekey ); ?>
					</td>
				</tr>
				<?php
			}
			$this->setLayout('notification'); echo $this->loadTemplate();
		?>
	</table>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="data[order][order_id]" value="<?php echo @$this->element->order_id;?>" />
	<input type="hidden" name="data[order][order_<?php echo $type?>_method]" value="<?php echo $this->method; ?>" />
	<input type="hidden" name="data[order][order_<?php echo $type?>_id]" value="<?php echo $this->id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

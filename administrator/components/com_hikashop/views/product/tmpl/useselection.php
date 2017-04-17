<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if($this->confirm) return; ?>
<div class="hika_confirm">
<?php if($this->singleUser) {?>
	<?php echo JText::_('HIKA_CONFIRM_PRODUCT')?><br/>
	<table class="hika_options">
		<tr>
			<td class="key"><label><?php echo JText::_('HIKA_NAME'); ?></label></td>
			<td id="hikashop_order_customer_name"><?php echo $this->rows->product_name; ?></td>
		</tr>
		<tr>
			<td class="key"><label><?php echo JText::_('ID'); ?></label></td>
			<td id="hikashop_order_customer_id"><?php echo $this->rows->product_id; ?></td>
		</tr>
	</table>
<?php } else { ?>
	<?php echo JText::_('HIKA_CONFIRM_PRODUCTS')?><br/>
	<table class="hika_listing adminlist">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('HIKA_NAME'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('ID'); ?>
				</th>
			</tr>
		</thead>
<?php foreach($this->rows as $row) { ?>
		<tr>
			<td><?php echo $row->product_name; ?></td>
			<td><?php echo $row->product_id; ?></td>
		</tr>
<?php } ?>
	</table>
<?php } ?>
	<div class="hika_confirm_btn"><a href="#" onclick="window.top.hikashop.submitBox(<?php echo $this->data; ?>); return false;"><img src="<?php echo HIKASHOP_IMAGES ?>save.png"/><span><?php echo 'OK'; ?></span></a></div>
</div>

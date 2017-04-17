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
<?php if($this->singleSelection) {?>
	<?php echo JText::_('HIKA_CONFIRM_USER')?><br/>
	<table class="admintable table hika_options">
		<tr>
			<td class="key"><label><?php echo JText::_('HIKA_NAME'); ?></label></td>
			<td id="hikashop_order_customer_name"><?php echo $this->rows->name; ?></td>
		</tr>
		<tr>
			<td class="key"><label><?php echo JText::_('HIKA_EMAIL'); ?></label></td>
			<td id="hikashop_order_customer_email"><?php echo $this->rows->email; ?></td>
		</tr>
		<tr>
			<td class="key"><label><?php echo JText::_('ID'); ?></label></td>
			<td id="hikashop_order_customer_id"><?php echo $this->rows->user_id; ?></td>
		</tr>
	</table>
<?php } else { ?>
	<?php echo JText::_('HIKA_CONFIRM_USERS')?><br/>
	<table class="adminlist hika_listing">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('HIKA_LOGIN'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_NAME'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_EMAIL'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('ID'); ?>
				</th>
			</tr>
		</thead>
<?php foreach($this->rows as $row) { ?>
		<tr>
			<td><?php echo $row->login; ?></td>
			<td><?php echo $row->name; ?></td>
			<td><?php echo $row->email; ?></td>
			<td><?php echo $row->user_id; ?></td>
		</tr>
<?php } ?>
	</table>
<?php } ?>
	<div class="hika_confirm_btn"><button class="btn" onclick="window.top.hikashop.submitBox(<?php echo $this->data; ?>); return false;"><img src="<?php echo HIKASHOP_IMAGES ?>save.png" style="vertical-align:middle" alt=""/> <span><?php echo 'OK'; ?></span></button></div>
</div>

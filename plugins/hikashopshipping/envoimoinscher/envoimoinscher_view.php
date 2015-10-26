<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_checkout_envoimoinscher_pickup">
<?php
	$weekDays = array(
		1 => JText::_('MONDAY'),
		2 => JText::_('TUESDAY'),
		3 => JText::_('WEDNESDAY'),
		4 => JText::_('THURSDAY'),
		5 => JText::_('FRIDAY'),
		6 => JText::_('SATURDAY'),
		7 => JText::_('SUNDAY')
	);
	$opts2 = array();
	$schedule = '';
	foreach($this->lpCl->listPoints as $key => $relai) {
		$value = $relai['code'] . '$' . $relai['name'] . ' : ' . $relai['address'] . ', ' . $relai['zipcode'] . ', ' . $relai['city'];
		$name = $this->shipping_id . '-emc_pickup@' . $this->warehouse_id;

?>
<p>
	<input type="radio" value="<?php echo $value; ?>" name="<?php echo $name; ?>'" />
	<?php echo $relai['name']; ?> : <?php echo $relai['address']; ?>, <?php echo $relai['zipcode']; ?>, <?php echo $relai['city']; ?>
</p>
<?php

		if($this->plugin_params->schedule_display != 1)
			continue;
?>
	<table class="<?php echo HIKASHOP_RESPONSIVE ? 'table-striped table table-bordered': 'shipping_pickup_table'; ?>">
		<thead>
			<tr>
				<th><?php echo JText::_('WEEKDAY'); ?></th>
				<th><?php echo JText::_('MORNING'); ?></th>
				<th><?php echo JText::_('AFTERNOON'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
		foreach($relai['days'] as $day) {
			if(empty($day['open_am']) && empty($day['close_am']) && empty($day['open_pm']) && empty($day['close_pm']))
				continue;
?>
			<tr>
				<td><?php echo $weekDays[$day['weekday']]; ?></td>
				<td><?php echo $day['open_am'] . ' - ' . $day['close_am']; ?></td>
				<td><?php echo $day['open_pm'] . ' - ' . $day['close_pm']; ?></td>
			</tr>
<?php
		}
?>
		</tbody>
	</table>
<?php
	}
?>
</div>

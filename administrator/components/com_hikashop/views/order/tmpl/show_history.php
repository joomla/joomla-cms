<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><legend><?php echo JText::_('HISTORY'); ?></legend>
<div class="hikashop_history_container">
<table id="hikashop_order_history_listing" class="hika_listing hika_table table table-striped table-hover">
	<thead>
		<tr>
			<th class="title"><?php
				echo JText::_('HIKA_TYPE');
			?></th>
			<th class="title"><?php
				echo JText::_('ORDER_STATUS');
			?></th>
			<th class="title"><?php
				echo JText::_('REASON');
			?></th>
			<th class="title"><?php
				echo JText::_('HIKA_USER').' / '.JText::_('IP');
			?></th>
			<th class="title"><?php
				echo JText::_('DATE');
			?></th>
			<th class="title"><?php
				echo JText::_('INFORMATION');
			?></th>
		</tr>
	</thead>
	<tbody>
<?php
$userClass = hikashop_get('class.user');
foreach($this->order->history as $k => $history) {
?>
		<tr>
			<td><?php
				$val = preg_replace('#[^a-z0-9]#i','_',strtoupper($history->history_type));
				$trans = JText::_($val);
				if($val != $trans)
					$history->history_type = $trans;
				echo $history->history_type;
			?></td>
			<td><?php
				echo hikashop_orderStatus($history->history_new_status);
			?></td>
			<td><?php
				echo $history->history_reason;
			?></td>
			<td><?php
				if(!empty($history->history_user_id)){
					$user = $userClass->get($history->history_user_id);
					echo $user->username.' / ';
				}
				echo $history->history_ip;
			?></td>
			<td><?php
				echo hikashop_getDate($history->history_created,'%Y-%m-%d %H:%M');
			?></td>
			<td><?php
				echo $history->history_data;
			?></td>
		</tr>
<?php
}
?>
	</tbody>
</table>
</div>
<script type="text/javascript">
window.orderMgr.updateHistory = function() {
	window.Oby.xRequest('<?php echo hikashop_completeLink('order&task=show&subtask=history&cid='.$this->order->order_id, true, false, true); ?>',{update:'hikashop_order_field_history'});
}
</script>

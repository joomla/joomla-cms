<?php
/**
 * @version		$Id: default_body.php 2013-07-29 11:37:09Z maverick $
 * @package		CoreJoomla.cjlib
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;
$user = JFactory::getUser();
?>
<?php foreach($this->items as $i => $item): ?>
	<tr class="row<?php echo $i % 2; ?>">
		<td>
			<?php echo $item->id; ?>
			<input type="hidden" name="message_id" value="<?php echo $item->id?>">
		</td>
		<td><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
		<td><?php echo $this->escape($item->subject); ?></td>
		<td><?php echo $this->escape($item->asset_name);?></td>
		<td><?php echo $this->escape($item->asset_id);?></td>
		<td><?php echo $this->escape($item->to_addr);?></td>
		<td><?php echo JHTML::Date($item->created, JText::_('DATE_FORMAT_LC2')); ?></td>
		<td><?php echo $item->status == 0 ? 'N/A' : JHTML::Date($item->processed, JText::_('DATE_FORMAT_LC2')); ?></td>
		<td align="center"><i class="<?php echo $item->html ? 'icon-ok' : 'icon-remove';?>"></i></td>
		<td align="center">
			<div class="badge <?php echo $item->status == 1 ? 'badge-success' : 'badge-warning';?> tooltip-hover"
				title="<?php echo $item->status == 1 ? JText::_('COM_CJLIB_SENT') : JText::_('COM_CJLIB_PENDING');?>">
				<i class="icon <?php echo $item->status == 1 ? 'icon-ok icon-white' : 'icon-spinner icon-spin';?>"></i>
			</div>
		</td>
	</tr>
<?php endforeach; ?>


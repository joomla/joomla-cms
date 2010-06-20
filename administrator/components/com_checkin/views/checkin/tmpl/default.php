<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_checkin
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<table id="global-checkin" class="adminlist">
	<thead>
		<tr>
			<th class="left"><?php echo JText::_('COM_CHECKIN_DATABASE_TABLE'); ?></th>
			<th><?php echo JText::_('COM_CHECKIN_ITEMS_CHECKED_IN'); ?></th>
			<th>&#160;</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	foreach ($this->tables as $table => $count): ?>
		<tr class="row<?php echo $k; ?>">
			<td>
				<?php
				echo JText::sprintf('COM_CHECKIN_CHECKING',$table); ?>
			</td>

			<?php if ($count > 0): ?>
				<td width="100" class="active center">
					<span class="success"><?php echo $count; ?></span>
				</td>
			<?php else: ?>
				<td width="200" class="center">
					<?php echo $count; ?>
				</td>
			<?php endif; ?>

			<td width="50">
				<?php if ($count > 0): ?>
				<div class="checkin-tick"><?php echo JText::_('COM_CHECKIN_TICK'); ?></div>
				<?php else: ?>
				&#160;
				<?php endif; ?>
			</td>
		</tr>
	<?php
	$k = 1 - $k;
	endforeach;
	?>
	<tr>
		<td colspan="3" class="center">
			<span class="stat-notice success"><?php echo JText::_('COM_CHECKIN_ALL_CHECKED'); ?></span>
		</td>
	</tr>
</table>

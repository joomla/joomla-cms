<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

?>
<h3>
<?php echo JText::sprintf('COM_CONTENTHISTORY_PREVIEW_SUBTITLE_DATE', $this->item->save_date); ?>
<?php if ($this->item->version_note) : ?>
	&nbsp;&nbsp;<?php echo JText::sprintf('COM_CONTENTHISTORY_PREVIEW_SUBTITLE', $this->item->version_note); ?>
<?php endif; ?>
</h3>
<table class="table table-striped" >
<thead><tr>
	<th width="25%"><?php echo JText::_('COM_CONTENTHISTORY_PREVIEW_FIELD'); ?></th>
	<th><?php echo JText::_('COM_CONTENTHISTORY_PREVIEW_VALUE'); ?></th>
</tr></thead>
<tbody>
<?php foreach ($this->item->data as $name => $value) : ?>
	<tr>
	<?php if (is_object($value->value)): ?>
		<td><strong><?php echo $value->label; ?></strong></td>
		<td></td><tr>
		<?php foreach ($value->value as $subName => $subValue): ?>
			<?php if ($subValue): ?>
				<tr>
				<td><i>&nbsp;&nbsp;<?php echo $subValue->label; ?></i></td>
				<td><?php echo $subValue->value; ?></td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php else: ?>
		<td><strong><?php echo $value->label; ?></strong></td>
		<td><?php echo $value->value; ?></td>
	<?php endif; ?>
	</tr>
<?php endforeach; ?>
</tbody>
</table>

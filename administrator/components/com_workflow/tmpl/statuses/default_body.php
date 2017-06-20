<?php
/**
 * Items Model for a Prove Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_prove
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */
defined('_JEXEC') or die('Restricted Access');
?>
<?php foreach ($this->statuses as $i => $item):
	$link = JRoute::_('index.php?option=com_workflow&view=status&task=status.edit&id=' . $item->id);
	?>
	<tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->id; ?>">
		<td class="order nowrap text-center hidden-sm-down">
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td>
			<a href="<?php echo $link ?>"><?php echo $item->name; ?></a>
		</td>
		<td>
			<?php echo $item->condition; ?>
		</td>
	</tr>
<?php endforeach ?>

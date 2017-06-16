
<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<?php foreach($this->workflows as $i => $item):
	$link = JRoute::_('index.php?option=com_prove&task=item.edit&id=' . $item->id);
	?>
	<tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->id; ?>">
		<td class="order nowrap text-center hidden-sm-down">
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td>
			<?php echo $item->title; ?>
		</td>
        <td class="text-center">
			<?php echo $item->created; ?>
        </td>
        <td class="text-center">
			<?php echo $item->modified; ?>
        </td>
	</tr>
<?php endforeach; ?>

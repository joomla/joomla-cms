<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>
<fieldset class="adminform">
	<legend><?php echo $this->label; ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
		<?php foreach($this->fields as $field): ?>
		<tr>
			<td width="185" class="key">
				<?php echo $field->label; ?>
			</td>
			<td>
				<?php echo $field->input; ?>

				<?php if ($annotation = $field->_element->attributes('annotation')): ?>
					<?php if ($field->_element->attributes('warning') == 'true'): ?>
						<?php echo $this->getWarningIcon($annotation); ?>
					<?php else: ?>
						<span><?php echo JText::_($annotation); ?></span>
					<?php endif; ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</fieldset>

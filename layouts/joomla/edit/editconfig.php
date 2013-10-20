<?php
	// We need to make a separate space for the configuration
	// so that those fields always show to those with permissions
?>

	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'editor', JText::_('COM_CONTENT_SLIDER_EDITOR_CONFIG', true)); ?>
		<?php foreach ($displayData->get('form')->getFieldset('editorConfig') as $field) : ?>
			<div class="control-group">
				<?php echo $field->label; ?>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
	<?php echo JHtml::_('bootstrap.endTab');

<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$template = JFactory::getApplication()->getTemplate();

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (document.formvalidator.isValid(document.id('component-form'))) {
			Joomla.submitform(task, document.getElementById('component-form'));
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_config');?>" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate form-horizontal">
	<h3>
		<?php echo JText::_($this->component->option.'_configuration') ?>
	</h3>
	<hr class="hr-condensed" />
	<div class="btn-toolbar">
		<button class="btn btn-primary" type="button" onclick="Joomla.submitform('component.save', this.form);">
			<i class="icon-ok icon-white"></i> <?php echo JText::_('JSAVE');?></button>
		<button class="btn" type="button" onclick="Joomla.submitform('component.apply', this.form);">
			<i class="icon-ok"></i> <?php echo JText::_('JAPPLY');?></button>
		<button class="btn pull-right" type="button" onclick="window.parent.jQuery('#modal').modal('hide');">
			<i class="icon-remove"></i> <?php echo JText::_('JCANCEL');?></button>
	</div>
	<ul class="nav nav-tabs" id="configTabs">
	<?php
		$fieldSets = $this->form->getFieldsets();
		foreach ($fieldSets as $name => $fieldSet) :
			$label = empty($fieldSet->label) ? 'COM_CONFIG_'.$name.'_FIELDSET_LABEL' : $fieldSet->label;
	?>
		<li><a href="#<?php echo $name;?>" data-toggle="tab"><?php echo  JText::_($label);?></a></li>
	<?php
		endforeach;
	?>
	</ul>
	<div class="tab-content">
		<?php
			$fieldSets = $this->form->getFieldsets();
			foreach ($fieldSets as $name => $fieldSet) :
		?>
			<div class="tab-pane" id="<?php echo $name;?>">
				<?php
				if (isset($fieldSet->description) && !empty($fieldSet->description)) :
					echo '<p class="tab-description">'.JText::_($fieldSet->description).'</p>';
				endif;
				foreach ($this->form->getFieldset($name) as $field):
				?>
					<div class="control-group">
					<?php if (!$field->hidden) : ?>
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
					<?php endif; ?>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php
				endforeach;
				?>
			</div>
		<?php
			endforeach;
		?>
	</div>
	<div>
		<input type="hidden" name="id" value="<?php echo $this->component->id;?>" />
		<input type="hidden" name="component" value="<?php echo $this->component->option;?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<script type="text/javascript">
		jQuery('#configTabs a:first').tab('show'); // Select first tab
</script>

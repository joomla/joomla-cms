<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$template = $app->getTemplate();

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (document.formvalidator.isValid(document.id('component-form')))
		{
			Joomla.submitform(task, document.getElementById('component-form'));
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_config'); ?>" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate form-horizontal">
	<div class="row-fluid">
		<!-- Begin Sidebar -->
		<div id="sidebar" class="span2">
			<div class="sidebar-nav">
				<?php echo $this->loadTemplate('navigation'); ?>
			</div>
		</div>
		<!-- End Sidebar -->
		<div class="span10">
			<ul class="nav nav-tabs" id="configTabs">
				<?php $fieldSets = $this->form->getFieldsets(); ?>
				<?php foreach ($fieldSets as $name => $fieldSet) : ?>
					<?php $label = empty($fieldSet->label) ? 'COM_CONFIG_' . $name . '_FIELDSET_LABEL' : $fieldSet->label; ?>
					<li><a href="#<?php echo $name; ?>" data-toggle="tab"><?php echo JText::_($label); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<div class="tab-content">
				<?php $fieldSets = $this->form->getFieldsets(); ?>
				<?php foreach ($fieldSets as $name => $fieldSet) : ?>
					<div class="tab-pane" id="<?php echo $name; ?>">
						<?php
						if (isset($fieldSet->description) && !empty($fieldSet->description))
						{
							echo '<p class="tab-description">' . JText::_($fieldSet->description) . '</p>';
						}
						?>
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<?php
							$class = '';
							$rel = '';
							if ($showon = $field->getAttribute('showon'))
							{
								JHtml::_('jquery.framework');
								JHtml::_('script', 'jui/cms.js', false, true);
								$id = $this->form->getFormControl();
								$showon = explode(':', $showon, 2);
								$class = ' showon_' . implode(' showon_', explode(',', $showon[1]));
								$rel = ' rel="showon_' . $id . '[' . $showon[0] . ']"';
							}
							?>
							<div class="control-group<?php echo $class; ?>"<?php echo $rel; ?>>
								<?php if (!$field->hidden && $name != "permissions") : ?>
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
								<?php endif; ?>
								<div class="<?php if ($name != "permissions") : ?>controls<?php endif; ?>">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div>
		<input type="hidden" name="id" value="<?php echo $this->component->id; ?>" />
		<input type="hidden" name="component" value="<?php echo $this->component->option; ?>" />
		<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<script type="text/javascript">
	jQuery('#configTabs a:first').tab('show'); // Select first tab
</script>

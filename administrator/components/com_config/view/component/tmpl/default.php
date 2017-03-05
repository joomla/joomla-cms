<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$template = $app->getTemplate();

// Load the tooltip behavior.
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', '.chzn-custom-value', null, array('disable_search_threshold' => 0));
JHtml::_('formbehavior.chosen', 'select');

// Load JS message titles
JText::script('ERROR');
JText::script('WARNING');
JText::script('NOTICE');
JText::script('MESSAGE');

JFactory::getDocument()->addScriptDeclaration(
	'
	Joomla.submitbutton = function(task)
	{
		if (task === "config.cancel.component" || document.formvalidator.isValid(document.getElementById("component-form")))
		{
			jQuery("#permissions-sliders select").attr("disabled", "disabled");
			Joomla.submitform(task, document.getElementById("component-form"));
		}
	};

	// Select first tab
	jQuery(document).ready(function() {
		jQuery("#configTabs a:first").tab("show");
	});'
);
?>

<form action="<?php echo JRoute::_('index.php?option=com_config'); ?>" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate form-horizontal">
	<div class="row-fluid">

		<!-- Begin Sidebar -->
		<div class="span2" id="sidebar">
			<div class="sidebar-nav">
				<?php echo $this->loadTemplate('navigation'); ?>
			</div>
		</div><!-- End Sidebar -->

		<div class="span10" id="config">

			<?php if ($this->fieldsets): ?>
			<ul class="nav nav-tabs" id="configTabs">
				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
					<?php $dataShowOn = ''; ?>
					<?php if (!empty($fieldSet->showon)) : ?>
						<?php JHtml::_('jquery.framework'); ?>
						<?php JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true)); ?>
						<?php $dataShowOn = ' data-showon=\'' . json_encode(JFormHelper::parseShowOnConditions($fieldSet->showon, $this->formControl)) . '\''; ?>
					<?php endif; ?>
					<?php $label = empty($fieldSet->label) ? 'COM_CONFIG_' . $name . '_FIELDSET_LABEL' : $fieldSet->label; ?>
					<li<?php echo $dataShowOn; ?>><a data-toggle="tab" href="#<?php echo $name; ?>"><?php echo JText::_($label); ?></a></li>
				<?php endforeach; ?>
			</ul><!-- /configTabs -->

			<div class="tab-content" id="configContent">
				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
					<div class="tab-pane" id="<?php echo $name; ?>">
						<?php if (isset($fieldSet->description) && !empty($fieldSet->description)) : ?>
							<div class="tab-description alert alert-info">
								<span class="icon-info"></span> <?php echo JText::_($fieldSet->description); ?>
							</div>
						<?php endif; ?>
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<?php $dataShowOn = ''; ?>
							<?php if ($field->showon) : ?>
								<?php JHtml::_('jquery.framework'); ?>
								<?php JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true)); ?>
								<?php $dataShowOn = ' data-showon=\'' . json_encode(JFormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . '\''; ?>
							<?php endif; ?>
							<?php if ($field->hidden) : ?>
								<?php echo $field->input; ?>
							<?php else : ?>
								<div class="control-group"<?php echo $dataShowOn; ?>>
									<?php if ($name != 'permissions') : ?>
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
									<?php endif; ?>
									<div class="<?php if ($name != 'permissions') : ?>controls<?php endif; ?>">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div><!-- /configContent -->
			<?php else: ?>
				<div class="alert alert-info"><i class="icon-info"></i> <?php echo JText::_('COM_CONFIG_COMPONENT_NO_CONFIG_FIELDS_MESSAGE'); ?></div>
			<?php endif; ?>

		</div><!-- /config -->

		<input type="hidden" name="id" value="<?php echo $this->component->id; ?>" />
		<input type="hidden" name="component" value="<?php echo $this->component->option; ?>" />
		<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

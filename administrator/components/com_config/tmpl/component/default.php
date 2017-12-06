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

JText::script('ERROR');
JText::script('WARNING');
JText::script('NOTICE');
JText::script('MESSAGE');

// Load the tooltip behavior.
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('formbehavior.chosen', '.chzn-custom-value', null, array('disable_search_threshold' => 0));

// @TODO delete this when custom elements modal is merged
JHtml::_('script', 'com_config/admin-application-default.min.js', ['relative' => true, 'version' => 'auto']);
?>

<form action="<?php echo JRoute::_('index.php?option=com_config'); ?>" id="component-form" method="post" class="form-validate" name="adminForm" autocomplete="off" data-cancel-task="config.cancel.component">
	<div class="row">

		<?php // Begin Sidebar ?>
		<div class="col-md-2" id="sidebar">
			<div class="sidebar-nav">
				<?php echo $this->loadTemplate('navigation'); ?>
			</div>
		</div>
		<?php // End Sidebar ?>

		<div class="col-md-10" id="config">

			<?php if ($this->fieldsets): ?>
			<ul class="nav nav-tabs flex-wrap" id="configTabs">
				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
					<?php $dataShowOn = ''; ?>
					<?php if (!empty($fieldSet->showon)) : ?>
						<?php JHtml::_('jquery.framework'); ?>
						<?php JHtml::_('script', 'system/cms.min.js', array('version' => 'auto', 'relative' => true)); ?>
						<?php $dataShowOn = ' data-showon=\'' . json_encode(JFormHelper::parseShowOnConditions($fieldSet->showon, $this->formControl)) . '\''; ?>
					<?php endif; ?>
					<?php $label = empty($fieldSet->label) ? 'COM_CONFIG_' . $name . '_FIELDSET_LABEL' : $fieldSet->label; ?>
					<li class="nav-item"<?php echo $dataShowOn; ?>><a class="nav-link" data-toggle="tab" href="#<?php echo $name; ?>"><?php echo JText::_($label); ?></a></li>
				<?php endforeach; ?>
			</ul>

			<div class="tab-content" id="configContent">
				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
					<div class="tab-pane" id="<?php echo $name; ?>">
						<?php if (isset($fieldSet->description) && !empty($fieldSet->description)) : ?>
							<joomla-alert type="info">
								<span class="icon-info" aria-hidden="true"></span> <?php echo JText::_($fieldSet->description); ?>
							</joomla-alert>
						<?php endif; ?>
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<?php
								$dataShowOn = '';
								$groupClass = $field->type === 'Spacer' ? ' field-spacer' : '';
							?>
							<?php if ($field->showon) : ?>
								<?php JHtml::_('jquery.framework'); ?>
								<?php JHtml::_('script', 'system/cms.min.js', array('version' => 'auto', 'relative' => true)); ?>
								<?php $dataShowOn = ' data-showon=\'' . json_encode(JFormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . '\''; ?>
							<?php endif; ?>
							<?php if ($field->hidden) : ?>
								<?php echo $field->input; ?>
							<?php else : ?>
								<div class="control-group<?php echo $groupClass; ?>"<?php echo $dataShowOn; ?>>
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
			</div>
			<?php else: ?>
				<joomla-alert type="info"><span class="icon-info" aria-hidden="true"></span> <?php echo JText::_('COM_CONFIG_COMPONENT_NO_CONFIG_FIELDS_MESSAGE'); ?></joomla-alert>
			<?php endif; ?>

		</div>

		<input type="hidden" name="id" value="<?php echo $this->component->id; ?>">
		<input type="hidden" name="component" value="<?php echo $this->component->option; ?>">
		<input type="hidden" name="return" value="<?php echo $this->return; ?>">
		<input type="hidden" name="task" value="">
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

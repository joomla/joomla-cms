<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

$app = Factory::getApplication();
$template = $app->getTemplate();

Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

// Load the tooltip behavior.
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

// @TODO delete this when custom elements modal is merged
HTMLHelper::_('script', 'com_config/admin-application-default.min.js', ['version' => 'auto', 'relative' => true]);
?>
<form action="<?php echo Route::_('index.php?option=com_config'); ?>" id="component-form" method="post" class="form-validate" name="adminForm" autocomplete="off" data-cancel-task="config.cancel.component">
	<div class="row">
		<div class="col-md-2" id="sidebar">
			<div class="sidebar-nav">
				<?php echo $this->loadTemplate('navigation'); ?>
			</div>
		</div>
		<div class="col-md-10" id="config">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'gc_config_component', array('active' => 'config-document')); ?>
			<?php if ($this->fieldsets): ?>
				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
					<?php echo HTMLHelper::_('uitab.addTab', 'gc_config_component', $name, Text::_($fieldSet->label));
					// @todo restore controlling the tab with showon, currently not supported by joomla-tabs
					// $dataShowOn = ' data-showon=\'' . json_encode(FormHelper::parseShowOnConditions($fieldSet->showon, $this->formControl)) . '\'';
					?>
						<?php if (isset($fieldSet->description) && !empty($fieldSet->description)) : ?>
							<div class="alert alert-info">
								<span class="icon-info" aria-hidden="true"></span> <?php echo Text::_($fieldSet->description); ?>
							</div>
						<?php endif; ?>
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<?php
								$dataShowOn = '';
								$groupClass = $field->type === 'Spacer' ? ' field-spacer' : '';
							?>
							<?php if ($field->showon) : ?>
								<?php HTMLHelper::_('script', 'system/showon.min.js', array('version' => 'auto', 'relative' => true)); ?>
								<?php $dataShowOn = ' data-showon=\'' . json_encode(FormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . '\''; ?>
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
					<?php echo HTMLHelper::_('uitab.endTab'); ?>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="alert alert-info">
					<span class="icon-info" aria-hidden="true"></span>
					<?php echo Text::_('COM_CONFIG_COMPONENT_NO_CONFIG_FIELDS_MESSAGE'); ?>
				</div>
			<?php endif; ?>
			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
		</div>
		<input type="hidden" name="id" value="<?php echo $this->component->id; ?>">
		<input type="hidden" name="component" value="<?php echo $this->component->option; ?>">
		<input type="hidden" name="return" value="<?php echo $this->return; ?>">
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>

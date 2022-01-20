<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Scheduler\Administrator\Task\TaskOption;
use Joomla\Component\Scheduler\Administrator\View\Task\HtmlView;

/** @var  HtmlView $this */

$wa = $this->document->getWebAssetManager();

$wa->useScript('keepalive');
$wa->useScript('form.validate');
$wa->useStyle('com_scheduler.admin-view-task-css');

/** @var AdministratorApplication $app */
$app = $this->app;

$input = $app->getInput();

// Fieldsets to be ignored by the `joomla.edit.params` template.
$this->ignore_fieldsets = ['aside', 'details', 'exec_hist', 'custom-cron-rules', 'basic', 'advanced', 'priority'];

// Used by the `joomla.edit.params` template to render the right template for UI tabs.
$this->useCoreUI = true;

$advancedFieldsets = $this->form->getFieldsets('params');

// Don't show the params fieldset, they will be loaded later
foreach ($advancedFieldsets as $name => $fieldset) :
	if ($name === 'task_params') :
		unset($advancedFieldsets[$name]);
		continue;
	endif;

	$this->ignore_fieldsets[] = $fieldset->name;
endforeach;

?>

<form action="<?php echo Route::_('index.php?option=com_scheduler&view=task&layout=edit&id=' . (int) $this->item->id); ?>"
	  method="post" name="adminForm" id="task-form"
	  aria-label="<?php echo Text::_('COM_SCHEDULER_FORM_TITLE_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>"
	  class="form-validate">

	<!-- The task title field -->
	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<!-- The main form card -->
	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<!-- The first (and the main) tab in the form -->
		<?php echo
		HTMLHelper::_(
			'uitab.addTab',
			'myTab',
			'general',
			empty($this->item->id) ? Text::_('COM_SCHEDULER_NEW_TASK') : Text::_('COM_SCHEDULER_EDIT_TASK')
		);
		?>
		<div class="row">
			<div class="col-lg-9">
				<!-- Task type title, description go here -->
				<?php if ($this->item->taskOption):
					/** @var TaskOption $taskOption */
					$taskOption = $this->item->taskOption; ?>
					<div id="taskOptionInfo">
						<h2 id="taskOptionTitle">
							<?php echo $taskOption->title ?>
						</h2>
						<?php
							$this->fieldset    = 'description';
							$short_description = Text::_($taskOption->desc);
							$long_description  = LayoutHelper::render('joomla.edit.fieldset', $this);

							if (!$long_description)
							{
								$truncated = HTMLHelper::_('string.truncate', $short_description, 550, true, false);

								if (strlen($truncated) > 500)
								{
									$long_description  = $short_description;
									$short_description = HTMLHelper::_('string.truncate', $truncated, 250);

									if ($short_description == $long_description)
									{
										$long_description = '';
									}
								}
							}
						?>
						<p><?php echo $short_description; ?></p>
						<?php if ($long_description) : ?>
							<p class="readmore">
								<a href="#" onclick="document.getElementById('myTab').activateTab(document.getElementById('description'));">
									<?php echo Text::_('JGLOBAL_SHOW_FULL_DESCRIPTION'); ?>
								</a>
							</p>
						<?php endif; ?>
					</div>
					<!-- If TaskOption does not exist -->
				<?php else:
					$app->enqueueMessage(Text::_('COM_SCHEDULER_WARNING_EXISTING_TASK_TYPE_NOT_FOUND'), 'warning');
					?>
				<?php endif; ?>
				<fieldset class="options-form">
					<legend><?php echo Text::_('COM_SCHEDULER_FIELDSET_BASIC'); ?></legend>
					<?php echo $this->form->renderFieldset('basic'); ?>
				</fieldset>

				<fieldset class="options-form match-custom"
						  data-showon='[{"field":"jform[execution_rules][rule-type]","values":["cron-expression"],"sign":"=","op":""}]'
				>
					<legend><?php echo Text::_('COM_SCHEDULER_FIELDSET_CRON_OPTIONS'); ?></legend>
					<?php echo $this->form->renderFieldset('custom-cron-rules'); ?>
				</fieldset>
				<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
			</div>

			<div class="col-lg-3">
				<?php echo $this->form->renderFieldset('aside'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php if (isset($long_description) && $long_description != '') : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'description', Text::_('JGLOBAL_FIELDSET_DESCRIPTION')); ?>
				<div class="card">
					<div class="card-body">
						<?php echo $long_description; ?>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>
		<!-- Tab for advanced options -->
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'advanced', Text::_('JGLOBAL_FIELDSET_ADVANCED')) ?>
		<div class="row">
			<div class="col-lg-9">
			<fieldset class="options-form">
				<legend><?php echo Text::_('COM_SCHEDULER_FIELDSET_PRIORITY') ?></legend>
				<?php echo $this->form->renderFieldset('priority') ?>
			</fieldset>
			<?php foreach ($advancedFieldsets as $fieldset) : ?>
				<fieldset class="options-form">
					<legend><?php echo Text::_($fieldset->label ?: 'COM_SCHEDULER_FIELDSET_' . $fieldset->name) ?></legend>
					<?php echo $this->form->renderFieldset($fieldset->name) ?>
				</fieldset>
			<?php endforeach; ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab') ?>

		<!-- Tab to show execution history -->
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'exec_hist', Text::_('COM_SCHEDULER_FIELDSET_EXEC_HIST')); ?>
		<div class="row">
			<div class="col-lg-9">
				<fieldset class="options-form">
					<legend><?php echo Text::_('COM_SCHEDULER_FIELDSET_EXEC_HIST'); ?></legend>
					<?php echo $this->form->renderFieldset('exec_hist'); ?>
				</fieldset>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<!-- Tab to show creation details-->
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('JDETAILS')); ?>
		<div class="row">
			<div class="col-lg-9">
				<fieldset class="options-form">
					<legend><?php echo Text::_('JDETAILS'); ?></legend>
					<?php echo $this->form->renderFieldset('details'); ?>
				</fieldset>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<!-- Item permissions tab, if user has admin privileges -->
		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JCONFIG_PERMISSIONS_LABEL')); ?>
			<fieldset id="fieldset-permissions" class="options-form">
				<legend><?php echo Text::_('JCONFIG_PERMISSIONS_LABEL'); ?></legend>
				<div>
					<?php echo $this->form->getInput('rules'); ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>
		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
		<?php echo $this->form->getInput('context'); ?>
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>

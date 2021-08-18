<?php
/**
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 *
 * @codingStandardsIgnoreStart
 */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Cronjobs\Administrator\Cronjobs\CronOption;
use Joomla\Component\Cronjobs\Administrator\View\Cronjob\HtmlView;

/** @var HtmlView $this */

$wa = $this->document->getWebAssetManager();

$wa->useScript('keepalive');
$wa->useScript('form.validate');
$wa->useStyle('com_cronjobs.admin-view-cronjob-css');

/** @var AdministratorApplication $app */
$app = $this->app;

$input = $app->getInput();

// ?
$this->ignore_fieldsets = [];

// ? : Are these of use here?
$isModal = $input->get('layout') === 'modal';
$layout = $isModal ? 'modal' : 'edit';
$tmpl = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<!-- Form begins -->
<form action="<?php echo Route::_('index.php?option=com_cronjobs&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>"
	  method="post" name="adminForm" id="cronjob-form"
	  aria-label="<?php echo Text::_('COM_CRONJOBS_FORM_TITLE_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>"
	  class="form-validate">

	<!-- The cronjob title field -->
	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<!-- The main form card -->
	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<!-- The first (and the main) tab in the form -->
		<?php echo
		HTMLHelper::_('uitab.addTab',
				'myTab', 'general',
				empty($this->item->id) ? Text::_('COM_CRONJOBS_NEW_CRONJOB') : Text::_('COM_CRONJOBS_EDIT_CRONJOB')
		);
		?>
		<div class="row">
			<div class="col-lg-9">
				<!-- Job type title, description go here -->
				<?php if ($this->item->cronOption):
					/** @var CronOption $cronOption */
					$cronOption = $this->item->cronOption; ?>
					<div id="cronOptionInfo">
						<h2 id="cronOptionTitle">
							<?php echo $cronOption->title ?>
						</h2>
						<p id="cronOptionDesc">
							<?php
							// TODO: For long descriptions, we'll want a "read more" functionality like com_modules
							$desc = HTMLHelper::_('string.truncate', $this->escape(strip_tags($cronOption->desc)), 250);
							echo $desc;
							?>
						</p>
					</div>
					<!-- If JobOption does not exist -->
				<?php else:
					$app->enqueueMessage(Text::_('COM_CRONJOBS_WARNING_EXISTING_JOB_TYPE_NOT_FOUND'), 'warning');
					?>
				<?php endif; ?>
				<fieldset class="options-form">
					<legend><?php echo Text::_('COM_CRONJOBS_FIELDSET_BASIC'); ?></legend>
					<?php echo $this->form->renderFieldset('basic'); ?>
				</fieldset>


				<fieldset class="options-form match-custom"
						  data-showon='[{"field":"jform[execution_rules][rule-type]","values":["custom"],"sign":"=","op":""}]'
				>
					<legend><?php echo Text::_('COM_CRONJOBS_FIELDSET_CRON_OPTIONS'); ?></legend>
					<?php echo $this->form->renderFieldset('custom-cron-rules'); ?>
				</fieldset>

				<fieldset class="options-form">
					<legend><?php echo Text::_('COM_CRONJOBS_FIELDSET_PARAMS_FS'); ?></legend>
					<?php
					// TODO: Render [all] fieldsets with the Joomla params template
					// ! Investigate why `render('joomla.edit.params', $this)` fails
					echo $this->form->renderFieldset('params-fs');
					?>
				</fieldset>
			</div>
			<div class="col-lg-3">
				<?php echo $this->form->renderFieldset('aside'); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<!-- Tab to show execution history-->
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'exec_hist', Text::_('COM_CRONJOBS_FIELDSET_EXEC_HIST')); ?>
		<div class="row">
			<div class="col-lg-9">
				<fieldset class="options-form">
					<legend><?php echo Text::_('COM_CRONJOBS_FIELDSET_EXEC_HIST'); ?></legend>
					<?php echo $this->form->renderFieldset('exec_hist'); ?>
				</fieldset>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<!-- Tab to show creation details-->
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_CRONJOBS_FIELDSET_DETAILS')); ?>
		<div class="row">
			<div class="col-lg-9">
				<fieldset class="options-form">
					<legend><?php echo Text::_('COM_CRONJOBS_FIELDSET_DETAILS'); ?></legend>
					<?php echo $this->form->renderFieldset('details'); ?>
				</fieldset>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<!-- Item permissions tab, if user has admin privileges -->
		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('COM_CRONJOBS_FIELDSET_RULES')); ?>
			<fieldset id="fieldset-permissions" class="options-form">
				<legend><?php echo Text::_('COM_CRONJOBS_FIELDSET_RULES'); ?></legend>
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

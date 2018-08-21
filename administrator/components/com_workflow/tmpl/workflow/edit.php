<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', '.advancedSelect', null, array('disable_search_threshold' => 0 ));

$app = Factory::getApplication();
$input = $app->input;

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';

?>

<form action="<?php echo Route::_('index.php?option=com_workflow&extension=' . $input->getCmd('extension', 'com_content') . '&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="workflow-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'description', Text::_('COM_WORKFLOW_DESCRIPTION'));?>
		<div class="row">
			<div class="col-md-9">
				<?php echo $this->form->renderField('description'); ?>
			</div>
			<div class="col-md-3">
				<div class="card card-block card-light">
					<div class="card-body">
						<fieldset class="form-vertical form-no-margin">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('published'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('published'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('default'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('default'); ?>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('COM_WORKFLOW_RULES_TAB')); ?>
		<?php echo $this->form->getInput('rules'); ?>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>
	<?php echo $this->form->getInput('extension'); ?>
	<input type="hidden" name="task" value="workflow.edit" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

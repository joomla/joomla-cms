<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$app = Factory::getApplication();
$input = $app->input;

// In case of modal
$isModal = $input->get('layout') === 'modal';
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';

?>

<form action="<?php echo Route::_('index.php?option=com_workflow&extension=' . $input->getCmd('extension') . '&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="workflow-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'description', Text::_('COM_WORKFLOW_DESCRIPTION'));?>
		<div class="row">
			<div class="col-lg-9">
				<div class="card card-block">
					<div class="card-body form-vertical">
					<?php echo $this->form->renderField('description'); ?>
					</div>
				</div>
			</div>
			<div class="col-lg-3">
				<div class="card card-block">
					<div class="card-body">
						<fieldset class="form-vertical">
							<?php echo $this->form->renderField('published'); ?>
							<?php echo $this->form->renderField('default'); ?>
						</fieldset>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('COM_WORKFLOW_RULES_TAB')); ?>
			<fieldset id="fieldset-rules" class="options-form">
				<legend><?php echo Text::_('COM_WORKFLOW_RULES_TAB'); ?></legend>
				<?php echo $this->form->getInput('rules'); ?>
			</fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>
	<?php echo $this->form->getInput('extension'); ?>
	<input type="hidden" name="task" value="workflow.edit" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

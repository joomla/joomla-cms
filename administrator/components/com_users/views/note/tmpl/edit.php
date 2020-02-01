<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration('
jQuery(document).ready(function() {
	Joomla.submitbutton = function(task)
	{
		if (task == "note.cancel" || document.formvalidator.isValid(document.getElementById("note-form")))
		{
			' . $this->form->getField('body')->save() . '
			Joomla.submitform(task, document.getElementById("note-form"));
		}
	}
});');
?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=note&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="note-form" class="form-validate form-horizontal">
		<fieldset class="adminform">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('subject'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('subject'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('user_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('user_id'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('catid'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('catid'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('state'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('state'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('review_time'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('review_time'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('version_note'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('version_note'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('body'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('body'); ?>
				</div>
			</div>

			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
</form>

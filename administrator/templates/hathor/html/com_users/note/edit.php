<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');

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
<form action="<?php echo JRoute::_('index.php?option=com_users&view=note&id=' . (int) $this->item->id);?>" method="post" name="adminForm" id="note-form" class="form-validate">
	<div class="col main-section">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_USERS_NEW_NOTE') : JText::sprintf('COM_USERS_EDIT_NOTE', $this->item->id); ?></legend>
			<ul class="adminformlist">
				<li>
					<?php echo $this->form->getLabel('subject'); ?>
					<?php echo $this->form->getInput('subject'); ?>
				</li>
				<li>
					<div class="clr"></div>
					<?php echo $this->form->getLabel('user_id'); ?>
					<?php echo $this->form->getInput('user_id'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('catid'); ?>
					<?php echo $this->form->getInput('catid'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('state'); ?>
					<?php echo $this->form->getInput('state'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('review_time'); ?>
					<?php echo $this->form->getInput('review_time'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('version_note'); ?>
					<?php echo $this->form->getInput('version_note'); ?>
				</li>
			</ul>
			<div class="clr"></div>
			<?php echo $this->form->getLabel('body'); ?>
			<div class="clr"></div>
			<div class="editor">
				<?php echo $this->form->getInput('body'); ?>
			</div>

			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
</form>

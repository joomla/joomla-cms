<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var $this UsersViewNote */

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script language="javascript" type="text/javascript">
Joomla.submitbutton = function(task)
{
	if (task == 'note.cancel' || document.formvalidator.isValid(document.id('note-form'))) {
		Joomla.submitform(task, document.getElementById('note-form'));
	}
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=note&id='.(int) $this->item->id);?>" method="post" name="adminForm" id="note-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend>
				<?php echo $this->item->id ? JText::sprintf('COM_USERS_EDIT_NOTE_N', $this->item->id) : JText::_('COM_USERS_NEW_NOTE');?>
			</legend>

			<ul class="adminformlist">
				<li>
					<?php echo $this->form->getLabel('subject'); ?>
					<?php echo $this->form->getInput('subject'); ?>
				</li>
				<li>
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
			</ul>

			<div>
				<?php echo $this->form->getLabel('body'); ?>
				<div class="clr"></div>
				<?php echo $this->form->getInput('body'); ?>
			</div>

			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</div>
</form>

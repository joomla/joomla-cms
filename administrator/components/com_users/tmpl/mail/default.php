<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');
JText::script('COM_USERS_MAIL_PLEASE_FILL_IN_THE_SUBJECT', true);
JText::script('COM_USERS_MAIL_PLEASE_SELECT_A_GROUP', true);
JText::script('COM_USERS_MAIL_PLEASE_FILL_IN_THE_MESSAGE', true);

JHtml::_('script', 'com_users/admin-users-mail.min.js', array('version' => 'auto', 'relative' => true));

$comUserParams = JComponentHelper::getParams('com_users');
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&view=mail'); ?>" name="adminForm" method="post" id="adminForm">
	<div class="row">
		<div class="col-md-9">
			<fieldset class="adminform">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('subject'); ?></div>
					<div class="controls">
						<div class="input-group">
							<?php if (!empty($comUserParams->get('mailSubjectPrefix'))) : ?>
								<span class="input-group-prepend">
									<span class="input-group-text"><?php echo $comUserParams->get('mailSubjectPrefix'); ?></span>
								</span>
							<?php endif; ?>
							<?php echo $this->form->getInput('subject'); ?>
						</div>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('message'); ?></div>
					<div class="controls">
						<?php echo $this->form->getInput('message'); ?>
						<?php if (!empty($comUserParams->get('mailBodySuffix'))) : ?>
							<div class="mt-1 card">
								<div class="card-body">
									<?php echo $comUserParams->get('mailBodySuffix'); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</fieldset>
			<input type="hidden" name="task" value="">
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<div class="col-md-3">
			<div class="card card-light">
				<div class="card-body">
					<div class="control-group">
						<?php echo $this->form->getInput('recurse'); ?>
						<?php echo $this->form->getLabel('recurse'); ?>
					</div>
					<div class="control-group">
						<?php echo $this->form->getInput('mode'); ?>
						<?php echo $this->form->getLabel('mode'); ?>
					</div>
					<div class="control-group">
						<?php echo $this->form->getInput('disabled'); ?>
						<?php echo $this->form->getLabel('disabled'); ?>
					</div>
					<div class="control-group">
						<?php echo $this->form->getInput('bcc'); ?>
						<?php echo $this->form->getLabel('bcc'); ?>
					</div>
					<div class="control-group">
						<?php echo $this->form->getLabel('group'); ?>
						<?php echo $this->form->getInput('group'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>

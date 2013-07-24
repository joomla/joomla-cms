<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$input = $app->input;

$assoc = isset($app->item_associations) ? $app->item_associations : 0;
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'contact.cancel' || document.formvalidator.isValid(document.id('contact-form')))
		{
			<?php echo $this->form->getField('misc')->save(); ?>

			if (window.opener && (task == 'contact.save' || task == 'contact.cancel'))
			{
				window.opener.document.closeEditWindow = self;
				window.opener.setTimeout('window.document.closeEditWindow.close()', 1000);
			}

			Joomla.submitform(task, document.getElementById('contact-form'));
		}
	}
</script>
<div class="container-popup">

<div class="pull-right">
	<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('contact.apply');"><?php echo JText::_('JTOOLBAR_APPLY') ?></button>
	<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('contact.save');"><?php echo JText::_('JTOOLBAR_SAVE') ?></button>
	<button class="btn" type="button" onclick="Joomla.submitbutton('contact.cancel');"><?php echo JText::_('JCANCEL') ?></button>
</div>

<div class="clearfix"> </div>
<hr class="hr-condensed" />

<form action="<?php echo JRoute::_('index.php?option=com_contact&layout=modal&tmpl=component&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="contact-form" class="form-validate form-horizontal">
	<div class="row-fluid">
		<!-- Begin contact -->
		<div class="span10 form-horizontal">
		<fieldset>
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', empty($this->item->id) ? JText::_('COM_CONTACT_NEW_CONTACT', true) : JText::sprintf('COM_CONTACT_EDIT_CONTACT', $this->item->id, true)); ?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('user_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('user_id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('catid'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('catid'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('ordering'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('ordering'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
				</div>
				<div class="control-group form-inline">
					<?php echo $this->form->getLabel('misc'); ?>
				</div>
					<?php echo $this->form->getInput('misc'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created_by_alias'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created_by_alias'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('publish_up'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('publish_up'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('publish_down'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('publish_down'); ?></div>
				</div>
					<?php if ($this->item->modified_by) : ?>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('modified_by'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('modified_by'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('modified'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('modified'); ?></div>
						</div>
					<?php endif; ?>
				<?php if ($this->item->version) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('version'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('version'); ?>
						</div>
					</div>
				<?php endif; ?>
				<?php if ($this->item->hits) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('hits'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('hits'); ?>
						</div>
					</div>
				<?php endif; ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'basic', JText::_('COM_CONTACT_CONTACT_DETAILS', true)); ?>

				<p><?php echo empty($this->item->id) ? JText::_('COM_CONTACT_DETAILS', true) : JText::sprintf('COM_CONTACT_EDIT_DETAILS', $this->item->id, true); ?></p>

				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('image'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('con_position'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('con_position'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('email_to'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('email_to'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('address'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('address'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('suburb'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('suburb'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('postcode'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('postcode'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('country'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('country'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('telephone'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('telephone'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('mobile'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('mobile'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('fax'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('fax'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('webpage'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('webpage'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('sortname1'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('sortname1'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('sortname2'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('sortname2'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('sortname3'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('sortname3'); ?></div>
				</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo $this->loadTemplate('params'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'metadata', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS', true)); ?>
					<?php echo $this->loadTemplate('metadata'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		</fieldset>

		<div class="hidden">
			<?php echo $this->loadTemplate('associations'); ?>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<!-- End content -->
	<!-- Begin Sidebar -->
		<?php echo JLayoutHelper::render('joomla.edit.details', $this); ?>
	<!-- End Sidebar -->
</form>
</div>
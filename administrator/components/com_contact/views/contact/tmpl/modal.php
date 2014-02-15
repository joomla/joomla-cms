<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();

$input = $app->input;
$assoc = JLanguageAssociations::isEnabled();
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

<form action="<?php echo JRoute::_('index.php?option=com_contact&layout=modal&tmpl=component&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="contact-form" class="form-validate form-horizontal">
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', empty($this->item->id) ? JText::_('COM_CONTACT_NEW_CONTACT', true) : JText::sprintf('COM_CONTACT_EDIT_CONTACT', $this->item->id, true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<div class="row-fluid form-horizontal-desktop">
					<div class="span6">
						<div >
							<?php echo $this->form->getControlGroup('user_id'); ?>
						</div>
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
					</div>
					<div class="span6">
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
					</div>
				</div>
			</div>
			<div class="span3">
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'misc', JText::_('JGLOBAL_FIELDSET_MISCELLANEOUS', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
				<div class="form-vertical">
					<?php echo $this->form->getControlGroup('misc'); ?>
				</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

		<?php if ($assoc) : ?>
			<div class="hidden"><?php echo $this->loadTemplate('associations'); ?></div>
		<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

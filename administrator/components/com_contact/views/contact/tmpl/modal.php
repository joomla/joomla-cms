<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();

$input = $app->input;
$assoc = JLanguageAssociations::isEnabled();

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "contact.cancel" || document.formvalidator.isValid(document.getElementById("contact-form")))
		{
			' . $this->form->getField('misc')->save() . '

			if (window.opener && (task == "contact.save" || task == "contact.cancel"))
			{
				window.opener.document.closeEditWindow = self;
				window.opener.setTimeout("window.document.closeEditWindow.close()", 1000);
			}

			Joomla.submitform(task, document.getElementById("contact-form"));
		}
	};
');

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('details', 'display', 'email', 'item_associations');
?>
<div class="container-popup">

<div class="pull-right">
	<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('contact.apply');"><?php echo JText::_('JTOOLBAR_APPLY') ?></button>
	<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('contact.save');"><?php echo JText::_('JTOOLBAR_SAVE') ?></button>
	<button class="btn" type="button" onclick="Joomla.submitbutton('contact.cancel');"><?php echo JText::_('JCANCEL') ?></button>
</div>

<div class="clearfix"> </div>
<hr class="hr-condensed" />

<form action="<?php echo JRoute::_('index.php?option=com_contact&layout=modal&tmpl=component&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="contact-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', empty($this->item->id) ? JText::_('COM_CONTACT_NEW_CONTACT', true) : JText::_('COM_CONTACT_EDIT_CONTACT', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<div class="row-fluid form-horizontal-desktop">
					<div class="span6">
						<?php echo $this->form->renderField('user_id'); ?>
						<?php echo $this->form->renderField('image'); ?>
						<?php echo $this->form->renderField('con_position'); ?>
						<?php echo $this->form->renderField('email_to'); ?>
						<?php echo $this->form->renderField('address'); ?>
						<?php echo $this->form->renderField('suburb'); ?>
						<?php echo $this->form->renderField('state'); ?>
						<?php echo $this->form->renderField('postcode'); ?>
						<?php echo $this->form->renderField('country'); ?>
					</div>
					<div class="span6">
						<?php echo $this->form->renderField('telephone'); ?>
						<?php echo $this->form->renderField('mobile'); ?>
						<?php echo $this->form->renderField('fax'); ?>
						<?php echo $this->form->renderField('webpage'); ?>
						<?php echo $this->form->renderField('sortname1'); ?>
						<?php echo $this->form->renderField('sortname2'); ?>
						<?php echo $this->form->renderField('sortname3'); ?>
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
					<?php echo $this->form->renderField('misc'); ?>
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

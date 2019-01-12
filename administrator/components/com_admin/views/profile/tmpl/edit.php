<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "profile.cancel" || document.formvalidator.isValid(document.getElementById("profile-form")))
		{
			Joomla.submitform(task, document.getElementById("profile-form"));
		}
	};
');

// Load chosen.css
JHtml::_('formbehavior.chosen', 'select');

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('user_details');
?>

<form action="<?php echo JRoute::_('index.php?option=com_admin&view=profile&layout=edit&id=' . $this->item->id); ?>" method="post" name="adminForm" id="profile-form" class="form-validate form-horizontal" enctype="multipart/form-data">
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'account')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'account', JText::_('COM_ADMIN_USER_ACCOUNT_DETAILS')); ?>
	<?php foreach ($this->form->getFieldset('user_details') as $field) : ?>
		<?php if ($field->fieldname === 'password2') : ?>
			<?php // Disables autocomplete ?>
			<input type="password" style="display:none">
		<?php endif; ?>
		<?php echo $field->renderField(); ?>
	<?php endforeach; ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>
	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

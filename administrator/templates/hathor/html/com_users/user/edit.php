<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'user.cancel' || document.formvalidator.isValid(document.getElementById('user-form')))
		{
			Joomla.submitform(task, document.getElementById('user-form'));
		}
	}

	Joomla.twoFactorMethodChange = function(e)
	{
		var selectedPane = 'com_users_twofactor_' + jQuery('#jform_twofactor_method').val();

		jQuery.each(jQuery('#com_users_twofactor_forms_container>div'), function(i, el) {
			if (el.id != selectedPane)
			{
				jQuery('#' + el.id).hide(0);
			}
			else
			{
				jQuery('#' + el.id).show(0);
			}
		});
	}
");
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="user-form" class="form-validate" enctype="multipart/form-data">
	<div class="col main-section">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_USERS_USER_ACCOUNT_DETAILS'); ?></legend>
			<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset('user_details') as $field) : ?>
				<li><?php echo $field->label; ?>
				<?php echo $field->input; ?></li>
			<?php endforeach; ?>
			</ul>
		</fieldset>
	</div>
	<div class="col options-section">
		<?php echo  JHtml::_('sliders.start', 'user-slider', array('useCookie' => 1)); ?>
		<?php if ($this->grouplist) : ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_USERS_ASSIGNED_GROUPS'), 'groups'); ?>
			<fieldset class="panelform">
				<legend class="element-invisible"><?php echo JText::_('COM_USERS_ASSIGNED_GROUPS'); ?></legend>
				<?php echo $this->loadTemplate('groups'); ?>
			</fieldset>
		<?php endif; ?>
		<?php
		foreach ($fieldsets as $fieldset) :
			if ($fieldset->name == 'user_details') :
				continue;
			endif;
			echo JHtml::_('sliders.panel', JText::_($fieldset->label), $fieldset->name);
		?>
		<fieldset class="panelform">
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
					<?php if ($field->hidden) : ?>
						<?php echo $field->input; ?>
					<?php else : ?>
						<li><?php echo $field->label; ?>
						<?php echo $field->input; ?></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</fieldset>
		<?php endforeach; ?>

		<?php if (!empty($this->tfaform) && $this->item->id): ?>
		<?php echo JHtml::_('sliders.panel', JText::_('COM_USERS_USER_TWO_FACTOR_AUTH'), 'twofactorauth'); ?>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_twofactor_method-lbl" for="jform_twofactor_method" class="hasTooltip"
						title="<?php echo '<strong>' . JText::_('COM_USERS_USER_FIELD_TWOFACTOR_LABEL') . '</strong><br />' . JText::_('COM_USERS_USER_FIELD_TWOFACTOR_DESC'); ?>">
					<?php echo JText::_('COM_USERS_USER_FIELD_TWOFACTOR_LABEL'); ?>
				</label>
			</div>
			<div class="controls">
				<?php echo JHtml::_('select.genericlist', Usershelper::getTwoFactorMethods(), 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange()'), 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false) ?>
			</div>
		</div>
		<div id="com_users_twofactor_forms_container">
			<?php foreach ($this->tfaform as $form): ?>
			<?php $style = $form['method'] == $this->otpConfig->method ? 'display: block' : 'display: none'; ?>
			<div id="com_users_twofactor_<?php echo $form['method'] ?>" style="<?php echo $style; ?>">
				<?php echo $form['form'] ?>
			</div>
			<?php endforeach; ?>
		</div>

		<fieldset>
			<legend>
				<?php echo JText::_('COM_USERS_USER_OTEPS') ?>
			</legend>
			<div class="alert alert-info">
				<?php echo JText::_('COM_USERS_USER_OTEPS_DESC') ?>
			</div>
			<?php if (empty($this->otpConfig->otep)): ?>
			<div class="alert alert-warning">
				<?php echo JText::_('COM_USERS_USER_OTEPS_WAIT_DESC') ?>
			</div>
			<?php else: ?>
			<?php foreach ($this->otpConfig->otep as $otep): ?>
			<span class="span3">
				<?php echo substr($otep, 0, 4) ?>-<?php echo substr($otep, 4, 4) ?>-<?php echo substr($otep, 8, 4) ?>-<?php echo substr($otep, 12, 4) ?>
			</span>
			<?php endforeach; ?>
			<div class="clearfix"></div>
			<?php endif; ?>
		</fieldset>
		<?php endif; ?>

		<?php echo JHtml::_('sliders.end'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

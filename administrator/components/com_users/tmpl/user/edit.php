<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('script', 'com_users/admin-users-user.min.js', array('version' => 'auto', 'relative' => true));

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
$settings  = array();
?>

<form action="<?php echo Route::_('index.php?option=com_users&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="user-form" enctype="multipart/form-data" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.item_title', $this); ?>

	<fieldset>
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('COM_USERS_USER_ACCOUNT_DETAILS')); ?>
				<?php foreach ($this->form->getFieldset('user_details') as $field) : ?>
					<div class="control-group">
						<div class="control-label">
								<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

			<?php if ($this->grouplist) : ?>
				<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'groups', Text::_('COM_USERS_ASSIGNED_GROUPS')); ?>
					<?php echo $this->loadTemplate('groups'); ?>
				<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
			<?php endif; ?>

			<?php
			$this->ignore_fieldsets = array('user_details');
			echo LayoutHelper::render('joomla.edit.params', $this);
			?>

		<?php if (!empty($this->tfaform) && $this->item->id) : ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'twofactorauth', Text::_('COM_USERS_USER_TWO_FACTOR_AUTH')); ?>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_twofactor_method-lbl" for="jform_twofactor_method" class="hasTooltip"
					title="<?php echo '<strong>' . Text::_('COM_USERS_USER_FIELD_TWOFACTOR_LABEL') . '</strong>'; ?>">
					<?php echo Text::_('COM_USERS_USER_FIELD_TWOFACTOR_LABEL'); ?>
				</label>
			</div>
			<div class="controls">
				<?php echo HTMLHelper::_('select.genericlist', Usershelper::getTwoFactorMethods(), 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange()', 'class' => 'custom-select'), 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false); ?>
			</div>
		</div>
		<div id="com_users_twofactor_forms_container">
			<?php foreach ($this->tfaform as $form) : ?>
			<?php $style = $form['method'] == $this->otpConfig->method ? 'display: block' : 'display: none'; ?>
			<div id="com_users_twofactor_<?php echo $form['method'] ?>" style="<?php echo $style; ?>">
				<?php echo $form['form'] ?>
			</div>
			<?php endforeach; ?>
		</div>

		<fieldset>
			<legend>
				<?php echo Text::_('COM_USERS_USER_OTEPS'); ?>
			</legend>
			<joomla-alert type="info"><?php echo Text::_('COM_USERS_USER_OTEPS_DESC'); ?></joomla-alert>
			<?php if (empty($this->otpConfig->otep)) : ?>
				<joomla-alert type="warning"><?php echo Text::_('COM_USERS_USER_OTEPS_WAIT_DESC'); ?></joomla-alert>
			<?php else : ?>
			<?php foreach ($this->otpConfig->otep as $otep) : ?>
			<span class="col-md-3">
				<?php echo substr($otep, 0, 4); ?>-<?php echo substr($otep, 4, 4); ?>-<?php echo substr($otep, 8, 4); ?>-<?php echo substr($otep, 12, 4); ?>
			</span>
			<?php endforeach; ?>
			<?php endif; ?>
		</fieldset>

		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
	</fieldset>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

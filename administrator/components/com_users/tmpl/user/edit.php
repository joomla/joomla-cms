<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Users\Administrator\Helper\UsersHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('script', 'com_users/admin-users-user.min.js', array('version' => 'auto', 'relative' => true));

$input = Factory::getApplication()->input;

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
$settings  = array();

$this->useCoreUI = true;
?>

<form action="<?php echo Route::_('index.php?option=com_users&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="user-form" enctype="multipart/form-data" class="form-validate">

	<h2><?php echo $this->form->getValue('name'); ?></h2>

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_USERS_USER_ACCOUNT_DETAILS')); ?>
			<div class="card">
				<div class="card-body">
					<div class="row">
						<div class="col-lg-8 col-xl-6">
						<?php echo $this->form->renderFieldset('user_details'); ?>
						</div>
					</div>
				</div>
			</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php if ($this->grouplist) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'groups', Text::_('COM_USERS_ASSIGNED_GROUPS')); ?>
				<fieldset id="fieldset-groups" class="options-form">
					<legend><?php echo Text::_('COM_USERS_ASSIGNED_GROUPS'); ?></legend>
					<div>
					<?php echo $this->loadTemplate('groups'); ?>
					</div>
				</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php
		$this->ignore_fieldsets = array('user_details');
		echo LayoutHelper::render('joomla.edit.params', $this);
		?>

	<?php if (!empty($this->tfaform) && $this->item->id) : ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'twofactorauth', Text::_('COM_USERS_USER_TWO_FACTOR_AUTH')); ?>
	<div class="control-group">
		<div class="control-label">
			<label id="jform_twofactor_method-lbl" for="jform_twofactor_method">
				<?php echo Text::_('COM_USERS_USER_FIELD_TWOFACTOR_LABEL'); ?>
			</label>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('select.genericlist', Usershelper::getTwoFactorMethods(), 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange()', 'class' => 'custom-select'), 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false); ?>
		</div>
	</div>
	<div id="com_users_twofactor_forms_container">
		<?php foreach ($this->tfaform as $form) : ?>
		<div id="com_users_twofactor_<?php echo $form['method'] ?>" class="hidden">
			<?php echo $form['form'] ?>
		</div>
		<?php endforeach; ?>
	</div>

	<fieldset>
		<legend>
			<?php echo Text::_('COM_USERS_USER_OTEPS'); ?>
		</legend>
		<div class="alert alert-info">
			<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
			<?php echo Text::_('COM_USERS_USER_OTEPS_DESC'); ?>
		</div>
		<?php if (empty($this->otpConfig->otep)) : ?>
			<div class="alert alert-warning">
				<span class="fas fa-exclamation-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('WARNING'); ?></span>
				<?php echo Text::_('COM_USERS_USER_OTEPS_WAIT_DESC'); ?>
			</div>
		<?php else : ?>
		<?php foreach ($this->otpConfig->otep as $otep) : ?>
		<span class="col-lg-3">
			<?php echo substr($otep, 0, 4); ?>-<?php echo substr($otep, 4, 4); ?>-<?php echo substr($otep, 8, 4); ?>-<?php echo substr($otep, 12, 4); ?>
		</span>
		<?php endforeach; ?>
		<?php endif; ?>
	</fieldset>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value="">
	<input type="hidden" name="return" value="<?php echo $input->getBase64('return'); ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

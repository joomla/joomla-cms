<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Users\Administrator\Helper\UsersHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->useScript('com_users.two-factor-switcher');

$input = Factory::getApplication()->input;

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
$settings  = array();

$this->useCoreUI = true;
?>
<form action="<?php echo Route::_('index.php?option=com_users&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="user-form" enctype="multipart/form-data" aria-label="<?php echo Text::_('COM_USERS_USER_FORM_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate">

	<h2><?php echo $this->form->getValue('name', null, Text::_('COM_USERS_USER_NEW_USER_TITLE')); ?></h2>

	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_USERS_USER_ACCOUNT_DETAILS')); ?>
			<fieldset class="options-form">
				<legend><?php echo Text::_('COM_USERS_USER_ACCOUNT_DETAILS'); ?></legend>
				<div class="form-grid">
					<?php echo $this->form->renderFieldset('user_details'); ?>
				</div>
			</fieldset>

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
			<fieldset class="options-form">
				<legend><?php echo Text::_('COM_USERS_USER_TWO_FACTOR_AUTH'); ?></legend>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_twofactor_method-lbl" for="jform_twofactor_method">
							<?php echo Text::_('COM_USERS_USER_FIELD_TWOFACTOR_LABEL'); ?>
						</label>
					</div>
					<div class="controls">
						<?php echo HTMLHelper::_('select.genericlist', UsersHelper::getTwoFactorMethods(), 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange();', 'class' => 'form-select'), 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false); ?>
					</div>
				</div>
				<div id="com_users_twofactor_forms_container">
					<?php foreach ($this->tfaform as $form) : ?>
						<?php $class = $form['method'] == $this->otpConfig->method ? '' : ' class="hidden"'; ?>
						<div id="com_users_twofactor_<?php echo $form['method'] ?>"<?php echo $class; ?>>
							<?php echo $form['form'] ?>
						</div>
					<?php endforeach; ?>
				</div>
			</fieldset>
			<hr>

			<h3>
				<?php echo Text::_('COM_USERS_USER_OTEPS'); ?>
			</h3>

			<div class="alert alert-info">
				<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('COM_USERS_USER_OTEPS_DESC'); ?>
			</div>
			<?php if (empty($this->otpConfig->otep)) : ?>
				<div class="alert alert-warning">
					<span class="icon-exclamation-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
					<?php echo Text::_('COM_USERS_USER_OTEPS_WAIT_DESC'); ?>
				</div>
			<?php else : ?>
				<?php foreach ($this->otpConfig->otep as $otep) : ?>
					<?php echo wordwrap($otep, 4, '-', true); ?><br>
				<?php endforeach; ?>
			<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="">
	<input type="hidden" name="return" value="<?php echo $input->getBase64('return'); ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

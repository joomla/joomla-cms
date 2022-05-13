<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Users\Administrator\Model\CaptiveModel;
use Joomla\Component\Users\Administrator\View\Captive\HtmlView;

// phpcs:ignoreFile

/**
 * @var HtmlView     $this  View object
 * @var CaptiveModel $model The model
 */
$model           = $this->getModel();

?>
<div class="users-tfa-captive card card-body">
	<h3 id="users-tfa-title">
		<?php if (!empty($this->renderOptions['help_url'])): ?>
			<span class="pull-right float-end">
		<a href="<?php echo  $this->renderOptions['help_url'] ?>"
				class="btn btn-sm btn-secondary"
				target="_blank"
		>
			<span class="icon icon-question-sign"></span>
		</a>
		</span>
		<?php endif;?>
		<?php if (!empty($this->title)): ?>
			<?php echo  $this->title ?> <small> &ndash;
		<?php endif; ?>
		<?php if (!$this->allowEntryBatching): ?>
			<?php echo  $this->escape($this->record->title) ?>
		<?php else: ?>
			<?php echo  $this->escape($this->getModel()->translateMethodName($this->record->method)) ?>
		<?php endif; ?>
		<?php if (!empty($this->title)): ?>
		</small>
		<?php endif; ?>
	</h3>

	<?php if ($this->renderOptions['pre_message']): ?>
		<div class="users-tfa-captive-pre-message text-muted">
			<?php echo  $this->renderOptions['pre_message'] ?>
		</div>
	<?php endif; ?>

	<form action="<?php echo  Route::_('index.php?option=com_users&task=captive.validate&record_id=' . ((int) $this->record->id)) ?>"
			id="users-tfa-captive-form"
			method="post"
			class="form-horizontal"
	>
		<?php echo  HTMLHelper::_('form.token') ?>

		<div id="users-tfa-captive-form-method-fields">
			<?php if ($this->renderOptions['field_type'] == 'custom'): ?>
				<?php echo  $this->renderOptions['html']; ?>
			<?php else:
				$js = <<< JS
; // Fix broken third party Javascript...
window.addEventListener("DOMContentLoaded", function() {
	document.getElementById('users-tfa-code').focus();
});

JS;
				$this->document->addScriptDeclaration($js);

			?>
				<div class="row mb-3">
					<?php if ($this->renderOptions['label']): ?>
					<label for="users-tfa-code" class="col-sm-3 col-form-label">
						<?php echo  $this->renderOptions['label'] ?>
					</label>
					<?php endif; ?>
					<div class="col-sm-9 <?php echo  $this->renderOptions['label'] ? '' : 'offset-sm-3' ?>">
						<input type="<?php echo  $this->renderOptions['input_type'] ?>"
							   name="code"
							   value=""
							<?php if (!empty($this->renderOptions['placeholder'])): ?>
								placeholder="<?php echo  $this->renderOptions['placeholder'] ?>"
							<?php endif; ?>
							   id="users-tfa-code"
							   class="form-control input-large"
						>
					</div>
				</div>

			<?php endif;?>

		</div>

		<div id="users-tfa-captive-form-standard-buttons" class="row mb-3">
			<div class="col-sm-9 offset-sm-3">
				<button class="btn btn-large btn-lg btn-primary me-3"
						id="users-tfa-captive-button-submit"
						style="<?php echo  $this->renderOptions['hide_submit'] ? 'display: none' : '' ?>"
						type="submit">
					<span class="icon icon-rightarrow icon-arrow-right" aria-hidden="true"></span>
					<?php echo  Text::_('COM_USERS_TFA_VALIDATE'); ?>
				</button>

				<?php if ($this->isAdmin): ?>
					<a href="<?php echo  Route::_('index.php?option=com_login&task=logout&' . Factory::getApplication()->getFormToken() . '=1') ?>"
					   class="btn btn-danger"
					   id="users-tfa-captive-button-logout">
						<span class="icon icon-lock" aria-hidden="true"></span>
						<?php echo  Text::_('COM_USERS_TFA_LOGOUT'); ?>
					</a>
				<?php else: ?>
					<a href="<?php echo  Route::_('index.php?option=com_users&task=user.logout&' . Factory::getApplication()->getFormToken() . '=1') ?>"
					   class="btn btn-danger" id="users-tfa-captive-button-logout">
						<span class="icon icon-lock" aria-hidden="true"></span>
						<?php echo  Text::_('COM_USERS_TFA_LOGOUT'); ?>
					</a>
				<?php endif; ?>
				<?php if (count($this->records) > 1): ?>
					<div id="users-tfa-captive-form-choose-another" class="my-3">
						<a href="<?php echo  Route::_('index.php?option=com_users&view=captive&task=select') ?>">
							<?php echo  Text::_('COM_USERS_TFA_USE_DIFFERENT_METHOD'); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</form>

	<?php if ($this->renderOptions['post_message']): ?>
		<div class="users-tfa-captive-post-message">
			<?php echo  $this->renderOptions['post_message'] ?>
		</div>
	<?php endif; ?>

</div>

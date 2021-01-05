<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.formvalidator');
$showFTP = false;

/** @var \Joomla\CMS\Installation\View\Preinstall\HtmlView $this */
?>
<div id="installer-view" class="container" data-page-name="preinstall">
	<form action="index.php" method="post" id="languageForm" class="lang-select">
		<fieldset class="j-install-step active">
			<legend class="j-install-step-header">
				<span class="icon-language" aria-hidden="true"></span> <?php echo Text::_('INSTL_SELECT_INSTALL_LANG'); ?>
			</legend>
			<div class="j-install-step-form">
				<div class="form-group">
					<?php echo $this->form->getLabel('language'); ?>
					<?php echo $this->form->getInput('language'); ?>
				</div>
				<input type="hidden" name="task" value="language.set">
				<input type="hidden" name="format" value="json">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</fieldset>
	</form>
	<div class="j-install-step active">
		<div class="j-install-step-header">
			<span class="icon-check" aria-hidden="true"></span> <?php echo Text::_('INSTL_PRECHECK_TITLE'); ?>
		</div>
		<div class="j-install-step-form">
			<?php foreach ($this->options as $option) : ?>
				<?php if ($option->state === 'JNO' || $option->state === false) : ?>
					<div class="alert preinstall-alert">
						<div class="alert-icon">
							<span class="alert-icon icon-exclamation-triangle" aria-hidden="true"></span>
						</div>
						<div class="alert-text">
							<strong><?php echo $option->label; ?></strong>
							<div class="form-text text-muted small"><?php echo $option->notice; ?></div>
						</div>
					</div>
					<?php $showFTP = isset($option->showFTP) ? $option->showFTP : $showFTP; ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
	<?php if ($showFTP) : ?>
		<form action="index.php" method="post" id="ftpForm" class="form-validate">
			<fieldset id="preinstall-ftp" class="j-install-step active">
				<legend class="j-install-step-header">
					<span class="icon-cog" aria-hidden="true"></span> <?php echo Text::_('INSTL_FTP'); ?>
				</legend>
				<div class="j-install-step-form">
					<div class="form-group">
						<?php echo $this->form->getLabel('ftp_user'); ?>
						<?php echo $this->form->getInput('ftp_user'); ?>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('ftp_pass'); ?>
						<?php echo $this->form->getInput('ftp_pass'); ?>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('ftp_host'); ?>
						<?php echo $this->form->getInput('ftp_host'); ?>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('ftp_root'); ?>
						<div class=" input-group ">
							<?php echo $this->form->getInput('ftp_root'); ?>
							<div class="input-group-append">
								<button id="findbutton" class="btn btn-secondary"><span class="icon-folder-open"></span> <?php echo Text::_('INSTL_AUTOFIND_FTP_PATH'); ?></button>
							</div>
						</div>
					</div>
					<div class="form-group">
						<?php echo $this->form->getLabel('ftp_port'); ?>
						<?php echo $this->form->getInput('ftp_port'); ?>
					</div>
					<div class="form-group">
						<button id="verifybutton" class="btn btn-success"><span class="icon-check icon-white"></span> <?php echo Text::_('INSTL_VERIFY_FTP_SETTINGS'); ?></button>
						<button id="skipFTPbutton" class="btn btn-secondary float-right"><span class="icon-times icon-white"></span> <?php echo Text::_('INSTL_SKIP_FTP'); ?></button>
					</div>
					<input type="hidden" name="format" value="json">
					<?php echo HTMLHelper::_('form.token'); ?>
				</div>
			</fieldset>
		</form>
	<?php endif; ?>
</div>

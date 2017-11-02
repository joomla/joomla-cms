<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var \Joomla\CMS\Installation\View\Preinstall\HtmlView $this */
?>
<div id="installer-view" class="container" data-page-name="preinstall">
		<div class="row">
			<div class="col-md-12 mb-4">
				<div class="j-install-step active">
					<div class="j-install-step-header">
						<span class="fa fa-check" aria-hidden="true"></span> <?php echo JText::_('INSTL_PRECHECK_TITLE'); ?>
					</div>
					<div class="j-install-step-form">
						<?php foreach ($this->options as $option) : ?>
							<?php if ($option->state === 'JNO' || $option->state === false) : ?>
								<div class="alert preinstall-alert">
									<div class="alert-icon">
										<span class="alert-icon fa fa-exclamation-triangle"></span>
									</div>
									<div class="alert-text">
										<strong><?php echo $option->label; ?></strong>
										<p class="form-text text-muted small"><?php echo $option->notice; ?></p>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
				
						
				<?php if ($option->state === false && preg_match('$configuration.php$', $option->label)) : ?>
					<div id="ftpOptions" class="ftp-options mb-4 hidden">
						<form action="index.php" method="post" id="ftpForm" class="form-validate">
							<!-- 					<h3><?php echo JText::_('INSTL_FTP'); ?></h3>
					<hr> -->
							<div class="form-group row">
								<div class="col-md-8 offset-md-2">
									<?php echo $this->form->getLabel('ftp_user'); ?>
									<?php echo $this->form->getInput('ftp_user'); ?>
									<p class="form-text text-muted small"><?php echo JText::_('INSTL_FTP_USER_DESC'); ?></p>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-8 offset-md-2">
									<?php echo $this->form->getLabel('ftp_pass'); ?>
									<?php echo $this->form->getInput('ftp_pass'); ?>
									<p class="form-text text-muted small"><?php echo JText::_('INSTL_FTP_PASSWORD_DESC'); ?></p>
								</div>
							</div>
							<div class="form-group row mb-4">
								<div class="col-md-8 offset-md-2">
									<?php echo $this->form->getLabel('ftp_host'); ?>
									<div class="input-append d-flex">
										<?php echo $this->form->getInput('ftp_host'); ?><button id="findbutton" class="btn btn-secondary ml-2" onclick="Joomla.installation.detectFtpRoot(this);"><span class="icon-folder-open"></span> <?php echo JText::_('INSTL_AUTOFIND_FTP_PATH'); ?></button>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-8 offset-md-2">
									<?php echo $this->form->getLabel('ftp_port'); ?>
									<?php echo $this->form->getInput('ftp_port'); ?>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-8 offset-md-2 justify-content-end d-flex">
									<button id="verifybutton" class="btn btn-success"><span class="icon-ok icon-white"></span> <?php echo JText::_('INSTL_VERIFY_FTP_SETTINGS'); ?></button>
								</div>
							</div>
							<input type="hidden" name="format" value="json">
							<?php echo JHtml::_('form.token'); ?>
						</form>
					</div>
				<?php endif; ?>
			</div>
		</div>
</div>

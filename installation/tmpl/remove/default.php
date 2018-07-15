<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var \Joomla\CMS\Installation\View\Remove\HtmlView $this */
?>
<div id="installer-view" data-page-name="remove">

	<fieldset id="installCongrat" class="j-install-step active">
		<legend class="j-install-step-header">
			<span class="fa fa-trophy" aria-hidden="true"></span> <?php echo JText::_('INSTL_COMPLETE_CONGRAT'); ?>
		</legend>
		<div class="j-install-step-form">
			<h3><?php echo JText::_('INSTL_COMPLETE_TITLE'); ?></h3>
			<p><?php echo JText::_('INSTL_COMPLETE_DESC'); ?></p>
			<div class="form-group">
				<button class="btn btn-primary btn-block" id="installAddFeatures"><?php echo JText::_('INSTL_COMPLETE_ADD_PRECONFIG'); ?> <span class="fa fa-chevron-right" aria-hidden="true"></span></button>
			</div>
		</div>
	</fieldset>

		<div id="installRecommended" class="j-install-step active">
			<div class="j-install-step-form">
			<?php $displayTable = false; ?>
			<?php foreach ($this->phpsettings as $setting) : ?>
				<?php if ($setting->state !== $setting->recommended) : ?>
					<?php $displayTable = true; ?>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php
			if ($displayTable) : ?>
				<p class="install-text"><?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_DESC'); ?></p>
				<table class="table table-sm">
					<thead>
					<tr>
						<th>
							<?php echo JText::_('INSTL_PRECHECK_DIRECTIVE'); ?>
						</th>
						<th>
							<?php echo JText::_('INSTL_PRECHECK_RECOMMENDED'); ?>
						</th>
						<th>
							<?php echo JText::_('INSTL_PRECHECK_ACTUAL'); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->phpsettings as $setting) : ?>
						<?php if ($setting->state !== $setting->recommended) : ?>
							<tr>
								<td>
									<?php echo $setting->label; ?>
								</td>
								<td>
							<span class="badge badge-success disabled">
								<?php echo JText::_($setting->recommended ? 'JON' : 'JOFF'); ?>
							</span>
								</td>
								<td>
							<span class="badge badge-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'warning'; ?>">
								<?php echo JText::_($setting->state ? 'JON' : 'JOFF'); ?>
							</span>
								</td>
							</tr>
						<?php endif; ?>
					<?php endforeach; ?>
					</tbody>
					<tfoot>
					<tr>
						<td colspan="3"></td>
					</tr>
					</tfoot>
				</table>

				<?php endif; ?>
				<?php	if ($this->development) : ?>
					<div class="alert flex-column">
						<b><?php echo JText::_('INSTL_SITE_DEVMODE_LABEL'); ?></b>
						<div class="form-check">
							<label class="form-check-label">
								<input type="checkbox" class="form-check-input">
								<?php echo JText::_('INSTL_SITE_DEVMODE_DESC'); ?>
							</label>
						</div>
					</div>
					<!-- <input type="button" class="btn btn-warning" name="instDefault" onclick="Install.removeFolder(this);" value="<?php echo JText::_('INSTL_COMPLETE_REMOVE_FOLDER'); ?>"> -->
				<?php endif; ?>
				<?php echo JHtml::_('form.token'); ?>

				<div class="form-group">
					<a class="btn btn-primary btn-block" href="<?php echo JUri::root(); ?>" title="<?php echo JText::_('JSITE'); ?>"><span class="fa fa-eye"></span> <?php echo JText::_('INSTL_COMPLETE_SITE_BTN'); ?></a>
					<a class="btn btn-primary btn-block" href="<?php echo JUri::root(); ?>administrator/" title="<?php echo JText::_('JADMINISTRATOR'); ?>"><span class="fa fa-lock"></span> <?php echo JText::_('INSTL_COMPLETE_ADMIN_BTN'); ?></a>
				</div>
			</div>
		</div>

		<fieldset id="installLanguages" class="j-install-step">
			<legend class="j-install-step-header">
				<span class="fa fa-commenting-o" aria-hidden="true"></span> <?php echo JText::_('INSTL_LANGUAGES'); ?>
			</legend>
			<div class="j-install-step-form">
				<?php if (!$this->items) : ?>
				<p><?php echo JText::_('INSTL_LANGUAGES_WARNING_NO_INTERNET') ?></p>
				<p>
					<a href="#"
							class="btn btn-primary"
							onclick="return Install.goToPage('remove');">
						<span class="fa fa-arrow-left icon-white"></span>
						<?php echo JText::_('INSTL_LANGUAGES_WARNING_BACK_BUTTON'); ?>
					</a>
				</p>
				<p><?php echo JText::_('INSTL_LANGUAGES_WARNING_NO_INTERNET2') ?></p>
			<?php else : ?>
			<form action="index.php" method="post" id="languagesForm" class="form-validate">
				<p id="install_languages_desc"><?php echo JText::_('INSTL_LANGUAGES_DESC'); ?></p>
				<p id="wait_installing" style="display: none;">
					<?php echo JText::_('INSTL_LANGUAGES_MESSAGE_PLEASE_WAIT') ?><br>
				<div id="wait_installing_spinner" class="spinner spinner-img" style="display: none;"></div>
				</p>
				<table class="table table-sm">
					<thead>
					<tr>
						<th width="1%" class="text-center">
							&nbsp;
						</th>
						<th>
							<?php echo JText::_('INSTL_LANGUAGES_COLUMN_HEADER_LANGUAGE'); ?>
						</th>
						<th width="15%">
							<?php echo JText::_('INSTL_LANGUAGES_COLUMN_HEADER_LANGUAGE_TAG'); ?>
						</th>
						<th width="5%" class="text-center">
							<?php echo JText::_('INSTL_LANGUAGES_COLUMN_HEADER_VERSION'); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php $version = new \Joomla\CMS\Version; ?>
					<?php $currentShortVersion = preg_replace('#^([0-9\.]+)(|.*)$#', '$1', $version->getShortVersion()); ?>
					<?php foreach ($this->items as $i => $language) : ?>
						<?php // Get language code and language image. ?>
						<?php preg_match('#^pkg_([a-z]{2,3}-[A-Z]{2})$#', $language->element, $element); ?>
						<?php $language->code = $element[1]; ?>
						<tr>
							<td>
								<input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $language->update_id; ?>">
							</td>
							<td>
								<label for="cb<?php echo $i; ?>"><?php echo $language->name; ?></label>
							</td>
							<td>
								<?php echo $language->code; ?>
							</td>
							<td class="text-center">
								<?php // Display a Note if language pack version is not equal to Joomla version ?>
								<?php if (substr($language->version, 0, 3) != $version::MAJOR_VERSION . '.' . $version::MINOR_VERSION || substr($language->version, 0, 5) != $currentShortVersion) : ?>
									<span class="badge badge-warning hasTooltip" title="<?php echo JText::_('JGLOBAL_LANGUAGE_VERSION_NOT_PLATFORM'); ?>"><?php echo $language->version; ?></span>
								<?php else : ?>
									<span class="badge badge-success"><?php echo $language->version; ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<div class="form-group">
					<?php echo JHtml::_('form.token'); ?>
					<?php endif; ?>
					<button id="installLanguagesButton" class="btn btn-block btn-primary">
						<?php echo JText::_('JNEXT'); ?>
					</button>
					<button id="skipLanguages" class="btn btn-block btn-secondary">
					<?php echo JText::_('JSKIP'); ?>
					</button>
				</div>
			</form>
			</div>
		</fieldset>

		<fieldset id="installSampleData" class="j-install-step">
			<legend class="j-install-step-header">
				<span class="fa fa-cog" aria-hidden="true"></span> <?php echo JText::_('INSTL_SITE_INSTALL_SAMPLE'); ?>
			</legend>
			<div class="j-install-step-form">
				<h3><?php echo JText::_('INSTL_SITE_INSTALL_SAMPLE_LABEL'); ?></h3>
				<p><?php echo JText::_('INSTL_SITE_INSTALL_SAMPLE_DESC'); ?></p>


				<form action="index.php" method="post" id="sampleDataForm" class="form-validate">
					<div class="form-group">
						<input type="hidden" name="sample_file" value="sample_testing.sql">
						<?php echo JHtml::_('form.token'); ?>
						<button id="installSampleDataButton" class="btn btn-primary btn-block"><?php echo JText::_('INSTL_SITE_INSTALL_SAMPLE'); ?> <span class="fa fa-chevron-right" aria-hidden="true"></span></button>
						<button id="skipSampleData" class="btn btn-block btn-secondary">
							<?php echo JText::_('JSKIP'); ?>
						</button>
					</div>
				</form>
			</div>
		</fieldset>

		<fieldset id="installFinal" class="j-install-step">
			<legend class="j-install-step-header">
				<span class="fa fa-joomla" aria-hidden="true"></span> <?php echo JText::_('INSTL_COMPLETE_FINAL'); ?>
			</legend>
			<div class="j-install-step-form">
				<p><?php echo JText::_('INSTL_COMPLETE_FINAL_DESC'); ?></p>
				<div class="form-group">
					<a class="btn btn-primary btn-block" href="<?php echo JUri::root(); ?>" title="<?php echo JText::_('JSITE'); ?>" role="button"><span class="fa fa-eye"></span> <?php echo JText::_('INSTL_COMPLETE_SITE_BTN'); ?></a>
					<a class="btn btn-primary btn-block" href="<?php echo JUri::root(); ?>administrator/" title="<?php echo JText::_('JADMINISTRATOR'); ?>" role="button"><span class="fa fa-lock"></span> <?php echo JText::_('INSTL_COMPLETE_ADMIN_BTN'); ?></a>
				</div>
			</div>
		</fieldset>


</div>

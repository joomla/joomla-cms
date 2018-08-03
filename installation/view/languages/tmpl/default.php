<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewLanguagesHtml $this */

// Get version of Joomla! to compare it with the version of the language package
$version = new JVersion;
?>
<script type="text/javascript">
	function installLanguages() {
		var $ = jQuery.noConflict();
		$('#install_languages_desc').hide();
		$('#wait_installing').show();
		$('#wait_installing_spinner').show();
		Install.submitform();
	}
</script>

<?php echo JHtml::_('InstallationHtml.helper.stepbarlanguages'); ?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<div class="btn-toolbar">
		<div class="btn-group pull-right">
			<a
				class="btn"
				href="#"
				onclick="return Install.goToPage('remove');"
				rel="prev"
				title="<?php echo JText::_('JPREVIOUS'); ?>">
				<span class="icon-arrow-left"></span>
				<?php echo JText::_('JPREVIOUS'); ?>
			</a>
			<a
				class="btn btn-primary"
				href="#"
				onclick="installLanguages()"
				rel="next"
				title="<?php echo JText::_('JNEXT'); ?>">
				<span class="icon-arrow-right icon-white"></span>
				<?php echo JText::_('JNEXT'); ?>
			</a>
		</div>
	</div>
	<h3><?php echo JText::_('INSTL_LANGUAGES'); ?></h3>
	<hr class="hr-condensed" />
	<?php if (!$this->items) : ?>
		<p><?php echo JText::_('INSTL_LANGUAGES_WARNING_NO_INTERNET') ?></p>
		<p>
			<a href="#"
			class="btn btn-primary"
			onclick="return Install.goToPage('remove');">
			<span class="icon-arrow-left icon-white"></span>
			<?php echo JText::_('INSTL_LANGUAGES_WARNING_BACK_BUTTON'); ?>
			</a>
		</p>
		<p><?php echo JText::_('INSTL_LANGUAGES_WARNING_NO_INTERNET2') ?></p>
	<?php else : ?>
		<p id="install_languages_desc"><?php echo JText::_('INSTL_LANGUAGES_DESC'); ?></p>
		<p id="wait_installing" style="display: none;">
			<?php echo JText::_('INSTL_LANGUAGES_MESSAGE_PLEASE_WAIT') ?><br />
			<div id="wait_installing_spinner" class="spinner spinner-img" style="display: none;"></div>
		</p>
		<table class="table table-striped table-condensed">
			<thead>
					<tr>
						<th width="1%" class="center">
							&nbsp;
						</th>
						<th>
							<?php echo JText::_('INSTL_LANGUAGES_COLUMN_HEADER_LANGUAGE'); ?>
						</th>
						<th width="15%">
							<?php echo JText::_('INSTL_LANGUAGES_COLUMN_HEADER_LANGUAGE_TAG'); ?>
						</th>
						<th width="5%" class="center">
							<?php echo JText::_('INSTL_LANGUAGES_COLUMN_HEADER_VERSION'); ?>
						</th>
					</tr>
			</thead>
			<tbody>
				<?php $currentShortVersion = preg_replace('#^([0-9\.]+)(|.*)$#', '$1', $version->getShortVersion()); ?>
				<?php foreach ($this->items as $i => $language) : ?>
					<?php // Get language code and language image. ?>
					<?php preg_match('#^pkg_([a-z]{2,3}-[A-Z]{2})$#', $language->element, $element); ?>
					<?php $language->code = $element[1]; ?>
					<tr>
						<td>
							<input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $language->update_id; ?>" />
						</td>
						<td>
							<label for="cb<?php echo $i; ?>"><?php echo $language->name; ?></label>
						</td>
						<td>
							<?php echo $language->code; ?>
						</td>
						<td class="center">
							<?php $minorVersion = $version::MAJOR_VERSION . '.' . $version::MINOR_VERSION; ?>
							<?php // Display a Note if language pack version is not equal to Joomla version ?>
							<?php if (strpos($language->version, $minorVersion) !== 0 || strpos($language->version, $currentShortVersion) !== 0) : ?>
								<span class="label label-warning hasTooltip" title="<?php echo JText::_('JGLOBAL_LANGUAGE_VERSION_NOT_PLATFORM'); ?>"><?php echo $language->version; ?></span>
							<?php else : ?>
								<span class="label label-success"><?php echo $language->version; ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<input type="hidden" name="task" value="InstallLanguages" />
		<?php echo JHtml::_('form.token'); ?>
	<?php endif; ?>
	<div class="row-fluid">
		<div class="btn-toolbar">
			<div class="btn-group pull-right">
				<a
					class="btn"
					href="#"
					onclick="return Install.goToPage('remove');"
					rel="prev"
					title="<?php echo JText::_('JPREVIOUS'); ?>">
					<span class="icon-arrow-left"></span>
					<?php echo JText::_('JPREVIOUS'); ?>
				</a>
				<a
					class="btn btn-primary"
					href="#"
					onclick="installLanguages()"
					rel="next"
					title="<?php echo JText::_('JNEXT'); ?>">
					<span class="icon-arrow-right icon-white"></span>
					<?php echo JText::_('JNEXT'); ?>
				</a>
			</div>
		</div>
	</div>
</form>

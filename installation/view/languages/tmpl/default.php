<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
						<th>
							<?php echo JText::_('INSTL_LANGUAGES_COLUMN_HEADER_LANGUAGE'); ?>
						</th>
						<th>
							<?php echo JText::_('INSTL_LANGUAGES_COLUMN_HEADER_VERSION'); ?>
						</th>
					</tr>
			</thead>
			<tbody>
				<?php foreach ($this->items as $i => $language) : ?>
					<tr>
						<td>
							<label class="checkbox">
								<input
									type="checkbox"
									id="cb<?php echo $i; ?>"
									name="cid[]"
									value="<?php echo $language->update_id; ?>"
									/> <?php echo $language->name; ?>

									<?php // Display a Note if language pack version is not equal to Joomla version ?>
									<?php if (substr($language->version, 0, 3) != $version::RELEASE
											|| substr($language->version, 0, 5) != $version->getShortVersion()) : ?>
										<div class="small"><?php echo JText::_('JGLOBAL_LANGUAGE_VERSION_NOT_PLATFORM'); ?></div>
									<?php endif; ?>
							</label>
						</td>
						<td>
							<span class="badge"><?php echo $language->version; ?></span>
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

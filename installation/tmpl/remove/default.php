<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\CMS\Installation\View\Remove\HtmlView $this */
?>
<div id="installer-view" data-page-name="remove">

	<fieldset id="installCongrat" class="j-install-step active">
		<legend class="j-install-step-header">
			<span class="fas fa-trophy" aria-hidden="true"></span> <?php echo Text::_('INSTL_COMPLETE_CONGRAT'); ?>
		</legend>
		<div class="j-install-step-form" id="customInstallation">
			<h2><?php echo Text::_('INSTL_COMPLETE_TITLE'); ?></h2>
			<p><?php echo Text::_('INSTL_COMPLETE_DESC'); ?></p>
			<div class="form-group">
				<button class="btn btn-primary btn-block" id="installAddFeatures"><?php echo Text::_('INSTL_COMPLETE_ADD_PRECONFIG'); ?> <span class="fas fa-chevron-right" aria-hidden="true"></span></button>
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

			<?php if ($displayTable) : ?>
				<p class="install-text"><?php echo Text::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_DESC'); ?></p>
				<table class="table table-sm">
					<thead>
					<tr>
						<th>
							<?php echo Text::_('INSTL_PRECHECK_DIRECTIVE'); ?>
						</th>
						<th>
							<?php echo Text::_('INSTL_PRECHECK_RECOMMENDED'); ?>
						</th>
						<th>
							<?php echo Text::_('INSTL_PRECHECK_ACTUAL'); ?>
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
								<?php echo Text::_($setting->recommended ? 'JON' : 'JOFF'); ?>
							</span>
								</td>
								<td>
							<span class="badge badge-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'warning'; ?>">
								<?php echo Text::_($setting->state ? 'JON' : 'JOFF'); ?>
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

				<?php if ($this->development) : ?>
					<div class="alert flex-column mb-1" id="removeInstallationTab">
						<span class="mb-1 font-weight-bold"><?php echo Text::_('INSTL_SITE_DEVMODE_LABEL'); ?></span>
						<button class="btn btn-danger mb-1" id="removeInstallationFolder"><?php echo Text::_('INSTL_COMPLETE_REMOVE_FOLDER'); ?></button>
					</div>
				<?php endif; ?>
				<?php echo HTMLHelper::_('form.token'); ?>


		
				<div id="defaultLanguage" class="j-install-step-form flex-column mt-5 border">
					<p><?php echo Text::_('INSTL_DEFAULTLANGUAGE_DESC'); ?></p>
					<table class="table table-sm">
						<thead>
						<tr>
							<th>
								<?php echo Text::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_SELECT'); ?>
							</th>
							<th>
								<?php echo Text::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_LANGUAGE'); ?>
							</th>
							<th>
								<?php echo Text::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_TAG'); ?>
							</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($this->installed_languages->administrator as $i => $lang) : ?>
							<tr>
								<td>
									<input
										id="admin-language-cb<?php echo $i; ?>"
										type="radio"
										name="administratorlang"
										value="<?php echo $lang->language; ?>"
										<?php if ($lang->published) echo 'checked="checked"'; ?>
									/>
								</td>
								<td>
									<label for="admin-language-cb<?php echo $i; ?>">
										<?php echo $lang->name; ?>
									</label>
								</td>
								<td>
									<?php echo $lang->language; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<p><?php echo Text::_('INSTL_DEFAULTLANGUAGE_DESC_FRONTEND'); ?></p>
					<table class="table table-sm">
						<thead>
						<tr>
							<th>
								<?php echo Text::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_SELECT'); ?>
							</th>
							<th>
								<?php echo Text::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_LANGUAGE'); ?>
							</th>
							<th>
								<?php echo Text::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_TAG'); ?>
							</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($this->installed_languages->frontend as $i => $lang) : ?>
							<tr>
								<td>
									<input
										id="site-language-cb<?php echo $i; ?>"
										type="radio"
										name="frontendlang"
										value="<?php echo $lang->language; ?>"
										<?php if ($lang->published) echo 'checked="checked"'; ?>
									/>
								</td>
								<td>
									<label for="site-language-cb<?php echo $i; ?>">
										<?php echo $lang->name; ?>
									</label>
								</td>
								<td>
									<?php echo $lang->language; ?>
								</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				</div>
				
				<div class="form-group j-install-last-step">
					<a class="btn btn-primary btn-block" href="<?php echo Uri::root(); ?>" title="<?php echo Text::_('JSITE'); ?>"><span class="fas fa-eye" aria-hidden="true"></span> <?php echo Text::_('INSTL_COMPLETE_SITE_BTN'); ?></a>
					<a class="btn btn-primary btn-block" href="<?php echo Uri::root(); ?>administrator/" title="<?php echo Text::_('JADMINISTRATOR'); ?>"><span class="fas fa-lock" aria-hidden="true"></span> <?php echo Text::_('INSTL_COMPLETE_ADMIN_BTN'); ?></a>
				</div>
			</div>
		</div>

	
	
		<fieldset id="installFinal" class="j-install-step">
			<legend class="j-install-step-header">
				<span class="fab fa-joomla" aria-hidden="true"></span> <?php echo Text::_('INSTL_COMPLETE_FINAL'); ?>
			</legend>
			<div class="j-install-step-form">
				<p><?php echo Text::_('INSTL_COMPLETE_FINAL_DESC'); ?></p>
			</div>
		</fieldset>


</div>

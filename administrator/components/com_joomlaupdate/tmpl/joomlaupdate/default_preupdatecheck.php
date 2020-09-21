<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\Html $this */
?>

<h2 class="mt-3 mb-3">
	<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_PREUPDATE_CHECK', '&#x200E;' . $this->updateInfo['latest']); ?>
</h2>

<div class="row">
	<div class="col-md-6">
		<fieldset class="options-form">
			<legend>
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_REQUIRED_SETTINGS'); ?>
			</legend>
				<table class="table" id="preupdatecheck">
					<caption class="sr-only">
						<?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_CHECK_CAPTION'); ?>
					</caption>
					<thead>
						<tr>
							<th scope="col">
								<?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_HEADING_REQUIREMENT'); ?>
							</th>
							<th scope="col">
								<?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_HEADING_CHECKED'); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->phpOptions as $option) : ?>
						<tr>
							<th scope="row">
								<?php echo $option->label; ?>
							</th>
							<td>
								<span class="badge badge-<?php echo $option->state ? 'success' : 'danger'; ?>">
									<?php echo Text::_($option->state ? 'JYES' : 'JNO'); ?>
									<?php if ($option->notice) : ?>
										<span class="fas fa-info icon-white" title="<?php echo $option->notice; ?>"></span>
									<?php endif; ?>
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
		</fieldset>
	</div>

	<div class="col-md-6">
		<fieldset class="options-form">
			<legend>
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_RECOMMENDED_SETTINGS'); ?>
			</legend>
			<table class="table" id="preupdatecheckphp">
				<caption>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_RECOMMENDED_SETTINGS_DESC'); ?>
				</caption>
				<thead>
					<tr>
						<th scope="col">
							<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DIRECTIVE'); ?>
						</th>
						<th scope="col">
							<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_RECOMMENDED'); ?>
						</th>
						<th scope="col">
							<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_ACTUAL'); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->phpSettings as $setting) : ?>
						<tr>
							<th scope="row">
								<?php echo $setting->label; ?>
							</th>
							<td>
								<?php echo Text::_($setting->recommended ? 'JON' : 'JOFF'); ?>
							</td>
							<td>
								<span class="badge badge-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'warning'; ?>">
									<?php echo Text::_($setting->state ? 'JON' : 'JOFF'); ?>
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
			</table>
		</fieldset>
	</div>
</div>
<div class="row">
	<?php if (!empty($this->nonCoreExtensions)) : ?>
	<div class="col-md-6">
		<fieldset class="options-form">
			<legend>
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS'); ?>
			</legend>
			<table class="table" id="preupdatecheckextensions">
				<caption class="sr-only">
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_DESC'); ?>
				</caption>
				<thead>
					<tr>
						<th scope="col">
							<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NAME'); ?>
						</th>
						<th scope="col">
							<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_COMPATIBLE'); ?>
						</th>
						<th scope="col">
							<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_INSTALLED_VERSION'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->nonCoreExtensions as $extension) : ?>
					<tr>
						<th scope="row">
							<?php echo Text::_($extension->name); ?>
						</th>
						<td class="extension-check"
							data-extension-id="<?php echo $extension->extension_id; ?>"
							data-extension-current-version="<?php echo $extension->version; ?>">
							<span class="fas fa-spinner fa-spin" aria-hidden="true"></span>
						</td>
						<td>
							<?php echo $extension->version; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</fieldset>
	</div>
	<div class="col-md-6">
		<fieldset class="options-form">
			<legend>
				<?php echo Text::_('NOTICE'); ?>
			</legend>
			<ul>
				<li><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DESCRIPTION_BREAK'); ?></li>
				<li><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DESCRIPTION_MISSING_TAG'); ?></li>
				<li><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DESCRIPTION_UPDATE_REQUIRED'); ?></li>
			</ul>
		</fieldset>
	</div>
    <?php else: ?>
    <div class="col-md-6">
        <h3>
            <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS'); ?>
        </h3>
        <div class="alert alert-no-items">
            <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_NONE'); ?>
        </div>
    </div>
	<?php endif; ?>
</div>

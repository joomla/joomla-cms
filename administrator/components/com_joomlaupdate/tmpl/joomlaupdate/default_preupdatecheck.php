<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView;

/** @var HtmlView $this */

// JText::script doesn't have a sprintf equivalent so work around this
Factory::getDocument()->addScriptOptions('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION', Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION', '<span class="icon-chevron-right"></span>', true))
	->addScriptOptions('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_LESS_COMPATIBILITY_INFORMATION', Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_LESS_COMPATIBILITY_INFORMATION', '<span class="icon-chevron-up"></span>', true))
	->addScriptOptions('nonCoreCriticalPlugins', $this->nonCoreCriticalPlugins);

$compatibilityTypes = array(
	'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_RUNNING_PRE_UPDATE_CHECKS' => array(
		'class' => 'alert-secondary',
		'notes' => 'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_RUNNING_PRE_UPDATE_CHECKS_NOTES',
		'group' => 0,
	),
	'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_PRE_UPDATE_CHECKS_FAILED' => array(
		'class' => 'alert-danger',
		'notes' => 'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_PRE_UPDATE_CHECKS_FAILED_NOTES',
		'group' => 4,
	),
	'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_UPDATE_SERVER_OFFERS_NO_COMPATIBLE_VERSION' => array(
		'class' => 'alert-danger',
		'notes' => 'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_UPDATE_SERVER_OFFERS_NO_COMPATIBLE_VERSION_NOTES',
		'group' => 1,
	),
	'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_REQUIRING_UPDATES_TO_BE_COMPATIBLE' => array(
		'class' => 'alert-warning',
		'notes' => 'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_REQUIRING_UPDATES_TO_BE_COMPATIBLE_NOTES',
		'group' => 2,
	),
	'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_PROBABLY_COMPATIBLE' => array(
		'class' => 'alert-success',
		'notes' => 'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_PROBABLY_COMPATIBLE_NOTES',
		'group' => 3,
	),
);
?>

<h2 class="mt-3 mb-3">
	<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_PREUPDATE_CHECK', '&#x200E;' . $this->updateInfo['latest']); ?>
</h2>
<p>
	<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXPLANATION_AND_LINK_TO_DOCS'); ?>
</p>

<div class="row">
	<div class="col-md-6">
		<fieldset class="options-form">
			<?php
			$labelClass = 'success';
			foreach ($this->phpOptions as $option) :
				if (!$option->state)
				{
					$labelClass = 'danger';
					break;
				}
			endforeach;
			?>
			<legend>
				<h3 class="alert alert-<?php echo $labelClass; ?>">
					<?php
					echo $labelClass === 'danger' ? Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_REQUIRED_SETTINGS_WARNING') : Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_REQUIRED_SETTINGS_PASSED');
					?>
					<div class="settingstoggle ms-1" data-state="closed"><?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION', '<span class="icon-chevron-right"></span>'); ?></div>
				</h3>
			</legend>
			<div class="settingsInfo hidden">
				<table class="table" id="preupdatecheck">
					<caption class="visually-hidden">
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
								<?php if ($option->notice) : ?>
								<div class="small">
									<?php echo $option->notice; ?>
								</div>
								<?php endif; ?>
							</th>
							<td>
								<span class="badge bg-<?php echo $option->state ? 'success' : 'danger'; ?>">
									<?php echo Text::_($option->state ? 'JYES' : 'JNO'); ?>
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</fieldset>
	</div>

	<div class="col-md-6">
		<fieldset class="options-form">
			<?php
			$labelClass = 'success';
			foreach ($this->phpSettings as $setting) :
				if ($setting->state !== $setting->recommended)
				{
					$labelClass = 'warning';
					break;
				}
			endforeach;
			?>

			<legend>
				<h3 class="alert alert-<?php echo $labelClass; ?>">
					<?php
					echo $labelClass === 'warning' ? Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_RECOMMENDED_SETTINGS_WARNING') : Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_RECOMMENDED_SETTINGS_PASSED');
					?>
					<div class="settingstoggle ms-1" data-state="closed"><?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION', '<span class="icon-chevron-right"></span>'); ?></div>
				</h3>
			</legend>
			<div class="settingsInfo hidden">
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
								<span class="badge bg-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'warning'; ?>">
									<?php echo Text::_($setting->state ? 'JON' : 'JOFF'); ?>
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			</div>
		</fieldset>
	</div>
</div>
<div class="row">
	<?php if (!empty($this->nonCoreExtensions)) : ?>
		<div>
			<h3>
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS'); ?>
			</h3>
			<?php foreach ($compatibilityTypes as $compatibilityType => $compatibilityData) : ?>
				<?php $compatibilityDisplayClass = $compatibilityData['class']; ?>
				<?php $compatibilityDisplayNotes = $compatibilityData['notes']; ?>
				<?php $compatibilityTypeGroup    = $compatibilityData['group']; ?>
				<fieldset id="compatibilitytype<?php echo $compatibilityTypeGroup;?>" class="col-md-12 compatibilitytypes">
					<legend>
						<h3 class="alert <?php echo $compatibilityDisplayClass; ?>">
							<?php if ($compatibilityType !== "COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_RUNNING_PRE_UPDATE_CHECKS") : ?>
								<div class="compatibilitytoggle" data-state="closed"><?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION', '<span class="icon-chevron-right"></span>'); ?></div>
							<?php endif; ?>
							<?php echo Text::_($compatibilityType); ?>
						</h3>
					</legend>
					<div class="compatibilityNotes">
						<?php echo Text::_($compatibilityDisplayNotes); ?>
					</div>
					<table class="table">
						<thead class="row-fluid">
							<tr>
								<th class="exname col-md-8">
									<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NAME'); ?>
								</th>
								<th class="extype col-md-4">
									<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_TYPE'); ?>
								</th>
								<th class="instver hidden">
									<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_INSTALLED_VERSION'); ?>
								</th>
								<th class="upcomp hidden">
									<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_COMPATIBLE_WITH_JOOMLA_VERSION', isset($this->updateInfo['current']) ? $this->updateInfo['current'] : JVERSION); ?>
								</th>
								<th class="currcomp hidden">
									<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_COMPATIBLE_WITH_JOOMLA_VERSION', $this->updateInfo['latest']); ?>
								</th>
							</tr>
						</thead>
						<tbody class="row-fluid">
						<?php // Only include this row once since the javascript moves the results into the right place ?>
						<?php if ($compatibilityType == "COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_RUNNING_PRE_UPDATE_CHECKS") : ?>
							<?php foreach ($this->nonCoreExtensions as $extension) : ?>
								<tr>
									<td class="exname col-md-8">
										<?php echo Text::_($extension->name); ?>
									</td>
									<td class="extype col-md-4">
										<?php echo Text::_('COM_INSTALLER_TYPE_' . strtoupper($extension->type)); ?>
									</td>
									<td class="instver hidden">
										<?php echo $extension->version; ?>
									</td>
									<td id="available-version-<?php echo $extension->extension_id; ?>" class="currcomp hidden" />
									<td
										class="extension-check upcomp hidden"
										data-extension-id="<?php echo $extension->extension_id; ?>"
										data-extension-current-version="<?php echo $extension->version; ?>"
									>
										<img src="<?php echo Uri::root(true); ?>/media/system/images/ajax-loader.gif">
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
						</tbody>
					</table>
				</fieldset>
			<?php endforeach; ?>
		</div>
	<?php else: ?>
	<div class="col-md-6">
		<h3>
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS'); ?>
		</h3>
		<div class="alert alert-info">
			<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_NONE'); ?>
		</div>
	</div>
	<?php endif; ?>
</div>

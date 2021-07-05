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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView;

/** @var HtmlView $this */

/** @var WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
	->useScript('com_joomlaupdate.default')
	->useScript('bootstrap.popover')
	->useScript('bootstrap.tab');

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

$latestJoomlaVersion = $this->updateInfo['latest'];
$currentJoomlaVersion = isset($this->updateInfo['current']) ? $this->updateInfo['current'] : JVERSION;

$updatePossible = true;

?>

<div id="joomlaupdate-wrapper" class="main-card p-3 mt-3" data-joomla-target-version="<?php echo $latestJoomlaVersion; ?>" data-joomla-current-version="<?php echo $currentJoomlaVersion; ?>">

	<h2 class="mt-3 mb-3">
		<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_PREUPDATE_CHECK', '&#x200E;' . $this->updateInfo['latest']); ?>
	</h2>
	<p>
		<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXPLANATION_AND_LINK_TO_DOCS'); ?>
	</p>

	<div class="d-flex flex-wrap flex-md-nowrap align-items-start my-4" id="preupdatecheck">
		<div class="nav flex-column text-nowrap nav-pills me-3 text-left" role="tablist" aria-orientation="vertical">
			<button class="nav-link d-flex justify-content-between align-items-center active" id="joomlaupdate-precheck-required-tab" data-bs-toggle="pill" data-bs-target="#joomlaupdate-precheck-required-content" type="button" role="tab" aria-controls="joomlaupdate-precheck-required-content" aria-selected="true">
				<?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_REQUIRED_SETTINGS'); ?>
				<?php $labelClass = 'success'; ?>
				<?php foreach ($this->phpOptions as $option) : ?>
					<?php if (!$option->state) : ?>
						<?php $labelClass = 'danger'; ?>
						<?php $updatePossible = false; ?>
						<?php break; ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<span class="fa fa-<?php echo $labelClass == 'danger' ? 'times' : 'check'; ?> p-1 bg-white ms-2 text-<?php echo $labelClass; ?>"></span>
			</button>
			<button class="nav-link d-flex justify-content-between align-items-center" id="joomlaupdate-precheck-recommended-tab" data-bs-toggle="pill" data-bs-target="#joomlaupdate-precheck-recommended-content" type="button" role="tab" aria-controls="joomlaupdate-precheck-recommended-content" aria-selected="false">
				<?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_RECOMMENDED_SETTINGS'); ?>
				<?php $labelClass = 'success'; ?>
				<?php foreach ($this->phpSettings as $setting) : ?>
					<?php if ($setting->state !== $setting->recommended) : ?>
						<?php $labelClass = 'warning'; ?>
						<?php break; ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<span class="fa fa-<?php echo $labelClass == 'warning' ? 'exclamation-triangle' : 'check'; ?> p-1 bg-white ms-2 text-<?php echo $labelClass; ?>"></span>
			</button>
			<button class="nav-link d-flex justify-content-between align-items-center" id="joomlaupdate-precheck-extensions-tab" data-bs-toggle="pill" data-bs-target="#joomlaupdate-precheck-extensions-content" type="button" role="tab" aria-controls="joomlaupdate-precheck-extensions-content" aria-selected="false">
				<?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_EXTENSIONS'); ?>
				<?php $labelClass = 'success'; ?>
				<span class="fa fa-clock p-1 bg-white ms-2 text-info"></span>
			</button>
		</div>

		<div class="tab-content w-100">
			<div class="tab-pane fade show active table-responsive" id="joomlaupdate-precheck-required-content" role="tabpanel" aria-labelledby="joomlaupdate-precheck-required-tab">
				<h3>
					<?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_REQUIRED_SETTINGS'); ?>
				</h3>
				<table class="table table-striped" id="preupdatecheck">
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
			<div class="tab-pane fade show table-responsive" id="joomlaupdate-precheck-recommended-content" role="tabpanel" aria-labelledby="joomlaupdate-precheck-recommended-tab">
				<h3>
					<?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_RECOMMENDED_SETTINGS'); ?>
				</h3>
				<table class="table table-striped" id="preupdatecheckphp">
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
			<div class="tab-pane fade show" id="joomlaupdate-precheck-extensions-content" role="tabpanel" aria-labelledby="joomlaupdate-precheck-extensions-tab">
			<?php if (!empty($this->nonCoreExtensions)) : ?>
				<div class="w-100 table-responsive">
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
										<div class="compatibilitytoggle" data-state="closed">
											<?php echo Text::sprintf(
												'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION',
												'<span class="icon-chevron-right"></span>'
											); ?>
										</div>
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
												<?php echo $extension->name; ?>
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
				<div class="">
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
		</div>
	</div>

	<?php if ($updatePossible) : ?>

	<form action="<?php echo Route::_('index.php?option=com_joomlaupdate'); ?>" method="post" class="d-flex flex-sm-column mb-5">

		<div class="form-check d-flex justify-content-center mb-3" id="preupdatecheckbox">
			<input type="checkbox" class="me-3" id="noncoreplugins" name="noncoreplugins" value="1" required aria-required="true" />
			<label class="form-check-label" for="joomlaupdate-confirm-backup">
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NON_CORE_PLUGIN_CONFIRMATION'); ?>
			</label>
		</div>

		<button class="btn btn-lg btn-warning disabled submitupdate mx-auto" type="submit" disabled>
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLUPDATE'); ?>
		</button>
	</form>
	<?php endif; ?>

	<?php if (Factory::getUser()->authorise('core.admin')) : ?>
		<div class="text-center">
		<?php echo HTMLHelper::_('link', Route::_('index.php?option=com_joomlaupdate&layout=upload'), Text::_('COM_JOOMLAUPDATE_EMPTYSTATE_APPEND')); ?>
		</div>
	<?php endif; ?>
</div>

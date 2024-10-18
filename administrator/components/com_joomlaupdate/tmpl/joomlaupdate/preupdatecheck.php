<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView;

/** @var HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('core')
    ->useScript('com_joomlaupdate.default')
    ->useScript('bootstrap.popover')
    ->useScript('bootstrap.tab');

// Text::script doesn't have a sprintf equivalent so work around this
$this->getDocument()->addScriptOptions('nonCoreCriticalPlugins', $this->nonCoreCriticalPlugins);

// Push Joomla! Update client-side error messages
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_CONFIRM_MESSAGE');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_LESS_COMPATIBILITY_INFORMATION');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_DESC');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_LIST');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_CONFIRM_MESSAGE');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_HELP');

// Push Joomla! core Joomla.Request error messages
Text::script('JLIB_JS_AJAX_ERROR_CONNECTION_ABORT');
Text::script('JLIB_JS_AJAX_ERROR_NO_CONTENT');
Text::script('JLIB_JS_AJAX_ERROR_OTHER');
Text::script('JLIB_JS_AJAX_ERROR_PARSE');
Text::script('JLIB_JS_AJAX_ERROR_TIMEOUT');

$compatibilityTypes = [
    'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_RUNNING_PRE_UPDATE_CHECKS' => [
        'class' => 'info',
        'icon'  => 'hourglass fa-spin',
        'notes' => 'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_RUNNING_PRE_UPDATE_CHECKS_NOTES',
        'group' => 0,
    ],
    'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_REQUIRING_UPDATES_TO_BE_COMPATIBLE' => [
        'class' => 'danger',
        'icon'  => 'times',
        'notes' => 'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_REQUIRING_UPDATES_TO_BE_COMPATIBLE_NOTES',
        'group' => 2,
    ],
    'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_PRE_UPDATE_CHECKS_FAILED' => [
        'class' => 'warning',
        'icon'  => 'exclamation-triangle',
        'notes' => 'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_PRE_UPDATE_CHECKS_FAILED_NOTES',
        'group' => 4,
    ],
    'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_UPDATE_SERVER_OFFERS_NO_COMPATIBLE_VERSION' => [
        'class' => 'warning',
        'icon'  => 'exclamation-triangle',
        'notes' => 'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_UPDATE_SERVER_OFFERS_NO_COMPATIBLE_VERSION_NOTES',
        'group' => 1,
    ],
    'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_PROBABLY_COMPATIBLE' => [
        'class' => 'success',
        'icon'  => 'check',
        'notes' => 'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_PROBABLY_COMPATIBLE_NOTES',
        'group' => 3,
    ],
];

$latestJoomlaVersion = $this->updateInfo['latest'];
$currentJoomlaVersion = $this->updateInfo['installed'] ?? JVERSION;

$updatePossible = true;

if (version_compare($this->updateInfo['latest'], Version::MAJOR_VERSION + 1, '>=') && $this->isDefaultBackendTemplate === false) {
    Factory::getApplication()->enqueueMessage(
        Text::sprintf(
            'COM_JOOMLAUPDATE_VIEW_DEFAULT_NON_CORE_BACKEND_TEMPLATE_USED_NOTICE',
            ucfirst($this->defaultBackendTemplate)
        ),
        'info'
    );
}
?>

<div id="joomlaupdate-wrapper" class="main-card p-3 mt-3" data-joomla-target-version="<?php echo $latestJoomlaVersion; ?>" data-joomla-current-version="<?php echo $currentJoomlaVersion; ?>">

    <h2 class="my-3">
        <?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_PREUPDATE_CHECK', '&#x200E;' . $this->updateInfo['latest']); ?>
    </h2>
    <p>
        <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXPLANATION_AND_LINK_TO_DOCS'); ?>
    </p>

    <div class="d-flex flex-wrap flex-lg-nowrap align-items-start my-4" id="preupdatecheck">
        <div class="nav flex-column text-nowrap nav-pills me-3 mb-4" role="tablist" aria-orientation="vertical">
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
                <span class="fa fa-<?php echo $labelClass === 'danger' ? 'times' : 'check'; ?> fa-fw py-1 bg-white text-<?php echo $labelClass; ?>" aria-hidden="true"></span>
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
                <span class="fa fa-<?php echo $labelClass === 'warning' ? 'exclamation-triangle' : 'check'; ?> fa-fw py-1 bg-white text-<?php echo $labelClass; ?>" aria-hidden="true"></span>
            </button>
            <button class="nav-link d-flex justify-content-between align-items-center" id="joomlaupdate-precheck-extensions-tab" data-bs-toggle="pill" data-bs-target="#joomlaupdate-precheck-extensions-content" type="button" role="tab" aria-controls="joomlaupdate-precheck-extensions-content" aria-selected="false">
                <?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_EXTENSIONS'); ?>
                <?php $labelClass = 'success'; ?>
                <span class="fa fa-spinner fa-spin fa-fw py-1" aria-hidden="true"></span>
            </button>
        </div>

        <div class="tab-content w-100">
            <div class="tab-pane fade show active" id="joomlaupdate-precheck-required-content" role="tabpanel" aria-labelledby="joomlaupdate-precheck-required-tab">
                <h3>
                    <?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_REQUIRED_SETTINGS'); ?>
                </h3>
                <div class="table-responsive">
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
            </div>
            <div class="tab-pane fade show" id="joomlaupdate-precheck-recommended-content" role="tabpanel" aria-labelledby="joomlaupdate-precheck-recommended-tab">
                <h3>
                    <?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_RECOMMENDED_SETTINGS'); ?>
                </h3>
                <div class="table-responsive">
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
            </div>
            <div class="tab-pane fade show" id="joomlaupdate-precheck-extensions-content" role="tabpanel" aria-labelledby="joomlaupdate-precheck-extensions-tab">
                <h3>
                    <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS'); ?>
                </h3>
                <div id="preupdateCheckWarning">
                    <div class="alert alert-warning">
                        <h4 class="alert-heading">
                            <?php echo Text::_('WARNING'); ?>
                        </h4>
                        <div class="alert-message">
                            <div class="preupdateCheckIncomplete">
                                <?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_CHECK_NOT_COMPLETE'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="preupdateCheckCompleteProblems" class="hidden">
                    <div class="alert alert-warning">
                        <h4 class="alert-heading">
                            <?php echo Text::_('WARNING'); ?>
                        </h4>
                        <div class="alert-message">
                            <div class="preupdateCheckComplete">
                                <?php echo Text::_('COM_JOOMLAUPDATE_PREUPDATE_CHECK_COMPLETED_YOU_HAVE_DANGEROUS_PLUGINS'); ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php if (!empty($this->nonCoreExtensions)) : ?>
                <div class="w-100">
                    <?php foreach ($compatibilityTypes as $compatibilityType => $data) : ?>
                    <div class="<?php echo $data['group'] > 0 ? 'hidden' : ''; ?> compatibilityTable" id="compatibilityTable<?php echo (int) $data['group']; ?>">
                        <h4 class="text-<?php echo $data['class']; ?> align-items-center">
                            <span class="fa fa-<?php echo $data['icon']; ?> me-2"></span>
                            <?php echo Text::_($compatibilityType); ?>
                            <?php if ($data['group'] > 0) : ?>
                                <button type="button" class="btn btn-primary btn-sm ms-3 compatibilitytoggle" data-state="closed">
                                    <?php echo Text::_(
                                        'COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION'
                                    ); ?>
                                </button>
                            <?php endif; ?>
                        </h4>

                        <div class="table-responsive mb-5">
                            <table class="table table-striped">
                                <caption>
                                <?php echo Text::_($data['notes']); ?>
                                </caption>
                                <thead class="row-fluid">
                                    <tr>
                                        <th class="exname" scope="col">
                                            <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NAME'); ?>
                                        </th>
                                        <th class="extype" scope="col">
                                            <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_TYPE'); ?>
                                        </th>
                                        <th class="instver hidden" scope="col">
                                            <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_INSTALLED_VERSION'); ?>
                                        </th>
                                        <th class="currcomp hidden" scope="col">
                                            <?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_COMPATIBLE_WITH_JOOMLA_VERSION', isset($this->updateInfo['installed']) ? $this->escape($this->updateInfo['installed']) : JVERSION); ?>
                                        </th>
                                        <th class="upcomp hidden" scope="col">
                                            <?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_COMPATIBLE_WITH_JOOMLA_VERSION', $this->escape($this->updateInfo['latest'])); ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="row-fluid">
                                <?php // Only include this row once since the javascript moves the results into the right place ?>
                                <?php if ($data['group'] == 0) : ?>
                                    <?php foreach ($this->nonCoreExtensions as $extension) : ?>
                                        <tr>
                                            <th class="exname" scope="row">
                                                <?php echo $extension->name; ?>
                                            </th>
                                            <td class="extype">
                                                <?php echo Text::_('COM_INSTALLER_TYPE_' . strtoupper($extension->type)); ?>
                                            </td>
                                            <td class="instver hidden">
                                                <?php echo $extension->version; ?>
                                            </td>
                                            <td id="available-version-<?php echo $extension->extension_id; ?>" class="currcomp hidden"></td>
                                            <td id="preUpdateCheck_<?php echo $extension->extension_id; ?>"
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
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="alert alert-info">
                    <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                    <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_NONE'); ?>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($updatePossible) : ?>
    <form action="<?php echo Route::_('index.php?option=com_joomlaupdate&layout=update'); ?>" method="post" class="d-flex flex-column mb-5">

        <?php if (!$this->noVersionCheck) : ?>
        <div id="preupdatecheckbox">
            <div class="form-check d-flex justify-content-center mb-3">
                <input type="checkbox" class="form-check-input me-3" id="noncoreplugins" name="noncoreplugins" value="1" required />
                <label class="form-check-label" for="noncoreplugins">
                    <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NON_CORE_PLUGIN_CONFIRMATION'); ?>
                </label>
            </div>
        </div>
        <?php endif; ?>

        <button class="btn btn-lg btn-warning <?php echo $this->noVersionCheck ? '' : 'disabled' ?> submitupdate mx-auto"
                type="submit" <?php echo $this->noVersionCheck ? '' : 'disabled' ?>>
            <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLUPDATE'); ?>
        </button>
    </form>
    <?php endif; ?>

    <form action="<?php echo Route::_('index.php?option=com_joomlaupdate&layout=update'); ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="task" value="">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

    <?php if ($this->getCurrentUser()->authorise('core.admin')) : ?>
        <div class="text-center">
            <a href="<?php echo Route::_('index.php?option=com_joomlaupdate&view=upload'); ?>" class="btn btn-sm btn-outline-secondary">
                <?php echo Text::_('COM_JOOMLAUPDATE_EMPTYSTATE_APPEND'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

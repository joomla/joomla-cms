<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Updater\Update;
use Joomla\CMS\Utility\Utility;
use Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView;

/** @var HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
    ->useScript('com_joomlaupdate.default')
    ->useScript('bootstrap.popover');

Text::script('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true);
Text::script('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG', true);
Text::script('JGLOBAL_SELECTED_UPLOAD_FILE_SIZE', true);

$latestJoomlaVersion = $this->updateInfo['latest'];
$currentJoomlaVersion = isset($this->updateInfo['installed']) ? $this->updateInfo['installed'] : JVERSION;
?>

<div id="joomlaupdate-wrapper" class="main-card mt-3 p-3" data-joomla-target-version="<?php echo $latestJoomlaVersion; ?>" data-joomla-current-version="<?php echo $currentJoomlaVersion; ?>">
<div class="alert alert-info">
    <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
    <?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPLOAD_INTRO', 'https://downloads.joomla.org/latest'); ?>
    <?php if (is_object($this->updateInfo['object']) && ($this->updateInfo['object'] instanceof Update)) : ?>
        <br><br>
        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
        <?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_PACKAGE_INFO', $this->updateInfo['object']->downloadurl->_data); ?>
    <?php endif; ?>
</div>

<?php if (count($this->warnings)) : ?>
    <h3><?php echo Text::_('COM_INSTALLER_SUBMENU_WARNINGS'); ?></h3>
    <?php foreach ($this->warnings as $warning) : ?>
        <div class="alert alert-warning">
            <h4 class="alert-heading">
                <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                <?php echo $warning['message']; ?>
            </h4>
            <p class="mb-0"><?php echo $warning['description']; ?></p>
        </div>
    <?php endforeach; ?>
    <div class="alert alert-info">
        <h4 class="alert-heading">
            <span class="icon-info-circle" aria-hidden="true"></span>
            <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
            <?php echo Text::_('COM_INSTALLER_MSG_WARNINGFURTHERINFO'); ?>
        </h4>
        <p class="mb-0"><?php echo Text::_('COM_INSTALLER_MSG_WARNINGFURTHERINFODESC'); ?></p>
    </div>
<?php endif; ?>

<form enctype="multipart/form-data" action="index.php" method="post" id="uploadForm">

    <div class="mb-3">
        <label for="install_package" class="form-label">
            <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPLOAD_PACKAGE_FILE'); ?>
        </label>

        <input class="form-control" type="file" id="install_package" name="install_package" accept=".zip,application/zip">
        <?php $maxSizeBytes = Utility::getMaxUploadSize(); ?>
        <?php $maxSize = HTMLHelper::_('number.bytes', $maxSizeBytes); ?>
        <input id="max_upload_size" name="max_upload_size" type="hidden" value="<?php echo $maxSizeBytes; ?>"/>
        <div class="form-text"><?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', '&#x200E;' . $maxSize); ?></div>
        <div class="form-text hidden" id="file_size"><?php echo Text::sprintf('JGLOBAL_SELECTED_UPLOAD_FILE_SIZE', '&#x200E;' . ''); ?></div>
        <div class="alert alert-warning hidden" id="max_upload_size_warn">
            <?php echo Text::_('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG'); ?>
        </div>
    </div>

    <div class="form-check mb-3 <?php echo $this->noBackupCheck ? 'd-none' : '' ?>">
        <input class="form-check-input me-2 <?php echo $this->noBackupCheck ? 'd-none' : '' ?>"
               type="checkbox" disabled value="" id="joomlaupdate-confirm-backup"
                <?php echo $this->noBackupCheck ? 'checked' : '' ?>>
        <label class="form-check-label" for="joomlaupdate-confirm-backup">
            <?php echo Text::_('COM_JOOMLAUPDATE_UPDATE_CONFIRM_BACKUP'); ?>
        </label>
    </div>

    <button id="uploadButton" class="btn btn-primary" disabled type="button"><?php echo Text::_('COM_INSTALLER_UPLOAD_AND_INSTALL'); ?></button>

    <input type="hidden" name="task" value="update.upload">
    <input type="hidden" name="option" value="com_joomlaupdate">
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
</div>

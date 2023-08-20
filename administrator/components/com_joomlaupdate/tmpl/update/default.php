<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
    ->useScript('com_joomlaupdate.admin-update')
    ->useScript('bootstrap.modal');

Text::script('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_FORBIDDEN');
Text::script('COM_JOOMLAUPDATE_ERRORMODAL_BODY_FORBIDDEN');
Text::script('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_SERVERERROR');
Text::script('COM_JOOMLAUPDATE_ERRORMODAL_BODY_SERVERERROR');
Text::script('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_GENERIC');
Text::script('COM_JOOMLAUPDATE_ERRORMODAL_BODY_INVALIDLOGIN');
Text::script('COM_JOOMLAUPDATE_UPDATING_FAIL');
Text::script('COM_JOOMLAUPDATE_UPDATING_COMPLETE');
Text::script('JLIB_SIZE_BYTES');
Text::script('JLIB_SIZE_KB');
Text::script('JLIB_SIZE_MB');
Text::script('JLIB_SIZE_GB');
Text::script('JLIB_SIZE_TB');
Text::script('JLIB_SIZE_PB');
Text::script('JLIB_SIZE_EB');
Text::script('JLIB_SIZE_ZB');
Text::script('JLIB_SIZE_YB');

$password = Factory::getApplication()->getUserState('com_joomlaupdate.password', null);
$filesize = Factory::getApplication()->getUserState('com_joomlaupdate.filesize', null);
$ajaxUrl = Uri::base() . 'components/com_joomlaupdate/extract.php';
$returnUrl = 'index.php?option=com_joomlaupdate&task=update.finalise&' . Factory::getSession()->getFormToken() . '=1';

$this->document->addScriptOptions(
    'joomlaupdate',
    [
        'password' => $password,
        'totalsize' => $filesize,
        'ajax_url' => $ajaxUrl,
        'return_url' => $returnUrl,
    ]
);

$helpUrl = 'https://docs.joomla.org/Special:MyLanguage/J4.x:Joomla_Update_Problems';
?>

<div class="px-4 py-5 my-5 text-center" id="joomlaupdate-progress">
    <span class="fa-8x mb-4 icon-loop joomlaupdate" aria-hidden="true"></span>
    <h1 class="display-5 fw-bold"><?php echo Text::_('COM_JOOMLAUPDATE_UPDATING_HEAD') ?></h1>
    <div class="col-lg-6 mx-auto">
        <p class="lead mb-4" id="update-title">
            <?php echo Text::_('COM_JOOMLAUPDATE_UPDATING_INPROGRESS'); ?>
        </p>
        <div id="progress" class="progress my-3">
            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        <div id="update-progress" class="container text-muted my-3">
            <div class="row">
                <div class="col">
                    <span class="fa fa-file-archive" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_BYTESREAD'); ?></span>
                    <span id="extbytesin"></span>
                </div>
                <div class="col">
                    <span class="fa fa-hdd" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_BYTESEXTRACTED'); ?></span>
                    <span id="extbytesout"></span>
                </div>
                <div class="col">
                    <span class="fa fa-copy" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_FILESEXTRACTED'); ?></span>
                    <span id="extfiles"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="px-4 d-none" id="joomlaupdate-error">
    <div class="card border-danger">
        <h1 class="card-header bg-danger text-white" id="errorDialogLabel"></h1>
        <div class="card-body">
            <div id="errorDialogMessage"></div>
        </div>
        <div class="card-footer">
            <div class="d-flex flex-row flex-wrap gap-2 align-items-center">
                <div>
                    <a href="<?php echo $helpUrl ?>"
                       target="_blank"
                       class="btn btn-info">
                        <span class="fa fa-info-circle" aria-hidden="true"></span>
                        <?php echo Text::_('COM_JOOMLAUPDATE_ERRORMODAL_BTN_HELP') ?>
                    </a>
                </div>
                <div>
                    <button type="button" id="joomlaupdate-resume"
                            class="btn btn-primary">
                        <span class="fa fa-play" aria-hidden="true"></span>
                        <?php echo Text::_('COM_JOOMLAUPDATE_ERRORSTATE_BTN_RETRY') ?>
                    </button>
                </div>
                <div>
                    <button type="button" id="joomlaupdate-restart"
                            class="btn btn-warning">
                        <span class="fa fa-redo" aria-hidden="true"></span>
                        <?php echo Text::_('COM_JOOMLAUPDATE_ERRORSTATE_BTN_RESTART') ?>
                    </button>
                </div>
                <div class="flex-grow-1"></div>
                <div>
                    <a href="<?php echo Route::_('index.php?option=com_joomlaupdate') ?>"
                       class="btn btn-danger btn-sm ms-3">
                        <?php echo Text::_('JCANCEL') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

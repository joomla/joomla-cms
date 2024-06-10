<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

// Load the scripts
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
    ->useScript('com_joomlaupdate.download')
    ->useScript('bootstrap.modal');

$token = Factory::getApplication()->getSession()->getFormToken();
$this->document->addScriptOptions(
    'com_joomlaupdate',
    [
        'ajaxUrl' => Uri::base() . 'index.php?option=com_joomlaupdate&task=update.stepdownload&' . $token . '=1',
        'returnUrl' => Uri::base() . 'index.php?option=com_joomlaupdate&task=update.install&' . $token . '=1',
        'minTime' => ComponentHelper::getParams('com_joomlaupdate')
                ->get('min_chunk_wait', 2) * 1000,
    ]
);

?>

<div class="px-4 py-5 my-5 text-center" id="download-progress" >
    <div id="dlprogress" class="container">
        <span class="fa-8x mb-4 icon-download joomladownload" aria-hidden="true"></span>
        <h1 class="display-5 fw-bold"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DOWNLOAD_INPROGRESS_HEAD') ?></h1>
        <div class="col-lg-6 mx-auto">
            <p class="lead mb-4" id="update-title">
                <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DOWNLOAD_INPROGRESS'); ?>
            </p>
            <div id="progress" class="progress progress-striped progress-bar-animated row">
                <div class="progress-bar" id="progress-bar" role="progressbar"
                     aria-label="<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_PERCENT') ?>"
                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="my-2 mx-0 border-bottom border-secondary extprogrow row">
                <div class="extlabel text-bold col-3">
                    <span class="fa fa-percentage" aria-hidden="true"></span>
                    <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_PERCENT'); ?>
                </div>
                <div class="extvalue col-9" id="dlpercent"></div>
            </div>
            <div class="my-2 mx-0 border-bottom border-secondary extprogrow row">
                <div class="extlabel text-bold col-3">
                    <span class="fa fa-cloud-download-alt" aria-hidden="true"></span>
                    <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DOWNLOAD_BYTESDL'); ?>
                </div>
                <div class="extvalue col-9" id="dlbytesin"></div>
            </div>
            <div class="my-2 mx-0 border-bottom border-secondary extprogrow row">
                <div class="extlabel text-bold col-3">
                    <span class="fa fa-file" aria-hidden="true"></span>
                    <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DOWNLOAD_BYTESTOTAL'); ?>
                </div>
                <div class="extvalue col-9" id="dlbytestotal"></div>
            </div>
        </div>
    </div>
</div>

<div id="download-error" class="alert alert-danger">
    <h3 class="alert-heading">
        <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DOWNLOAD_ERROR') ?>
    </h3>
    <p id="dlerror"></p>
    <hr>
    <div>
        <button type="button"
                id="dlrestart"
                class="btn btn-primary">
            <span class="fa fa-redo-alt" aria-hidden="true"></span>
            <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DOWNLOAD_RESTART') ?>
        </button>
        &nbsp;
        <button type="button"
                id="dlcancel"
                class="btn btn-danger">
            <span class="fa fa-times-circle" aria-hidden="true"></span>
            <?php echo Text::_('JCANCEL') ?>
        </button>
    </div>
</div>

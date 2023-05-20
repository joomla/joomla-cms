<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to $displayData file
// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects
/** @var $displayData array */
$headerlabeloptions = ['hiddenLabel' => true];
$fieldhiddenoptions = ['hidden' => true];
?>
<div class="span10 form-horizontal">
    <div class="row vel-header-row">
        <div class="col-md-12 vel-header">
            <h1>Published Title
                <button type="button" class="" id="buildTitle">
                    <span class="icon-wand"></span>
                </button>
            </h1>
            <?php echo $displayData->renderField('title', null, null, $headerlabeloptions); ?>
        </div>
    </div>
    <div class="row vel-header-row">
        <div class="col-md-3 vel-header">
            <h1>Extension Name</h1>

            <?php echo $displayData->renderField('vulnerable_item_name', null, null, $headerlabeloptions); ?>
        </div>
        <div class="col-md-3  vel-header">
            <h1>Version</h1>
            <?php echo $displayData->renderField('vulnerable_item_version', null, null, $headerlabeloptions); ?>
        </div>
        <div class="col-md-3  vel-header">
            <h1>Status</h1>
            <?php echo $displayData->renderField('status', null, null, $headerlabeloptions); ?>

        </div>
        <div class="col-md-3  vel-header">
            <h1>Risk</h1>
            <?php echo $displayData->renderField('risk_level', null, null, $headerlabeloptions); ?>

        </div>
    </div>
    <div class="row vel-header-row">
        <div class="col-md-3 vel-header">
            <h1>First Version containing vulnerability</h1>

            <?php echo $displayData->renderField('start_version', null, null, $headerlabeloptions); ?>
        </div>
        <div class="col-md-3  vel-header">
            <h1>Latest Version containing vulnerability</h1>
            <?php echo $displayData->renderField('vulnerable_version', null, null, $headerlabeloptions); ?>
        </div>
        <div class="col-md-3  vel-header">
            <h1>Patched Version</h1>
            <?php echo $displayData->renderField('patch_version', null, null, $headerlabeloptions); ?>

        </div>
        <div class="col-md-3  vel-header">
            <h1>Exploit Type</h1>
            <?php echo $displayData->renderField('exploit_type', null, null, $headerlabeloptions); ?>
            <?php echo $displayData->renderField('exploit_other_description', null, null, $headerlabeloptions); ?>
        </div>
    </div>



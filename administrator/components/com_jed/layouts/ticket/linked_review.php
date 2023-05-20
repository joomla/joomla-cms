<?php

/**
 * @package           JED
 *
 * @subpackage        Tickets
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license           GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to file
use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects
/* @var $displayData array */
$headerlabeloptions = ['hiddenLabel' => true, 'readonly' => true];
$fieldhiddenoptions = ['hidden' => true];
$linked_form        = $displayData["linked_form"];
//echo "<pre>";print_r($displayData);echo "</pre>";exit();
$linked_extension = $displayData["extension"];
$linked_data      = $displayData["linked_data"];

JedHelper::lockFormFields($linked_form, ['published']);

?>
<div class="span10 form-horizontal">

    <div class="col-md-2 ticket-header">
        <h1>Status - <?php echo $linked_form->renderField('published', null, null, $headerlabeloptions); ?>
            &nbsp;&nbsp;<button id="btn_save_published" type="button" class="">
                <span class="icon-save"></span>
            </button>
        </h1>
        <p id="jform_review_status_updated" style="display:none">Status Updated</p>

    </div>
    <div class="row ticket-header-row">
        <div class="col-md-3 ticket-header">

            <h1>Extension - <strong><?php echo $linked_extension->title ?></strong></h1>

        </div>
        <div class="col-md-3  ticket-header">

            <h1>Version - <?php echo $linked_data[0]->version; ?></h1>

        </div>
        <div class="col-md-3  ticket-header">

            <h1>Type - <?php echo $linked_data[0]->supply_type; ?></h1>

        </div>
        <div class="col-md-3  ticket-header">

            <h1>Reviewer - <?php echo $linked_data[0]->review_creator; ?></h1>

        </div>

    </div>
    <P>&nbsp;</P>
    <div class="row ticket-header-row">
        <div class="col-md-6   ticket-header">
            <?php echo $linked_form->renderField('title', null, null); ?>
        </div>
        <div class="col-md-6   ticket-header">

            <?php echo $linked_form->renderField('alias', null, null); ?>
        </div>

    </div>
    <div class="row ticket-header-row">
        <div class="col-md-12   ticket-header">
            <?php echo $linked_form->renderField('body', null, null); ?>
        </div>
        <div class="col-md-12   ticket-header">
            <?php echo $linked_form->renderField('used_for', null, null); ?>
        </div>
    </div>
    <div class="row ticket-header-row">
        <div class="col-md-2   ticket-header">
            <h1><?php echo Text::_('COM_JED_REVIEWS_FIELD_FUNCTIONALITY_LABEL') . ' - ' . $linked_data[0]->functionality; ?></h1>
        </div>
        <div class="col-md-10   ticket-header">
            <?php echo $linked_form->renderField('functionality_comment', null, null, $headerlabeloptions); ?>
        </div>
    </div>
    <div class="row ticket-header-row">
        <div class="col-md-2   ticket-header">
            <h1><?php echo Text::_('COM_JED_REVIEWS_FIELD_EASE_OF_USE_LABEL') . ' - ' . $linked_data[0]->ease_of_use; ?></h1>
        </div>
        <div class="col-md-10   ticket-header">
            <?php echo $linked_form->renderField('ease_of_use_comment', null, null, $headerlabeloptions); ?>
        </div>
    </div>
    <div class="row ticket-header-row">
        <div class="col-md-2   ticket-header">
            <h1><?php echo Text::_('COM_JED_REVIEWS_FIELD_SUPPORT_LABEL') . ' - ' . $linked_data[0]->support; ?></h1>
        </div>
        <div class="col-md-10   ticket-header">
            <?php echo $linked_form->renderField('support_comment', null, null, $headerlabeloptions); ?>
        </div>
    </div>
    <div class="row ticket-header-row">
        <div class="col-md-2   ticket-header">
            <h1><?php echo Text::_('COM_JED_REVIEWS_FIELD_DOCUMENTATION_LABEL') . ' - ' . $linked_data[0]->documentation; ?></h1>
        </div>
        <div class="col-md-10   ticket-header">
            <?php echo $linked_form->renderField('documentation_comment', null, null, $headerlabeloptions); ?>
        </div>
    </div>
    <div class="row ticket-header-row">
        <div class="col-md-2   ticket-header">
            <h1><?php echo Text::_('COM_JED_REVIEWS_FIELD_VALUE_FOR_MONEY_LABEL') . ' - ' . $linked_data[0]->value_for_money; ?></h1>
        </div>
        <div class="col-md-10   ticket-header">
            <?php echo $linked_form->renderField('value_for_money_comment', null, null, $headerlabeloptions); ?>
        </div>
    </div>
    <div class="row ticket-header-row">
        <div class="col-md-2   ticket-header">
            <h1><?php echo Text::_('COM_JED_REVIEWS_FIELD_OVERALL_SCORE_LABEL') . ' - ' . $linked_data[0]->overall_score; ?></h1>
        </div>
        <div class="col-md-10   ticket-header">
            <h1>Created on - <?php echo $linked_data[0]->created_on; ?>&nbsp;&nbsp;IP Address
                - <?php echo $linked_data[0]->ip_address; ?></h1>
        </div>
    </div>


</div>


<?php


$fieldsets['scores']['title']       = Text::_('COM_JED_REVIEW_SCORES_TITLE');
$fieldsets['scores']['description'] = Text::_('COM_JED_REVIEW_SCORES_DESCR');
$fieldsets['scores']['fields']      = [

    'functionality',
    'ease_of_use',
    'support',
    'documentation',
    'value_for_money',
    'overall_score'];


$fieldsets['comments']['title']       = Text::_('COM_JED_REVIEW_COMMENTS_TITLE');
$fieldsets['comments']['description'] = Text::_('COM_JED_REVIEW_COMMENTS_DESCR');
$fieldsets['comments']['fields']      = ['functionality_comment',
    'ease_of_use_comment',
    'support_comment',
    'documentation_comment',
    'value_for_money_comment'];
$fieldsets['comments']['hidden']      = [];

$fieldsets['hidden']['title']       = '';
$fieldsets['hidden']['description'] = '';
$fieldsets['hidden']['fields']      = ['flagged',
    'ip_address',
    'published',
    'created_on'];
$fieldsets['hidden']['hidden']      = $fieldsets['hidden']['fields']

?>

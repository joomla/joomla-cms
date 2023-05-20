<?php

/**
 * @package       JED
 *
 * @subpackage    Tickets
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to file
// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

/* @var $displayData array */
$headerlabeloptions = ['hiddenLabel' => true];
$fieldhiddenoptions = ['hidden' => true];
//var_dump($displayData);exit();
$rawData = $displayData->getData();

/* Set up Data fieldsets */

$fieldsets['aboutyou']['title']  = Text::_('COM_JED_VEL_GENERAL_FIELD_ABOUT_YOU_LABEL');
$fieldsets['aboutyou']['fields'] = [
    'contact_fullname',
    'contact_organisation',
    'contact_email'];

$fieldsets['vulnerabilitydetails']['title']  = Text::_('COM_JED_VEL_DEVELOPERUPDATES_FORM_VULNERABILITY_DETAILS_TITLE');
$fieldsets['vulnerabilitydetails']['fields'] = [
    'vulnerable_item_name',
    'vulnerable_item_version',
    'extension_update',
    'new_version_number',
    'update_notice_url',
    'changelog_url',
    'download_url',
    'consent_to_process',
    'update_date_submitted'];


$fieldsets['final']['title']       = "VEL Details";
$fieldsets['final']['description'] = "";

$fieldsets['final']['fields'] = [
    'vel_item_id'];
$fscount                      = 0;

?>
<div class="row">
    <div class="col">
        <div class="widget">
            <h1><?php echo $fieldsets['vulnerabilitydetails']['title']; ?></h1>
            <div class="container">
                <div class="row">
                    <?php foreach ($fieldsets['vulnerabilitydetails']['fields'] as $field) {
                        $displayData->setFieldAttribute($field, 'readonly', 'true');
                        echo $displayData->renderField($field, null, null, ['class' => 'control-wrapper-' . $field]);
                    } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="widget">
            <h1><?php echo $fieldsets['aboutyou']['title']; ?></h1>
            <div class="container">
                <div class="row">
                    <?php foreach ($fieldsets['aboutyou']['fields'] as $field) {
                        $displayData->setFieldAttribute($field, 'readonly', 'true');
                        echo $displayData->renderField($field, null, null, ['class' => 'control-wrapper-' . $field]);
                    } ?>
                    <input type="hidden" id="veldeveloperupdate_id" name="jform[veldeveloperupdate_id]"
                           value="<?php echo $rawData->get('id'); ?>">
                </div>
            </div>
        </div>

        <div class="widget">
            <h1><?php echo $fieldsets['final']['title']; ?></h1>
            <div class="container">
                <div class="row">
                    <?php foreach ($fieldsets['final']['fields'] as $field) {
                        //$displayData->setFieldAttribute($field, 'readonly', 'true');
                        echo $displayData->renderField($field, null, null, ['class' => 'control-wrapper-' . $field]);
                    } ?>
                    <div id="veldeveloperbutton"></div>
                </div>
            </div>
        </div>

        <div class="widget">
            <h1>Actions</h1>
            <div class="container">
                <div class="row">
                    <?php
                    if ($rawData->get('vel_item_id') > 0) {
                        echo Text::_('COM_JED_GENERAL_LABEL_NO_ACTIONS');
                    } else {
                        ?>
                        <button type="button" class="btn btn-primary"
                                onclick="Joomla.submitbutton('jedticket.assignDeveloperUpdatetoVEL')">
                            <?php echo Text::_('COM_JED_VEL_DEVELOPERUPDATES_BUTTON_ASSIGN_TO_VEL'); ?>
                        </button>
                        <?php
                    } ?>
                </div>
            </div>
        </div>


    </div>
</div>


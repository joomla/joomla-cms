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
// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects

/** @var $displayData array */

use Joomla\CMS\Language\Text;

$headerlabeloptions = ['hiddenLabel' => true];
$fieldhiddenoptions = ['hidden' => true];
$rawData            = $displayData->getData();

/* Set up Data fieldsets */

$fieldsets['aboutyou']['title'] = Text::_('COM_JED_VEL_GENERAL_FIELD_ABOUT_YOU_LABEL');

$fieldsets['aboutyou']['fields'] = [
    'reporter_fullname',
    'reporter_email',
    'reporter_organisation'];

$fieldsets['extensiondetails']['title']  = Text::_('COM_JED_VEL_ABANDONEDREPORT_EXTENSION_TITLE');
$fieldsets['extensiondetails']['fields'] = [
    'extension_name',
    'developer_name',
    'extension_version',
    'extension_url',
    'abandoned_reason',
    'consent_to_process'];

?>
<div class="row">
    <div class="col">
        <div class="widget">
            <h1><?php echo $fieldsets['extensiondetails']['title']; ?></h1>
            <div class="container">
                <div class="row">
                    <?php foreach ($fieldsets['extensiondetails']['fields'] as $field) {
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
                                onclick="Joomla.submitbutton('jedticket.copyAbandonedReporttoVEL')">
                            <?php echo Text::_('COM_JED_VEL_GENERAL_BUTTON_CREATE_ABANDONED_VEL'); ?>
                        </button>
                        <?php
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>

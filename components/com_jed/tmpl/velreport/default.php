<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

if (!is_null($this->item)) {
    ?>

    <div class="item_fields">

        <table class="table">


            <tr>
                <th><?php echo Text::_('JGLOBAL_FIELD_ID_LABEL'); ?></th>
                <td><?php echo $this->item->id; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_GENERAL_FIELD_CONTACT_FULLNAME_LABEL'); ?></th>
                <td><?php echo $this->item->reporter_fullname; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_GENERAL_FIELD_CONTACT_EMAIL_LABEL'); ?></th>
                <td><?php echo $this->item->reporter_email; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_GENERAL_FIELD_REPORTER_ORGANISATION_LABEL'); ?></th>
                <td><?php echo $this->item->reporter_organisation; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_PASS_DETAILS_OK_LABEL'); ?></th>
                <td><?php echo $this->item->pass_details_ok; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_VULNERABILITY_TYPE_LABEL'); ?></th>
                <td><?php echo $this->item->vulnerability_type; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_REPORTS_FIELD_VULNERABLE_ITEM_NAME_LABEL'); ?></th>
                <td><?php echo $this->item->vulnerable_item_name; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_VULNERABLE_ITEM_VERSION_LABEL'); ?></th>
                <td><?php echo $this->item->vulnerable_item_version; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_EXPLOIT_TYPE_LABEL'); ?></th>
                <td><?php echo $this->item->exploit_type; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_EXPLOIT_OTHER_DESCRIPTION_LABEL'); ?></th>
                <td><?php echo nl2br($this->item->exploit_other_description); ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_VULNERABILITY_DESCRIPTION_LABEL'); ?></th>
                <td><?php echo nl2br($this->item->vulnerability_description); ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_VULNERABILITY_HOW_FOUND_LABEL'); ?></th>
                <td><?php echo nl2br($this->item->vulnerability_how_found); ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_VULNERABILITY_ACTIVELY_EXPLOITED_LABEL'); ?></th>
                <td><?php echo $this->item->vulnerability_actively_exploited; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_VULNERABILITY_PUBLICLY_AVAILABLE_LABEL'); ?></th>
                <td><?php echo $this->item->vulnerability_publicly_available; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_REPORTS_FIELD_VULNERABILITY_PUBLICLY_URL_LABEL'); ?></th>
                <td><?php echo $this->item->vulnerability_publicly_url; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_VULNERABILITY_SPECIFIC_IMPACT_LABEL'); ?></th>
                <td><?php echo nl2br($this->item->vulnerability_specific_impact); ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_DEVELOPER_COMMUNICATION_TYPE_LABEL'); ?></th>
                <td><?php echo $this->item->developer_communication_type; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_DEVELOPER_PATCH_DOWNLOAD_URL_LABEL'); ?></th>
                <td><?php echo $this->item->developer_patch_download_url; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_DEVELOPER_NAME_LABEL'); ?></th>
                <td><?php echo $this->item->developer_name; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_DEVELOPER_CONTACT_EMAIL_LABEL'); ?></th>
                <td><?php echo $this->item->developer_contact_email; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_TRACKING_DB_NAME_LABEL'); ?></th>
                <td><?php echo $this->item->tracking_db_name; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_TRACKING_DB_ID_LABEL'); ?></th>
                <td><?php echo $this->item->tracking_db_id; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_GENERAL_FIELD_JED_URL_LABEL'); ?></th>
                <td><?php echo $this->item->jed_url; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_DEVELOPER_ADDITIONAL_INFO_LABEL'); ?></th>
                <td><?php echo nl2br($this->item->developer_additional_info); ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_DOWNLOAD_URL_LABEL'); ?></th>
                <td><?php echo $this->item->download_url; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_CONSENT_TO_PROCESS_NOTIFICATION_LABEL'); ?></th>
                <td><?php echo $this->item->consent_to_process; ?></td>
            </tr>
            <?php /*
            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_PASSED_TO_VEL_LABEL'); ?></th>
                <td><?php echo $this->item->passed_to_vel; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_VEL_ITEM_ID_LABEL'); ?></th>
                <td><?php echo $this->item->vel_item_id; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_DATA_SOURCE_LABEL'); ?></th>
                <td><?php echo $this->item->data_source; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_GENERAL_FIELD_DATE_SUBMITTED_LABEL'); ?></th>
                <td><?php echo $this->item->date_submitted; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_REPORTS_FIELD_USER_IP_LABEL'); ?></th>
                <td><?php echo $this->item->user_ip; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_GENERAL_FIELD_CREATED_BY_LABEL'); ?></th>
                <td><?php echo $this->item->created_by_name; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL'); ?></th>
                <td><?php echo $this->item->modified_by_name; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('JGLOBAL_CREATED'); ?></th>
                <td><?php echo $this->item->created; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('JGLOBAL_MODIFIED'); ?></th>
                <td><?php echo $this->item->modified; ?></td>
            </tr>
*/ ?>
        </table>

    </div>


    <?php
} else { ?>
    <div class="jed-error">
        <?php echo Text::sprintf('COM_JED_VEL_REDIRECT_TO_MY_LISTS', Text::_('COM_JED_VEL_REDIRECT_VELREPORT')); ?>
        <br/>
        <a href="index.php?option=com_jed&view=velreports" class="btn btn-primary"><?php echo Text::_('JYES'); ?></a>
    </div>
    <?php
}
?>

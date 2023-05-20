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
                <td><?php echo $this->item->contact_fullname; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_DEVELOPERUPDATES_FIELD_CONTACT_ORGANISATION_LABEL'); ?></th>
                <td><?php echo $this->item->contact_organisation; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_GENERAL_FIELD_CONTACT_EMAIL_LABEL'); ?></th>
                <td><?php echo $this->item->contact_email; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_VULNERABLE_ITEM_NAME_LABEL'); ?></th>
                <td><?php echo $this->item->vulnerable_item_name; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_VULNERABLE_ITEM_VERSION_LABEL'); ?></th>
                <td><?php echo $this->item->vulnerable_item_version; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_EXTENSION_UPDATE_LABEL'); ?></th>
                <td><?php echo $this->item->extension_update; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_NEW_VERSION_NUMBER_LABEL'); ?></th>
                <td><?php echo $this->item->new_version_number; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_UPDATE_NOTICE_URL_LABEL'); ?></th>
                <td><?php echo $this->item->update_notice_url; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_CHANGELOG_URL_LABEL'); ?></th>
                <td><?php echo $this->item->changelog_url; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_DOWNLOAD_URL_LABEL'); ?></th>
                <td><?php echo $this->item->download_url; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_CONSENT_TO_PROCESS_NOTIFICATION_LABEL'); ?></th>
                <td><?php echo $this->item->consent_to_process; ?></td>
            </tr>


            <tr>
                <th><?php echo Text::_('COM_JED_GENERAL_FIELD_DATE_SUBMITTED_LABEL'); ?></th>
                <td><?php echo $this->item->update_date_submitted; ?></td>
            </tr>
            <?php /*

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_VEL_ITEM_ID_LABEL'); ?></th>
                <td><?php echo $this->item->vel_item_id; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_GENERAL_FIELD_DATA_SOURCE_LABEL'); ?></th>
                <td><?php echo $this->item->update_data_source; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('COM_JED_VEL_DEVELOPERUPDATES_FIELD_UPDATE_USER_IP_LABEL'); ?></th>
                <td><?php echo $this->item->update_user_ip; ?></td>
            </tr>

            <tr>
                <th><?php echo Text::_('JGLOBAL_FIELD_CREATED_BY_LABEL'); ?></th>
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
        <?php echo Text::sprintf('COM_JED_VEL_REDIRECT_TO_MY_LISTS', Text::_('COM_JED_VEL_REDIRECT_DEVELOPERUPDATES')); ?>
        <br/>
        <a href="index.php?option=com_jed&view=veldeveloperupdates"
           class="btn btn-primary"><?php echo Text::_('JYES'); ?></a>
    </div>
    <?php
}
?>

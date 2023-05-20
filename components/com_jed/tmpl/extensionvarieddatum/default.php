<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;


?>

<div class="item_fields">

    <table class="table">
        

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_ID'); ?></th>
            <td><?php echo $this->item->id; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_EXTENSION_ID'); ?></th>
            <td><?php echo $this->item->extension_id; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_SUPPLY_OPTION_ID'); ?></th>
            <td><?php echo $this->item->supply_option_id; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_INTRO_TEXT'); ?></th>
            <td><?php echo $this->item->intro_text; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_DESCRIPTION'); ?></th>
            <td><?php echo nl2br($this->item->description); ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_HOMEPAGE_LINK'); ?></th>
            <td><?php echo $this->item->homepage_link; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_DOWNLOAD_LINK'); ?></th>
            <td><?php echo $this->item->download_link; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_DEMO_LINK'); ?></th>
            <td><?php echo $this->item->demo_link; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_SUPPORT_LINK'); ?></th>
            <td><?php echo $this->item->support_link; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_DOCUMENTATION_LINK'); ?></th>
            <td><?php echo $this->item->documentation_link; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_LICENSE_LINK'); ?></th>
            <td><?php echo $this->item->license_link; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_TAGS'); ?></th>
            <td><?php echo $this->item->tags; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_STATE'); ?></th>
            <td>
            <i class="icon-<?php echo ($this->item->state == 1) ? 'publish' : 'unpublish'; ?>"></i></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_GENERAL_FIELD_CREATED_BY_LABEL '); ?></th>
            <td><?php echo $this->item->created_by_name; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_UPDATE_URL'); ?></th>
            <td><?php echo $this->item->update_url; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_UPDATE_URL_OK'); ?></th>
            <td><?php echo $this->item->update_url_ok; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_DOWNLOAD_INTEGRATION_TYPE'); ?></th>
            <td>
            <?php

            if (!empty($this->item->download_integration_type) || $this->item->download_integration_type === 0) {
                echo Text::_('COM_JED_EXTENSIONVARIEDDATA_DOWNLOAD_INTEGRATION_TYPE_OPTION_' . $this->item->download_integration_type);
            }
            ?></td>
        </tr>
        <?php if ($this->item->download_integration_type == "1" || $this->item->download_integration_type == "2" || $this->item->download_integration_type == "3" || $this->item->download_integration_type == "4" || $this->item->download_integration_type == "5") : ?>
        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_DOWNLOAD_INTEGRATION_URL'); ?></th>
            <td> echo $this->item->download_integration_url; ?></td>
        </tr>

        <?php endif; ?>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_IS_DEFAULT_DATA'); ?></th>
            <td><?php echo $this->item->is_default_data; ?></td>
        </tr>

        <tr>
            <th><?php echo Text::_('COM_JED_FORM_LBL_EXTENSIONVARIEDDATUM_TRANSLATION_LINK'); ?></th>
            <td><?php echo $this->item->translation_link; ?></td>
        </tr>

    </table>

</div>


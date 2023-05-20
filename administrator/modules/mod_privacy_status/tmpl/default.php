<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_status
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<table class="table">
    <thead>
        <tr>
            <th scope="col" class="w-20"><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_STATUS'); ?></th>
            <th scope="col"><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_CHECK'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <?php if ($privacyPolicyInfo['published'] && $privacyPolicyInfo['articlePublished']) : ?>
                    <span class="badge bg-success">
                        <span class="icon-check-square" aria-hidden="true"></span>
                        <?php echo Text::_('JPUBLISHED'); ?>
                    </span>
                <?php elseif ($privacyPolicyInfo['published'] && !$privacyPolicyInfo['articlePublished']) : ?>
                    <span class="badge bg-warning text-dark">
                        <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                        <?php echo Text::_('JUNPUBLISHED'); ?>
                    </span>
                <?php else : ?>
                    <span class="badge bg-warning text-dark">
                        <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                        <?php echo Text::_('COM_PRIVACY_STATUS_CHECK_NOT_AVAILABLE'); ?>
                    </span>
                <?php endif; ?>
            </td>
            <td>
                <div><?php echo Text::_('COM_PRIVACY_STATUS_CHECK_PRIVACY_POLICY_PUBLISHED'); ?></div>
                <?php if ($privacyPolicyInfo['editLink'] !== '') : ?>
                    <small><a href="<?php echo $privacyPolicyInfo['editLink']; ?>"><?php echo Text::_('COM_PRIVACY_EDIT_PRIVACY_POLICY'); ?></a></small>
                <?php else : ?>
                    <?php $link = Route::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . $privacyConsentPluginId); ?>
                    <small><a href="<?php echo $link; ?>"><?php echo Text::_('COM_PRIVACY_EDIT_PRIVACY_CONSENT_PLUGIN'); ?></a></small>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php if ($requestFormPublished['published'] && $requestFormPublished['exists']) : ?>
                    <span class="badge bg-success">
                        <span class="icon-check-square" aria-hidden="true"></span>
                        <?php echo Text::_('JPUBLISHED'); ?>
                    </span>
                <?php elseif (!$requestFormPublished['published'] && $requestFormPublished['exists']) : ?>
                    <span class="badge bg-warning text-dark">
                        <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                        <?php echo Text::_('JUNPUBLISHED'); ?>
                    </span>
                <?php else : ?>
                    <span class="badge bg-warning text-dark">
                        <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                        <?php echo Text::_('COM_PRIVACY_STATUS_CHECK_NOT_AVAILABLE'); ?>
                    </span>
                <?php endif; ?>
            </td>
            <td>
                <div><?php echo Text::_('COM_PRIVACY_STATUS_CHECK_REQUEST_FORM_MENU_ITEM_PUBLISHED'); ?></div>
                <?php if ($requestFormPublished['link'] !== '') : ?>
                    <small><a href="<?php echo $requestFormPublished['link']; ?>" class="break-word"><?php echo $requestFormPublished['link']; ?></a></small>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php if ($numberOfUrgentRequests === 0) : ?>
                    <span class="badge bg-success">
                        <span class="icon-check-square" aria-hidden="true"></span>
                        <?php echo Text::_('JNONE'); ?>
                    </span>
                <?php else : ?>
                    <span class="badge bg-danger">
                        <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                        <?php echo Text::_('WARNING'); ?>
                    </span>
                <?php endif; ?>
            </td>
            <td>
                <div><?php echo Text::_('COM_PRIVACY_STATUS_CHECK_OUTSTANDING_URGENT_REQUESTS'); ?></div>
                <small><?php echo Text::plural('COM_PRIVACY_STATUS_CHECK_OUTSTANDING_URGENT_REQUESTS_DESCRIPTION', $urgentRequestDays); ?></small>
                <?php if ($numberOfUrgentRequests > 0) : ?>
                    <small><a href="<?php echo Route::_('index.php?option=com_privacy&view=requests&filter[status]=1&list[fullordering]=a.requested_at ASC'); ?>"><?php echo Text::_('COM_PRIVACY_SHOW_URGENT_REQUESTS'); ?></a></small>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php if ($sendMailEnabled) : ?>
                    <span class="badge bg-success">
                        <span class="icon-check-square" aria-hidden="true"></span>
                        <?php echo Text::_('JENABLED'); ?>
                    </span>
                <?php else : ?>
                    <span class="badge bg-danger">
                        <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                        <?php echo Text::_('JDISABLED'); ?>
                    </span>
                <?php endif; ?>
            </td>
            <td>
                <?php if (!$sendMailEnabled) : ?>
                    <div><?php echo Text::_('COM_PRIVACY_STATUS_CHECK_SENDMAIL_DISABLED'); ?></div>
                    <small><?php echo Text::_('COM_PRIVACY_STATUS_CHECK_SENDMAIL_DISABLED_DESCRIPTION'); ?></small>
                <?php else : ?>
                    <div><?php echo Text::_('COM_PRIVACY_STATUS_CHECK_SENDMAIL_ENABLED'); ?></div>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php if ($databaseConnectionEncryption !== '') : ?>
                    <span class="badge bg-success">
                        <span class="icon-check-square" aria-hidden="true"></span>
                        <?php echo Text::_('JENABLED'); ?>
                    </span>
                <?php else : ?>
                    <span class="badge bg-warning text-dark">
                        <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                        <?php echo Text::_('COM_PRIVACY_STATUS_CHECK_NOT_AVAILABLE'); ?>
                    </span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($databaseConnectionEncryption === '') : ?>
                    <?php echo Text::_('MOD_PRIVACY_STATUS_CHECK_DATABASE_CONNECTION_ENCRYPTION_DISABLED'); ?>
                <?php else : ?>
                    <?php echo Text::sprintf('MOD_PRIVACY_STATUS_CHECK_DATABASE_CONNECTION_ENCRYPTION_ENABLED', $databaseConnectionEncryption); ?>
                <?php endif; ?>
            </td>
        </tr>
    </tbody>
</table>

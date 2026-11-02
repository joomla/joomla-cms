<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Users\Administrator\View\Users\HtmlView $this */

?>

<div class="p-3">
    <p><?php echo Text::_('COM_USERS_USERS_CONFIRM_SEND_NEW_PASSWORD'); ?></p>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="requireReset" value="1" id="newpassword-require-reset" checked>
        <label class="form-check-label" for="newpassword-require-reset">
            <?php echo Text::_('COM_USERS_REQUIRE_PASSWORD_RESET'); ?>
        </label>
    </div>
</div>
<div class="btn-toolbar p-3">
    <joomla-toolbar-button task="users.sendNewPassword" class="ms-auto">
        <button type="button" class="btn btn-danger"><?php echo Text::_('COM_USERS_TOOLBAR_SEND_NEW_PASSWORD'); ?></button>
    </joomla-toolbar-button>
</div>

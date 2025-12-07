<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_backward
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

// Only super user can view this data
if (!$app->getIdentity()->authorise('core.admin')) {
    return;
}

?>
<?php if ($compat || $compat6) : ?>
    <div class="header-item-content joomlaversion">
        <div class="header-item-text no-link">
            <span class="icon-shield-alt" aria-hidden="true"></span>
            <span aria-hidden="true"><?php echo Text::_('MOD_BACKWARD_TEXT') . ($compat ? '5' : '') . ($compat && $compat6 ? ', ' : '') . ($compat6 ? '6' : ''); ?></span>
        </div>
    </div>
<?php endif; ?>

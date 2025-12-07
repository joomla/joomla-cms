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

?>
<?php if (!empty($compat) || !empty($compatNext)) : ?>
    <div class="header-item-content joomlaversion">
        <div class="header-item-text no-link">
            <span class="icon-shield-alt" aria-hidden="true"></span>
            <span aria-hidden="true"><?php echo Text::_('MOD_BACKWARD_TEXT') . $compat . ($compat && $compatNext ? ', ' : '') . $compatNext); ?></span>
        </div>
    </div>
<?php endif; ?>

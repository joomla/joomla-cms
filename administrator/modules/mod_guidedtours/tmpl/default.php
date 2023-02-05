<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Load the Bootstrap Dropdown
HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');
?>
<div class="header-item-content dropdown header-profile">
    <button class="dropdown-toggle d-flex align-items-center ps-0 py-0" data-bs-toggle="dropdown" type="button"
        title="<?php echo Text::_('MOD_GUIDEDTOURS_MENU'); ?>">
        <div class="header-item-icon">
            <span class="icon-map-signs" aria-hidden="true"></span>
        </div>
        <div class="header-item-text">
            <?php echo Text::_('MOD_GUIDEDTOURS_MENU'); ?>
        </div>
        <span class="icon-angle-down" aria-hidden="true"></span>
    </button>
    <div class="dropdown-menu dropdown-menu-end">
        <?php foreach ($tours as $i => $tour) : ?>
        <a class="button-tour dropdown-item" onclick="tourWasSelected(this); return false" href="#" data-id="<?php echo $tour->id ?>">
            <span class="icon-map-signs" aria-hidden="true"></span>
            <?php echo Text::_($tour->title); ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

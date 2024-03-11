<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<?php HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle'); ?>
<div class="sorting">
    <label id="sorting_label" for="sorting_btn"><?php echo Text::_('COM_FINDER_SORT_BY'); ?></label>
    <div class="sorting__select btn-group">
        <?php foreach ($this->sortOrderFields as $sortOrderField) : ?>
            <?php if ($sortOrderField->active) : ?>
                <button id="sorting_btn" class="btn btn-secondary dropdown-toggle" type="button"
                        data-bs-toggle="dropdown"
                        aria-haspopup="listbox"
                        aria-expanded="false" aria-controls="finder_sorting_list">
                    <?php echo $this->escape($sortOrderField->label); ?>
                </button>
                <?php
                break;
            endif; ?>
        <?php endforeach; ?>

        <ul id="finder_sorting_list" class="sorting__list block dropdown-menu" role="listbox" aria-labelledby="finder_sorting_desc">
            <?php foreach ($this->sortOrderFields as $sortOrderField) : ?>
            <li  class="sorting__list-li <?php echo $sortOrderField->active ? 'sorting__list-li-active' : ''; ?>">
                <a class="dropdown-item" role="option" href="<?php echo Route::_($sortOrderField->url);?>" <?php echo $sortOrderField->active ? 'aria-current="true"' : ''; ?>>
                    <?php echo $this->escape($sortOrderField->label); ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="clearfix"></div>
</div>

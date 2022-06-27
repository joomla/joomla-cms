<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filter\OutputFilter;

HTMLHelper::_('behavior.core');
?>
<?php if ($displayData->displayMenu || $displayData->displayFilters) : ?>
<div id="j-toggle-sidebar-wrapper">
    <div id="sidebar" class="sidebar">
        <button class="btn btn-sm btn-secondary my-2 options-menu d-md-none" type="button" data-bs-toggle="collapse" data-bs-target=".sidebar-nav" aria-controls="sidebar-nav" aria-expanded="false" aria-label="<?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>">
            <span class="icon-align-justify" aria-hidden="true"></span>
            <?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>
        </button>
        <div class="sidebar-nav">
            <?php if ($displayData->displayMenu) : ?>
            <ul class="nav flex-column">
                <?php foreach ($displayData->list as $item) :
                    if (isset($item[2]) && $item[2] == 1) : ?>
                    <li class="active">
                    <?php else : ?>
                    <li>
                    <?php endif;
                    if ($displayData->hide) : ?>
                    <a class="nolink"><?php echo $item[0]; ?></a>
                    <?php else :
                        if ($item[1] !== '') : ?>
                        <a href="<?php echo OutputFilter::ampReplace($item[1]); ?>"><?php echo $item[0]; ?></a>
                        <?php else : ?>
                            <?php echo $item[0]; ?>
                        <?php endif;
                    endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <?php if ($displayData->displayMenu && $displayData->displayFilters) : ?>
            <hr>
            <?php endif; ?>
            <?php if ($displayData->displayFilters) : ?>
            <div class="filter-select d-none d-md-block">
                <h4 class="page-header"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?></h4>
                <?php foreach ($displayData->filters as $filter) : ?>
                    <label for="<?php echo $filter['name']; ?>" class="visually-hidden"><?php echo $filter['label']; ?></label>
                    <select name="<?php echo $filter['name']; ?>" id="<?php echo $filter['name']; ?>" class="form-select" onchange="this.form.submit()">
                        <?php if (!$filter['noDefault']) : ?>
                            <option value=""><?php echo $filter['label']; ?></option>
                        <?php endif; ?>
                        <?php echo $filter['options']; ?>
                    </select>
                    <hr>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div id="j-toggle-sidebar"></div>
</div>
<?php endif; ?>

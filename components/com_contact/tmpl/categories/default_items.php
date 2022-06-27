<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Contact\Site\Helper\RouteHelper;

if ($this->maxLevelcat != 0 && count($this->items[$this->parent->id]) > 0) :
    ?>
    <?php foreach ($this->items[$this->parent->id] as $id => $item) : ?>
        <?php if ($this->params->get('show_empty_categories_cat') || $item->numitems || count($item->getChildren())) : ?>
            <div class="com-contact-categories__items">
                <h3 class="page-header item-title">
                    <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($item->id, $item->language)); ?>">
                    <?php echo $this->escape($item->title); ?></a>
                    <?php if ($this->params->get('show_cat_items_cat') == 1) :?>
                        <span class="badge bg-info">
                            <?php echo Text::_('COM_CONTACT_NUM_ITEMS'); ?>&nbsp;
                            <?php echo $item->numitems; ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($this->maxLevelcat > 1 && count($item->getChildren()) > 0) : ?>
                        <button
                            type="button"
                            id="category-btn-<?php echo $item->id; ?>"
                            data-bs-target="#category-<?php echo $item->id; ?>"
                            data-bs-toggle="collapse"
                            class="btn btn-secondary btn-sm float-end"
                            aria-label="<?php echo Text::_('JGLOBAL_EXPAND_CATEGORIES'); ?>"
                        >
                            <span class="icon-plus" aria-hidden="true"></span>
                        </button>
                    <?php endif; ?>
                </h3>
                <?php if ($this->params->get('show_subcat_desc_cat') == 1) : ?>
                    <?php if ($item->description) : ?>
                        <div class="category-desc">
                            <?php echo HTMLHelper::_('content.prepare', $item->description, '', 'com_contact.categories'); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($this->maxLevelcat > 1 && count($item->getChildren()) > 0) : ?>
                    <div class="collapse fade" id="category-<?php echo $item->id; ?>">
                        <?php
                        $this->items[$item->id] = $item->getChildren();
                        $this->parent = $item;
                        $this->maxLevelcat--;
                        echo $this->loadTemplate('items');
                        $this->parent = $item->getParent();
                        $this->maxLevelcat++;
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?><?php
endif; ?>

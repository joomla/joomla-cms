<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Newsfeeds\Site\Helper\RouteHelper;

/** @var \Joomla\Component\Newsfeeds\Site\View\Categories\HtmlView $this */
?>
<?php if ($this->maxLevelcat != 0 && count($this->items[$this->parent->id]) > 0) : ?>
    <?php foreach ($this->items[$this->parent->id] as $id => $item) : ?>
        <?php if ($this->params->get('show_empty_categories_cat') || $item->numitems || count($item->getChildren())) : ?>
            <div class="com-newsfeeds-categories__items">
                <h3 class="page-header item-title">
                    <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($item->id, $item->language)); ?>">
                        <?php echo $this->escape($item->title); ?>
                    </a>
                    <?php if ($this->params->get('show_cat_items_cat') == 1) : ?>
                        <span class="badge bg-info">
                            <?php echo Text::_('COM_NEWSFEEDS_NUM_ITEMS'); ?>&nbsp;
                            <?php echo $item->numitems; ?>
                        </span>
                    <?php endif; ?>
                    <?php if (count($item->getChildren()) > 0 && $this->maxLevelcat > 1) : ?>
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
                        <div class="com-newsfeeds-categories__description category-desc">
                            <?php echo HTMLHelper::_('content.prepare', $item->description, '', 'com_newsfeeds.categories'); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (count($item->getChildren()) > 0 && $this->maxLevelcat > 1) : ?>
                    <div class="com-newsfeeds-categories__children collapse fade" id="category-<?php echo $item->id; ?>">
                        <?php $this->items[$item->id] = $item->getChildren(); ?>
                        <?php $this->parent = $item; ?>
                        <?php $this->maxLevelcat--; ?>
                        <?php echo $this->loadTemplate('items'); ?>
                        <?php $this->parent = $item->getParent(); ?>
                        <?php $this->maxLevelcat++; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

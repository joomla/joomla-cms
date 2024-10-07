<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

if ($params->get('articles_layout') == 1) {
    $gridCols = 'grid-cols-' . $params->get('layout_columns');
}

?>
<ul class="mod-articles-items<?php echo ($params->get('articles_layout') == 1 ? ' mod-articles-grid ' . $gridCols : ''); ?> mod-list">
    <?php foreach ($items as $item) : ?>
        <?php
        $displayInfo = $item->displayHits || $item->displayAuthorName || $item->displayCategoryTitle || $item->displayDate;
        ?>
        <li>
            <article class="mod-articles-item" itemscope itemtype="https://schema.org/Article">

                <?php if ($params->get('item_title') || $displayInfo || $params->get('show_tags') || $params->get('show_introtext') || $params->get('show_readmore')) : ?>
                    <div class="mod-articles-item-content">

                        <?php if ($params->get('item_title')) : ?>
                            <?php $item_heading = $params->get('item_heading', 'h4'); ?>
                            <<?php echo $item_heading; ?> class="mod-articles-title" itemprop="name">
                                <?php if ($params->get('link_titles') == 1) : ?>
                                    <?php $attributes = ['class' => 'mod-articles-link ' . $item->active, 'itemprop' => 'url']; ?>
                                    <?php $link = htmlspecialchars($item->link, ENT_COMPAT, 'UTF-8', false); ?>
                                    <?php $title = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false); ?>
                                    <?php echo HTMLHelper::_('link', $link, $title, $attributes); ?>
                                <?php else : ?>
                                    <?php echo $item->title; ?>
                                <?php endif; ?>
                            </<?php echo $item_heading; ?>>
                        <?php endif; ?>

                        <?php echo $item->event->afterDisplayTitle; ?>

                        <?php if ($displayInfo) : ?>
                            <?php $listClass = ($params->get('info_layout') == 1) ? 'list-inline' : 'list-unstyled'; ?>
                            <dl class="<?php echo $listClass; ?>">
                                <dt class="article-info-term">
                                    <span class="visually-hidden">
                                        <?php echo Text::_('MOD_ARTICLES_INFO'); ?>
                                    </span>
                                </dt>

                                <?php if ($item->displayAuthorName) : ?>
                                    <dd class="mod-articles-writtenby <?php echo ($params->get('info_layout') == 1 ? 'list-inline-item' : ''); ?>">
                                        <?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'icon-user icon-fw']); ?>
                                        <?php echo $item->displayAuthorName; ?>
                                    </dd>
                                <?php endif; ?>

                                <?php if ($item->displayCategoryTitle) : ?>
                                    <dd class="mod-articles-category <?php echo ($params->get('info_layout') == 1 ? 'list-inline-item' : ''); ?>">
                                        <?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'icon-folder-open icon-fw']); ?>
                                        <?php if ($item->displayCategoryLink) : ?>
                                            <a href="<?php echo $item->displayCategoryLink; ?>">
                                                <?php echo $item->displayCategoryTitle; ?>
                                            </a>
                                        <?php else : ?>
                                            <?php echo $item->displayCategoryTitle; ?>
                                        <?php endif; ?>
                                    </dd>
                                <?php endif; ?>

                                <?php if ($item->displayDate) : ?>
                                    <dd class="mod-articles-date <?php echo ($params->get('info_layout') == 1 ? 'list-inline-item' : ''); ?>">
                                        <?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'icon-calendar icon-fw']); ?>
                                        <?php echo $item->displayDate; ?>
                                    </dd>
                                <?php endif; ?>

                                <?php if ($item->displayHits) : ?>
                                    <dd class="mod-articles-hits <?php echo ($params->get('info_layout') == 1 ? 'list-inline-item' : ''); ?>">
                                        <?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'icon-eye icon-fw']); ?>
                                        <?php echo $item->displayHits; ?>
                                    </dd>
                                <?php endif; ?>
                            </dl>
                        <?php endif; ?>

                        <?php if (in_array($params->get('img_intro_full'), ['intro', 'full']) && !empty($item->imageSrc)) : ?>
                            <?php echo LayoutHelper::render('joomla.content.' . $params->get('img_intro_full') . '_image', $item); ?>
                        <?php endif; ?>

                        <?php if ($params->get('show_tags', 0) && $item->tags->itemTags) : ?>
                            <div class="mod-articles-tags">
                                <?php echo LayoutHelper::render('joomla.content.tags', $item->tags->itemTags); ?>
                            </div>
                        <?php endif; ?>

                        <?php echo $item->event->beforeDisplayContent; ?>

                        <?php if ($params->get('show_introtext', 1)) : ?>
                            <?php echo $item->displayIntrotext; ?>
                        <?php endif; ?>

                        <?php echo $item->event->afterDisplayContent; ?>

                        <?php if ($params->get('show_readmore')) : ?>
                            <?php if ($params->get('show_readmore_title', '') !== '') : ?>
                                <?php $item->params->set('show_readmore_title', $params->get('show_readmore_title')); ?>
                                <?php $item->params->set('readmore_limit', $params->get('readmore_limit')); ?>
                            <?php endif; ?>
                            <?php echo LayoutHelper::render('joomla.content.readmore', ['item' => $item, 'params' => $item->params, 'link' => $item->link]); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>
        </li>
    <?php endforeach; ?>
</ul>

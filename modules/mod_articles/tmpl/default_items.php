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
<ul class="mod-articles-items <?php echo ($params->get('articles_layout') == 1 ? 'mod-articles-grid ' . $gridCols : ''); ?> mod-list">
    <?php foreach ($items as $item) : ?>
        <?php
            $displayInfo = $item->displayHits || $item->displayAuthorName || $item->displayCategoryTitle || $item->displayDate;
        ?>
        <li>
            <article class="mod-articles-item <?php echo ($params->get('card_link') ? 'mod-articles-item-card card' : ''); ?>" itemscope itemtype="https://schema.org/Article">

                <?php if ($params->get('item_title') || $displayInfo || $params->get('show_tags') || $params->get('show_introtext') || $params->get('show_readmore')) : ?>
                    <div class="mod-articles-item-content <?php echo ($params->get('card_link') ? 'card-body' : ''); ?>">

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
                                        <?php if ($item->displayCategoryLink && $params->get('card_link') == 0) : ?>
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

                        <?php if ($params->get('show_tags', 0) && $item->tags->itemTags) : ?>
                            <div class="mod-articles-tags">
                                <?php echo LayoutHelper::render('joomla.content.tags', $item->tags->itemTags); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($params->get('show_introtext')) : ?>
                            <p><?php echo $item->displayIntrotext; ?></p>
                        <?php endif; ?>

                        <?php if ($params->get('show_readmore')) : ?>
                            <p class="mod-articles-readmore">

                                <<?php echo ($params->get('card_link') ? 'span' : 'a'); ?> class="btn btn-secondary" href="<?php echo $item->link; ?>">
                                    <?php if ($item->params->get('access-view') == false) : ?>
                                        <?php echo Text::_('MOD_ARTICLES_REGISTER_TO_READ_MORE'); ?>
                                    <?php elseif ($item->alternative_readmore) : ?>
                                        <?php echo $item->alternative_readmore; ?>
                                        <?php echo HTMLHelper::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
                                            <?php if ($params->get('show_readmore_title', 0)) : ?>
                                                <?php echo HTMLHelper::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
                                            <?php endif; ?>
                                    <?php elseif ($params->get('show_readmore_title', 0)) : ?>
                                        <?php echo Text::_('MOD_ARTICLES_READ_MORE'); ?>
                                        <?php echo HTMLHelper::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
                                    <?php else : ?>
                                        <?php echo Text::_('MOD_ARTICLES_READ_MORE_TITLE'); ?>
                                    <?php endif; ?>
                                </<?php echo ($params->get('card_link') ? 'span' : 'a'); ?>>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($params->get('img_intro_full') !== 'none' && !empty($item->imageSrc)) : ?>
                    <figure class="mod-articles-image">
                        <?php echo LayoutHelper::render(
                            'joomla.html.image',
                            [
                                'src' => $item->imageSrc,
                                'alt' => $item->imageAlt,
                            ]
                        ); ?>
                        <?php if (!empty($item->imageCaption)) : ?>
                            <figcaption>
                                <?php echo $item->imageCaption; ?>
                            </figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endif; ?>
            </article>
        </li>
    <?php endforeach; ?>
</ul>

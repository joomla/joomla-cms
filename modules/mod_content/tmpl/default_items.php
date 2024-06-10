<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_content
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

?>
<?php foreach ($items as $item) : ?>
<div class="item">
    <?php if ($params->get('link_titles') == 1) : ?>
        <?php $attributes = ['class' => 'mod-content-title ' . $item->active]; ?>
        <?php $link = htmlspecialchars($item->link, ENT_COMPAT, 'UTF-8', false); ?>
        <?php $title = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false); ?>
        <?php echo HTMLHelper::_('link', $link, $title, $attributes); ?>
    <?php else : ?>
        <?php echo $item->title; ?>
    <?php endif; ?>

    <?php if ($item->displayHits) : ?>
        <span class="mod-content-hits">
            (<?php echo $item->displayHits; ?>)
        </span>
    <?php endif; ?>

    <?php if ($params->get('show_author')) : ?>
        <span class="mod-content-writtenby">
            <?php echo $item->displayAuthorName; ?>
        </span>
    <?php endif; ?>

    <?php if ($item->displayCategoryTitle) : ?>
        <span class="mod-content-category">
            (<?php echo $item->displayCategoryTitle; ?>)
        </span>
    <?php endif; ?>

    <?php if ($item->displayDate) : ?>
        <span class="mod-content-date"><?php echo $item->displayDate; ?></span>
    <?php endif; ?>

    <?php if ($params->get('show_tags', 0) && $item->tags->itemTags) : ?>
        <div class="mod-content-tags">
            <?php echo LayoutHelper::render('joomla.content.tags', $item->tags->itemTags); ?>
        </div>
    <?php endif; ?>
    <?php if ($params->get('img_intro_full') !== 'none' && !empty($item->imageSrc)) : ?>
        <figure class="newsflash-image">
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
    <?php if ($params->get('show_introtext')) : ?>
        <?php echo $item->displayIntrotext; ?>
    <?php endif; ?>

    <?php if ($params->get('show_readmore')) : ?>
        <p class="mod-content-readmore">
            <a class="mod-content-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
                <?php if ($item->params->get('access-view') == false) : ?>
                    <?php echo Text::_('MOD_CONTENT_REGISTER_TO_READ_MORE'); ?>
                <?php elseif ($item->alternative_readmore) : ?>
                    <?php echo $item->alternative_readmore; ?>
                    <?php echo HTMLHelper::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
                        <?php if ($params->get('show_readmore_title', 0)) : ?>
                            <?php echo HTMLHelper::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
                        <?php endif; ?>
                <?php elseif ($params->get('show_readmore_title', 0)) : ?>
                    <?php echo Text::_('MOD_CONTENT_READ_MORE'); ?>
                    <?php echo HTMLHelper::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
                <?php else : ?>
                    <?php echo Text::_('MOD_CONTENT_READ_MORE_TITLE'); ?>
                <?php endif; ?>
            </a>
        </p>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_feed
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filter\OutputFilter;

// Check if feed URL has been set
if (empty($rssurl)) {
    echo '<div>' . Text::_('MOD_FEED_ERR_NO_URL') . '</div>';

    return;
}

if (!empty($feed) && is_string($feed)) {
    echo $feed;
} else {
    $lang      = $app->getLanguage();
    $myrtl     = $params->get('rssrtl', 0);
    $direction = ' ';

    if ($lang->isRtl() && $myrtl == 0) {
        $direction = ' redirect-rtl';
    } elseif ($lang->isRtl() && $myrtl == 1) {
        // Feed description
        $direction = ' redirect-ltr';
    } elseif ($lang->isRtl() && $myrtl == 2) {
        $direction = ' redirect-rtl';
    } elseif ($myrtl == 0) {
        $direction = ' redirect-ltr';
    } elseif ($myrtl == 1) {
        $direction = ' redirect-ltr';
    } elseif ($myrtl == 2) {
        $direction = ' redirect-rtl';
    }

    if ($feed != false) :
        ?>
        <div style="direction: <?php echo $rssrtl ? 'rtl' : 'ltr'; ?>; text-align: <?php echo $rssrtl ? 'right' : 'left'; ?> !important" class="feed">
        <?php

        // Feed title
        if (!is_null($feed->title) && $params->get('rsstitle', 1)) : ?>
            <h2 class="<?php echo $direction; ?>">
                <a href="<?php echo str_replace('&', '&amp;', $rssurl); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo $feed->title; ?></a>
            </h2>
        <?php endif;
        // Feed date
        if ($params->get('rssdate', 1) && ($feed->publishedDate !== null)) : ?>
            <h3>
            <?php echo HTMLHelper::_('date', $feed->publishedDate, Text::_('DATE_FORMAT_LC3')); ?>
            </h3>
        <?php endif; ?>

        <?php // Feed description ?>
        <?php if ($params->get('rssdesc', 1)) : ?>
            <?php echo $feed->description; ?>
        <?php endif; ?>

        <?php // Feed image ?>
        <?php if ($params->get('rssimage', 1) && $feed->image) : ?>
            <img class="w-100" src="<?php echo $feed->image->uri; ?>" alt="<?php echo $feed->image->title; ?>"/>
        <?php endif; ?>


        <?php // Show items ?>
        <?php if (!empty($feed)) : ?>
        <ul class="newsfeed list-group">
            <?php for ($i = 0; $i < $params->get('rssitems', 3); $i++) :
                if (!$feed->offsetExists($i)) :
                    break;
                endif;
                $uri  = $feed[$i]->uri || !$feed[$i]->isPermaLink ? trim($feed[$i]->uri) : trim($feed[$i]->guid);
                $uri  = !$uri || stripos($uri, 'http') !== 0 ? $rssurl : $uri;
                $text = $feed[$i]->content !== '' ? trim($feed[$i]->content) : '';
                ?>
                <li class="list-group-item mb-2">
                    <?php if (!empty($uri)) : ?>
                        <h5 class="feed-link">
                        <a href="<?php echo $uri; ?>" target="_blank">
                        <?php echo trim($feed[$i]->title); ?></a></h5>
                    <?php else : ?>
                        <h5 class="feed-link"><?php echo trim($feed[$i]->title); ?></h5>
                    <?php endif; ?>

                    <?php if ($params->get('rssitemdate', 0)  && $feed[$i]->publishedDate !== null) : ?>
                        <div class="feed-item-date">
                            <?php echo HTMLHelper::_('date', $feed[$i]->publishedDate, Text::_('DATE_FORMAT_LC3')); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($params->get('rssitemdesc', 1) && $text !== '') : ?>
                        <div class="feed-item-description">
                        <?php
                            // Strip the images.
                            $text = OutputFilter::stripImages($text);
                            $text = HTMLHelper::_('string.truncate', $text, $params->get('word_count', 0), true, false);
                            echo str_replace('&apos;', "'", $text);
                        ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>
        </ul>
        <?php endif; ?>
    </div>
    <?php endif;
}

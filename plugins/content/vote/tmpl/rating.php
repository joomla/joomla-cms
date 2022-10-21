<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->app->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('plg_content_vote', 'plg_content_vote/rating.css');

/**
 * Layout variables
 * -----------------
 * @var   string   $context  The context of the content being passed to the plugin
 * @var   object   &$row     The article object
 * @var   object   &$params  The article params
 * @var   integer  $page     The 'page' number
 * @var   array    $parts    The context segments
 * @var   string   $path     Path to this file
 */

if ($context === 'com_content.categories') {
    return;
}

// Get the icons
$iconStar     = HTMLHelper::_('image', 'plg_content_vote/vote-star.svg', '', '', true, true);
$iconHalfstar = HTMLHelper::_('image', 'plg_content_vote/vote-star-half.svg', '', '', true, true);

// If you can't find the icons then skip it
if ($iconStar === null || $iconHalfstar === null) {
    return;
}

// Get paths to icons
$pathStar     = JPATH_ROOT . substr($iconStar, strlen(Uri::root(true)));
$pathHalfstar = JPATH_ROOT . substr($iconHalfstar, strlen(Uri::root(true)));

// Write inline '<svg>' elements
$star     = file_exists($pathStar) ? file_get_contents($pathStar) : '';
$halfstar = file_exists($pathHalfstar) ? file_get_contents($pathHalfstar) : '';

// Get rating
$rating = (float) $row->rating;
$rcount = (int) $row->rating_count;

// Round to 0.5
$rating = round($rating / 0.5) * 0.5;

// Determine number of stars
$stars = $rating;
$img   = '';

for ($i = 0; $i < floor($stars); $i++) {
    $img .= '<li class="vote-star">' . $star . '</li>';
}

if (($stars - floor($stars)) >= 0.5) {
    $img .= '<li class="vote-star-empty">' . $star . '</li>';
    $img .= '<li class="vote-star-half">' . $halfstar . '</li>';

    ++$stars;
}

for ($i = $stars; $i < 5; $i++) {
    $img .= '<li class="vote-star-empty">' . $star . '</li>';
}

?>
<div class="content_rating" role="img" aria-label="<?php echo Text::sprintf('PLG_VOTE_STAR_RATING', $rating); ?>">
    <?php if ($rcount) : ?>
        <p class="visually-hidden" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
            <?php echo Text::sprintf('PLG_VOTE_USER_RATING', '<span itemprop="ratingValue">' . $rating . '</span>', '<span itemprop="bestRating">5</span>'); ?>
            <meta itemprop="ratingCount" content="<?php echo $rcount; ?>">
            <meta itemprop="worstRating" content="1">
        </p>
        <?php if ($this->params->get('show_total_votes', 0)) : ?>
            <?php echo Text::sprintf('PLG_VOTE_TOTAL_VOTES', $rcount); ?>
        <?php endif; ?>
    <?php endif; ?>
    <ul>
        <?php echo $img; ?>
    </ul>
</div>

<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="content_rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
    <p class="unseen element-invisible">
        <?php echo JText::sprintf('PLG_VOTE_USER_RATING', '<span itemprop="ratingValue">' . $rating . '</span>', '<span itemprop="bestRating">5</span>'); ?>
        <meta itemprop="ratingCount" content="<?php echo (int) $row->rating_count; ?>" />
        <meta itemprop="worstRating" content="0" />
    </p>
    <?php echo $img; ?>
</div>
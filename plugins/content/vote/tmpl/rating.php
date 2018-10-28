<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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

if ($context == 'com_content.categories')
{
	return;
}

$rating = (int) $row->rating;
$rcount = (int) $row->rating_count;

// Look for images in template if available
$starImageOn  = JHtml::_('image', 'system/rating_star.png', JText::_('PLG_VOTE_STAR_ACTIVE'), null, true);
$starImageOff = JHtml::_('image', 'system/rating_star_blank.png', JText::_('PLG_VOTE_STAR_INACTIVE'), null, true);

$img = '';
for ($i = 0; $i < $rating; $i++)
{
	$img .= $starImageOn;
}

for ($i = $rating; $i < 5; $i++)
{
	$img .= $starImageOff;
}

?>
<?php if ($rcount) : ?>
<div class="content_rating" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
	<p class="unseen element-invisible">
		<?php echo JText::sprintf('PLG_VOTE_USER_RATING', '<span itemprop="ratingValue">' . $rating . '</span>', '<span itemprop="bestRating">5</span>'); ?>
		<meta itemprop="ratingCount" content="<?php echo $rcount; ?>" />
		<meta itemprop="worstRating" content="1" />
	</p>
</div>
<?php endif; ?>
<?php echo $img; ?>

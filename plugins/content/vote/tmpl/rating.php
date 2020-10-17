<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getDocument()->getWebAssetManager();
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

if ($context === 'com_content.categories')
{
	return;
}

$rating = (float) $row->rating;
$rating = round($rating / 0.5) * 0.5; // round to 0.5
$rcount = (int) $row->rating_count;

$img   = '';
$stars = $rating;
$link  = 'media/plg_content_vote/images/';

for ($i = 0; $i < floor($stars); $i++)
{
	$img .= HTMLHelper::_('image', $link . 'star.svg', Text::_('PLG_VOTE_STAR_ACTIVE'), array('class' => 'vote-star'));
}

if (($stars - floor($stars)) >= 0.5)
{
	$img .= HTMLHelper::_('image', $link . 'star.svg', '', array('class' => 'vote-star-empty'));

	if (Factory::getLanguage()->isRTL())
	{
		$img .= HTMLHelper::_('image', $link . 'star-half.svg', Text::_('PLG_VOTE_STAR_ACTIVE_HALF'), array('class' => 'vote-star-rtl'));
	}
	else
	{
		$img .= HTMLHelper::_('image', $link . 'star-half.svg', Text::_('PLG_VOTE_STAR_ACTIVE_HALF'), array('class' => 'vote-star-ltr'));
	}

	$stars += 1;
}

for ($i = $stars; $i < 5; $i++)
{
	$img .= HTMLHelper::_('image', $link . 'star.svg', Text::_('PLG_VOTE_STAR_INACTIVE'), array('class' => 'vote-star-empty'));
}

?>
<div class="content_rating" role="img" aria-label="<?php echo Text::sprintf('PLG_VOTE_STAR_RATING', $rating); ?>">
	<?php if ($rcount) : ?>
		<p class="sr-only" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
			<?php echo Text::sprintf('PLG_VOTE_USER_RATING', '<span itemprop="ratingValue">' . $rating . '</span>', '<span itemprop="bestRating">5</span>'); ?>
			<meta itemprop="ratingCount" content="<?php echo $rcount; ?>">
			<meta itemprop="worstRating" content="1">
		</p>
	<?php endif; ?>
<?php echo $img; ?>
</div>

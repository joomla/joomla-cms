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
$stars  = $rating;

$rcount = (int) $row->rating_count;

$img      = '';
$star     = file_get_contents(JPATH_ROOT . '/media/plg_content_vote/images/vote-star.svg');
$halfstar = file_get_contents(JPATH_ROOT . '/media/plg_content_vote/images/vote-star-half.svg');

for ($i = 0; $i < floor($stars); $i++)
{
	$staractive = str_replace('{{description}}', Text::_('PLG_VOTE_STAR_ACTIVE'), $star);

	$img .= '<li class="vote-star">' . $staractive . '</li>';
}

if (($stars - floor($stars)) >= 0.5)
{
	$starinactive   = str_replace('{{description}}', Text::_('PLG_VOTE_STAR_INACTIVE'), $star);
	$halfstaractive = str_replace('{{description}}', Text::_('PLG_VOTE_STAR_ACTIVE_HALF'), $halfstar);

	$img .= '<li class="vote-star-empty">' . $starinactive . '</li>';
	$img .= '<li class="vote-star-half">' . $halfstaractive . '</li>';

	$stars += 1;
}

for ($i = $stars; $i < 5; $i++)
{
	$starinactive = str_replace('{{description}}', Text::_('PLG_VOTE_STAR_INACTIVE'), $star);

	$img .= '<li class="vote-star-empty">' . $starinactive . '</li>';
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
	<?php if ($img) : ?>
		<ul>
			<?php echo $img; ?>
		</ul>
	<?php endif; ?>
</div>

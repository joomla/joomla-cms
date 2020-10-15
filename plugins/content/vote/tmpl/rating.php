<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

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

for ($i = 0; $i < floor($stars); $i++)
{
	$text = Text::_('PLG_VOTE_STAR_ACTIVE');

	$img .= '<span class="text-warning fas fa-star" role="img" aria-label="' . $text . '" aria-hidden="true"></span>';
}

if (($stars - floor($stars)) >= 0.5)
{
	$text = Text::_('PLG_VOTE_STAR_ACTIVE_HALF');

	$img .= '<span class="text-muted fas fa-star" aria-hidden="true"></span>';
	$img .= '<span style="margin-left: -1.2em" class="text-warning fas fa-star-half" role="img" aria-label="' . $text . '"aria-hidden="true"></span>';

	$stars += 1;
}

for ($i = $stars; $i < 5 ; $i++)
{
	$text = Text::_('PLG_VOTE_STAR_INACTIVE');

	$img .= '<span class="text-muted fas fa-star" role="img" aria-label="' . $text . '" aria-hidden="true"></span>';
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

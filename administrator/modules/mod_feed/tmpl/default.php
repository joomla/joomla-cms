<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_feed
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (!empty($feed) && is_string($feed))
{
	echo $feed;
}
else
{
	$lang      = JFactory::getLanguage();
	$myrtl     = $params->get('rssrtl');
	$direction = ' ';

	if ($lang->isRtl() && $myrtl == 0)
	{
		$direction = ' redirect-rtl';
	}

	// Feed description
	elseif ($lang->isRtl() && $myrtl == 1)
	{
		$direction = ' redirect-ltr';
	}

	elseif ($lang->isRtl() && $myrtl == 2)
	{
		$direction = ' redirect-rtl';
	}

	elseif ($myrtl == 0)
	{
		$direction = ' redirect-ltr';
	}
	elseif ($myrtl == 1)
	{
		$direction = ' redirect-ltr';
	}
	elseif ($myrtl == 2)
	{
		$direction = ' redirect-rtl';
	}

	if ($feed != false) :
		// Image handling
		$iUrl   = isset($feed->image) ? $feed->image : null;
		$iTitle = isset($feed->imagetitle) ? $feed->imagetitle : null;
		?>
		<div style="direction: <?php echo $rssrtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $rssrtl ? 'right' :'left'; ?> ! important"  class="feed<?php echo $moduleclass_sfx; ?>">
		<?php

		// Feed description
		if (!is_null($feed->title) && $params->get('rsstitle', 1)) : ?>
			<h2 class="<?php echo $direction; ?>">
				<a href="<?php echo str_replace('&', '&amp;', $rssurl); ?>" target="_blank">
				<?php echo $feed->title; ?></a>
			</h2>
		<?php endif; ?>

		<!-- Feed description -->
		<?php if ($params->get('rssdesc', 1)) : ?>
			<?php echo $feed->description; ?>
		<?php endif; ?>

		<!--  Feed image  -->
		<?php if ($params->get('rssimage', 1) && $iUrl) : ?>
			<img src="<?php echo $iUrl; ?>" alt="<?php echo @$iTitle; ?>"/>
		<?php endif; ?>


	<!-- Show items -->
	<?php if (!empty($feed)) : ?>
		<ul class="newsfeed<?php echo $params->get('moduleclass_sfx'); ?>">
		<?php for ($i = 0; $i < $params->get('rssitems', 5); $i++) :

			if (!$feed->offsetExists($i)) :
				break;
			endif;
			$uri  = (!empty($feed[$i]->uri) || !is_null($feed[$i]->uri)) ? $feed[$i]->uri : $feed[$i]->guid;
			$uri  = substr($uri, 0, 4) != 'http' ? $params->get('rsslink') : $uri;
			$text = !empty($feed[$i]->content) ||  !is_null($feed[$i]->content) ? $feed[$i]->content : $feed[$i]->description;
			?>
				<li>
					<?php if (!empty($uri)) : ?>
						<h5 class="feed-link">
						<a href="<?php echo $uri; ?>" target="_blank">
						<?php  echo $feed[$i]->title; ?></a></h5>
					<?php else : ?>
						<h5 class="feed-link"><?php  echo $feed[$i]->title; ?></h5>
					<?php  endif; ?>

					<?php if ($params->get('rssitemdesc') && !empty($text)) : ?>
						<div class="feed-item-description">
						<?php
							// Strip the images.
							$text = JFilterOutput::stripImages($text);
							$text = JHtml::_('string.truncate', $text, $params->get('word_count'), true, false);
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

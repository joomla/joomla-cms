<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_feed
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
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
	$myrtl     = $params->get('rssrtl', 0);
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
		?>
		<div style="direction: <?php echo $rssrtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $rssrtl ? 'right' :'left'; ?> !important"  class="feed<?php echo $moduleclass_sfx; ?>">
		<?php

		// Feed title
		if (!is_null($feed->title) && $params->get('rsstitle', 1)) : ?>
			<h2 class="<?php echo $direction; ?>">
				<a href="<?php echo str_replace('&', '&amp;', $rssurl); ?>" target="_blank">
				<?php echo $feed->title; ?></a>
			</h2>
		<?php endif;
		// Feed date
		if ($params->get('rssdate', 1)) : ?>
			<h3>
			<?php echo JHtml::_('date', $feed->publishedDate, JText::_('DATE_FORMAT_LC3')); ?>
			</h3>
		<?php endif; ?>

		<!-- Feed description -->
		<?php if ($params->get('rssdesc', 1)) : ?>
			<?php echo $feed->description; ?>
		<?php endif; ?>

		<!--  Feed image  -->
		<?php if ($params->get('rssimage', 1) && $feed->image) : ?>
			<img src="<?php echo $feed->image->uri; ?>" alt="<?php echo $feed->image->title; ?>"/>
		<?php endif; ?>


	<!-- Show items -->
	<?php if (!empty($feed)) : ?>
		<?php // postinstall override ?>
		<?php if ($rssurl === 'https://www.joomla.org/announcements/release-news.feed?type=rss') : ?>
			<?php $style = 'style="direction: ltr; text-align: left !important;"'; ?>
			<ul class="newsfeed" <?php echo $style; ?>>
		<?php else : ?>
			<ul class="newsfeed<?php echo $params->get('moduleclass_sfx'); ?>">
		<?php endif; ?>
		<?php for ($i = 0; $i < $params->get('rssitems', 3); $i++) :

			if (!$feed->offsetExists($i)) :
				break;
			endif;
			$uri  = $feed[$i]->uri || !$feed[$i]->isPermaLink ? trim($feed[$i]->uri) : trim($feed[$i]->guid);
			$uri  = !$uri || stripos($uri, 'http') !== 0 ? $rssurl : $uri;
			$text = $feed[$i]->content !== '' ? trim($feed[$i]->content) : '';
			?>
				<li>
					<?php if (!empty($uri)) : ?>
						<h5 class="feed-link">
						<a href="<?php echo $uri; ?>" target="_blank">
						<?php echo trim($feed[$i]->title); ?></a></h5>
					<?php else : ?>
						<h5 class="feed-link"><?php  echo $feed[$i]->title; ?></h5>
					<?php  endif; ?>
					<?php if ($params->get('rssitemdate', 0)) : ?>
						<div class="feed-item-date">
							<?php echo JHtml::_('date', $feed[$i]->publishedDate, JText::_('DATE_FORMAT_LC3')); ?>
						</div>
					<?php endif; ?>
					<?php if ($params->get('rssitemdesc', 1) && $text !== '') : ?>
						<div class="feed-item-description">
						<?php
							// Strip the images.
							$text = JFilterOutput::stripImages($text);
							$text = JHtml::_('string.truncate', $text, $params->get('word_count', 0), true, false);
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

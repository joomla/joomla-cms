<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_feed
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<?php
if (!empty($feed) && is_string($feed))
{
	echo $feed;
}
else
{
	$lang      = JFactory::getLanguage();
	$myrtl     = $params->get('rssrtl', 0);
	$direction = ' ';

	$isRtl = $lang->isRtl();

	if ($isRtl && $myrtl == 0)
	{
		$direction = ' redirect-rtl';
	}

	// Feed description
	elseif ($isRtl && $myrtl == 1)
	{
		$direction = ' redirect-ltr';
	}

	elseif ($isRtl && $myrtl == 2)
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

	if ($feed !== false)
	{
		?>
		<div style="direction: <?php echo $rssrtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $rssrtl ? 'right' :'left'; ?> !important" class="feed<?php echo $moduleclass_sfx; ?>">
		<?php
		// Feed description
		if ($feed->title !== null && $params->get('rsstitle', 1))
		{
			?>
					<h2 class="<?php echo $direction; ?>">
						<a href="<?php echo htmlspecialchars($rssurl, ENT_COMPAT, 'UTF-8'); ?>" target="_blank">
						<?php echo $feed->title; ?></a>
					</h2>
			<?php
		}
		// Feed date
		if ($params->get('rssdate', 1)) : ?>
			<h3>
			<?php echo JHtml::_('date', $feed->publishedDate, JText::_('DATE_FORMAT_LC3')); ?>
			</h3>
		<?php endif;
		// Feed description
		if ($params->get('rssdesc', 1))
		{
		?>
			<?php echo $feed->description; ?>
			<?php
		}
		// Feed image
		if ($feed->image && $params->get('rssimage', 1)) :
		?>
			<img src="<?php echo $feed->image->uri; ?>" alt="<?php echo $feed->image->title; ?>"/>
		<?php endif; ?>


	<!-- Show items -->
	<?php if (!empty($feed))
	{ ?>
		<ul class="newsfeed<?php echo $params->get('moduleclass_sfx'); ?>">
		<?php for ($i = 0, $max = min(count($feed), $params->get('rssitems', 3)); $i < $max; $i++) { ?>
			<?php
				$uri  = $feed[$i]->uri || !$feed[$i]->isPermaLink ? trim($feed[$i]->uri) : trim($feed[$i]->guid);
				$uri  = !$uri || stripos($uri, 'http') !== 0 ? $rssurl : $uri;
				$text = $feed[$i]->content !== '' ? trim($feed[$i]->content) : '';
			?>
				<li>
					<?php if (!empty($uri)) : ?>
						<span class="feed-link">
						<a href="<?php echo htmlspecialchars($uri, ENT_COMPAT, 'UTF-8'); ?>" target="_blank">
						<?php echo trim($feed[$i]->title); ?></a></span>
					<?php else : ?>
						<span class="feed-link"><?php echo trim($feed[$i]->title); ?></span>
					<?php endif; ?>
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
							$text = JHtml::_('string.truncate', $text, $params->get('word_count', 0));
							echo str_replace('&apos;', "'", $text);
						?>
						</div>
					<?php endif; ?>
				</li>
		<?php } ?>
		</ul>
	<?php } ?>
	</div>
	<?php }
}

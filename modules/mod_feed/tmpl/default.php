<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_feed
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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

	if ($feed != false)
	{
		// Image handling
		$iUrl   = isset($feed->image) ? $feed->image : null;
		$iTitle = isset($feed->imagetitle) ? $feed->imagetitle : null;
		?>
		<div style="direction: <?php echo $rssrtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $rssrtl ? 'right' :'left'; ?> ! important"  class="feed<?php echo $moduleclass_sfx; ?>">
		<?php
		// Feed description
		if (!is_null($feed->title) && $params->get('rsstitle', 1))
		{
			?>
					<h2 class="<?php echo $direction; ?>">
						<a href="<?php echo htmlspecialchars($rssurl, ENT_COMPAT, 'UTF-8'); ?>" target="_blank">
						<?php echo $feed->title; ?></a>
					</h2>
			<?php
		}
		// Feed description
		if ($params->get('rssdesc', 1))
		{
		?>
			<?php echo $feed->description; ?>
			<?php
		}
		// Feed image
		if ($params->get('rssimage', 1) && $iUrl) :
		?>
			<img src="<?php echo $iUrl; ?>" alt="<?php echo @$iTitle; ?>"/>
		<?php endif; ?>


	<!-- Show items -->
	<?php if (!empty($feed))
	{ ?>
		<ul class="newsfeed<?php echo $params->get('moduleclass_sfx'); ?>">
		<?php for ($i = 0, $max = min(count($feed), $params->get('rssitems', 5)); $i < $max; $i++) { ?>
			<?php
				$uri   = (!empty($feed[$i]->uri) || !is_null($feed[$i]->uri)) ? trim($feed[$i]->uri) : trim($feed[$i]->guid);
				$uri   = substr($uri, 0, 4) != 'http' ? $params->get('rsslink') : $uri;
				$text  = !empty($feed[$i]->content) ||  !is_null($feed[$i]->content) ? trim($feed[$i]->content) : trim($feed[$i]->description);
				$title = trim($feed[$i]->title);
			?>
				<li>
					<?php if (!empty($uri)) : ?>
						<span class="feed-link">
						<a href="<?php echo htmlspecialchars($uri, ENT_COMPAT, 'UTF-8'); ?>" target="_blank">
						<?php echo $feed[$i]->title; ?></a></span>
					<?php else : ?>
						<span class="feed-link"><?php echo $title; ?></span>
					<?php endif; ?>

					<?php if ($params->get('rssitemdesc') && !empty($text)) : ?>
						<div class="feed-item-description">
						<?php
							// Strip the images.
							$text = JFilterOutput::stripImages($text);

							$text = JHtml::_('string.truncate', $text, $params->get('word_count'));
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

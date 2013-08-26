<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_feed
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
	$lang = JFactory::getLanguage();
	$myrtl = $params->get('rssrtl');
	$direction = " ";
	if ($lang->isRTL() && $myrtl == 0)
	{
		$direction = " redirect-rtl";
	}

	// feed description
	elseif ($lang->isRTL() && $myrtl == 1)
	{
		$direction = " redirect-ltr";
	}

	elseif ($lang->isRTL() && $myrtl == 2)
	{
		$direction = " redirect-rtl";
	}

	elseif ($myrtl == 0)
	{
		$direction = " redirect-ltr";
	}
	elseif ($myrtl == 1)
	{
		$direction = " redirect-ltr";
	}
	elseif ($myrtl == 2)
	{
		$direction = " redirect-rtl";	}
	?>
	<?php
	if ($feed != false)
	{
		//image handling
		$iUrl	= isset($feed->image)	? $feed->image	: null;
		$iTitle = isset($feed->imagetitle) ? $feed->imagetitle : null;
		?>
		<div style="direction: <?php echo $rssrtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $rssrtl ? 'right' :'left'; ?> ! important"  class="feed<?php echo $moduleclass_sfx; ?>">
		<?php
		// feed description
		if (!is_null($feed->title) && $params->get('rsstitle', 1))
		{
			?>
					<h2 class="<?php echo $direction; ?>">
						<a href="<?php echo str_replace('&', '&amp', $feed->link->uri); ?>" target="_blank">
						<?php echo $feed->title; ?></a>
					</h2>
			<?php
		}
		// feed description
		if ($params->get('rssdesc', 1))
		{
		?>
			<?php echo $feed->description; ?>
			<?php
		}
		// feed image
		if ($params->get('rssimage', 1) && $iUrl) :
		?>
			<img src="<?php echo $iUrl; ?>" alt="<?php echo @$iTitle; ?>"/>
		<?php endif; ?>

	<ul class="newsfeed<?php echo $params->get('moduleclass_sfx'); ?>">
	<!-- Show items -->
	<?php if (!empty($feed))
	{ ?>
	<ul>
		<?php for  ($i = 0; $i < $params->get('rssitems', 5); $i++)
		{
			if( !$feed->offsetExists($i)) {
				break;
			}
			?>
			<?php
				$uri = (!empty($feed[$i]->guid) || !is_null($feed[$i]->guid)) ? $feed[$i]->guid : $feed[$i]->uri;
				$uri = substr($uri, 0, 4) != 'http' ? $params->get('rsslink') : $uri;
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

							$text = JHtml::_('string.truncate', $text, $params->get('word_count'));
							echo str_replace('&apos;', "'", $text);
						?>

						</div>
					<?php endif; ?>
					</li>
			<?php } ?>
			</ul>
		</ul>
	</div>
	<?php }
	}
}

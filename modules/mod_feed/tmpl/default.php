<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_feed
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;
?>
<div style="direction: <?php echo $rssrtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $rssrtl ? 'right' :'left'; ?> ! important">
<?php
if ($feed != false)
{
	//image handling
	$iUrl 	= isset($feed->image->url)   ? $feed->image->url   : null;
	$iTitle = isset($feed->image->title) ? $feed->image->title : null;
	?>
	<table cellpadding="0" cellspacing="0" class="moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php
	// feed description
	if (!is_null($feed->title) && $params->get('rsstitle', 1)) {
		?>
		<tr>
			<td>
				<strong>
					<a href="<?php echo str_replace('&', '&amp', $feed->link); ?>" target="_blank">
						<?php echo $feed->title; ?></a>
				</strong>
			</td>
		</tr>
		<?php
	}

	// feed description
	if ($params->get('rssdesc', 1)) {
	?>
		<tr>
			<td><?php echo $feed->description; ?></td>
		</tr>
		<?php
	}

	// feed image
	if ($params->get('rssimage', 1) && $iUrl) {
	?>
		<tr>
			<td><img src="<?php echo $iUrl; ?>" alt="<?php echo @$iTitle; ?>"/></td>
		</tr>
	<?php
	}

	$actualItems = count($feed->items);
	$setItems    = $params->get('rssitems', 5);

	if ($setItems > $actualItems) {
		$totalItems = $actualItems;
	} else {
		$totalItems = $setItems;
	}
	?>
	<tr>
		<td>
			<ul class="newsfeed<?php echo $params->get('moduleclass_sfx'); ?>"  >
			<?php
			$words = $params->def('word_count', 0);
			for ($j = 0; $j < $totalItems; $j ++)
			{
				$currItem = & $feed->items[$j];
				// item title
				?>
				<li>
				<?php
				if (!is_null($currItem->get_link())) {
				?>
					<a href="<?php echo $currItem->get_link(); ?>" target="_blank">
					<?php echo $currItem->get_title(); ?></a>
				<?php
				}

				// item description
				if ($params->get('rssitemdesc', 1))
				{
					// item description
					$text = $currItem->get_description();
					$text = str_replace('&apos;', "'", $text);

					// word limit check
					if ($words)
					{
						$texts = explode(' ', $text);
						$count = count($texts);
						if ($count > $words)
						{
							$text = '';
							for ($i = 0; $i < $words; $i ++) {
								$text .= ' '.$texts[$i];
							}
							$text .= '...';
						}
					}
					?>
					<div style="text-align: <?php echo $params->get('rssrtl', 0) ? 'right': 'left'; ?> ! important" class="newsfeed_item<?php echo $params->get('moduleclass_sfx'); ?>"  >
						<?php echo $text; ?>
					</div>
					<?php
				}
				?>
				</li>
				<?php
			}
			?>
			</ul>
		</td>
		</tr>
	</table>
<?php } ?>
</div>

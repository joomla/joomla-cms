<?php
/**
 * @version		$Id: helper.php 11617 2009-02-07 14:16:17Z kdevine $
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die;

class modFeedHelper
{
	function render($params)
	{
		// module params
		$rssurl				= $params->get('rssurl', '');
		$rssitems			= $params->get('rssitems', 5);
		$rssdesc				= $params->get('rssdesc', 1);
		$rssimage			= $params->get('rssimage', 1);
		$rssitemdesc			= $params->get('rssitemdesc', 1);
		$words				= $params->def('word_count', 0);
		$rsstitle			= $params->get('rsstitle', 1);
		$rssrtl				= $params->get('rssrtl', 0);
		$moduleclass_sfx		= $params->get('moduleclass_sfx', '');

		//  get RSS parsed object
		$options = array();
		$options['rssUrl'] 		= $rssurl;
		if ($params->get('cache')) {
			$options['cache_time']  = $params->get('cache_time', 15) ;
			$options['cache_time']	*= 60;
		} else {
			$options['cache_time'] = null;
		}

		$rssDoc = &JFactory::getXMLparser('RSS', $options);

		if ($rssDoc != false)
		{
			// channel header and link
			$channel['title'] = $rssDoc->get_title();
			$channel['link'] = $rssDoc->get_link();
			$channel['description'] = $rssDoc->get_description();

			// channel image if exists
			$image['url'] = $rssDoc->get_image_url();
			$image['title'] = $rssDoc->get_image_title();

			//image handling
			$iUrl 	= isset($image['url']) ? $image['url'] : null;
			$iTitle = isset($image['title']) ? $image['title'] : null;

			// items
			$items = $rssDoc->get_items();

			// feed elements
			$items = array_slice($items, 0, $rssitems);
			?>
			<table cellpadding="0" cellspacing="0" class="moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
			<?php
			// feed description
			if (!is_null($channel['title']) && $rsstitle) {
			?>
				<tr>
				<td>
					<strong>
						<a href="<?php echo str_replace('&', '&amp;', $channel['link']); ?>" target="_blank">
						<?php echo $channel['title']; ?></a>
					</strong>
				</td>
				</tr>
			<?php
			}

			// feed description
			if ($rssdesc) {
			?>
				<tr>
					<td>
						<?php echo $channel['description']; ?>
					</td>
				</tr>
			<?php
			}

			// feed image
			if ($rssimage && $iUrl) {
			?>
				<tr>
					<td align="center">
						<img src="<?php echo $iUrl; ?>" alt="<?php echo @$iTitle; ?>"/>
					</td>
				</tr>
			<?php
			}

			$actualItems = count($items);
			$setItems = $rssitems;

			if ($setItems > $actualItems) {
				$totalItems = $actualItems;
			} else {
				$totalItems = $setItems;
			}
			?>
			<tr>
			<td>
				<ul class="newsfeed<?php echo $moduleclass_sfx; ?>"  >
				<?php
				for ($j = 0; $j < $totalItems; $j ++)
				{
					$currItem = & $items[$j];
					// item title
					?>
					<li>
					<?php
					if (!is_null($currItem->get_link())) {
					?>
						<a href="<?php echo $currItem->get_link(); ?>" target="_child">
						<?php echo $currItem->get_title(); ?></a>
					<?php
					}

					// item description
					if ($rssitemdesc)
					{
						// item description
						$text = html_entity_decode($currItem->get_description());
						$text = str_replace('&apos;', "'", $text);

						// word limit check
						if ($words) {
							$texts = explode(' ', $text);
							$count = count($texts);
							if ($count > $words) {
								$text = '';
								for ($i = 0; $i < $words; $i ++)
								{
									$text .= ' '.$texts[$i];
								}
								$text .= '...';
							}
						}
						?>
						<div style="text-align: <?php echo $rssrtl ? 'right': 'left'; ?> ! important">
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
		<?php
		}
	}
}

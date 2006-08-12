<?php
/**
* version $Id$
* @package Joomla
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * HTML View class for the Newsfeeds component
 *
 * @static
 * @package Joomla
 * @subpackage Newsfeeds
 * @since 1.0
 */
class NewsfeedsViewNewsfeed
{
	function show( &$newsfeed, &$lists, &$params )
	{
		?>
		<div style="direction: <?php echo $newsfeed->rtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $newsfeed->rtl ? 'right' :'left'; ?>">
		<table width="100%" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php
		if ( $params->get( 'header' ) ) {
			?>
			<tr>
				<td class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>" colspan="2">
				<?php echo $params->get( 'header' ); ?>
				</td>
			</tr>
			<?php
		}
		// feed elements
		$currChannel	= $lists['channel'];
		$image			= $lists['image'];
		$items 			= $lists['items'];
		$descrip 		= 0;
		$iUrl			= 0;

		//image handling
		$iUrl = isset($image['url']) ? $image['url'] : null;
		$iTitle = isset($image['title']) ? $image['title'] : null;
		if ( isset( $currChannel['description']) ) {
			$descrip = 1;
			// hide com_rss descrip in 4.5.0 feeds
			if ( $currChannel['description'] == 'com_rss' ) {
				$descrip = 0;
			}
		}
		// display channel info and image
		?>
		<tr>
			<td class="contentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<a href="<?php echo ampReplace( $currChannel['link'] ); ?>" target="_blank">
			<?php echo str_replace('&apos;', "'", $currChannel['title']); ?>
			</a>
			</td>
		</tr>
		<?php
		// feed description
		if ( $descrip && $params->get( 'feed_descr' ) ) {
			?>
			<tr>
				<td>
				<?php echo str_replace('&apos;', "'", $currChannel['description']); ?>
				<br />
				<br />
				</td>
			</tr>
			<?php
		}
		// feed image
		if ( $iUrl && $params->get( 'feed_image' ) ) {
			?>
			<tr>
				<td>
				<img src="<?php echo $iUrl; ?>" alt="<?php echo $iTitle; ?>" />
				</td>
			</tr>
			<?php
		}

		// determine number of items to display
		$actualItems 	= count( $items );
		$setItems 		= $newsfeed->numarticles;
		if ( $setItems > $actualItems ) {
			$totalItems = $actualItems;
		} else {
			$totalItems = $setItems;
		}
		?>
		<tr>
			<td>
			<?php
			if (count($totalItems)) {
			?>
			<ul>
				<?php

				// display list of channel items
				for ( $j = 0; $j < $totalItems; $j++ ) {
					$currItem =& $items[$j];
					?>
					<li>
						<?php
						if ( !is_null( $currItem['link'] ) ) {
							?>
							<a href="<?php echo ampReplace( $currItem['link'] ); ?>" target="_blank">
								<?php echo $currItem['title']; ?></a>
							<?php
						}
						// Note: enclosure tag removed as not supported by magpierss
						// item description
						if ( $params->get( 'item_descr' ) ) {
							$text   = $currItem['description'];
							$text   = str_replace('&apos;', "'", $text);
							$num 	= $params->get( 'word_count' );

							// word limit check
							if ( $num ) {
								$texts = explode( ' ', $text );
								$count = count( $texts );
								if ( $count > $num ) {
									$text = '';
									for( $i=0; $i < $num; $i++ ) {
										$text .= ' '. $texts[$i];
									}
									$text .= '...';
								}
							}
							?>
							<br />
							<?php echo $text; ?>
							<br />
							<br />
							<?php
						}
						?>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
			}
			?>
			</td>
		</tr>
		<tr>
			<td>
			<br />
			</td>
		</tr>
		<?php
		?>
		</table>
		</div>
		<?php
	}
}
?>
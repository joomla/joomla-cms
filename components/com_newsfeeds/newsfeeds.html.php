<?php
/**
* version $Id$
* @package Joomla
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
* @package Joomla
* @subpackage Newsfeeds
*/
class HTML_newsfeed {

	function displaylist( &$categories, &$rows, $catid, $currentcat=NULL, &$params, $tabclass, &$page ) {
		global $Itemid, $hide_js;

		if ( $params->get( 'page_title' ) ) {
			?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $currentcat->header; ?>
			</div>
			<?php
		}
		?>
		<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php
		if ( @$currentcat->img || @$currentcat->descrip ) {
			?>
			<tr>
				<td valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php
					// show image
					if ( $currentcat->img ) {
						?>
						<img src="<?php echo $currentcat->img; ?>" align="<?php echo $currentcat->align; ?>" hspace="6" alt="<?php echo JText::_( 'Web Links' ); ?>" />
						<?php
					}
					echo $currentcat->descrip;
					?>
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td width="60%" colspan="2">
				<?php
				if ( count( $rows ) ) {
					HTML_newsfeed::showTable( $params, $rows, $catid, $tabclass, $page );
				}
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php
				// Displays listing of Categories
				if ( ( $params->get( 'type' ) == 'category' ) && $params->get( 'other_cat' ) ) {
					HTML_newsfeed::showCategories( $params, $categories, $catid );
				} else if ( ( $params->get( 'type' ) == 'section' ) && $params->get( 'other_cat_section' ) ) {
					HTML_newsfeed::showCategories( $params, $categories, $catid );
				}
				?>
			</td>
		</tr>
		</table>
		<?php
	}

	/**
	* Display Table of items
	*/
	function showTable( &$params, &$rows, $catid, $tabclass, &$page ) {
		global $Itemid;

		// icon in table display
		$img = mosAdminMenus::ImageCheck( 'con_info.png', '/images/M_images/', $params->get( 'icon' ) );
		?>
		<form action="index.php" method="post" name="adminForm">

		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php
		if ($params->get('display')) {
			?>
			<tr>
				<td align="right" colspan="4">
					<?php
					echo JText::_('Display Num') .'&nbsp;';
					$link = "index.php?option=com_newsfeeds&amp;catid=$catid&amp;Itemid=$Itemid";
					echo $page->getLimitBox($link);
					?>
				</td>
			</tr>
			<?php
		}
		?>
		<?php
		if ( $params->get( 'headings' ) ) {
			?>
			<tr>
				<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="5">
					<?php echo JText::_('Num'); ?>
				</td>
				<?php
				if ( $params->get( 'name' ) ) {
					?>
					<td height="20" width="90%" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
						<?php echo JText::_( 'Feed Name' ); ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $params->get( 'articles' ) ) {
					?>
					<td height="20" width="10%" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" align="center" nowrap="nowrap">
						<?php echo JText::_( 'Num Articles' ); ?>
					</td>
					<?php
				}
				?>
			</tr>
			<?php
		}

		$k = 0;
		$i = 1;
		foreach ($rows as $row) {
			$link = 'index.php?option=com_newsfeeds&amp;task=view&amp;feedid='. $row->id .'&amp;Itemid='. $Itemid;
			?>
			<tr class="<?php echo $tabclass[$k]; ?>">
				<td align="center" width="5">
					<?php echo $i; ?>
				</td>
				<?php
				if ( $params->get( 'name' ) ) {
					?>
					<td height="20" width="90%">
						<a href="<?php echo sefRelToAbs( $link ); ?>" class="category<?php echo $params->get( 'pageclass_sfx' ); ?>">
							<?php echo $row->name; ?></a>
					</td>
					<?php
				}
				?>
				<?php
				if ( $params->get( 'articles' ) ) {
					?>
					<td height="20" width="10%" align="center">
						<?php echo $row->numarticles; ?>
					</td>
					<?php
				}
				?>
			</tr>
			<?php
			$k = 1 - $k;
			$i++;
		}
		?>
		<tr>
			<td align="center" colspan="4" class="sectiontablefooter<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php
				$link = "index.php?option=com_weblinks&amp;catid=$catid&amp;Itemid=$Itemid";
				echo $page->writePagesLinks($link);
				?>
			</td>
		</tr>
		<tr>
			<td colspan="4" align="right">
				<?php echo $page->writePagesCounter(); ?>
			</td>
		</tr>
		</table>

		</form>
		<?php
	}

	/**
	* Display links to categories
	*/
	function showCategories( &$params, &$categories, $catid ) {
		global $Itemid;

		if (count($categories)) {
			?>
			<ul>
			<?php
			foreach ( $categories as $cat ) {
				if ( $catid == $cat->catid ) {
					?>
					<li>
						<b>
						<?php echo $cat->title;?>
						</b>
						&nbsp;
						<span class="small">
						(<?php echo $cat->numlinks;?>)
						</span>
					</li>
					<?php
				} else {
					$link = 'index.php?option=com_newsfeeds&amp;catid='. $cat->catid .'&amp;Itemid='. $Itemid;
					?>
					<li>
						<a href="<?php echo sefRelToAbs( $link ); ?>" class="category<?php echo $params->get( 'pageclass_sfx' ); ?>">
						<?php echo $cat->title;?>
						</a>
						<?php
						if ( $params->get( 'cat_items' ) ) {
							?>
							&nbsp;
							<span class="small">
							(<?php echo $cat->numlinks;?>)
							</span>
							<?php
						}
						?>
						<?php
						// Writes Category Description
						if ( $params->get( 'cat_description' ) && $cat->description ) {
							echo '<br />';
							echo $cat->description;
						}
						?>
					</li>
					<?php
				}
			}
			?>
			</ul>
			<?php
		}
	}


	/**
	* Display feed header and items
	*/
	function showNewsfeeds( &$newsfeed, &$lists, &$params ) {
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
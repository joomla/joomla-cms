<?php
/**
* version $Id$
* @package Joomla
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
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

	function displaylist( &$categories, &$rows, $catid, $currentcat=NULL, &$params, $tabclass ) {
		global $Itemid, $hide_js;

		if ( $params->get( 'page_title' ) ) {
			?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $currentcat->header; ?>
			</div>
			<?php
		}
		?>
		<form action="index.php" method="post" name="adminForm">

		<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td width="60%" valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>" colspan="2">
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
		<tr>
			<td>
			<?php
			if ( count( $rows ) ) {
				HTML_newsfeed::showTable( $params, $rows, $catid, $tabclass );
			}
			?>
			</td>
		</tr>
		<tr>
			<td>&nbsp;

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
		</form>
		<?php
		// displays back button
		mosHTML::BackButton ( $params, $hide_js );
	}

	/**
	* Display Table of items
	*/
	function showTable( &$params, &$rows, $catid, $tabclass ) {
		global $Itemid;

		// icon in table display
		$img = mosAdminMenus::ImageCheck( 'con_info.png', '/images/M_images/', $params->get( 'icon' ) );
		?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
		<?php
		if ( $params->get( 'headings' ) ) {
			?>
			<tr>
				<?php
				if ( $params->get( 'name' ) ) {
					?>
					<td height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php echo JText::_( 'Feed Name' ); ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $params->get( 'articles' ) ) {
					?>
					<td height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" align="center">
					<?php echo JText::_( 'Num Articles' ); ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $params->get( 'link' ) ) {
					?>
					<td height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php echo JText::_( 'Feed Link' ); ?>
					</td>
					<?php
				}
				?>
				<td width="100%" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>"></td>
			</tr>
			<?php
		}

		$k = 0;
		foreach ($rows as $row) {
			$link = 'index.php?option=com_newsfeeds&amp;task=view&amp;feedid='. $row->id .'&amp;Itemid='. $Itemid;
			?>
			<tr>
				<?php
				if ( $params->get( 'name' ) ) {
					?>
					<td width="30%" height="20" class="<?php echo $tabclass[$k]; ?>">
					<a href="<?php echo sefRelToAbs( $link ); ?>" class="category<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php echo $row->name; ?>
					</a>
					</td>
					<?php
				}
				?>
				<?php
				if ( $params->get( 'articles' ) ) {
					?>
					<td width="20%" class="<?php echo $tabclass[$k]; ?>" align="center">
					<?php echo $row->numarticles; ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $params->get( 'link' ) ) {
					?>
					<td width="50%" class="<?php echo $tabclass[$k]; ?>">
					<?php echo ampReplace( $row->link ); ?>
					</td>
					<?php
				}
				?>
				<td width="100%"></td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
		<?php
	}

	/**
	* Display links to categories
	*/
	function showCategories( &$params, &$categories, $catid ) {
		global $Itemid;
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
					if ( $params->get( 'cat_description' ) ) {
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


	function showNewsfeeds( &$newsfeeds, $LitePath, $cacheDir, &$params ) {
		?>
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

		foreach ( $newsfeeds as $newsfeed ) {
			// full RSS parser used to access image information
			$rssDoc =& JFactory::getXMLParser('RSS');
			$rssDoc->useCacheLite( true, $LitePath, $cacheDir, $newsfeed->cache_time );
			$rssDoc->loadRSS( $newsfeed->link );
			$totalChannels = $rssDoc->getChannelCount();

			for ( $i = 0; $i < $totalChannels; $i++ ) {
				$currChannel	=& $rssDoc->getChannel($i);
				$elements 		= $currChannel->getElementList();
				$descrip 		= 0;
				$iUrl			= 0;
				foreach ( $elements as $element ) {
					//image handling
					if ( $element == 'image' ) {
						$image =& $currChannel->getElement( DOMIT_RSS_ELEMENT_IMAGE );
						$iUrl	= $image->getUrl();
						$iTitle	= $image->getTitle();
					}
					if ( $element == 'description' ) {
						$descrip = 1;
						// hide com_rss descrip in 4.5.0 feeds
						if ( $currChannel->getDescription() == 'com_rss' ) {
							$descrip = 0;
						}
					}
				}
				?>
				<tr>
					<td class="contentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<a href="<?php echo ampReplace( $currChannel->getLink() ); ?>" target="_blank">
					<?php echo str_replace('&apos;', "'", $currChannel->getTitle()); ?>
					</a>
					</td>
				</tr>
				<?php
				// feed description
				if ( $descrip && $params->get( 'feed_descr' ) ) {
					?>
					<tr>
						<td>
						<?php echo str_replace('&apos;', "'", $currChannel->getDescription()); ?>
						<br /><br />
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
				$actualItems 	= $currChannel->getItemCount();
				$setItems 		= $newsfeed->numarticles;
				if ( $setItems > $actualItems ) {
					$totalItems = $actualItems;
				} else {
					$totalItems = $setItems;
				}
				?>
				<tr>
					<td>
					<ul>
					<?php
					for ( $j = 0; $j < $totalItems; $j++ ) {
						$currItem =& $currChannel->getItem($j);

						// START fix for RSS enclosure tag url not showing
						if ($currItem->getLink()) {
						?>
							<a href="<?php echo $currItem->getLink(); ?>" target="_child">
							<?php echo $currItem->getTitle(); ?>
							</a>
						<?php
						} else if ($currItem->getEnclosure()) {
							$enclosure = $currItem->getEnclosure();
							$eUrl	= $enclosure->getUrl();
						?>
							<a href="<?php echo $eUrl; ?>" target="_child">
							<?php echo $currItem->getTitle(); ?>
							</a>
						<?php
						}  else if (($currItem->getEnclosure()) && ($currItem->getLink())) {
							$enclosure = $currItem->getEnclosure();
							$eUrl	= $enclosure->getUrl();
						?>
							<a href="<?php $currItem->getLink(); ?>" target="_child">
							<?php echo $currItem->getTitle(); ?>
							</a><br>
							Link: <a href="<?php echo $eUrl; ?>" target="_child">
							<?php echo $eUrl; ?>
							</a>
						<?php
						}
						// END fix for RSS enclosure tag url not showing

						// item description
						if ( $params->get( 'item_descr' ) ) {
							$text   = $currItem->getDescription();
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
							<br/><br/>
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
				<tr>
					<td>
					<br />
					</td>
				</tr>
				<?php
			}
		}
		?>
		</table>
		<?php
		// displays back button
		mosHTML::BackButton ( $params );
	}
}
?>

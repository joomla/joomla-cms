<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Search
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Search
*/
class search_html {

	function openhtml( $params ) {
		if ( $params->get( 'page_title' ) ) {
			?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php echo $params->get( 'header' ); ?>
			</div>
			<?php
		}
	}

	function searchbox( $searchword, &$lists, $params ) {
		global $Itemid;
		?>
		<form action="index.php" method="get">
		<input type="hidden" name="option" value="com_search" />
		<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
		<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<tr>
				<td nowrap="nowrap">
					<label for="search_searchword">
						<?php echo _PROMPT_KEYWORD; ?>:
					</label>
				</td>
				<td nowrap="nowrap">
					<input type="text" name="searchword" id="search_searchword" size="30" maxlength="20" value="<?php echo stripslashes($searchword);?>" class="inputbox" />
				</td>
				<td width="100%" nowrap="nowrap">
					<input type="submit" name="submit" value="<?php echo _SEARCH_TITLE;?>" class="button" />
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<?php echo $lists['searchphrase']; ?>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<label for="search_ordering">
						<?php echo _CMN_ORDERING;?>:
					</label>
					<?php echo $lists['ordering'];?>
				</td>
			</tr>
		</table>
		</form>
		<?php
	}

	function searchintro( $searchword, $params ) {
		?>
		<table class="searchintro<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td colspan="3" >
			<?php echo _PROMPT_KEYWORD . ' <b>' . stripslashes($searchword) . '</b>'; ?>
		<?php
	}

	function message( $message, $params ) {
		?>
		<table class="searchintro<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td colspan="3" >
			<?php eval ('echo "'.$message.'";'); ?>
			</td>
		</tr>
		</table>
		<?php
	}

	function displaynoresult() {
		?>
			</td>
		</tr>
		<?php
	}

	function display( &$rows, $params, $pageNav, $limitstart, $limit, $total, $totalRows, $searchword ) {
		global $mosConfig_hideCreateDate;
		global $mosConfig_live_site, $option, $Itemid;


		$c = count ($rows);

				// number of matches found
				echo '<br/>';
				eval ('echo "'._CONCLUSION.'";');

				$image = mosAdminMenus::ImageCheck( 'google.png', '/images/M_images/', NULL, NULL, 'Google', 'Google', 1 );
				?>
				<a href="http://www.google.com/search?q=<?php echo stripslashes($searchword);?>" target="_blank">
				<?php echo $image; ?>
				</a>
			</td>
		</tr>
		</table>
		<br />
		<div align="center">
			<?php

			$searchphrase = trim( strtolower( mosGetParam( $_REQUEST, 'searchphrase', 'any' ) ) );
			$ordering = trim( strtolower( mosGetParam( $_REQUEST, 'ordering', 'newest' ) ) );

			$link = $mosConfig_live_site ."/index.php?option=$option&amp;Itemid=$Itemid&amp;searchword=$searchword&amp;searchphrase=$searchphrase&amp;ordering=$ordering";
			echo $pageNav->getLimitBox( $link );
			echo "<br />\n";
			echo $pageNav->writePagesCounter();
			?>
		</div>
		<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr class="<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<td>
				<?php
				$z		= $limitstart + 1;
				$end 	= $limit + $z;
				if ( $end > $total ) {
					$end = $total + 1;
				}
				for( $i=$z; $i < $end; $i++ ) {
					$row = $rows[$i-1];
					if ($row->created) {
						$created = mosFormatDate ($row->created, _DATE_FORMAT_LC);
					} else {
						$created = '';
					}
					?>
					<fieldset>
						<div>
							<span class="small<?php echo $params->get( 'pageclass_sfx' ); ?>">
								<?php echo $i.'. ';?>
							</span>
							<?php
							if ( $row->href ) {
								$row->href = ampReplace( $row->href );
								if ($row->browsernav == 1 ) {
									?>
									<a href="<?php echo sefRelToAbs($row->href); ?>" target="_blank">
									<?php
								} else {
									?>
									<a href="<?php echo sefRelToAbs($row->href); ?>">
									<?php
								}
							}

							echo $row->title;

							if ( $row->href ) {
								?>
								</a>
								<?php
							}
							if ( $row->section ) {
								?>
								<br/>
								<span class="small<?php echo $params->get( 'pageclass_sfx' ); ?>">
									(<?php echo $row->section; ?>)
								</span>
								<?php
							}
							?>
						</div>

						<div>
							<?php echo ampReplace( $row->text );?>
						</div>

						<?php
						if ( !$mosConfig_hideCreateDate ) {
							?>
							<div class="small<?php echo $params->get( 'pageclass_sfx' ); ?>">
								<?php echo $created; ?>
							</div>
							<?php
						}
						?>
					</fieldset>
					<br/>
					<?php
				}
				?>
			</td>
		<?php
	}

	function conclusion( $totalRows, $searchword, $pageNav ) {
		global $mosConfig_live_site, $option, $Itemid;
		?>
		<tr>
			<td colspan="3">
				<div align="center">
					<?php

				$searchphrase = trim( strtolower( mosGetParam( $_REQUEST, 'searchphrase', 'any' ) ) );
				$ordering = trim( strtolower( mosGetParam( $_REQUEST, 'ordering', 'newest' ) ) );

				$link = $mosConfig_live_site ."/index.php?option=$option&Itemid=$Itemid&searchword=$searchword&searchphrase=$searchphrase&ordering=$ordering";

				echo $pageNav->writePagesLinks( $link );
					?>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="3">

			</td>
		</tr>
		</table>
		<?php
	}
}
?>
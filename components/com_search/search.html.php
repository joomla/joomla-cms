<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Search
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
	/**
	 * @param string
	 * @param array
	 * @param object
	 * @param array Array of the selected areas
	 */
	function searchbox( $searchword, &$lists, $params, &$areas ) {
		global $Itemid;

		$showAreas = mosGetParam( $lists, 'areas', array() );
		$allAreas = array();
		foreach ($showAreas as $area) {
			$allAreas = array_merge( $allAreas, $area );
		}

		?>
		<form action="index.php" method="get">
		
		<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td nowrap="nowrap">
				<label for="search_searchword">
					<?php echo JText::_( 'Search Keyword' ); ?>:
				</label>
			</td>
			<td nowrap="nowrap">
				<input type="text" name="searchword" id="search_searchword" size="30" maxlength="20" value="<?php echo stripslashes($searchword);?>" class="inputbox" />
			</td>
			<td width="100%" nowrap="nowrap">
				<input type="submit" name="submit" value="<?php echo JText::_( 'Search' );?>" class="button" />
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<?php echo $lists['searchphrase']; ?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<label for="ordering">
					<?php echo JText::_( 'Ordering' );?>:
				</label>
				<?php echo $lists['ordering'];?>
			</td>
		</tr>
		</table>
		
		<?php
		if ($params->get( 'search_areas', 1 )) {
			?>
			<?php echo JText::_( 'Search Only' );?>:
			<?php
			$hasAreas = is_array( $areas );
			foreach ($allAreas as $val => $txt) {
				$checked = $hasAreas && in_array( $val, $areas ) ? 'checked="true"' : '';
				?>
				<input type="checkbox" name="areas[]" value="<?php echo $val;?>" id="area_<?php echo $val;?>" <?php echo $checked;?> />
				<label for="area_<?php echo $val;?>">
					<?php echo $txt;?>
				</label>
				<?php
			}
		} 
		?>
		<input type="hidden" name="option" value="com_search" />
		<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
		</form>
		<?php
	}

	function searchintro( $searchword, $params ) {
		?>
		<table class="searchintro<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td colspan="3" >
				<?php echo JText::_( 'Search Keyword' ) .' <b>'. stripslashes($searchword) .'</b>'; ?>
		<?php
	}

	/**
	 * Displays no result information
	 * 
	 * @static 
	 * @return void
	 */
	function displaynoresult() {
		?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Displays the search result
	 *
	 * @static 
	 * @param array $rows
	 * @param object $params
	 * @param object $pageNav
	 * @param int $limitstart
	 * @param int $limit
	 * @param int $total
	 * @param int $totalRows
	 * @param string $searchword
	 * @return void
	 * @since 1.0
	 */
	function display( &$rows, $params, $pageNav, $limitstart, $limit, $total, $totalRows, $searchword ) {
		global $mosConfig_hideCreateDate;
		global $option, $Itemid;

		$c = count ($rows);

				// number of matches found
				echo '<br />';
            	$strResult = sprintf( JText::_( 'TOTALRESULTSFOUND' ), $totalRows, $searchword );
				eval ('echo "'. $strResult .'";');

				$image = mosAdminMenus::ImageCheck( 'google.png', '/images/M_images/', NULL, NULL, 'Google', 'Google', 1 );
				?>
				<a href="http://www.google.com/search?q=<?php echo stripslashes($searchword);?>" target="_blank">
					<?php echo $image; ?></a>
			</td>
		</tr>
		</table>
		
		<br />
		
		<div align="center">
			<?php

			$searchphrase = trim( strtolower( JRequest::getVar( 'searchphrase', 'any' ) ) );
			$ordering = trim( strtolower( JRequest::getVar( 'ordering', 'newest' ) ) );

			$link = "index.php?option=$option&amp;Itemid=$Itemid&amp;searchword=$searchword&amp;searchphrase=$searchphrase&amp;ordering=$ordering";
			?>
			<div style="float: right;">
				<label for="limit">			
					<?php echo JText::_( 'Display Num' ); ?>
				</label>
				<?php echo $pageNav->getLimitBox( $link ); ?>
			</div>
			<div>
				<?php echo $pageNav->writePagesCounter(); ?>
			</div>
		</div>
		
		<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
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
						$created = mosFormatDate ( $row->created, JText::_( 'DATE_FORMAT_LC' ) );
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
								<br />
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
					
					<br />
					<?php
				}
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Writes search conclusion
	 *
	 * @static 
	 * @param int $totalRows
	 * @param string $searchword
	 * @param obj $pageNav
	 * @return void
	 * @since 1.0
	 */
	function conclusion( $totalRows, $searchword, $pageNav ) {
		global $option, $Itemid;
		?>
		<tr>
			<td colspan="3">
				<div align="center">
					<?php
					$searchphrase = trim( strtolower( JRequest::getVar( 'searchphrase', 'any' ) ) );
					$ordering = trim( strtolower( JRequest::getVar( 'ordering', 'newest' ) ) );
	
					$link = "index.php?option=$option&Itemid=$Itemid&searchword=$searchword&searchphrase=$searchphrase&ordering=$ordering";
	
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
	

	/**
	 * Shows a message output within a table
	 *
	 * @static 
	 * @param string $message
	 * @param object $params
	 * @return void
	 * @since 1.0
	 * @deprecated 1.5
	 */
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
	
	/**
	 * Method to show an empty container if there is no data to display
	 * 
	 * @static
	 * @param string $msg The message to show
	 * @return void
	 * @since 1.5
	 */
	function emptyContainer($msg) {
		echo '<p>'.$msg.'</p>';
	}
	
	/**
	 * Writes a user input error message and if javascript is enabled goes back
	 * to the previous screen to try again.
	 * 
	 * @param string $msg The error message to display
	 * @return void
	 * @since 1.5
	 */
	function userInputError($msg) {
		josErrorAlert($msg);
	}
}
?>
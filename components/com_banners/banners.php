<?php
/**
* @version $Id: banners.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Banners
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Banners
 * @subpackage Banners
 */
class bannersTasks_Front extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function bannersTasks_Front() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_templates', 'manage' );
	}

	function view() {
		global $database;

		$query = "SELECT COUNT(*) AS numrows"
		. "\n FROM #__banner"
		. "\n WHERE showBanner = 1"
		;
		$database->setQuery( $query );
		$numrows = $database->loadResult();
		if ( $numrows === null ) {
			mosErrorAlert( $database->stderr() );
		}

		// randomizing code
		if ( $numrows > 1 ) {
			mt_srand( (double) microtime()*1000000 );
			$bannum = mt_rand( 0, --$numrows );
		} else {
			$bannum = 0;
		}

		$banner = null;
		$query = "SELECT *"
		. "\n FROM #__banner"
		. "\n WHERE showBanner = 1"
		;
		$database->setQuery( $query, $bannum, 1 );
		$database->loadObject( $banner );

		if ( $banner ) {
			$query = "UPDATE #__banner"
			. "\n SET impmade = impmade + 1"
			. "\n WHERE bid = '$banner->bid'"
			;
			$database->setQuery( $query );
			if(!$database->query()) {
				mosErrorAlert( $database->stderr() );
			}

			// impression count increase
			$banner->impmade++;

			if ( $numrows > 0 ) {
				// Check if this impression is the last one and print the banner
				if ( $banner->imptotal == $banner->impmade ) {
					$query = "INSERT INTO #__bannerfinish"
					. "\n ( cid, type, name, impressions, clicks, imageurl, datestart, dateend )"
					. "\n VALUES ( '$banner->cid', '$banner->type', '$banner->name', '$banner->impmade', '$banner->clicks', '$banner->imageurl', '$banner->date', now() )"
					;
					$database->setQuery($query);
					if(!$database->query()) {
						die($database->stderr(true));
					}

					$query = "DELETE FROM #__banner"
					. "\n WHERE bid = $banner->bid"
					;
					$database->setQuery( $query );
					if( !$database->query() ) {
						mosErrorAlert( $database->stderr() );
					}
				}

				mosFS::load( '@front_html' );

				bannerScreens_front::item( $banner );
			}
		}
	}

	/**
	/* Function to redirect the clicks to the correct url and add 1 click
	*/
	function click() {
		global $database, $mainframe;

		$bid = intval( mosGetParam( $_REQUEST, 'bid', 0 ) );

		mosFS::load( '@class' );

		$row = new mosBanner($database);
		$row->load( $bid );

		$row->clicks();

		$pat = "http.*://";
		if (!eregi( $pat, $row->clickurl )) {
			$clickurl = 'http://'. $row->clickurl;
		} else {
			$clickurl = $row->clickurl;
		}

		mosRedirect( $clickurl );
	}
}

$tasker = new bannersTasks_Front();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();
?>
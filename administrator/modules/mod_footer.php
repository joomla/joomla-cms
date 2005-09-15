<?php
/**
* @version $Id: mod_footer.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

global $tstart;

mosFS::load( 'includes/footer.php' );

?>
<div class="small" align="center">
<?php
// display page generation time
if ( !empty( $tstart ) ) {
	$tend 		= mosProfiler::getmicrotime();
	$totaltime 	= ($tend - @$tstart);
	printf ( $_LANG->_( 'Page was generated in' ) ." %f ". $_LANG->_( 'seconds' ), $totaltime );
}
?>
</div>
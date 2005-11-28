<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Wrapper
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
* @subpackage Wrapper
*/
class HTML_wrapper {

	function displayWrap( &$row, &$params, &$menu ) {
		?>
		<script language="javascript" type="text/javascript">
		function iFrameHeight() {
			var h = 0;
			if ( !document.all ) {
				h = document.getElementById('blockrandom').contentDocument.height;
				document.getElementById('blockrandom').style.height = h + 60 + 'px';
			} else if( document.all ) {
				h = document.frames('blockrandom').document.body.scrollHeight;
				document.all.blockrandom.style.height = h + 20 + 'px';
			}
		}
		</script>
		<div class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">

		<?php
		if ( $params->get( 'page_title' ) ) {
			?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $params->get( 'header' ); ?>
			</div>
			<?php
		}
		?>
		<iframe
		<?php echo $row->load; ?>
		id="blockrandom"
		name="iframe"
		src="<?php echo $row->url; ?>"
		width="<?php echo $params->get( 'width' ); ?>"
		height="<?php echo $params->get( 'height' ); ?>"
		scrolling="<?php echo $params->get( 'scrolling' ); ?>"
		align="top"
		frameborder="0"
		class="wrapper<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php echo JText::_( 'NO_IFRAMES' ); ?>
		</iframe>

		</div>
		<?php
		// displays back button
		mosHTML::BackButton ( $params );
	}
}
?>

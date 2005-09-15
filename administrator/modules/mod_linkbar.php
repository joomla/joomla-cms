<?php
/**
* @version $Id: mod_linkbar.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Base class for Linkbar
 * @package Joomla
 */
class mosLinkBar extends mosAbstractTasker {
	/** @var array An array of data for the links */
	var $_links=null;

	/**
	 * Adds a link
	 * @param string The text to display
	 * @param string The href for the link
	 * @param string The title attribute for the link
	 */
 	function addLink( $text, $href='', $title='' ) {
  		$this->_links[] = array(
  			'text' => $text,
 			'href' => $href,
 			'title' => $title
  			);
	}

	/**
	 * Displays the Linkbar
	 */
	function display() {
 		if (is_array( $this->_links ) && count( $this->_links ) > 0) {
 			?>
 			<ul id="linkbar">
 			<?php
  			foreach ($this->_links as $i => $link) {
 				// title attrib for a tag uses text param value unless a title param value exists
 				$title = $link['text'];
 				if ( $link['title'] ) {
 					$title = $link['title'];
 				}
 				?>
 				<li>
 				<a id="a<?php echo $i; ?>" href="<?php echo  $link['href']; ?>" title="<?php echo $title; ?>">
 				<?php echo $link['text']; ?></a>
 				</li>
 				<?php
  			}
 			?>
 			</ul>
 			<?php
  		}
  	}
}

// include the linkbar file if available
if ( $path = $mainframe->getPath( 'linkbar' ) ) {
	include_once( $path );
}
?>
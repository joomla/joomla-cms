<?php
/**
* @version $Id: linkbar.sections.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Sections
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Linkbar for Sections Component
 * @package Mambo
 * @subpackage Sections
 */
class sectionsLinkbar extends mosLinkbar {
	/**
	 * Constructor
	 */
	function sectionsLinkbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'defaultOptions' );
	}

	function defaultOptions() {
		if ( mosGetParam( $_REQUEST, 'task', '' ) == '' ) {
			global $_LANG;
			global $my;

			$this->addLink( $_LANG->_( 'Content' ), 'index2.php?option=com_content', $_LANG->_( 'Content Items Manager' ) );
			$this->addLink( $_LANG->_( 'Categories' ), 'index2.php?option=com_categories&amp;section=content', $_LANG->_( 'Categories Manager' ) );
			$this->addLink( $_LANG->_( 'Media' ), 'index2.php?option=com_media', $_LANG->_( 'Media Manager' ) );
			if ( $my->usertype != 'Manager' && $my->usertype != 'manager' ) {
				$this->addLink( $_LANG->_( 'Trash' ), 'index2.php?option=com_trash', $_LANG->_( 'Trash Manager' ) );
			}
		}
	}
}

/*
$linkBar = new sectionsLinkbar();
$linkBar->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$linkBar->display();
unset( $linkBar );
*/
?>
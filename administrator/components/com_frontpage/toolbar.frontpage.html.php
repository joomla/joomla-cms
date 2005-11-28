<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Content
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
* @subpackage Content
*/
class TOOLBAR_FrontPage {
	function _DEFAULT() {

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Frontpage Manager' ), 'frontpage.png' );
		mosMenuBar::archiveList();
		mosMenuBar::spacer();
		mosMenuBar::publishList();
		mosMenuBar::spacer();
		mosMenuBar::unpublishList();
		mosMenuBar::spacer();
		mosMenuBar::custom('remove','delete.png','delete_f2.png',JText::_( 'Remove' ), true);
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.frontpage' );
		mosMenuBar::endTable();
	}
}
?>
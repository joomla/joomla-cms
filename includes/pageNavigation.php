<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.presentation.pagination');

/**
* Legacy class, derive from JPagination instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
* @subpackage 1.1
*/
class mosPageNav extends JPagination {

	function mosPageNav( $total, $limitstart, $limit ) {
		parent::__construct($total, $limitstart, $limit);
	}
}
?>

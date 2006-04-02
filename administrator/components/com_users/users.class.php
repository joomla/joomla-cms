<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Users
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.presentation.parameter.parameter');

/**
 * Legacy class, use JParameter instead
 * @deprecated As of version 1.1
 */
class mosUserParameters extends JParameter {
	function __construct($text, $file = '', $type = 'component') {
		parent::__construct($text, $file);
	}
}

?>
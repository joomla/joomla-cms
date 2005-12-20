<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.installer.module');

/**
 * Legacy class, use JInstallerModule instead
 * @deprecated As of version 1.1
 */
class mosInstallerModule extends JInstallerModule
{
	function __construct() {
		parent::__construct();
	}
}
?>

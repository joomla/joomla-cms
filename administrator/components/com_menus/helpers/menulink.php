<?php
/**
 * @version $Id: admin.menus.php 3504 2006-05-15 05:25:43Z eddieajau $
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * @package Joomla
 * @subpackage Menus
 * @author Louis Landry <louis.landry@joomla.org>
 */
class JMenuHelperMenulink extends JObject
{
	var $_parent = null;

	function __construct(&$parent)
	{
		$this->_parent =& $parent;
		$app =& $this->_parent->getApplication();
		$option = $app->getUserStateFromRequest('menuwizard.menulink.menu', 'menu');
	}

	function loadXML()
	{
		$path = dirname(__FILE__).DS.'xml'.DS.'menulink.xml';
		$this->_parent->_wizard->loadXML($path, 'control');
	}

	/**
	 * @param string A params string
	 * @param string The option
	 */
	function &getFinalized( &$vals, $step )
	{
		$final = new stdClass();
		$final->values =& $vals;
		$final->message = null;
		$final->menutype = 'menulink';
		$final->link = $this->_url;
		$final->type = null;
		$final->componentid = null;
		$final->params =& $vals;
		$final->mvcrt = 0;

		return $final;
	}
}
?>
<?php
/**
 * @version $Id: admin.menus.php 3504 2006-05-15 05:25:43Z eddieajau $
 * @package Joomla
 * @subpackage Content
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
class JContentHelperLinkToMenu extends JObject
{
	var $_parent = null;

	var $_type = null;

	var $_url = null;

	function __construct(&$parent)
	{
		$this->_parent =& $parent;
	}

	/**
	 * Initializes the helper class with the wizard object and loads the wizard xml.
	 * 
	 * @param object JWizard
	 */
	function init(&$wizard)
	{
		$app =& $this->_parent->getApplication();
		$this->_wizard =& $wizard;
		$this->_url = $app->getUserStateFromRequest('menuwizard.url.url', 'url');

		$this->loadXML();
	}

	/**
	 * Sets the wizard object for the helper class
	 * 
	 * @param object JWizard
	 */
	function setWizard(&$wizard)
	{
		$this->_wizard =& $wizard;
	}

	function loadXML()
	{
		$path = dirname(__FILE__).DS.'xml'.DS.'url.xml';
		$this->_wizard->loadXML($path, 'control');
	}

	/**
	 * Returns the wizard name
	 * @return string
	 */
	function getWizardName()
	{
		return 'content.linktomenu';
	}

	/**
	 * @param string A params string
	 * @param string The option
	 */
	function &getConfirmation()
	{
		$values	=& $this->_wizard->getConfirmation();

		$final['type']	= 'url';
		$final['url']	= $this->_url;
		$final['menu_type']	= $this->_type;

		return $final;
	}
}
?>
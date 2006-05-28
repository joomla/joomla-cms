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
class JMenuHelperUrl extends JObject
{
	/**
	 * @var string The component file name
	 */
	var $_url = null;

	var $_metadata;

	var $_app = null;

	var $_steps = array( 1 => 'Parameters');

	function __construct(&$app)
	{
		$this->_app =& $app;
		$this->_menu = $app->getUserStateFromRequest('menuwizard.url.url', 'uri');

		// load the xml metadata
		$this->_metadata = null;
		$path = dirname(__FILE__).DS.'xml'.DS.'url.xml';
		if (file_exists( $path )) {
			$xml = & JFactory::getXMLParser('Simple');

			if ($xml->loadFile($path)) {
				$this->_metadata = &$xml;
			}
		}
	}

	/**
	 * Returns the option
	 * @return string
	 */
	function getSteps()
	{
		return $this->_steps;
	}

	/**
	 * Gets the componet table object related to this menu item
	 */
	function &getParams(&$vals, $step)
	{
		$params = new JParameter('');
		$params->loadArray($vals);

		if ($xmlDoc =& $this->_getMetadataDoc()) {
			if ($cParams = $xmlDoc->getElementByPath( 'control/params' )) {
				$params->setXML( $cParams );
			}
		}
		return $params;
	}

	/**
	 * @param string A params string
	 * @param string The option
	 */
	function &getFinalized( &$vals, $step )
	{
		$final = new stdClass();
		$final->values =& $vals;
		$final->menutype = 'url';
		$final->link = $this->_url;
		$final->type = null;
		$final->componentid = null;
		$final->params =& $vals;
		$final->mvcrt = 0;

		return $final;
	}

	/**
	 * @access private
	 */
	function &_getMetadataDoc()
	{
		$result = null;
		if (isset( $this->_metadata->document )) {
			$result = &$this->_metadata->document;
		}
		return $result;
	}
}
?>
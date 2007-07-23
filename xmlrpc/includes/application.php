<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Installation
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Joomla! XML-RPC Application class
*
* Provide many supporting API functions
*
* @package		Joomla
* @final
*/
class JXMLRPC extends JApplication
{
	/**
	 * The url of the site
	 *
	 * @var string
	 * @access protected
	 */
	var $_siteURL = null;

	/**
	 * The encoding (default: UTF-8)
	 *
	 * @var string
	 * @access protected
	 */
	var $_encoding = null;

	function __construct()
	{
		$url = JURI::base();
		$url = str_replace('xmlrpc/', '', $url);

		$this->_siteURL = $url;
		$this->_encoding = "UTF-8";
	}

	/**
	 * Get the charset encoding
	 *
	 * @return string the charset encoding
	 * @since 1.5
	 */
	function getEncoding() {
		return $this->_encoding;
	}

	/**
	 * Set the charset encoding
	 *
	 * @var $encoding The encoding of the charset
	 */
	function setEncoding($encoding) {
		$this->_encoding = $encoding;
	}

	/**
	 * Get the url of the site
	 *
	 * @return string The site URL
	 * @since 1.5
	 */
	function getSiteURL()
	{
		return $this->_siteURL;
	}
}
?>
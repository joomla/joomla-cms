<?php
/**
* @version $Id$
* @package Joomla.Framework
* @subpackage Document
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * DocumentPDF class, provides an easy interface to parse and display a pdf document
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */

class JDocumentPDF extends JDocument
{
	/**
	 * Class constructore
	 *
	 * @access protected
	 * @param	array	$options Associative array of options
	 */
	function __construct($options = array())
	{
		parent::__construct($options);

		//set mime type
		$this->_mime = 'application/pdf';

		//set document type
		$this->_type = 'pdf';
	}

	/**
	 * Outputs the document to the browser.
	 *
	 * @access public
	 * @param boolean 	$cache		If true, cache the output
	 * @param array		$params	    Associative array of attributes
	 */
	function display( $cache = false, $params = array())
	{
		$data = $this->getBuffer();

		JResponse::setHeader( 'Expires', gmdate( 'D, d M Y H:i:s', time() + 900 ) . ' GMT' );
		if ($mdate = $this->getModifiedDate()) {
			JResponse::setHeader( 'Last-Modified', $mdate );
		}
		//JResponse::setHeader( 'Cache-Control', 'no-store, no-cache, must-revalidate' );
		//JResponse::setHeader( 'Cache-Control', 'post-check=0, pre-check=0', false );	// HTTP/1.1
		JResponse::setHeader( 'Pragma', 'no-cache' );									// HTTP/1.0
		JResponse::setHeader( 'Content-Type', $this->_mime .  '; charset=' . $this->_charset);
		JResponse::setHeader( 'Content-Length', strlen($data) );									// HTTP/1.0

		JResponse::setBody($data);
	}
}
?>

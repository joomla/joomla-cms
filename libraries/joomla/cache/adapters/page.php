<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Class to support page caching
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCachePage extends JCache
{
	/**
	 * Constructor
	 *
	 * @access protected
	 * @param array $options options
	 */
	function _construct($options) {
		 parent::_construct($options);
	}

	/**
	 * Get the cached data
	 *
	 * @access public
	 * @param string $id cache id
	 * @param string $group name of the cache group
	 * @return boolean true if the cache is hit (false else)
	 */
	function loadPage( $id, $group = 'default' )
	{
		if ( !headers_sent() && isset($_SERVER['HTTP_IF_NONE_MATCH']) && $this->_caching ){
			$etag = stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] );
			if( $etag == $id) {
				$this->sendNoChangeHttpHeader($id);
				exit();
			}
		}

		$data = $this->get($id, $group, !$this->_validateCache);

		if ($data !== false) {
			$this->sendEtagHttpHeader($this->_id);
			return $data;
		}
		
		return false;
	}

	/**
	 * Set the data to cache
	 *
	 * @access public
	 */
	function savePage($data) {
		$this->save( $data, $this->_id, $this->_group );
	}

	function generateId($data) {
		return md5(serialize($data));
	}

	function sendNoChangeHttpHeader(){
		header( 'HTTP/1.x 304 Not Modified', true );
	}

	function sendEtagHttpHeader($md5) {
		header( 'ETag:'.$md5 );
	}
}
?>
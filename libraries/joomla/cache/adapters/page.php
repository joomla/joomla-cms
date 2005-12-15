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

/**
* Class to support page caching
* 
* @package Joomla
* @subpackage JFramework
* @since 1.1
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
    * Enable/disbale caching
    *
    * @access public
    * @param boolean $enable If true enable caching.
    */
	function setCaching($enable)  {
		$this->_caching = $enable;
	}

	/**
	 * Start the cache
	 *
	 * @access public
	 * @param string $id cache id
	 * @param string $group name of the cache group
	 * @return boolean true if the cache is hit (false else)
	 */
	function start( $id, $group = 'default' ) 
	{
		if ( !headers_sent() && isset($_SERVER['HTTP_IF_NONE_MATCH']) ){
			$etag = stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] );
			if( $etag == $id) {
				$this->sendNoChangeHttpHeader($id);
				return true;
			}
		}

		$data = $this->get($id, $group, !$this->_validateCache);

		if ($data !== false) {
			$this->sendEtagHttpHeader($this->_id);
			echo($data);
			return true;
		} else {
			ob_start();
			ob_implicit_flush( false );
			return false;
		}
	}

	/**
	 * Stop the cache
	 *
	 * @access public
	 */
	function end()
	{
		$data = ob_get_contents();
		ob_end_clean();

		$this->save( $data, $this->_id, $this->_group );
		echo $data;
	}

	function generateId($data) {
		return md5(serialize($data));
	}

	function sendNoChangeHttpHeader(){
		header( 'HTTP/1.x 304 Not Modified', true );
	}

	function sendEtagHttpHeader($md5) {
		header( 'Etag: '.$md5 );
	}
}
?>
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
 * Class to support output caching
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.1
 */
class JCacheOutput extends JCache
{
	/**
	 * Constructor
	 *
	 * @param array $options options
	 * @access protected
	 */
	function _construct( $options ) {
		 parent::_construct($options);
	}

	/**
	 * Start the cache
	 *
	 * @access public
	 * @param string $id cache id
	 * @param string $group name of the cache group
	 * @return boolean true if the cache is hit (false else)
	 */
	function start( $id, $group = 'default')
	{
		$data = $this->get($id, $group, !$this->_validateCache);
		if ($data !== false) {
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
		echo( $data );
	}
}
?>
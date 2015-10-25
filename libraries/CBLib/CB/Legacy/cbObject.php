<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/16/14 2:46 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

defined('CBLIB') or die();

/**
 * cbObject Compatibility Class implementation
 * Was in plugin.foundation.php
 * @deprecated 2.0 Implement directly CBLib\Registry\GetterInterface instead
 */
class cbObject
{
	/**
	 * Gets a param value
	 *
	 * @param  string        $key      The name of the param
	 * @param  mixed         $default  The default value if not found (if array(), the return will be an array too)
	 * @return string|array            The value
	 */
	public function get( $key, $default = null )
	{
		if ( isset( $this->$key ) ) {
			return $this->$key;
		}

		return $default;
	}

	/**
	 * Sets a value to a param
	 *
	 * @param  string    $key    The name of the param
	 * @param  string    $value  The value of the parameter
	 * @return cbObject          For chaining
	 */
	public function set( $key, $value='' )
	{
		$this->$key		=	$value;

		return $this;
	}
}

<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Input Base Class
 *
 * This is an abstracted input class used to manage retrieving data from the application environment.
 *
 * @package     Joomla.Platform
 * @subpackage  Input
 * @since       11.1
 * @deprecated  4.0 (CMS) Use {@link \Joomla\Input\Input} directly
 *
 * @property-read    JInput                $get
 * @property-read    JInput                $post
 * @property-read    JInput                $request
 * @property-read    JInput                $server
 * @property-read    \Joomla\Input\Files   $files
 * @property-read    \Joomla\Input\Cookie  $cookie
 *
 * @method      integer  getInt()       getInt($name, $default = null)    Get a signed integer.
 * @method      integer  getUint()      getUint($name, $default = null)   Get an unsigned integer.
 * @method      float    getFloat()     getFloat($name, $default = null)  Get a floating-point number.
 * @method      boolean  getBool()      getBool($name, $default = null)   Get a boolean.
 * @method      string   getWord()      getWord($name, $default = null)
 * @method      string   getAlnum()     getAlnum($name, $default = null)
 * @method      string   getCmd()       getCmd($name, $default = null)
 * @method      string   getBase64()    getBase64($name, $default = null)
 * @method      string   getString()    getString($name, $default = null)
 * @method      string   getHtml()      getHtml($name, $default = null)
 * @method      string   getPath()      getPath($name, $default = null)
 * @method      string   getUsername()  getUsername($name, $default = null)
 */
class JInput extends \Joomla\Input\Input
{
	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is $_REQUEST)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 *
	 * @since   11.1
	 * @deprecated  4.0 (CMS) Use {@link \Joomla\Input\Input} directly
	 */
	public function __construct($source = null, array $options = array())
	{
		// B/C Layer for CMS 3.x and Framework 1.x - Framework doesn't access $_REQUEST by reference
		if (is_null($source))
		{
			$source = &$_REQUEST;
		}

		parent::__construct($source, $options);
	}
}

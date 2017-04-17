<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 8/24/14 11:44 PM $
* @package CBLib\Registry
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\Registry;


defined('CBLIB') or die();

interface SetterInterface
{
	/**
	 * Sets a value to a param
	 *
	 * @param  string  $key    The name of the param or sub-param, e.g. a.b.c
	 * @param  string  $value  The value of the parameter
	 * @return void
	 *
	 * @throws \InvalidArgumentException  If $key has a namespace/ in it.
	 */
	public function set( $key, $value );
}

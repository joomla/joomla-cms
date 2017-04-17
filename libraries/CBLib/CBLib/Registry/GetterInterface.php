<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/30/13 12:01 PM $
* @package CBLib\CBLib
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\Registry;


interface GetterInterface {
	const COMMAND	=	'command';
	const INT		=	'int';
	const UINT		=	'uint';
	const NUMERIC   =	'numeric';
	const FLOAT		=	'float';
	const BOOLEAN	=	'boolean';
	const STRING	=	'string';
	const HTML  	=	'html';
	const BASE64    =   'base64';
	const RAW		=	'raw';

	/**
	 * Cleaning input method
	 *
	 * @param   string|string[]        $key      Name of index or array of names of indexes, each with name or input-name-encoded array selection, e.g. a.b.c
	 * @param   mixed|GetterInterface  $default  [optional] Default value, or, if instanceof GetterInterface, parent GetterInterface for the default value
	 * @param   string|array           $type     [optional] Default: null: GetterInterface::COMMAND. Or const int GetterInterface::COMMAND|GetterInterface::INT|... or array( const ) or array( $key => const )
	 * @return  mixed
	 *
	 * @throws \Exception
	 */
	public function get( $key, $default = null, $type = null );

	/**
	 * Check if a key path exists.
	 *
	 * @param   string  $key  The name of the param or sub-param, e.g. a.b.c
	 * @return  boolean
	 */
	public function has( $key );
}

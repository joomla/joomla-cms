<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 19.06.13 18:45 $
* @package CBLib
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Application;

use CBLib\Registry\Registry;
use CBLib\Registry\RegistryInterface;

defined('CBLIB') or die();

/**
 * CBLib\Config Class implementation
 * 
 */
class Config extends Registry
{
	/**
	 * @var self
	 */
	static $mainConfig	=	null;

	/**
	 * @param  callable|array|RegistryInterface  $config  Configuration
	 * @param  Application                       $app     Application for the config callable parameter, if callable
	 * @return Config
	 *
	 * @throws \UnexpectedValueException
	 */
	public static function setMainConfig( $config, Application $app )
	{
		while ( is_callable( $config ) ) {
			$config					=	call_user_func_array( $config, array( $app ) );
		}

		if ( is_array( $config ) ) {
			static::$mainConfig		=	new static( $config );
		} elseif ( is_object( $config ) ) {
			if ( $config instanceof RegistryInterface ) {
				static::$mainConfig	=	$config;
			} else {
				throw new \UnexpectedValueException('Unexpected type for config parameter');
			}
		}
		return static::$mainConfig;
	}
}

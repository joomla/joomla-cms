<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 11/28/13 5:43 PM $
* @package CBLib\AhaWow\Model
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\AhaWow\Model;

use CB\Database\Table\PluginTable;
use CBLib\Registry\Registry;
use CBLib\Registry\RegistryInterface;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\Model\Context Class implementation
 * 
 */
class Context {

	protected static $registryCache	=	array();

	public function getAppConfig( )
	{
		global $ueConfig;

		if ( ! isset( static::$registryCache[0] ) ) {
			static::$registryCache[0]	=	new Registry( $ueConfig );
		}

		return static::$registryCache[0];
	}

	/**
	 * returns the registry of params of the currently loaded plugin
	 *
	 * @return RegistryInterface
	 */
	public function &getParams() {
		global $_PLUGINS;

		$id		=	$this->getPluginId();

		if ( ! isset( static::$registryCache[$id] ) ) {
			if ( $id ) {
				static::$registryCache[$id]	=	$_PLUGINS->getPluginParams( $this->getPluginObject( $id ) );
			} else {
				static::$registryCache[$id]	=	$this->getAppConfig();
			}
		}

		return static::$registryCache[$id];
	}

	public function getPluginId( )
	{
		global $_PLUGINS;

		return $_PLUGINS->getPluginId();
	}

	/**
	 * @return PluginTable|null
	 */
	public function getPluginObject( )
	{
		global $_PLUGINS;

		return $_PLUGINS->getPluginObject( $this->getPluginId() );

	}

	/**
	 * returns absolute path to plugins folder
	 *
	 * @param  null|PluginTable  $plugin
	 * @return string
	 */
	public function getPluginPath( $plugin = null ) {
		global $_PLUGINS;

		return $_PLUGINS->getPluginPath( $plugin );
	}

	/**
	 * returns live path to plugins folder
	 *
	 * @param  null|PluginTable  $plugin
	 * @return string
	 */
	public function getPluginLivePath( $plugin = null ) {
		global $_PLUGINS;

		return $_PLUGINS->getPluginLivePath( $plugin );
	}
}

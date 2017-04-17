<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/18/14 2:22 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Input\InputInterface;
use CBLib\Language\CBTxt;
use CBLib\Registry\GetterInterface;
use CBLib\Registry\ParamsInterface;
use CBLib\Registry\Registry;
use CBLib\Xml\SimpleXMLElement;
use CB\Database\Table\FieldTable;
use CB\Database\Table\PluginTable;

defined('CBLIB') or die();

/**
 * cbPluginHandler Class implementation
 * 
 */
class cbPluginHandler
{
	/**
	 * Index of the plugin being loaded
	 * (needs to be public for backwards compatibility)
	 * @var int
	 */
	public $_loading		=	null;
	/**
	 * Element of the plugin
	 * (needs to be public for backwards compatibility)
	 * @var string
	 */
	public $element		=	null;
	/**
	 * Parameters
	 * (needs to be public for backwards compatibility)
	 * @var Registry
	 */
	public $params			=	null;
	/**
	 * @var cbPluginHandler
	 */
	private $pluginObject	=	null;

	/**
	 * @var InputInterface
	 */
	private $input			=	null;

	/**
	 * An array of functions in event groups
	 * @var array
	 */
	private $_events		=	array();
	/**
	 * An array of classes and pluginids for field-types
	 * (needs to be public for backwards compatibility)
	 * @var array
	 */
	public $_fieldTypes		=	array();
	/**
	 * An array of classes for additional field-parameters
	 * @var array
	 */
	private $_fieldParams	=	array();
	/**
	 * An array of classes for additional tabs-parameters
	 * @var array
	 */
	private $_tabParams		=	array();
	/**
	 * An array of menu and status items (array)
	 * @var array
	 */
	private $_menus			=	null;
	/**
	 * An array of loaded plugins objects, index=pluginId
	 * @var array
	 */
	private $_plugins		=	array();
	/**
	 * An array indexed by the group-name of arrays of plugin ids of the plugins already loaded containing stdClass objects of the plugin table entry
	 * @var array
	 */
	private $_pluginGroups	=	array();
	/**
	 * Index of the plugin instance
	 * @var int
	 */
	private $_cbpluginid	=	null;
	/**
	 * Collection of debug data
	 * @var array
	 */
	private $debugMSG		=	array();
	/**
	 * Error Message
	 * @var string
	 */
	private $errorMSG		=	array();
	/**
	 * Is in error state
	 * (needs to be public for backwards compatibility)
	 * @var boolean
	 */
	public $_iserror		=	false;

	/**
	 * Constructor (needed for backwards and future compatibility as inheritors call parent::_construct())
	 */
	public function __construct()
	{
	}

	/**
	 * Constructor named old-fashion for backwards compatibility reason
	 * until all classes extending cbPluginHandler call $this->__construct() instead of $this->cbPluginHandler()
	 * @deprecated 2.0 use $this->__construct() instead.
	 */
	public function cbPluginHandler( )
	{
		$this->__construct();
	}

	/**
	 * Loads all the bot files for a particular group (if group not already loaded)
	 *
	 * @param  string   $group             The group name, relates to the sub-directory in the plugins directory
	 * @param  mixed    $ids               array of int : ids of plugins to load. OR: string : name of element (OR new in CB 1.2.2: string if ends with a ".": elements starting with "string.")
	 * @param  int      $publishedStatus   if 1 (DEFAULT): load only published plugins, if 0: load all plugins including unpublished ones
	 * @return boolean                     TRUE: load done, FALSE: no plugin loaded
	 */
	public function loadPluginGroup( $group, $ids = null, $publishedStatus = 1 )
	{
		global $_CB_framework, $_CB_database;

		static $dbCache				=	null;

		$this->_iserror				=	false;
		$group						=	trim( $group );

		if ( ( $group && ( ! isset( $this->_pluginGroups[$group] ) ) ) || ( ! $this->all_in_array_key( $ids, $this->_plugins ) ) ) {
			$cmsAccess				=	Application::MyUser()->getAuthorisedViewLevels();
			$cmsAccessCleaned		=	implode( ',', cbArrayToInts( $cmsAccess ) );

			if ( ! isset( $dbCache[$publishedStatus][$cmsAccessCleaned][$group] ) ) {
				$where				=	array();

				if ( $publishedStatus == 1 ) {
					$where[0]		=	$_CB_database->NameQuote( 'published' ) . ' = 1';
				} else {
					$where[0]		=	$_CB_database->NameQuote( 'published' ) . ' >= ' . (int) $publishedStatus;
				}

				$where[1]			=	$_CB_database->NameQuote( 'viewaccesslevel' ) . ' IN (' . $cmsAccessCleaned . ')';

				if ( $group ) {
					$where[2]		=	$_CB_database->NameQuote( 'type' ) . ' = ' . $_CB_database->Quote( trim ( $group ) );
				}

				$queryFunction		=	function( $where ) use ( $_CB_database )
				{
					return 'SELECT *'
					.	', CONCAT_WS( "/", ' . $_CB_database->NameQuote( 'folder' ) . ', ' . $_CB_database->NameQuote( 'element' ) . ' ) AS lookup'
					.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin' )
					.	"\n WHERE " . implode( "\n AND ", $where )
					.	"\n ORDER BY " . $_CB_database->NameQuote( 'ordering' );
				};

				$query				=	$queryFunction( $where );
				$_CB_database->setQuery( $query );

				try
				{
					$plugins		=	$_CB_database->loadObjectList( 'id', '\CB\Database\Table\PluginTable', array( &$_CB_database ) );
				}
				catch ( \RuntimeException $e )
				{
					try
					{
						$cmsAccessOld	=	array();
						foreach ( $cmsAccess as $level ) {
							$cmsAccessOld[]		=	$level > 3 ? $level : $level - 1;
						}

						$where[1]		=	$_CB_database->NameQuote( 'access' ) . ' IN ' . $_CB_database->safeArrayOfIntegers( $cmsAccessOld );

						$query				=	$queryFunction( $where );
						$_CB_database->setQuery( $query );

						$plugins		=	$_CB_database->loadObjectList( 'id', '\CB\Database\Table\PluginTable', array( &$_CB_database ) );

						$_CB_framework->enqueueMessage( CBTxt::T( 'CB_PLUGINS_DATABASE_NOT_UPGRADED', 'CB Plugins database not upgraded.') . ' '
							. CBTxt::T( 'CB_DATABASE_PLEASE_CHECK_WITH_INSTRUCTIONS', 'Please check and fix CB database in administration area in Components / Community Builder / Tools / Check Community Builder Database.' )
							. ( Application::MyUser()->isSuperAdmin() ? '<br />SQL Error (visible to super-admins only): ' . $e->getMessage() : '' ),
							$_CB_framework->getUi() == 2 ? 'warning' : 'notice' );
					}
					catch ( \RuntimeException $e )
					{
						$_CB_framework->enqueueMessage( CBTxt::T( 'CB_PLUGINS_DATABASE_ERROR', 'CB Plugins database error.') . ' '
							. CBTxt::T( 'CB_DATABASE_PLEASE_CHECK_WITH_INSTRUCTIONS', 'Please check and fix CB database in administration area in Components / Community Builder / Tools / Check Community Builder Database.' )
							. ( Application::MyUser()->isSuperAdmin() ? '<br />SQL Error (visible to super-admins only): ' . $e->getMessage() : '' ),
							'error' );
						$dbCache[$publishedStatus][$cmsAccessCleaned][$group]	=	array();
						return false;
					}
				}

				if ( $_CB_database->getErrorNum() ) {
					$dbCache[$publishedStatus][$cmsAccessCleaned][$group]	=	null;
					return false;
				} else {
					$dbCache[$publishedStatus][$cmsAccessCleaned][$group]	=	$plugins;
				}
			}

			if ( count( $ids ) == 0 ) {
				$ids				=	null;
			}

			$plugins				=	$dbCache[$publishedStatus][$cmsAccessCleaned][$group];

			if ( $plugins ) foreach ( $plugins AS $plugin ) {
				if ( ( $ids === null ) || ( is_array( $ids ) ? in_array( $plugin->id, $ids ) : ( ( substr( $ids, ( strlen( $ids ) - 1 ), 1 ) == '.' ) ? ( substr( $plugin->element, 0, strlen( $ids ) ) == $ids ) : ( $plugin->element == $ids ) ) ) ) {
					if ( ( ! isset( $this->_plugins[$plugin->id] ) ) && $this->_checkPluginFile( $plugin ) ) {
						$this->_plugins[$plugin->id]							=	$plugin;

						if ( ! isset( $this->_pluginGroups[$plugin->type][$plugin->id] ) ) {
							$this->_pluginGroups[$plugin->type][$plugin->id]	=&	$this->_plugins[$plugin->id];
						}

						$this->_loadPluginFile( $plugin );
					}
				}
			} else {
				return false;
			}
		}

		return true;
	}

	/**
	 * returns plugins of a specific group
	 *
	 * @param  string  $group
	 * @return array
	 */
	public function & getLoadedPluginGroup( $group )
	{
		if ( ! $group ) {
			$plugins	=	array_filter( $this->_plugins );
		} elseif ( isset( $this->_pluginGroups[$group] ) ) {
			$plugins	=	$this->_pluginGroups[$group];
		} else {
			$plugins	=	array();
		}

		return $plugins;
	}

	/**
	 * returns plugin of a specific group based off element
	 *
	 * @param  string               $group
	 * @param  string               $element
	 * @return boolean|PluginTable
	 */
	public function getLoadedPlugin( $group, $element )
	{
		$plugins	=	$this->getLoadedPluginGroup( $group );

		if ( $plugins ) foreach ( $this->getLoadedPluginGroup( $group ) as $pluginId => $plugin ) {
			if ( $plugin->element == $element ) {
				return $this->_pluginGroups[$group][$pluginId];
			}
		}

		return false;
	}

	/**
	 * checks if all elements of array needles are in array haystack
	 *
	 * @param  string|array  $needles
	 * @param  array         $haystack
	 * @return bool
	 */
	public function all_in_array( $needles, $haystack )
	{
		if ( is_array( $needles ) ) {
			foreach ( $needles as $needle ) {
				if ( ! in_array( $needle, $haystack ) ) {
					return false;
				}
			}
		} else {
			if ( ! in_array( $needles, $haystack ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * checks if all elements of array needles are in array haystack
	 *
	 * @param  string|array  $needles
	 * @param  array         $haystack
	 * @return bool
	 */
	public function all_in_array_key( $needles, $haystack )
	{
		if ( is_array( $needles ) ) {
			foreach ( $needles as $needle ) {
				if ( ! array_key_exists( $needle, $haystack ) ) {
					return false;
				}
			}
		} else {
			if ( ! array_key_exists( $needles, $haystack ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * sets what plugin is currently loading
	 *
	 * @param  PluginTable  $plugin
	 * @param  boolean      $loading
	 * @return int
	 */
	public function _setLoading( $plugin, $loading = true )
	{
		$savePreviousPluginId	=	$this->_loading;

		if ( $loading === true ) {
			$this->_loading		=	$plugin->id;
		} elseif ( $loading === false) {
			$this->_loading		=	null;
		} else {
			$this->_loading		=	$loading;
		}

		return $savePreviousPluginId;
	}

	/**
	 * loads a plugins main php file
	 *
	 * @param  PluginTable  $plugin
	 * @return bool
	 */
	public function _loadPluginFile( $plugin )
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $_CB_framework, $_PLUGINS;	// $_PLUGINS is needed for the include below.

		// We don't want language files PHP loading as we do that through cbimport:
		if ( $plugin->type == 'language' ) {
			return false;
		}

		$path						=	$_CB_framework->getCfg( 'absolute_path' ) . '/' . $this->getPluginRelPath( $plugin ) . '/' . $plugin->element . '.php';

		if ( file_exists( $path ) && is_readable( $path ) ) {
			$savePreviousPluginId	=	$this->_setLoading( $plugin, true );

			$langCache				=	CBTxt::setLanguage( null );
			$plgLangPath			=	$_CB_framework->getCfg( 'absolute_path' ) . '/' . $this->getPluginRelPath( $plugin ) . '/language';
			$langPath				=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin/language';
			$lang					=	$_CB_framework->getCfg( 'lang_tag' );

			if ( $_CB_framework->getUi() == 2 ) {
				$langLoaded			=	CBTxt::import( $langPath, $lang, 'cbplugin/' . $plugin->element . '-admin_language.php', false );

				if ( ! $langLoaded ) {
					CBTxt::import( $plgLangPath, $lang, 'admin_language.php' );
				}
			}

			$langLoaded				=	CBTxt::import( $langPath, $lang, 'cbplugin/' . $plugin->element . '-language.php', false );

			if ( ! $langLoaded ) {
				CBTxt::import( $plgLangPath, $lang, 'language.php' );
			}

			// We don't want plugins language files to alter the current language loaded so lets reset it:
			CBTxt::setLanguage( $langCache );

			/** @noinspection PhpIncludeInspection */
			require_once( $path );

			$this->_setLoading( $plugin, $savePreviousPluginId );

			return true;
		} else {
			return false;
		}
	}

	/**
	 * checks a plugins main php file to see if it exists or not
	 *
	 * @param  PluginTable  $plugin
	 * @return bool
	 */
	public function _checkPluginFile( $plugin )
	{
		global $_CB_framework;

		$path						=	$_CB_framework->getCfg( 'absolute_path' ) . '/' . $this->getPluginRelPath( $plugin ) . '/' . $plugin->element . '.php';

		if ( file_exists( $path ) && is_readable( $path ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * returns the plugin id of the currently loaded plugin
	 *
	 * @return int
	 */
	public function getPluginId()
	{
		global $_PLUGINS;

		return (int) $_PLUGINS->_loading;
	}

	/**
	 * returns the plugin object of the currently loaded plugin or specified plugin id
	 *
	 * @param null|int $pluginId
	 * @return null|PluginTable
	 */
	public function & getPluginObject( $pluginId = null )
	{
		global $_PLUGINS;

		if ( $pluginId === null ) {
			$pluginId		=	(int) $this->_cbpluginid;

			if ( ! $pluginId ) {
				$pluginId	=	(int) $_PLUGINS->_loading;
			}
		}

		return $_PLUGINS->_plugins[$pluginId];
	}

	/**
	 * returns the plugin object of the currently loaded plugin or specified plugin object or specified plugin id from cache
	 *
	 * @param  null|PluginTable|int  $plugin
	 * @return null|PluginTable
	 */
	public function getCachedPluginObject( $plugin )
	{
		static $cache						=	array();

		if ( $plugin === null ) { // We need current plugin so there's nothing to cache by; grab it and cache it:
			$plugin							=	$this->getPluginObject();
			$pluginId						=	(int) $plugin->id;

			$cache[$pluginId]				=	$plugin;
		} elseif ( ! is_object( $plugin ) ) { // We need to pull plugin from plugin id, but check cache first:
			$pluginId						=	(int) $plugin;

			if ( ! isset( $cache[$pluginId] ) ) {
				$plugin						=	$this->getPluginObject( $pluginId );

				if ( ! $plugin ) { // The plugin isn't in global cache; lets load it:
					$plugin					=	new PluginTable();

					if ( ! $plugin->load( $pluginId ) ) {
						$cache[$pluginId]	=	null;
					} else {
						$cache[$pluginId]	=	$plugin;
					}
				} else {
					$cache[$pluginId]		=	$plugin;
				}
			}
		} else { // We already have the plugin object; just cache it and return it:
			$pluginId						=	(int) $plugin->id;

			$cache[$pluginId]				=	$plugin;
		}

		return $cache[$pluginId];
	}

	/**
	 * returns plugin class object
	 *
	 * @param  string           $class
	 * @param  null|int         $pluginId
	 * @return cbPluginHandler
	 */
	public function & getInstanceOfPluginClass( $class, $pluginId = null )
	{
		if ( $pluginId === null ) {
			$pluginId	=	$this->getPluginId();
		} else {
			$pluginId	=	(int) $pluginId;
		}

		if ( ! isset( $this->_plugins[$pluginId]->classInstance ) ) {
			$this->_plugins[$pluginId]->classInstance							=	array();
		}

		if ( ! isset( $this->_plugins[$pluginId]->classInstance[$class] ) ) {
			$this->_plugins[$pluginId]->classInstance[$class]					=	new $class();
			$this->_plugins[$pluginId]->classInstance[$class]->_cbpluginid		=	$pluginId;
		}

		return $this->_plugins[$pluginId]->classInstance[$class];
	}

	/**
	 * returns variable from plugin class object
	 *
	 * @param  null|int  $pluginId
	 * @param  string    $class
	 * @param  string    $variable
	 * @return mixed
	 */
	public function getVar( $pluginId, $class, $variable )
	{
		if ( $pluginId === null ) {
			$pluginId	=	$this->getPluginId();
		} else {
			$pluginId	=	(int) $pluginId;
		}

		if ( ( $class != null ) && class_exists( $class ) && isset( $this->_plugins[$pluginId] ) ) {
			if ( $this->_plugins[$pluginId]->published ) {
				if ( isset( $this->_plugins[$pluginId]->classInstance[$class]->$variable ) ) {
					return $this->_plugins[$pluginId]->classInstance[$class]->$variable;
				}
			}
		}

		return false;
	}

	/**
	 * returns absolute path to plugins folder
	 *
	 * @param  null|PluginTable|int  $plugin
	 * @return string
	 */
	public function getPluginPath( $plugin = null )
	{
		global $_CB_framework;

		$plugin		=	$this->getCachedPluginObject( $plugin );

		if ( ! $plugin ) {
			return '';
		}

		return $_CB_framework->getCfg( 'absolute_path' ) . '/' . $this->getPluginRelPath( $plugin );
	}

	/**
	 * returns live path to plugins folder
	 *
	 * @param  null|PluginTable|int  $plugin
	 * @return string
	 */
	public function getPluginLivePath( $plugin = null )
	{
		global $_CB_framework;

		$plugin		=	$this->getCachedPluginObject( $plugin );

		if ( ! $plugin ) {
			return '';
		}

		return $_CB_framework->getCfg( 'live_site' ) . '/' . $this->getPluginRelPath( $plugin );
	}

	/**
	 * returns absolute path to plugins xml file
	 *
	 * @param  null|PluginTable|int  $plugin
	 * @return string
	 */
	public function getPluginXmlPath( $plugin = null )
	{
		global $_CB_framework;

		$plugin		=	$this->getCachedPluginObject( $plugin );

		if ( ! $plugin ) {
			return '';
		}

		return $_CB_framework->getCfg( 'absolute_path' ) . '/' . $this->getPluginRelPath( $plugin ) . '/' . $plugin->element . '.xml';
	}

	/**
	 * returns relative path to plugins folder
	 *
	 * @param  null|PluginTable  $plugin
	 * @return null|string
	 */
	public function getPluginRelPath( $plugin = null )
	{
		if ( $plugin === null ) {
			$plugin		=&	$this->getPluginObject();
		}

		if ( $plugin === null ) {
			return null;
		} elseif ( $plugin->folder && ( $plugin->folder[0] == '/' ) ) {
			return substr( $plugin->folder, 1 );
		} else {
			return 'components/com_comprofiler/plugin/' . $plugin->type . '/'. $plugin->folder;
		}
	}

	/**
	 * returns params object for plugin
	 *
	 * @param  null|PluginTable  $plugin
	 * @return Registry
	 */
	public function getPluginParams( $plugin = null )
	{
		if ( $plugin === null ) {
			$plugin	=	$this->getPluginObject();
		}

		if ( $plugin === null ) {
			return new Registry( null );
		}

		if ( ( $plugin->id == $this->_loading ) && $this->pluginObject ) {
			if ( ! $this->pluginObject->params ) {
				$this->pluginObject->_loadParams( $plugin->id );
			}

			return $this->pluginObject->params;
		}

		return $this->_getPluginParamsFromTable( $plugin );
	}

	/**
	 * Gets the parameters from the PluginTable
	 *
	 * @param  PluginTable      $plugin  The plugin Table entry
	 * @return ParamsInterface           The Parameters
	 */
	private function _getPluginParamsFromTable( PluginTable $plugin )
	{
		$params	=	$plugin->params;

		if ( $params instanceof Registry ) {
			return $params;
		}

		/** @noinspection PhpDeprecationInspection */
		if ( $params instanceof cbParamsBase ) {
			/** @noinspection PhpDeprecationInspection */
			$params	=	$params->toParamsArray();
		}

		return new Registry( $params );
	}

	/**
	 * loads a plugins params into memory
	 *
	 * @param null|int              $pluginId
	 * @param null|Registry|string  $extraParams
	 */
	public function _loadParams( $pluginId, $extraParams = null )
	{
		if ( $pluginId === null ) {
			$pluginId				=	$this->getPluginId();
		} else {
			$pluginId				=	(int) $pluginId;
		}

		$plugin						=	$this->getPluginObject( $pluginId );

		if ( $plugin === null ) {
			return;
		}

		$paramsBase					=	$this->_getPluginParamsFromTable( $plugin );

		if ( $extraParams ) {
			if ( ! ( $extraParams instanceof Registry ) ) {
				/** @noinspection PhpDeprecationInspection */
				if ( $extraParams instanceof cbParamsBase ) {
					/** @noinspection PhpDeprecationInspection */
					$extraParams	=	new Registry( $extraParams->toParamsArray() );
				} else {
					$extraParams	=	new Registry( $extraParams );
				}
			}

			$extraArray			=	$extraParams->asArray();

			foreach ( $extraArray as $k => $v ) {
				$paramsBase->set( $k, $v );
			}
		}

		$this->params			=	$paramsBase;
	}

	/**
	 * returns currently loaded params
	 *
	 * @return Registry
	 */
	public function & getParams( )
	{
		$params		=	$this->params;

		if ( ! $params ) {
			return new Registry( null );
		}

		if ( $params instanceof Registry ) {
			return $params;
		}

		/** @noinspection PhpDeprecationInspection */
		if ( $params instanceof cbParamsBase ) {
			/** @noinspection PhpDeprecationInspection */
			return new Registry( $params->toParamsArray() );
		}

		return new Registry( $params );
	}

	/**
	 * Sets plugin inputs
	 *
	 * @param  InputInterface $input
	 * @return void
	 */
	public function setInput( InputInterface $input )
	{
		$this->input	=	$input;
	}

	/**
	 * Get plugin inputs
	 *
	 * @return InputInterface
	 */
	public function getInput( )
	{
		if ( $this->input ) {
			return $this->input;
		}

		return Application::Input();
	}

	/**
	 * Cleaning input method
	 *
	 * @param   string|string[]        $key      Name of index or array of names of indexes, each with name or input-name-encoded array selection, e.g. a.b.c
	 * @param   mixed|GetterInterface  $default  Default value, or, if instanceof GetterInterface, parent GetterInterface for the default value
	 * @param   string|array           $type     null: GetterInterface::COMMAND. Or const int GetterInterface::COMMAND|GetterInterface::INT|... or array( const ) or array( $key => const )
	 * @return  mixed
	 *
	 * @throws \Exception
	 */
	public function input( $key, $default, $type )
	{
		return $this->getInput()->get( $key, $default, $type );
	}

	/**
	 * returns plugin xml
	 * note this is a placeholder and function needs to be overriden on plugin by plugin basis
	 *
	 * @param  null $type
	 * @param  null $typeValue
	 * @return null
	 */
	public function getXml( /** @noinspection PhpUnusedParameterInspection */ $type = null, $typeValue = null )
	{
		return null;
	}

	/**
	 * returns if an error is logged or not
	 *
	 * @return boolean
	 */
	public function is_errors( )
	{
		return $this->_iserror;
	}

	/**
	 * gets or sets plugin object variable
	 *
	 * @param  null|int  $pluginId
	 * @param  string    $var
	 * @param  mixed     $value     Value to set
	 * @return mixed                Previous value
	 */
	public function plugVarValue( $pluginId, $var, $value = null )
	{
		if ( $pluginId === null ) {
			$pluginId			=	$this->getPluginId();
		} else {
			$pluginId			=	(int) $pluginId;
		}

		$plugin					=&	$this->getPluginObject( $pluginId );

		if ( $plugin !== null ) {
			$currentValue		=	$plugin->$var;

			if ( $value !== null ) {
				$plugin->$var	=	$value;
			}

			return $currentValue;
		} else {
			return null;
		}
	}

	/**
	 * calls a plugin function
	 *
	 * @param  null|int                 $pluginId               Plugin Id
	 * @param  string|callable|Closure  $method                 String name of plugin method
	 * @param  string                   $class                  String name of plugin class
	 * @param  array                    $args                   Array set of variables to path to class/method
	 * @param  null|string              $extraParams            String additional parameters external to plugin params (e.g. tab params)
	 * @param  boolean                  $ignorePublishedStatus  Should ignore "published" status of the plugin ?
	 * @return mixed                                            Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function call( $pluginId, $method, $class, &$args, $extraParams = null, $ignorePublishedStatus = false )
	{
		if ( $pluginId === null ) {
			$pluginId								=	$this->getPluginId();
		} else {
			$pluginId								=	(int) $pluginId;
		}

		if ( ( $class != null ) && class_exists( $class ) ) {
			if ( $this->_plugins[$pluginId]->published || $ignorePublishedStatus ) {
				$pluginClassInstance				=	$this->getInstanceOfPluginClass( $class, $pluginId );

				if ( method_exists( $pluginClassInstance, $method ) ) {
					$pluginClassInstance->_loadParams( $pluginId, $extraParams );

					$pluginClassInstance->element	=	$this->_plugins[$pluginId]->element;	// needed for _getPrefix for _getReqParam & co

					$savePreviousPluginId			=	$this->_loading;
					$savePreviousClassInstance		=	$this->pluginObject;

					$this->_loading					=	$pluginId;
					$this->pluginObject				=	$pluginClassInstance;

					$ret							=	call_user_func_array( array( &$pluginClassInstance, $method ), $args );

					$this->_loading					=	$savePreviousPluginId;
					$this->pluginObject				=	$savePreviousClassInstance;

					return $ret;
				}
			}
		} elseif ( is_callable( $method ) ) {
			if ( $this->_plugins[$pluginId]->published || $ignorePublishedStatus ) {
				$this->_loadParams( $pluginId, $extraParams );

				$savePreviousPluginId				=	$this->_loading;
				$savePreviousClassInstance			=	$this->pluginObject;

				$this->_loading						=	$pluginId;
				$this->pluginObject					=	null;

				$ret								=	call_user_func_array( $method, $args );

				$this->_loading						=	$savePreviousPluginId;
				$this->pluginObject					=	$savePreviousClassInstance;

				return $ret;
			}
		}

		return false;
	}

	/**
	 * sets the error condition and priority (for now 1)
	 *
	 * @return boolean
	 */
	public function raiseError( )
	{
		$this->_iserror		=	true;

		return true;
	}

	/**
	 * returns logged debug message array
	 *
	 * @return array
	 */
	public function getDebugMSG( )
	{
		return $this->debugMSG;
	}

	/**
	 * adds debug message to debug message array
	 *
	 * @param  string   $method
	 * @param  string   $msg
	 * @return boolean
	 */
	public function _setDebugMSG( $method, $msg )
	{
		$debugMsg			=	array();
		$debugMsg['class']	=	get_class( $this );
		$debugMsg['method']	=	$method;
		$debugMsg['msg']	=	$msg;

		$this->debugMSG[]	=	$debugMsg;

		return true;
	}

	/**
	 * adds error message to error message array
	 *
	 * @param  string   $msg
	 * @return boolean
	 */
	public function _setErrorMSG( $msg )
	{
		$this->errorMSG[]	=	$msg;

		return true;
	}

	/**
	 * returns logged error message array or separated message string
	 *
	 * @param  string|boolean  $separator
	 * @return string|array
	 */
	public function getErrorMSG( $separator = "\n" )
	{
		if ( $separator === false ) {
			return $this->errorMSG;
		} else {
			$error		=	null;

			if ( count( $this->errorMSG ) > 0 ) {
				$error	=	implode( $separator, $this->errorMSG );
			}

			return $error;
		}
	}

	/**
	 * PLUGIN FIELD MANAGEMENT
	 */

	/**
	 * registers a field type which can be used by users
	 *
	 * @param  array     $typesArray  Names of types of fields
	 * @param  null|int  $pluginId    Id of plugin to associate with field type (internal use only)
	 * @return void
	 */
	public function registerUserFieldTypes( $typesArray, $pluginId = null )
	{
		if ( $pluginId === null ) {
			$pluginId					=	$this->getPluginId();
		} else {
			$pluginId					=	(int) $pluginId;
		}

		if ( $typesArray ) foreach ( $typesArray as $type => $class ) {
			$this->_fieldTypes[$type]	=	array( $class, $pluginId );
		}
	}

	/**
	 * returns array of field types
	 *
	 * @return array names of types registered
	 */
	public function getUserFieldTypes( )
	{
		return array_keys( $this->_fieldTypes );
	}

	/**
	 * returns a field types plugin id
	 *
	 * @param  string    $fieldType
	 * @return null|int
	 */
	public function getUserFieldPluginId( $fieldType )
	{
		if ( isset( $this->_fieldTypes[$fieldType] ) ) {
			return $this->_fieldTypes[$fieldType][1];
		}

		return null;
	}

	/**
	 * returns a field types class
	 *
	 * @param  string       $fieldType
	 * @return null|string
	 */
	public function getUserFieldClass( $fieldType )
	{
		if ( isset( $this->_fieldTypes[$fieldType] ) ) {
			return $this->_fieldTypes[$fieldType][0];
		}

		return null;
	}

	/**
	 * Calls a function of a plugin fieldtype
	 *
	 * @param  string      $fieldType
	 * @param  string      $method
	 * @param  null|array  $args
	 * @param  FieldTable  $field
	 * @return mixed
	 */
	public function callField( $fieldType, $method, $args = null, /** @noinspection PhpUnusedParameterInspection */ $field )
	{
		global $_PLUGINS;

		$result 				=	null;

		if ( $args === null ) {
			$args				=	array();
		}

		if ( isset( $this->_fieldTypes[$fieldType] ) ) {
			$event				=	'onBefore' . $method;

			if ( isset( $_PLUGINS->_events[$event] ) ) {
				$result			=	implode( '', $_PLUGINS->trigger( $event, $args ) );
			}

			if ( ! $result ) {
				$result			=	$this->call( $this->_fieldTypes[$fieldType][1], $method, $this->_fieldTypes[$fieldType][0], $args );
			}

			$event				=	'onAfter' . $method;

			if ( isset( $_PLUGINS->_events[$event] ) ) {
				$args[]			=&	$result;

				$_PLUGINS->trigger( $event, $args );
			}
		}

		return $result;
	}

	/**
	 * Registers field params for fields
	 *
	 * @param  null|string  $class  Name of class if overriding core class cbFieldParamsHandler which then needs to be extended
	 * @return void
	 */
	public function registerUserFieldParams( $class = null )
	{
		$pluginId							=	$this->getPluginId();

		if ( $class === null ) {
			$class							=	'cbFieldParamsHandler';
		}

		$this->_fieldParams[$pluginId]		=	$class;
	}

	/**
	 * returns array of registered field params
	 *
	 * @return array  Plugin id => class name
	 */
	public function getUserFieldParamsPluginIds( )
	{
		return $this->_fieldParams;
	}

	/**
	 * PLUGIN TAB MANAGEMENT
	 */

	/**
	 * registers tab params for tabs
	 *
	 * @param  null|string  $class  Name of class if overriding core class cbTabParamsHandler which then needs to be extended
	 * @return void
	 */
	public function registerUserTabParams( $class = null )
	{
		$pluginId						=	$this->getPluginId();

		if ( $class === null ) {
			$class						=	'cbTabParamsHandler';
		}

		$this->_tabParams[$pluginId]	=	$class;
	}

	/**
	 * returns array of registered tab params
	 *
	 * @return array  Plugin id => class name
	 */
	public function getUserTabParamsPluginIds( )
	{
		return $this->_tabParams;
	}

	/**
	 * PLUGIN TEMPLATE MANAGEMENT
	 */

	/**
	 * calls a function of a plugin fieldtype
	 *
	 * @param  string  $element   Plugin element
	 * @param  string  $subClass  Method subclass
	 * @param  string  $method    Method to call
	 * @param   array  $args      An array of callback arguments
	 * @param  string  $output    'html' (in future: 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit')
	 * @return mixed
	 */
	public function callTemplate( $element, $subClass, $method, $args, $output = 'html' )
	{
		if ( $output == 'htmledit' ) {
			$output		=	'html';
		}

		foreach ( array_keys( $this->_pluginGroups['templates'] ) as $pluginId ) {
			if ( $this->_pluginGroups['templates'][$pluginId]->element == $element ) {
				return $this->call( $pluginId, $method, ( 'CB' . $subClass . 'View_' . $output . '_' . $element ), $args );
			}
		}

		return null;
	}

	/**
	 * CB MENU
	 */

	/**
	 * registers a menu or status item to a particular menu position
	 * @deprecated 2.0 use addMenuInternal
	 *
	 * @param  array  $menuItem
	 * @return void
	 */
	public function _internalPLUGINSaddMenu( $menuItem )
	{
		$this->addMenuInternal( $menuItem );
	}

	/**
	 * registers a menu or status item to a particular menu position to current menus array
	 *
	 * $menuItem				=	array();
	 * $menuItem['arrayPos']	=	array( '_UE_MENU_EDIT' => array( '_UE_MENU_CUSTOM' => null ) );
	 * $menuItem['position']	=	'menuBar';
	 * $menuItem['caption']		=	htmlspecialchars( CBTxt-translated( 'Custom Menu Item' ) );
	 * $menuItem['url']			=	'index.php';
	 * $menuItem['target']		=	'';
	 * $menuItem['img']			=	'<img src="images/test.png" width="16" height="16" />';
	 * $menuItem['tooltip']		=	htmlspecialchars( CBTxt-translated( 'Just a custom menu item test' ) );
	 * $_PLUGINS->addMenuInternal( $menuItem );
	 *
	 * @param  array  $menuItem
	 * @return void
	 */
	public function addMenuInternal( $menuItem )
	{
		$this->_menus[]	=	$menuItem;
	}

	/**
	 * registers a menu or status item to a particular menu position
	 *
	 * $menuItem				=	array();
	 * $menuItem['arrayPos']	=	array( '_UE_MENU_EDIT' => array( '_UE_MENU_CUSTOM' => null ) );
	 * $menuItem['position']	=	'menuBar';
	 * $menuItem['caption']		=	htmlspecialchars( CBTxt-translated( 'Custom Menu Item' ) );
	 * $menuItem['url']			=	'index.php';
	 * $menuItem['target']		=	'';
	 * $menuItem['img']			=	'<img src="images/test.png" width="16" height="16" />';
	 * $menuItem['tooltip']		=	htmlspecialchars( CBTxt-translated( 'Just a custom menu item test' ) );
	 * $this->addMenu( $menuItem );
	 *
	 * @param  array  $menuItem
	 * @return void
	 */
	public function addMenu( $menuItem )
	{
		global $_PLUGINS;

		$_PLUGINS->addMenuInternal( $menuItem );
	}

	/**
	 * returns all registered menu items
	 *
	 * @return array
	 */
	public function getMenus( )
	{
		return $this->_menus;
	}

	/**
	 * EVENTS AND TRIGGERS
	 */

	/**
	 * registers method to an event
	 *
	 * @param  string        $event
	 * @param  string|array  $method
	 * @param  null|string   $class
	 * @return void
	 */
	public function registerFunction( $event, $method, $class = null )
	{
		$this->_events[$event][]	=	array( $class, $method, $this->getPluginId() );
	}

	/**
	 * checks if at least one event listener exists for a trigger $event
	 * (this is a fast function, avoiding building args array for trigger method)
	 *
	 * @param  string   $event
	 * @return boolean
	 */
	public function triggerListenersExist( $event )
	{
		return isset( $this->_events[$event] );
	}

	/**
	 * calls methods associated with an event
	 *
	 * @param  string      $event
	 * @param  null|array  $args
	 * @return array
	 */
	public function trigger( $event, $args = null )
	{
		$result			=	array();

		if ( $args === null ) {
			$args		=	array();
		}

		if ( isset( $this->_events[$event] ) ) foreach ( $this->_events[$event] as $func ) {
			$result[]	=	$this->call( $func[2], $func[1], $func[0], $args );
		}

		return $result;
	}

	/**
	 * PLUGIN XML MANAGEMENT
	 */

	/**
	 * xml file for plugin
	 *
	 * @param  string             $actionType
	 * @param  string             $action
	 * @param  int                $pluginId
	 * @return SimpleXMLElement
	 */
	public function & loadPluginXML( $actionType, $action, $pluginId = null )
	{
		global $_CB_framework;

		static $cache						=	array();

		if ( $pluginId === null ) {
			$pluginId						=	$this->getPluginId();
		} else {
			$pluginId						=	(int) $pluginId;
		}

		$row								=&	$this->getPluginObject( $pluginId );
		$xmlString							=	null;

		if ( $row ) {
			// security sanitization to disable use of `/`, `\\` and `:` in $action variable
			$unSecureChars					=	array( '/', '\\', ':', ';', '{', '}', '(', ')', "\"", "'", '.', ',', "\0", ' ', "\t", "\n", "\r", "\x0B" );
			$className						=	'CBplug_' . strtolower( substr( str_replace( $unSecureChars, '', $row->element ), 0, 32 ) );
			$actionCleaned					=	strtolower( substr( str_replace( $unSecureChars, '', $action ),		  0, 32 ) );

			if ( isset( $cache[$className][$actionType][$actionCleaned] ) ) {
				return $cache[$className][$actionType][$actionCleaned];
			}

			if ( class_exists( $className ) ) {
				// class CBplug_pluginname exists:
				if ( ( $_CB_framework->getUi() == 2 ) && is_callable( array( $className, 'loadAdmin' ) ) ) {
					// function loadAdmin exists:
					$array					=	array();
					/** @noinspection PhpUndefinedCallbackInspection */
					$this->call( $row->id, 'loadAdmin', $className, $array, null, true );
				}

				// $xmlString	=	$pluginClass->getXml( 'action', $actionCleaned );
				$array						=	array( $actionType, $actionCleaned );
				$xmlString					=	$this->call( $row->id, 'getXml', $className, $array, null, true );

				if ( $xmlString ) {
					$cache[$className][$actionType][$actionCleaned]	=	new SimpleXMLElement( $xmlString );

					return $cache[$className][$actionType][$actionCleaned];
				}
			}

			if ( $actionCleaned ) {
				// try action-specific file: xml/edit.actiontype.xml :
				$xmlFile					=	$_CB_framework->getCfg( 'absolute_path' ) . '/' . $this->getPluginRelPath( $row ) . '/xml/edit.' . $actionType . '.' . $actionCleaned .'.xml';

				if ( file_exists( $xmlFile ) ) {
					$cache[$className][$actionType][$actionCleaned]	=	new SimpleXMLElement( trim( file_get_contents( $xmlFile ) ) );

					return $cache[$className][$actionType][$actionCleaned];
				}
			}

			// try specific file for after installations: xml/edit.plugin.xml :
			$xmlFile						=	$_CB_framework->getCfg( 'absolute_path' ) . '/' . $this->getPluginRelPath( $row ) . '/xml/edit.plugin.xml';

			if ( file_exists( $xmlFile ) ) {
				$cache[$className][$actionType][$actionCleaned]		=	new SimpleXMLElement( trim( file_get_contents( $xmlFile ) ) );

				return $cache[$className][$actionType][$actionCleaned];
			}

			// try plugin installation file:
			$xmlFile						=	$_CB_framework->getCfg( 'absolute_path' ) . '/' . $this->getPluginRelPath( $row ) . '/' . $row->element . '.xml';

			if ( isset( $cache[$xmlFile] ) ) {
				return $cache[$xmlFile];
			} else {
				if ( file_exists( $xmlFile ) ) {
					$cache[$xmlFile]		=	new SimpleXMLElement( trim( file_get_contents( $xmlFile ) ) );

					return $cache[$xmlFile];
				}
			}

		}

		$element							=	null;

		return $element;
	}

	/**
	 * returns plugins xml version
	 *
	 * @param  null|PluginTable|int  $plugin    The plugin id or object to check version for
	 * @param  bool                  $raw       1/True: version only (no farm), 0/False: Formatted version (green/red/shortened), 2: array of version information ( $version, $latestVersion, $isLatest, $latestURL )
	 * @param  int                   $duration  The duration to cache the plugin version xml file (null/0 for no limit)
	 * @param  int                   $length    The maximum version length to display (null/0 for no limit)
	 * @return null|string
	 */
	public function getPluginVersion( $plugin, $raw = false, $duration = 24, $length = 0 )
	{
		global $_CB_framework, $ueConfig;

		cbimport( 'cb.snoopy' );

		static $plgVersions							=	null;

		if ( $plgVersions === null ) {
			$cacheFile								=	$_CB_framework->getCfg( 'absolute_path' ) . '/cache/cbpluginsversions.xml';
			$plgVersionsXML							=	null;

			if ( file_exists( $cacheFile ) ) {
				if ( ( ! $duration ) || ( intval( ( $_CB_framework->now() - filemtime( $cacheFile ) ) / 3600 ) > $duration ) ) {
					$request						=	true;
				} else {
					$plgVersionsXML					=	new SimpleXMLElement( trim( file_get_contents( $cacheFile ) ) );

					$request						=	false;
				}
			} else {
				$request							=	true;
			}

			if ( $request ) {
				$s									=	new CBSnoopy();
				$s->read_timeout					=	30;
				$s->referer							=	$_CB_framework->getCfg( 'live_site' );

				@$s->fetch( 'http://update.joomlapolis.net/cbpluginsversions20.xml' );

				if ( (int) $s->status == 200 ) {
					try {
						$plgVersionsXML				=	new SimpleXMLElement( $s->results );

						$plgVersionsXML->saveXML( $cacheFile );
					} catch ( Exception $e ) {}
				}
			}

			if ( $plgVersionsXML ) {
				$plgVersions						=	$plgVersionsXML->getElementByPath( 'cb_plugins/' . ( checkJversion() >= 2 ? 'j30' : 'j15' ) );
			} else {
				$plgVersions						=	false;
			}
		}

		$plugin										=	$this->getCachedPluginObject( $plugin );

		if ( ! $plugin ) {
			return ( $raw === 2 ? array( null, null, null, null ) : null );
		}

		static $cache								=	array();

		$pluginId									=	(int) $plugin->id;

		if ( ! isset( $cache[$pluginId][$raw] ) ) {
			$xmlFile								=	$this->getPluginXmlPath( $plugin );
			$version								=	null;
			$latestVersion							=	null;
			$isLatest								=	null;
			$latestURL								=	null;

			if ( file_exists( $xmlFile ) ) {
				try {
					$xml = new SimpleXMLElement( trim( file_get_contents( $xmlFile ) ) );
				} catch ( \Exception $e ) {
					$xml							=	null;
					echo "$xmlFile not an XML file!!!";
				}

				if ( $xml !== null ) {
					$ver							=	null;

					if ( isset( $xml->release ) ) {
						// New release XML variable used by incubator projects:
						$ver						=	$xml->release;
					} elseif ( isset( $xml->cbsubsversion ) ) {
						// CBSubs plugin versions are same as the CBSubs version; lets grab them:
						$cbsubsVer					=	$xml->cbsubsversion->attributes();

						if ( isset( $cbsubsVer['version'] ) ) {
							$ver					=	$cbsubsVer['version'];
						}
					} elseif ( isset( $xml->description ) ) {
						// Attempt to parse plugin description for a version using logical naming:
						if ( preg_match( '/(?:plugin|field|fieldtype|ver|version|' . preg_quote( $plugin->name ) . ') ((?:[0-9]+(?:\.)?(?:(?: )?RC)?(?:(?: )?B)?(?:(?: )?BETA)?)+)/i', $xml->description, $matches ) ) {
							$ver					=	$matches[1];
						}
					}

					// Check if version was found; if it was lets clean it up:
					if ( $ver ) {
						if ( preg_match( '/^\d+(\.\d+)+(-[a-z]+\.\d+)?(\+\w)?$/', $ver ) ) {
							$version				=	$ver;
						} else {
							$version					=	preg_replace( '/\.*([a-zA-Z]+)\.*/i', '.$1.', preg_replace( '/^[a-zA-Z]+/i', '', str_replace( array( '-', '_', '+' ), '.', str_replace( ' ', '', strtoupper( $ver ) ) ) ) );
						}

						if ( is_integer( $version ) ) {
							$version				=	implode( '.', str_split( $version ) );
						} elseif ( preg_match( '/^(\d{2,})(\.[a-zA-Z].+)/i', $version, $matches ) ) {
							$version				=	implode( '.', str_split( $matches[1] ) ) . $matches[2];
						}

						$version					=	trim( str_replace( '..', '.', $version ), '.' );

						// Encase the version is too long lets cut it short for readability and display full version as mouseover title:
						if ( $version && $length && ( cbIsoUtf_strlen( $version ) > $length ) ) {
							$versionName			=	rtrim( trim( cbIsoUtf_substr( $version, 0, $length ) ), '.' ) . '&hellip;';
							$versionShort			=	true;
						} else {
							$versionName			=	$version;
							$versionShort			=	false;
						}

						// Lets try and parse out latest version and latest url from versions xml data:
						if ( $plgVersions ) foreach ( $plgVersions as $plgVersion ) {
							$plgName				=	(string) $plgVersion->name;
							$plgFile				=	(string) $plgVersion->file;

							if ( ( $plgName == $plugin->name ) || ( strpos( $plgName, $plugin->name ) !== false ) || ( strpos( $plgFile, $plugin->folder ) !== false ) ) {
								$latestVersion		=	(string) $plgVersion->version;
								$latestURL			=	(string) $plgVersion->url;
							}
						}

						if ( $latestVersion ) {
							if ( version_compare( $version, $latestVersion ) >= 0 ) {
								$isLatest			=	true;
							} else {
								$isLatest			=	false;
							}
						}

						// Format version display:
						if ( ! $raw ) {
							if ( $latestVersion ) {
								if ( $isLatest ) {
									$version		=	'<span class="text-success"' . ( $versionShort ? ' title="' . htmlspecialchars( $version ) . '"' : null ) . '><strong>' . $versionName . '</strong></span>';
								} else {
									$version		=	'<span class="text-danger" title="' . htmlspecialchars( $latestVersion ) . '"><strong>' . $versionName . '</strong></span>';

									if ( $latestURL ) {
										$version	=	'<a href="' . htmlspecialchars( $latestURL ) . '" target="_blank">' . $version . '</a>';
									}
								}
							} else {
								if ( $versionShort ) {
									$version		=	'<span title="' . htmlspecialchars( $version ) . '">' . $versionName . '</span>';
								} else {
									$version		=	$versionName;
								}
							}
						}
					}
				}
			}

			if ( ( ! $version ) && ( ! $raw ) ) {
				if ( $plugin->iscore ) {
					// core plugins are same version as CB it self:
					if ( $length && ( cbIsoUtf_strlen( $ueConfig['version'] ) > $length ) ) {
						$version					=	'<span title="' . htmlspecialchars( $ueConfig['version'] ) . '">' . rtrim( trim( cbIsoUtf_substr( $ueConfig['version'], 0, $length ) ), '.' ) . '&hellip;</span>';
					} else {
						$version					=	$ueConfig['version'];
					}
				} else {
					$version						=	'-';
				}
			}

			if ( $raw === 2 ) {
				$version							=	array( $version, $latestVersion, $isLatest, $latestURL );
			}

			$cache[$pluginId][$raw]					=	$version;
		}

		return $cache[$pluginId][$raw];
	}

	/**
	 * returns true or false if plugin is compatible with current major CB release
	 *
	 * @param  null|PluginTable|int  $plugin
	 * @return boolean
	 */
	public function checkPluginCompatibility( $plugin )
	{
		global $ueConfig;

		static $cbVersion					=	null;

		if ( $cbVersion === null ) {
			$cbVersion						=	preg_replace( '/\.*([a-zA-Z]+)\.*/i', '.$1.', preg_replace( '/^[a-zA-Z]+/i', '', str_replace( array( '-', '_', '+' ), '.', str_replace( ' ', '', strtoupper( $ueConfig['version'] ) ) ) ) );

			if ( is_integer( $cbVersion ) ) {
				$cbVersion					=	implode( '.', str_split( $cbVersion ) );
			} elseif ( preg_match( '/^(\d{2,})(\.[a-zA-Z].+)/i', $cbVersion, $matches ) ) {
				$cbVersion					=	implode( '.', str_split( $matches[1] ) ) . $matches[2];
			}

			$cbVersion						=	trim( str_replace( '..', '.', $cbVersion ), '.' );
		}

		$plugin								=	$this->getCachedPluginObject( $plugin );

		if ( ! $plugin ) {
			return false;
		}

		static $cache						=	array();

		$pluginId							=	(int) $plugin->id;

		if ( ! isset( $cache[$pluginId] ) ) {
			$xmlFile						=	$this->getPluginXmlPath( $plugin );
			$compatible						=	true;

			if ( file_exists( $xmlFile ) ) {
				try {
					$xml					= new SimpleXMLElement( trim( file_get_contents( $xmlFile ) ) );
				} catch ( \Exception $e ) {
					$xml							=	null;
					echo "$xmlFile not an XML file!!!";
				}

				if ( $xml !== null ) {
					$xmlVersion				=	$xml->getElementByPath( 'version' );
					if ( $xmlVersion !== false ) {
						$version			=	$xmlVersion->data();
					} else {
						$version			=	'0.0';
					}
					$version				=	preg_replace( '/\.*([a-zA-Z]+)\.*/i', '.$1.', preg_replace( '/^[a-zA-Z]+/i', '', str_replace( array( '-', '_', '+' ), '.', str_replace( ' ', '', strtoupper( $version ) ) ) ) );

					if ( is_integer( $version ) ) {
						$version			=	implode( '.', str_split( $version ) );
					} elseif ( preg_match( '/^(\d{2,})(\.[a-zA-Z].+)/i', $version, $matches ) ) {
						$version			=	implode( '.', str_split( $matches[1] ) ) . $matches[2];
					}

					$version				=	trim( str_replace( '..', '.', $version ), '.' );

					if ( ! ( version_compare( $version, ( $cbVersion[0] . '.0' ) ) >= 0 ) ) {
						$compatible			=	false;
					}
				}
			}

			$cache[$pluginId]				=	$compatible;
		}

		return $cache[$pluginId];
	}

	/**
	 * parses xml for backend plugin management menu display
	 *
	 * @param  null|PluginTable|int  $plugin
	 * @return array
	 */
	public function getPluginBackendMenu( $plugin )
	{
		global $_CB_framework;

		$plugin											=	$this->getCachedPluginObject( $plugin );

		if ( ! $plugin ) {
			return false;
		}

		static $cache									=	array();

		$pluginId										=	(int) $plugin->id;

		if ( ! isset( $cache[$pluginId] ) ) {
			$menus										=	array();

			// Lets parse the XML file for backend menu items and exclude duplicates added by legacy:
			$xmlFile									=	$this->getPluginXmlPath( $plugin );

			if ( file_exists( $xmlFile ) ) {
				$xml									=	new SimpleXMLElement( trim( file_get_contents( $xmlFile ) ) );

				if ( $xml !== null ) {
					$menu								=	$xml->getElementByPath( 'adminmenus' );

					if ( $menu !== false ) {
						if ( ( count( $menu->children() ) > 0 ) ) foreach ( $menu->children() as $menuItem ) {
							$menuItemTask				=	$menuItem->attributes( 'action' );

							if ( ! array_key_exists( $menuItemTask, $menus ) ) {
								$menus[$menuItemTask]	=	array( $menuItem->data(), $_CB_framework->backendUrl( 'index.php?option=com_comprofiler&view=pluginmenu&pluginid=' . (int) $plugin->id . '&menu=' . urlencode( $menuItemTask ), false ) );
							}
						}
					}
				}
			}

			$cache[$pluginId]							=	$menus;
		}

		return $cache[$pluginId];
	}
}

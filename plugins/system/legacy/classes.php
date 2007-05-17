<?php
/**
* @version		$Id$
* @package		Joomla.Legacy
* @subpackage	1.5
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport( 'joomla.database.database.mysql' );
jimport( 'joomla.database.table' );

/**
 * Tables need to be included using a regular include to avoid conflict
 */
include_once(JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'category.php');
include_once(JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'component.php');
include_once(JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'content.php');
include_once(JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'plugin.php');
include_once(JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'menu.php');
include_once(JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'module.php');
include_once(JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'section.php');
include_once(JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'user.php');
include_once(JPATH_ADMINISTRATOR.DS.'includes'.DS.'toolbar.php');

/**
 * Legacy class, derive from {@link JApplication} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosMainFrame extends JApplication
{
	/**
	 * Class constructor
	 * @param database A database connection object
	 * @param string The url option [DEPRECATED]
	 * @param string The path of the mos directory [DEPRECATED]
	 */
	function __construct( &$db, $option, $basePath=null, $client=0 )
	{
		parent::__construct( $client );
	}

	/**
	 * Class constructor
	 * @param database A database connection object
	 * @param string The url option [DEPRECATED]
	 * @param string The path of the mos directory [DEPRECATED]
	 */
	function mosMainFrame( &$db, $option, $basePath=null, $client=0 )
	{
		parent::__construct( $client );
	}

	/**
	 * Initialises the user session
	 *
	 * Old sessions are flushed based on the configuration value for the cookie
	 * lifetime. If an existing session, then the last access time is updated.
	 * If a new session, a session id is generated and a record is created in
	 * the mos_sessions table.
	 */
	function initSession( )
	{
		// TODO: Quick fix to preserve session when going to https
		global $mainframe;
		$this->setSession(str_replace( 'https:', 'http:', $mainframe->getCfg('live_site') ).$this->getClientId());
	}

	/**
	 * Gets the base path for the client
	 * @param mixed A client identifier
	 * @param boolean True (default) to add traling slash
	 */
	function getBasePath( $client=0, $addTrailingSlash=true )
	{
		switch ($client)
		{
			case '0':
			case 'site':
			case 'front':
			default:
				return mosPathName( JPATH_SITE, $addTrailingSlash );
				break;

			case '2':
			case 'installation':
				return mosPathName( JPATH_INSTALLATION, $addTrailingSlash );
				break;

			case '1':
			case 'admin':
			case 'administrator':
				return mosPathName( JPATH_ADMINISTRATOR, $addTrailingSlash );
				break;

		}
	}

	/**
	* Deprecated, use {@link JDocument::setTitle() JDocument->setTitle()} instead or override in your application class
	*
	* @since 1.5
	* @deprecated As of version 1.5
	*/
	function setPageTitle( $title=null )
	{
		$document=& JFactory::getDocument();
		$document->setTitle($title);
	}

	/**
	* Deprecated, use {@link JDocument::getTitle() JDocument->getTitle()} instead or override in your application class
	* @since 1.5
	* @deprecated As of version 1.5
	*/
	function getPageTitle()
	{
		$document=& JFactory::getDocument();
		return $document->getTitle();
	}
}

/**
 * Legacy class, derive from {@link JTable} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosDBTable extends JTable
{
	/**
	 * Constructor
	 */
	function __construct($table, $key, &$db)
	{
		parent::__construct( $table, $key, $db );
	}

	function mosDBTable($table, $key, &$db)
	{
		parent::__construct( $table, $key, $db );
	}

	/**
	 * Legacy Method, use {@link JTable::reorder()} instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )
	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use {@link JTable::publish()} instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 )
	{
		$this->publish( $cid, $publish, $user_id );
	}

	/**
	 * Legacy Method, make sure you use {@link JRequest::get()} or {@link JRequest::getVar()} instead
	 * @deprecated As of 1.5
	 */
	function filter( $ignoreList=null )
	{
		$ignore = is_array( $ignoreList );

		jimport('joomla.filter.input');
		$filter = & JInputFilter::getInstance();
		foreach ($this->getPublicProperties() as $k)
		{
			if ($ignore && in_array( $k, $ignoreList ) )
			{
				continue;
			}
			$this->$k = $filter->clean( $this->$k );
		}
	}
}

/**
 * Legacy class, use {@link JTableCategory} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosCategory extends JTableCategory
{
	/**
	 * Constructor
	 */
	function __construct( &$db)
	{
		parent::__construct( $db );
	}

	function mosCategory(&$db)
	{
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use {@link JTable::reorder()} instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )
	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use {@link JTable::publish()} instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 )
	{
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use {@link JTableComponent} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosComponent extends JTableComponent
{
	/**
	 * Constructor
	 */
	function __construct(&$db)
	{
		parent::__construct( $db );
	}

	function mosComponent( &$db)
	{
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use {@link JTable::reorder()} instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )
	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use {@link JTable::publish()} instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 )
	{
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use {@link JTableContent} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosContent extends JTableContent
{
	/**
	 * Constructor
	 */
	function __construct( &$db )
	{
		parent::__construct( $db );
	}

	function mosComponent( &$db )
	{
		parent::__construct($db );
	}

	/**
	 * Legacy Method, use {@link JTable::reorder()} instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )
	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use {@link JTable::publish()} instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 )
	{
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, replaced by {@link JTablePlugin}
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosMambot extends JTablePlugin
{
	/**
	 * Constructor
	 */
	function __construct(&$db)
	{
		parent::__construct( $db );
	}

	function mosMambot(&$db)
	{
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use {@link JTable::reorder()} instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )
	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use {@link JTable::publish()} instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 )
	{
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use {@link JTableMenu} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosMenu extends JTableMenu
{
	/**
	 * Constructor
	 */
	function __construct(&$db)
	{
		parent::__construct( $db );
	}

	function mosMenu(&$db)
	{
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use {@link JTable::reorder()} instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )
	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use {@link JTable::publish()} instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 )
	{
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use {@link JTableModule} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosModule extends JTableModule
{
	/**
	 * Constructor
	 */
	function __construct(&$db)
	{
		parent::__construct( $db );
	}

	function mosModule(&$db)
	{
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use {@link JTable::reorder()} instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )
	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use {@link JTable::publish()} instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 )
	{
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use {@link JTableSection} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosSection extends JTableSection
{
	/**
	 * Constructor
	 */
	function __construct(&$db)
	{
		parent::__construct( $db );
	}

	function mosSection(&$db)
	{
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use {@link JTable::reorder()} instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )
	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use {@link JTable::publish()} instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 )
	{
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use {@link JTableSession} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosSession extends JTableSession
{
	/**
	 * Constructor
	 */
	function __construct(&$db)
	{
		parent::__construct(  $db );
	}

	function mosSession(&$db)
	{
		parent::__construct( $db );
	}

	/**
	 * Encodes a session id
	 */
	function hash( $value )
	{
		global $mainframe;

		if (phpversion() <= '4.2.1') {
			$agent = getenv( 'HTTP_USER_AGENT' );
		} else {
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}

		return md5( $agent . $mainframe->getCfg('secret') . $value . $_SERVER['REMOTE_ADDR'] );
	}

	/**
	 * Set the information to allow a session to persist
	 */
	function persist()
	{
		global $mainframe;

		$usercookie = mosGetParam( $_COOKIE, 'usercookie', null );
		if ($usercookie) {
			// Remember me cookie exists. Login with usercookie info.
			$mainframe->login( $usercookie['username'], $usercookie['password'] );
		}
	}

	/**
	 * Legacy Method, use {@link JTable::reorder()} instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )
	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use {@link JTable::publish()} instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 )
	{
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use {@link JTableUser} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosUser extends JTableUser
{
	/**
	 * Constructor
	 */
	function __construct(&$db)
	{
		parent::__construct( $db );
	}

	function mosUser(&$db)
	{
		parent::__construct( $db);
	}

	/**
	 * Legacy Method, use {@link JTable::reorder()} instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )
	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use {@link JTable::publish()} instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 )
	{
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use {@link JDatabase} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class database extends JDatabaseMySQL
{
	function __construct ($host='localhost', $user, $password, $database='', $prefix='', $offline = true)
	{
		$options        = array ( 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix );
		parent::__construct( $options );
	}

	/**
	* This global function loads the first row of a query into an object
	*
	* If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
	* If <var>object</var> has a value of null, then all of the returned query fields returned in the object.
	*
	* @param object The address of variable
	*/
	function loadObject( &$object )
	{
		if ($object != null)
		{
			if (!($cur = $this->query())) {
				return false;
			}

			if ($array = mysql_fetch_assoc( $cur ))
			{
				mysql_free_result( $cur );
				mosBindArrayToObject( $array, $object, null, null, false );
				return true;
			} else {
				return false;
			}

		}
		else
		{
			$object = parent::loadObject();
			return $object;
		}
	}

	/**
	* Execute a batch query
	*
	* @abstract
	* @access public
	* @return mixed A database resource if successful, FALSE if not.
	*/
	function query_batch( $abort_on_error=true, $p_transaction_safe = false)
	{
		return parent::queryBatch( $abort_on_error, $p_transaction_safe);
	}
}

 /**
 * Legacy class, use &{@link JFactory::getCache()} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosCache
{
	/**
	* @return object A function cache object
	*/
	function &getCache(  $group=''  )
	{
		return JFactory::getCache($group);
	}
	/**
	* Cleans the cache
	*/
	function cleanCache( $group=false )
	{
		$cache =& JFactory::getCache($group);
		$cache->clean($group);
	}
}

 /**
 * Legacy class, use {@link JProfiler} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
jimport('joomla.utilities.profiler');
class mosProfiler extends JProfiler
{
	/**
	* @return object A function cache object
	*/
	function JProfiler (  $prefix=''  )
	{
		parent::__construct($prefix);
	}
}

 /**
 * Legacy class, use {@link JParameter} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosParameters extends JParameter
{
	/**
	* @param string The raw parms text
	* @param string Path to the xml setup file
	* @param string The type of setup file
	*/
	function __construct($text, $path = '', $type = 'component')
	{
		parent::__construct($text, $path);
	}

	/**
	 * Legacy function, use {@link JParameter::toObject() JParameter->toObject()} instead
	 *
	 * @deprecated As of version 1.5
	 */
	function toObject()
	{
		parent::toObject();
	}

	/**
	 * Legacy function, use {@link JParameter::toArray() JParameter->toArray()} instead
	 *
	 * @deprecated As of version 1.5
	 */
	function toArray()
	{
		parent::toArray();
	}

	/**
	 * Parse an .ini string, based on phpDocumentor phpDocumentor_parse_ini_file function
	 *
	 * @access public
	 * @param mixed The ini string or array of lines
	 * @param boolean add an associative index for each section [in brackets]
	 * @return object
	 */
	function parse($txt, $process_sections = false, $asArray = false)
	{
		$this->loadINI($txt);

		if($asArray) {
			return $this->toArray();
		}

		return $this->toObject( );
	}

	/**
	* Special handling for textarea param
	*/
	function textareaHandling( &$txt )
	{
		$total = count( $txt );
		for( $i=0; $i < $total; $i++ ) {
			if ( strstr( $txt[$i], "\n" ) ) {
				$txt[$i] = str_replace( "\n", '<br />', $txt[$i] );
			}
		}
		$txt = implode( "\n", $txt );

		return $txt;
	}
}

/**
 * Legacy class, replaced by full MVC implementation.  See {@link JController}
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosAbstractTasker
{
	function mosAbstractTasker()
	{
		die( 'mosAbstractTasker deprecated, use JController instead' );
	}
}

/**
 * Legacy class, use {@link JEventDispatcher} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosMambotHandler extends JEventDispatcher
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	* Loads all the bot files for a particular group
	* @param string The group name, relates to the sub-directory in the plugins directory
	*/
	function loadBotGroup( $group )
	{
		return JPluginHelper::importPlugin($group, null, false);
	}
	/**
	 * Loads the bot file
	 * @param string The folder (group)
	 * @param string The elements (name of file without extension)
	 * @param int Published state
	 * @param string The params for the bot
	 */
	function loadBot( $folder, $element, $published, $params='' )
	{
		return JPluginHelper::_import($folder, $element, $published, $params='' );
	}

	/**
	* Registers a function to a particular event group
	*
	* @param string The event name
	* @param string The function name
	*/
	function registerFunction( $event, $function )
	{
		 JApplication::registerEvent( $event, $function );
	}

	/**
	* Deprecated, use {@link JEventDispatcher::trigger() JEventDispatcher->trigger()} instead and handle return values
	* in your code
	*
	* @param string The event name
	* @since 1.5
	* @deprecated As of 1.5
	*/
	function call($event)
	{
		$args = & func_get_args();
		array_shift($args);

		$retArray = $this->trigger( $event, $args );
		return $retArray[0];
	}
}

/**
 * Legacy class, removed
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosEmpty
{
	function def( $key, $value='' )
	{
		return 1;
	}
	function get( $key, $default='' )
	{
		return 1;
	}
}

/**
 * Legacy class, use {@link JHTML} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosHTML
{
	/**
 	 * Legacy function, use {@link JHTML::_('select.option')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function makeOption( $value, $text='', $value_name='value', $text_name='text' )
	{
		return JHTML::_('select.option', $value, $text, $value_name, $text_name);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('select.genericlist')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function selectList( &$arr, $tag_name, $tag_attribs, $key, $text, $selected=NULL, $idtag=false, $flag=false )
	{
		return JHTML::_('select.genericlist', $arr, $tag_name, $tag_attribs, $key, $text, $selected, $idtag, $flag );
	}

	/**
 	 * Legacy function, use {@link JHTML::_('select.integerlist')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function integerSelectList( $start, $end, $inc, $tag_name, $tag_attribs, $selected, $format="" )
	{
		return JHTML::_('select.integerlist', $start, $end, $inc, $tag_name, $tag_attribs, $selected, $format) ;
	}

	/**
 	 * Legacy function, use {@link JHTML::_('select.radiolist')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function radioList( &$arr, $tag_name, $tag_attribs, $selected=null, $key='value', $text='text', $idtag=false )
	{
		return JHTML::_('select.radiolist', $arr, $tag_name, $tag_attribs, $selected, $key, $text, $idtag) ;
	}

	/**
 	 * Legacy function, use {@link JHTML::_('select.booleanlist')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function yesnoRadioList( $tag_name, $tag_attribs, $selected, $yes='yes', $no='no', $id=false )
	{
		return JHTML::_('select.booleanlist',  $tag_name, $tag_attribs, $selected, $yes, $no, $id ) ;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function writableCell( $folder, $relative=1, $text='', $visible=1 )
	{
		$writeable 		= '<b><font color="green">'. JText::_( 'Writeable' ) .'</font></b>';
		$unwriteable 	= '<b><font color="red">'. JText::_( 'Unwriteable' ) .'</font></b>';

		echo '<tr>';
		echo '<td class="item">';
		echo $text;
		if ( $visible ) {
			echo $folder . '/';
		}
		echo '</td>';
		echo '<td >';
		if ( $relative ) {
			echo is_writable( "../$folder" ) 	? $writeable : $unwriteable;
		} else {
			echo is_writable( "$folder" ) 		? $writeable : $unwriteable;
		}
		echo '</td>';
		echo '</tr>';
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function monthSelectList( $tag_name, $tag_attribs, $selected )
	{
		$arr = array(
			mosHTML::makeOption( '01', JText::_( 'JAN' ) ),
			mosHTML::makeOption( '02', JText::_( 'FEB' ) ),
			mosHTML::makeOption( '03', JText::_( 'MAR' ) ),
			mosHTML::makeOption( '04', JText::_( 'APR' ) ),
			mosHTML::makeOption( '05', JText::_( 'MAY' ) ),
			mosHTML::makeOption( '06', JText::_( 'JUN' ) ),
			mosHTML::makeOption( '07', JText::_( 'JUL' ) ),
			mosHTML::makeOption( '08', JText::_( 'AUG' ) ),
			mosHTML::makeOption( '09', JText::_( 'SEP' ) ),
			mosHTML::makeOption( '10', JText::_( 'OCT' ) ),
			mosHTML::makeOption( '11', JText::_( 'NOV' ) ),
			mosHTML::makeOption( '12', JText::_( 'DEC' ) )
		);

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', $selected );
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function treeSelectList( &$src_list, $src_id, $tgt_list, $tag_name, $tag_attribs, $key, $text, $selected )
	{

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ($src_list as $v ) {
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$ilist = JHTML::_('menu.treerecurse', 0, '', array(), $children );

		// assemble menu items to the array
		$this_treename = '';
		foreach ($ilist as $item) {
			if ($this_treename) {
				if ($item->id != $src_id && strpos( $item->treename, $this_treename ) === false) {
					$tgt_list[] = mosHTML::makeOption( $item->id, $item->treename );
				}
			} else {
				if ($item->id != $src_id) {
					$tgt_list[] = mosHTML::makeOption( $item->id, $item->treename );
				} else {
					$this_treename = "$item->treename/";
				}
			}
		}
		// build the html select list
		return mosHTML::selectList( $tgt_list, $tag_name, $tag_attribs, $key, $text, $selected );
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function yesnoSelectList( $tag_name, $tag_attribs, $selected, $yes='yes', $no='no' )
	{
		$arr = array(
			mosHTML::makeOption( 0, JText::_( $no ) ),
			mosHTML::makeOption( 1, JText::_( $yes ) ),
		);

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', (int) $selected );
	}

	/**
 	 * Legacy function, use {@link JHTML::_('grid.id')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function idBox( $rowNum, $recId, $checkedOut=false, $name='cid' )
	{
		return JHTML::_('grid.id', $rowNum, $recId, $checkedOut, $name);
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function sortIcon( $text, $base_href, $field, $state='none' )
	{
		$alts = array(
			'none' 	=> JText::_( 'No Sorting' ),
			'asc' 	=> JText::_( 'Sort Ascending' ),
			'desc' 	=> JText::_( 'Sort Descending' ),
		);

		$next_state = 'asc';
		if ($state == 'asc') {
			$next_state = 'desc';
		} else if ($state == 'desc') {
			$next_state = 'none';
		}

		if ($state == 'none') {
			$img = '';
		} else {
			$img = "<img src=\"images/sort_$state.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"{$alts[$next_state]}\" />";
		}

		$html = "<a href=\"$base_href&field=$field&order=$next_state\">"
		. JText::_( $text )
		. '&nbsp;&nbsp;'
		. $img
		. "</a>";

		return $html;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function CloseButton ( &$params, $hide_js=NULL )
	{

		// displays close button in Pop-up window
		if ( $params->get( 'popup' ) && !$hide_js ) {
			?>
			<div align="center" style="margin-top: 30px; margin-bottom: 30px;">
				<script type="text/javascript">
					document.write('<a href="#" onclick="javascript:window.close();"><span class="small"><?php echo JText::_( 'Close Window' );?></span></a>');
				</script>
				<?php
				if ( $_SERVER['HTTP_REFERER'] != "") {
					echo '<noscript>';
					echo '<a href="'. $_SERVER['HTTP_REFERER'] .'"><span class="small">'. JText::_( 'BACK' ) .'</span></a>';
					echo '</noscript>';
				}
				?>
			</div>
			<?php
		}
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function BackButton ( &$params, $hide_js=NULL )
	{

		// Back Button
		if ( $params->get( 'back_button' ) && !$params->get( 'popup' ) && !$hide_js) {
			?>
			<div class="back_button">
				<a href='javascript:history.go(-1)'>
					<?php echo JText::_( 'BACK' ); ?></a>
			</div>
			<?php
		}
	}

	/**
 	 * Legacy function, use {@link JOutputFilter::cleanText()} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function cleanText ( &$text )
	{
		jimport('joomla.filter.output');
		return JOutputFilter::cleanText($text);
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function PrintIcon( &$row, &$params, $hide_js, $link, $status=NULL )
	{

		if ( $params->get( 'print' )  && !$hide_js ) {
			// use default settings if none declared
			if ( !$status ) {
				$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
			}

			// checks template image directory for image, if non found default are loaded
			if ( $params->get( 'icons' ) ) {
				$image = mosAdminMenus::ImageCheck( 'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'Print' ), JText::_( 'Print' ) );
			} else {
				$image = JText::_( 'ICON_SEP' ) .'&nbsp;'. JText::_( 'Print' ) .'&nbsp;'. JText::_( 'ICON_SEP' );
			}

			if ( $params->get( 'popup' ) && !$hide_js ) {
				// Print Preview button - used when viewing page
				?>
				<script type="text/javascript">
					document.write('<td align="right" width="100%" class="buttonheading">');
					document.write('<a href="#" onclick="javascript:window.print(); return false" title="<?php echo JText::_( 'Print' );?>">');
					document.write('<?php echo $image;?>');
					document.write('</a>');
					document.write('</td>');
				</script>
				<?php
			} else {
				// Print Button - used in pop-up window
				?>
				<td align="right" width="100%" class="buttonheading">
				<a href="<?php echo $link; ?>" onclick="window.open('<?php echo $link; ?>','win2','<?php echo $status; ?>'); return false;" title="<?php echo JText::_( 'Print' );?>">
				<?php echo $image;?>
				</a>
				</td>
				<?php
			}
		}
	}

	/**
 	 * Legacy function, use {@link JHTML::_('email.cloak')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function emailCloaking( $mail, $mailto=1, $text='', $email=1 )
	{
		return JHTML::_('email.cloak', $mail, $mailto, $text, $email);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('behavior.keepalive')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function keepAlive()
	{
		JHTML::_('behavior.keepalive');
	}
}

/**
 * Legacy class, use {@link JHTML::_()} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosCommonHTML
{
	/**
 	 * Legacy function, use {@link JHTML::_('legend');} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function ContentLegend( )
	{
		JHTML::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_content'.DS.'html' );
		JHTML::_('grid.legend');
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function menuLinksContent( &$menus )
	{
		foreach( $menus as $menu ) {
			?>
			<tr>
				<td colspan="2">
					<hr />
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
					<?php echo JText::_( 'Menu' ); ?>
				</td>
				<td>
					<a href="javascript:go2('go2menu','<?php echo $menu->menutype; ?>');" title="<?php echo JText::_( 'Go to Menu' ); ?>">
						<?php echo $menu->menutype; ?></a>
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
				<?php echo JText::_( 'Link Name' ); ?>
				</td>
				<td>
					<strong>
					<a href="javascript:go2('go2menuitem','<?php echo $menu->menutype; ?>','<?php echo $menu->id; ?>');" title="<?php echo JText::_( 'Go to Menu Item' ); ?>">
						<?php echo $menu->name; ?></a>
					</strong>
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
					<?php echo JText::_( 'State' ); ?>
				</td>
				<td>
					<?php
					switch ( $menu->published ) {
						case -2:
							echo '<font color="red">'. JText::_( 'Trashed' ) .'</font>';
							break;
						case 0:
							echo JText::_( 'UnPublished' );
							break;
						case 1:
						default:
							echo '<font color="green">'. JText::_( 'Published' ) .'</font>';
							break;
					}
					?>
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td colspan="2">
				<input type="hidden" name="menu" value="" />
				<input type="hidden" name="menuid" value="" />
			</td>
		</tr>
		<?php
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function menuLinksSecCat( &$menus )
	{
		$i = 1;
		foreach( $menus as $menu ) {
			?>
			<fieldset>
				<legend align="right"> <?php echo $i; ?>. </legend>

				<table class="admintable">
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'Menu' ); ?>
					</td>
					<td>
						<a href="javascript:go2('go2menu','<?php echo $menu->menutype; ?>');" title="<?php echo JText::_( 'Go to Menu' ); ?>">
							<?php echo $menu->menutype; ?></a>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'Type' ); ?>
					</td>
					<td>
						<?php echo $menu->type; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'Item Name' ); ?>
					</td>
					<td>
						<strong>
						<a href="javascript:go2('go2menuitem','<?php echo $menu->menutype; ?>','<?php echo $menu->id; ?>');" title="<?php echo JText::_( 'Go to Menu Item' ); ?>">
							<?php echo $menu->name; ?></a>
						</strong>
					</td>
				</tr>
				<tr>
					<td valign="top" class="key">
						<?php echo JText::_( 'State' ); ?>
					</td>
					<td>
						<?php
						switch ( $menu->published ) {
							case -2:
								echo '<font color="red">'. JText::_( 'Trashed' ) .'</font>';
								break;
							case 0:
								echo JText::_( 'UnPublished' );
								break;
							case 1:
							default:
								echo '<font color="green">'. JText::_( 'Published' ) .'</font>';
								break;
						}
						?>
					</td>
				</tr>
				</table>
			</fieldset>
			<?php
			$i++;
		}
		?>
		<input type="hidden" name="menu" value="" />
		<input type="hidden" name="menuid" value="" />
		<?php
	}

	/**
 	 * Legacy function, use {@link JHTMLGrid::checkedOut()} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function checkedOut( &$row, $overlib=1 )
	{
		return JHTMLGrid::_checkedOut($row, $overlib);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('behavior.tooltip')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function loadOverlib()
	{
		JHTML::_('behavior.tooltip');
	}

	/**
 	 * Legacy function, use {@link JHTML::_('behavior.calendar')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function loadCalendar()
	{
		JHTML::_('behavior.calendar');
	}

	/**
 	 * Legacy function, use {@link JHTML::_('grid.access')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function AccessProcessing( &$row, $i, $archived=NULL )
	{
		return JHTML::_('grid.access',  $row, $i, $archived);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('grid.checkedout')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function CheckedOutProcessing( &$row, $i )
	{
		return JHTML::_('grid.checkedout',  $row, $i);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('grid.published')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function PublishedProcessing( &$row, $i, $imgY='tick.png', $imgX='publish_x.png' )
	{
		return JHTML::_('grid.published',$row, $i, $imgY, $imgX);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('grid.state')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function selectState( $filter_state=NULL, $published='Published', $unpublished='Unpublished', $archived=NULL )
	{
		return JHTML::_('grid.state', $filter_state, $published, $unpublished, $archived);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('grid.order')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function saveorderButton( $rows, $image='filesave.png' )
	{
		echo JHTML::_('grid.order', $rows, $image);
	}

	/**
 	 * Legacy function, use {@link echo JHTML::_('grid.sort')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function tableOrdering( $text, $ordering, &$lists, $task=NULL )
	{
		// TODO: We may have to invert order_Dir here because this control now does the flip for you
		echo JHTML::_('grid.sort',  $text, $ordering, @$lists['order_Dir'], @$lists['order'], $task);
	}
}

/**
 * Utility class for drawing admin menu HTML elements
 *
 * @static
 * @package 	Joomla.Legacy
 * @subpackage	1.5
 * @since	1.0
 * @deprecated	As of version 1.5
 */
class mosAdminMenus
{
	/**
 	 * Legacy function, use {@link JHTML::_('menu.ordering')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Ordering( &$row, $id )
	{
		return JHTML::_('menu.ordering', $row, $id);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('list.accesslevel', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Access( &$row )
	{
		return JHTML::_('list.accesslevel', $row);
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Published( &$row )
	{
		$published = JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $row->published );
		return $published;
	}

	/**
 	 * Legacy function, use {@link JAdminMenus::MenuLinks()} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function MenuLinks( &$lookup, $all=NULL, $none=NULL, $unassigned=1 )
	{
		$options = JHTML::_('menu.linkoptions', $lookup, $all, $none|$unassigned);
		if (empty( $lookup )) {
			$lookup = array( JHTML::_('select.option',  -1 ) );
		}
		$pages = JHTML::_('select.genericlist',   $options, 'selections[]', 'class="inputbox" size="15" multiple="multiple"', 'value', 'text', $lookup, 'selections' );
		return $pages;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Category( &$menu, $id, $javascript='' )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT c.id AS `value`, c.section AS `id`, CONCAT_WS( " / ", s.title, c.title) AS `text`'
		. ' FROM #__sections AS s'
		. ' INNER JOIN #__categories AS c ON c.section = s.id'
		. ' WHERE s.scope = "content"'
		. ' ORDER BY s.name, c.name'
		;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		$category = '';

		$category .= JHTML::_('select.genericlist',   $rows, 'componentid', 'class="inputbox" size="10"'. $javascript, 'value', 'text', $menu->componentid );
		$category .= '<input type="hidden" name="link" value="" />';

		return $category;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Section( &$menu, $id, $all=0 )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT s.id AS `value`, s.id AS `id`, s.title AS `text`'
		. ' FROM #__sections AS s'
		. ' WHERE s.scope = "content"'
		. ' ORDER BY s.name'
		;
		$db->setQuery( $query );
		if ( $all ) {
			$rows[] = JHTML::_('select.option',  0, '- '. JText::_( 'All Sections' ) .' -' );
			$rows = array_merge( $rows, $db->loadObjectList() );
		} else {
			$rows = $db->loadObjectList();
		}

		$section = JHTML::_('select.genericlist',   $rows, 'componentid', 'class="inputbox" size="10"', 'value', 'text', $menu->componentid );
		$section .= '<input type="hidden" name="link" value="" />';

		return $section;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Component( &$menu, $id )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT c.id AS value, c.name AS text, c.link'
		. ' FROM #__components AS c'
		. ' WHERE c.link <> ""'
		. ' ORDER BY c.name'
		;
		$db->setQuery( $query );
		$rows = $db->loadObjectList( );

		$component = JHTML::_('select.genericlist',   $rows, 'componentid', 'class="inputbox" size="10"', 'value', 'text', $menu->componentid, '', 1 );

		return $component;
	}


	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function ComponentName( &$menu, $id )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT c.id AS value, c.name AS text, c.link'
		. ' FROM #__components AS c'
		. ' WHERE c.link <> ""'
		. ' ORDER BY c.name'
		;
		$db->setQuery( $query );
		$rows = $db->loadObjectList( );

		$component = 'Component';
		foreach ( $rows as $row ) {
			if ( $row->value == $menu->componentid ) {
				$component = JText::_( $row->text );
			}
		}

		return $component;
	}


	/**
 	 * Legacy function, use {@link JHTML::_('list.images', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Images( $name, &$active, $javascript=NULL, $directory=NULL )
	{
		return JHTML::_('list.images', $name, $active, $javascript, $directory);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('list.specificordering', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function SpecificOrdering( &$row, $id, $query, $neworder=0 )
	{
		return JHTML::_('list.specificordering', $row, $id, $query, $neworder);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('list.users', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function UserSelect( $name, $active, $nouser=0, $javascript=NULL, $order='name', $reg=1 )
	{
		return JHTML::_('list.users', $name, $active, $nouser, $javascript, $order, $reg);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('list.positions', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Positions( $name, $active=NULL, $javascript=NULL, $none=1, $center=1, $left=1, $right=1, $id=false )
	{
		return JHTML::_('list.positions', $name, $active, $javascript, $none, $center, $left, $right, $id);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('list.category', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function ComponentCategory( $name, $section, $active=NULL, $javascript=NULL, $order='ordering', $size=1, $sel_cat=1 )
	{
		return JHTML::_('list.category', $name, $section, $active, $javascript, $order, $size, $sel_cat);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('list.section', )} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function SelectSection( $name, $active=NULL, $javascript=NULL, $order='ordering' )
	{
		return JHTML::_('list.section', $name, $active, $javascript, $order);
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function Links2Menu( $type, $and )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT * '
		. ' FROM #__menu '
		. ' WHERE type = "'.$type.'"'
		. ' AND published = 1'
		. $and
		;
		$db->setQuery( $query );
		$menus = $db->loadObjectList();

		return $menus;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function MenuSelect( $name='menuselect', $javascript=NULL )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT params'
		. ' FROM #__modules'
		. ' WHERE module = "mod_mainmenu"'
		;
		$db->setQuery( $query );
		$menus = $db->loadObjectList();
		$total = count( $menus );
		$menuselect = array();
		for( $i = 0; $i < $total; $i++ )
		{
			$registry = new JRegistry();
			$registry->loadINI($menus[$i]->params);
			$params = $registry->toObject( );

			$menuselect[$i]->value 	= $params->menutype;
			$menuselect[$i]->text 	= $params->menutype;
		}
		// sort array of objects
		JArrayHelper::sortObjects( $menuselect, 'text', 1 );

		$menus = JHTML::_('select.genericlist',   $menuselect, $name, 'class="inputbox" size="10" '. $javascript, 'value', 'text' );

		return $menus;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function ReadImages( $imagePath, $folderPath, &$folders, &$images )
	{
		jimport( 'joomla.filesystem.folder' );
		$imgFiles = JFolder::files( $imagePath );

		foreach ($imgFiles as $file)
		{
			$ff_ 	= $folderPath.DS.$file;
			$ff 	= $folderPath.DS.$file;
			$i_f 	= $imagePath .'/'. $file;

			if ( is_dir( $i_f ) && $file <> 'CVS' && $file <> '.svn') {
				$folders[] = JHTML::_('select.option',  $ff_ );
				mosAdminMenus::ReadImages( $i_f, $ff_, $folders, $images );
			} else if ( eregi( "bmp|gif|jpg|png", $file ) && is_file( $i_f ) ) {
				// leading / we don't need
				$imageFile = substr( $ff, 1 );
				$images[$folderPath][] = JHTML::_('select.option',  $imageFile, $file );
			}
		}
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function GetImageFolders( &$folders, $path )
	{
		$javascript 	= "onchange=\"changeDynaList( 'imagefiles', folderimages, document.adminForm.folders.options[document.adminForm.folders.selectedIndex].value, 0, 0);  previewImage( 'imagefiles', 'view_imagefiles', '$path/' );\"";
		$getfolders 	= JHTML::_('select.genericlist',   $folders, 'folders', 'class="inputbox" size="1" '. $javascript, 'value', 'text', '/' );
		return $getfolders;
	}

	/**
	 * Legacy function, deprecated
	 *
	 * @deprecated	As of version 1.5
	 */
	function GetImages( &$images, $path )
	{
		if ( !isset($images['/'] ) ) {
			$images['/'][] = JHTML::_('select.option',  '' );
		}

		//$javascript	= "onchange=\"previewImage( 'imagefiles', 'view_imagefiles', '$path/' )\" onfocus=\"previewImage( 'imagefiles', 'view_imagefiles', '$path/' )\"";
		$javascript	= "onchange=\"previewImage( 'imagefiles', 'view_imagefiles', '$path/' )\"";
		$getimages	= JHTML::_('select.genericlist',   $images['/'], 'imagefiles', 'class="inputbox" size="10" multiple="multiple" '. $javascript , 'value', 'text', null );

		return $getimages;
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function GetSavedImages( &$row, $path )
	{
		$images2 = array();
		foreach( $row->images as $file ) {
			$temp = explode( '|', $file );
			if( strrchr($temp[0], '/') ) {
				$filename = substr( strrchr($temp[0], '/' ), 1 );
			} else {
				$filename = $temp[0];
			}
			$images2[] = JHTML::_('select.option',  $file, $filename );
		}
		//$javascript	= "onchange=\"previewImage( 'imagelist', 'view_imagelist', '$path/' ); showImageProps( '$path/' ); \" onfocus=\"previewImage( 'imagelist', 'view_imagelist', '$path/' )\"";
		$javascript	= "onchange=\"previewImage( 'imagelist', 'view_imagelist', '$path/' ); showImageProps( '$path/' ); \"";
		$imagelist 	= JHTML::_('select.genericlist',   $images2, 'imagelist', 'class="inputbox" size="10" '. $javascript, 'value', 'text' );

		return $imagelist;
	}

	/**
 	 * Legacy function, use {@link JHTML::_('image.site')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function ImageCheck( $file, $directory='/images/M_images/', $param=NULL, $param_directory='/images/M_images/', $alt=NULL, $name='image', $type=1, $align='top' )
	{
		return JHTML::_('image.site', $file, $directory, $param, $param_directory, $alt, $name, $type, $align);
	}

	/**
 	 * Legacy function, use {@link JHTML::_('image.administrator')} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function ImageCheckAdmin( $file, $directory='/images/', $param=NULL, $param_directory='/images/', $alt=NULL, $name=NULL, $type=1, $align='middle' )
	{
		return JHTML::_('image.administrator', $file, $directory, $param, $param_directory, $alt, $name, $type, $align);
	}

	/**
 	 * Legacy function, use {@link JMenuHelper::getMenuTypes()} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function menutypes()
	{
		JError::raiseNotice( 0, 'mosAdminMenus::menutypes method deprecated' );
	}

	/**
 	 * Legacy function, use {@link JMenuHelper::menuItem()} instead
 	 *
 	 * @deprecated	As of version 1.5
 	*/
	function menuItem( $item )
	{
		JError::raiseNotice( 0, 'mosAdminMenus::menuItem method deprecated' );
	}
}

/**
 * Legacy class, removed
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class MENU_Default
{
	function MENU_Default()
	{
		JToolBarHelper::startTable();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList();
		JToolBarHelper::spacer();
		JToolBarHelper::endTable();
	}
}

/**
 * Legacy class, use {@link JPanel} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
jimport('joomla.html.pane');
class mosTabs extends JPaneTabs
{
	var $useCookies = false;

	function __construct( $useCookies, $xhtml = null)
	{
		parent::__construct( array('useCookies' => $useCookies) );
	}

	function startTab( $tabText, $paneid )
	{
		$this->startPanel( $tabText, $paneid);
	}

	function endTab()
	{
		$this->endPanel();
	}
}

/**
 * Legacy class, use {@link JTemplate::getInstance()} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class patFactory
{
	function &createTemplate( $option, $isAdmin=false, $useCache=false )
	{
		global $mainframe;

		$bodyHtml='';
		$files=null;

		jimport('joomla.template.template');
		$tmpl = new JTemplate();

		// load the wrapper and common templates
		$tmpl->readTemplatesFromFile( 'page.html' );
		$tmpl->applyInputFilter('ShortModifiers');

		// load the stock templates
		if (is_array( $files )) {
			foreach ($files as $file)
			{
				$tmpl->readTemplatesFromInput( $file );
			}
		}

		// TODO: Do the protocol better
		$tmpl->addVar( 'form', 'formAction', basename($_SERVER['PHP_SELF']) );
		$tmpl->addVar( 'form', 'formName', 'adminForm' );

		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl');
		$tmpl->setNamespace( 'mos' );

		if ($bodyHtml) {
			$tmpl->setAttribute( 'body', 'src', $bodyHtml );
		}
		return $tmpl;
	}
}

/**
 * Legacy class, use {@link JToolBarHelper} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosMenuBar extends JToolBarHelper
{
	/**
	* @deprecated As of Version 1.5
	*/
	function startTable()
	{
		return;
	}

	/**
	* @deprecated As of Version 1.5
	*/
	function endTable()
	{
		return;
	}

	/**
	 * Default $task has been changed to edit instead of new
	 *
	 * @deprecated As of Version 1.5
	 */
	function addNew($task = 'new', $alt = 'New')
	{
		parent::addNew($task, $alt);
	}

	/**
	 * Default $task has been changed to edit instead of new
	 *
	 * @deprecated As of Version 1.5
	 */
	function addNewX($task = 'new', $alt = 'New')
	{
		parent::addNew($task, $alt);
	}

	/**
	 * Deprecated
	 *
	 * @deprecated As of Version 1.5
	 */
	function saveedit()
	{
		parent::save('saveedit');
	}

}
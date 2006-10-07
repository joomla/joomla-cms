<?php
/**
* @version $Id$
* @package Joomla.Legacy
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.database.database' );
jimport( 'joomla.database.database.mysql' );
jimport( 'joomla.database.table' );
jimport( 'joomla.database.table.category'  );
jimport( 'joomla.database.table.component' );
jimport( 'joomla.database.table.content'   );
jimport( 'joomla.database.table.plugin'    );
jimport( 'joomla.database.table.menu'      );
jimport( 'joomla.database.table.module'    );
jimport( 'joomla.database.table.section'   );

/**
 * Legacy class, derive from JApplication instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
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
	function __construct( &$db, $option, $basePath=null, $client=0 ) {
		parent::__construct( $client );
	}

	/**
	 * Class constructor
	 * @param database A database connection object
	 * @param string The url option [DEPRECATED]
	 * @param string The path of the mos directory [DEPRECATED]
	 */
	function mosMainFrame( &$db, $option, $basePath=null, $client=0 ) {
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
	function initSession( ) {
		// TODO: Quick fix to preserve session when going to https
		global $mainframe;
		$this->setSession(str_replace( 'https:', 'http:', $mainframe->getCfg('live_site') ).$this->getClientId());
	}

	/**
	 * Gets the base path for the client
	 * @param mixed A client identifier
	 * @param boolean True (default) to add traling slash
	 */
	function getBasePath( $client=0, $addTrailingSlash=true ) {
		switch ($client) {
			case '0':
			case 'site':
			case 'front':
			default:
				return mosPathName( JPATH_BASE, $addTrailingSlash );
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
	* Depreacted, use JDocument->setTitle instead or override in your application class
	* @since 1.5
	*/
	function setPageTitle( $title=null ) {
		$document=& JFactory::getDocument();
		$document->setTitle($title);
	}

	/**
	* Depreacted, use JDocument->getTitle instead or override in your application class
	* @since 1.5
	*/
	function getPageTitle() {
		$document=& JFactory::getDocument();
		return $document->getTitle();
	}

	/**
	* Depreacted, use JFactory::getUser instead
	* @since 1.5
	*/
	function &getUser() {
		$user =& JFactory::getUser();
		return $user;
	}
}

/**
 * Legacy class, derive from JTable instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosDBTable extends JTable
{
	/**
	 * Constructor
	 */
	function __construct($table, $key, &$db) {
		parent::__construct( $table, $key, $db );
	}

	function mosDBTable($table, $key, &$db) {
		parent::__construct( $table, $key, $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
	
	/**
	 * Legacy Method, make sure u use JRequest::get or JRequest::getVar
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
 * Legacy class, use JTableCategory instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosCategory extends JTableCategory
{
	/**
	 * Constructor
	 */
	function __construct( &$db) {
		parent::__construct( $db );
	}

	function mosCategory(&$db) {
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableComponent instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosComponent extends JTableComponent
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosComponent( &$db) {
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableContent instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosContent extends JTableContent
{
	/**
	 * Constructor
	 */
	function __construct( &$db ) {
		parent::__construct( $db );
	}

	function mosComponent( &$db ) {
		parent::__construct($db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, replaced by JTablePlugin
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosMambot extends JTablePlugin
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosMambot(&$db) {
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableMenu instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosMenu extends JTableMenu
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosMenu(&$db) {
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableModule instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosModule extends JTableModule
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosModule(&$db) {
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableSection instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosSection extends JTableSection
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosSection(&$db) {
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableSession instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosSession extends JTableSession
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct(  $db );
	}

	function mosSession(&$db) {
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
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableUser instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosUser extends JTableUser
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosUser(&$db) {
		parent::__construct( $db);
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JDatabase
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class database extends JDatabaseMySQL
{
	function __construct ($host='localhost', $user, $pass, $db='', $table_prefix='', $offline = true) {
		parent::__construct( $host, $user, $pass, $db, $table_prefix );
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
}

 /**
 * Legacy class, use & JFactory::getCache instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosCache
{
	/**
	* @return object A function cache object
	*/
	function &getCache(  $group=''  ) {
		return JFactory::getCache($group);
	}
	/**
	* Cleans the cache
	*/
	function cleanCache( $group=false ) {
		$cache =& JFactory::getCache($group);
		$cache->cleanCache($group);
	}
}

 /**
 * Legacy class, use JProfiler instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
jimport('joomla.utilities.profiler');
class mosProfiler extends JProfiler
{
	/**
	* @return object A function cache object
	*/
	function JProfiler (  $prefix=''  ) {
		parent::__construct($prefix);
	}
}

 /**
 * Legacy class, use JParameter instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosParameters extends JParameter
{
	/**
	* @param string The raw parms text
	* @param string Path to the xml setup file
	* @param string The type of setup file
	*/
	function __construct($text, $path = '', $type = 'component') {
		parent::__construct($text, $path);
	}

	/**
	 * Legacy function, use JParameter->toObject instead
	 *
	 * @deprecated As of version 1.5
	 */
	function toObject() {
		$this->toObject();
	}

	/**
	 * Legacy function, use JParameter->toArray instead
	 *
	 * @deprecated As of version 1.5
	 */
	function toArray() {
		$this->toArray();
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
	function textareaHandling( &$txt ) {
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
 * Legacy class, replaced by full MVC implementation
 *
 * @deprecated	As of version 1.5, use JController instead
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosAbstractTasker {
	function mosAbstractTasker() {
		die( 'mosAbstractTasker deprecated, use JController instead' );
	}
}

/**
 * Legacy class, use JEventDispatcher instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosMambotHandler extends JEventDispatcher
{
	function __construct() {
		parent::__construct();
	}

	/**
	* Loads all the bot files for a particular group
	* @param string The group name, relates to the sub-directory in the plugins directory
	*/
	function loadBotGroup( $group ) {
		return JPluginHelper::importPlugin($group);
	}
	/**
	 * Loads the bot file
	 * @param string The folder (group)
	 * @param string The elements (name of file without extension)
	 * @param int Published state
	 * @param string The params for the bot
	 */
	function loadBot( $folder, $element, $published, $params='' ) {
		return JPluginHelper::_import($folder, $element, $published, $params='' );
	}

	/**
	* Registers a function to a particular event group
	*
	* @param string The event name
	* @param string The function name
	*/
	function registerFunction( $event, $function ) {
		 JApplication::registerEvent( $event, $function );
	}

	/**
	* Deprecated, use JEventDispatcher->trigger intead and handle return values
	* in your code
	* @since 1.5
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
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosEmpty
{
	function def( $key, $value='' ) {
		return 1;
	}
	function get( $key, $default='' ) {
		return 1;
	}
}

/**
 * Legacy class, use JHTML instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosHTML 
{
	/**
 	 * Legacy function, JHTML::makeOption instead
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function makeOption( $value, $text='', $value_name='value', $text_name='text' ) {
		JHTML::makeOption($value, $text, $value_name, $text_name);
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
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
 	 * Legacy function, JHTML::selectList instead
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function selectList( &$arr, $tag_name, $tag_attribs, $key, $text, $selected=NULL, $idtag=false, $flag=false ) {
		JHTML::selectList($arr, $tag_name, $tag_attribs, $key, $text, $selected, $idtag, $flag);
	}

	/**
 	 * Legacy function, JHTML::integerSelectList instead
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function integerSelectList( $start, $end, $inc, $tag_name, $tag_attribs, $selected, $format="" ) {
		JHTML::integerSelectList($start, $end, $inc, $tag_name, $tag_attribs, $selected, $format);
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function monthSelectList( $tag_name, $tag_attribs, $selected ) {
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
 	 * @package		Joomla.Legacy
 	*/
	function treeSelectList( &$src_list, $src_id, $tgt_list, $tag_name, $tag_attribs, $key, $text, $selected ) {

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
		$ilist = mosTreeRecurse( 0, '', array(), $children );

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
 	 * @package		Joomla.Legacy
 	*/
	function yesnoSelectList( $tag_name, $tag_attribs, $selected, $yes='yes', $no='no' ) {
		$arr = array(
			mosHTML::makeOption( 0, JText::_( $no ) ),
			mosHTML::makeOption( 1, JText::_( $yes ) ),
		);

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', (int) $selected );
	}

	/**
 	 * Legacy function, JHTML::radioList instead
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function radioList( &$arr, $tag_name, $tag_attribs, $selected=null, $key='value', $text='text', $idtag=false ) {
		JHTML::radioList($arr, $tag_name, $tag_attribs, $selected, $key, $text, $idtag);
	}

	/**
 	 * Legacy function, JHTML::yesnoRadioList instead
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function yesnoRadioList( $tag_name, $tag_attribs, $selected, $yes='yes', $no='no', $id=false ) {
		JHTML::yesnoRadioList($tag_name, $tag_attribs, $selected, $yes, $no, $id);
	}

	/**
 	 * Legacy function, use JHTML::idBox instead
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function idBox( $rowNum, $recId, $checkedOut=false, $name='cid' ) {
		JHTML::idBox($rowNum, $recId, $checkedOut, $name);
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
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
 	 * @package		Joomla.Legacy
 	*/
	function CloseButton ( &$params, $hide_js=NULL ) {

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
 	 * @package		Joomla.Legacy
 	*/
	function BackButton ( &$params, $hide_js=NULL ) {

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
 	 * Legacy function, use JOutputFilter::cleanText instead
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function cleanText ( &$text ) {
		jimport('joomla.filter.output');
		JOutputFilter::cleanText($text);
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function PrintIcon( &$row, &$params, $hide_js, $link, $status=NULL ) {

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
 	 * Legacy function, use JHTML::emailCloaking instead
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function emailCloaking( $mail, $mailto=1, $text='', $email=1 ) {
		JHTML::emailCloaking($mail, $mailto, $text, $email);
	}

	/**
 	 * Legacy function, use JHTML::keepAlive instead
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function keepAlive() {
		JHTML::keepAlive();
	}
}

/**
 * Legacy class, use JCommonHTML instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosCommonHTML 
{
	/**
 	 * Legacy function, use JHTML::keepAlive instead
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function ContentLegend( ) 
	{
		JCommonHTML::ContentLegend();
	}

	/**
 	 * Legacy function, deprecated
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
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
 	 * @package		Joomla.Legacy
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
 	 * Legacy function, use JCommonHTML::checkedOut
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function checkedOut( &$row, $overlib=1 ) {
		JCommonHTML::checkedOut($row, $overlib);
	}

	/**
 	 * Legacy function, use JCommonHTML::loadOverlib
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function loadOverlib() {
		JCommonHTML::loadOverlib();
	}

	/**
 	 * Legacy function, use JCommonHTML::loadCalendar
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function loadCalendar() {
		JCommonHTML::loadCalendar();
	}

	/**
 	 * Legacy function, use JCommonHTML::AccessProcessing
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function AccessProcessing( &$row, $i, $archived=NULL ) {
		JCommonHTML::AccessProcessing($orw, $i, $archived);
	}

	/**
 	 * Legacy function, use JCommonHTML::CheckedOutProcessing
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function CheckedOutProcessing( &$row, $i ) {
		JCommonHTML::CheckedOutProcessing($row, $i);
	}

	/**
 	 * Legacy function, use JCommonHTML::PublishedProcessing
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function PublishedProcessing( &$row, $i, $imgY='tick.png', $imgX='publish_x.png' ) {
		JCommonHTML::PublishedProcessing($row, $i, $imgY, $imgX);
	}

	/**
 	 * Legacy function, use JCommonHTML::selectState
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function selectState( $filter_state=NULL, $published='Published', $unpublished='Unpublished', $archived=NULL )	{
		JCommonHTML::selectState($filter_state, $published, $unpublished, $archived);
	}

	/**
 	 * Legacy function, use JCommonHTML::saveorderButton
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function saveorderButton( $rows, $image='filesave.png' ) {
		JCommonHTML::saveorderButton($rows, $image);
	}

	/**
 	 * Legacy function, use JCommonHTML::tableOrdering
 	 *
 	 * @deprecated	As of version 1.5
 	 * @package		Joomla.Legacy
 	*/
	function tableOrdering( $text, $ordering, &$lists, $task=NULL ) {
		?>
		<a href="javascript:tableOrdering('<?php echo $ordering; ?>','<?php echo $lists['order_Dir']; ?>','<?php echo $task; ?>');" title="<?php echo JText::_( 'Order by' ); ?> <?php echo JText::_( $text ); ?>">
			<?php echo JText::_( $text ); ?>
			<?php JCommonHTML::tableOrdering_img( $ordering, $lists ); ?></a>
		<?php
	}
}

/**
 * Legacy class, removed
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class MENU_Default
{
	function MENU_Default() {
		JMenuBar::startTable();
		JMenuBar::publishList();
		JMenuBar::unpublishList();
		JMenuBar::addNew();
		JMenuBar::editList();
		JMenuBar::deleteList();
		JMenuBar::spacer();
		JMenuBar::endTable();
	}
}

/**
 * Legacy class, use JPanel instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
jimport('joomla.html.pane');
class mosTabs extends JPaneTabs
{
	var $useCookies = false;

	function __construct( $useCookies, $xhtml = null) {
		parent::__construct( array('useCookies' => $useCookies) );
	}

	function startTab( $tabText, $paneid ) {
		$this->startPanel( $tabText, $paneid);
	}

	function endTab()  {
		$this->endPanel();
	}
}

/**
 * Legacy class, use JTemplate::getInstance instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
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
?>
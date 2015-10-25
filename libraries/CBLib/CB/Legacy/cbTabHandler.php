<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/18/14 2:59 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Registry\Registry;
use CBLib\Xml\SimpleXMLElement;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

defined('CBLIB') or die();

/**
 * cbTabHandler Class implementation
 * Tab Class for handling the CB tab api
 */
class cbTabHandler extends cbPluginHandler
{
	/**
	 * XML of the Plugin of this tab
	 * @var SimpleXMLElement
	 */
	private $_xml	=	null;
	/**
	 * XML of this tab
	 * @var SimpleXMLElement
	 */
	private $_tabXml	=	null;

	/**
	 * Constructor named old-fashion for backwards compatibility reason
	 * until all classes extending cbTabHandler call parent::__construct() instead of $this->cbTabHandler()
	 * @deprecated 2.0 use parent::__construct() instead.
	 */
	public function cbTabHandler( )
	{
		parent::__construct();
	}

	/**
	 * Generates the menu and user status to display on the user profile by calling back $this->addMenu
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @return boolean               Either true, or false if ErrorMSG generated
	 */
	public function getMenuAndStatus( $tab, $user, $ui )
	{
	}

	/**
	 * Generates the HTML to display the user profile tab
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayTab( $tab, $user, $ui )
	{
	}

	/**
	 * Generates the HTML to display the user edit tab
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getEditTab( $tab, $user, $ui )
	{
	}

	/**
	 * Saves the user edit tab postdata into the tab's permanent storage
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @param  array      $postdata  _POST data for saving edited tab content as generated with getEditTab
	 * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function saveEditTab( $tab, &$user, $ui, $postdata )
	{
	}

	/**
	 * Generates the HTML to display the registration tab/area
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @param  array      $postdata  _POST data for saving edited tab content as generated with getEditTab
	 * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayRegistration( $tab, $user, $ui, $postdata )
	{
	}

	/**
	 * Saves the registration tab/area postdata into the tab's permanent storage
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @param  array      $postdata  _POST data for saving edited tab content as generated with getEditTab
	 * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function saveRegistrationTab( $tab, &$user, $ui, $postdata )
	{
	}

	/**
	 * WARNING: UNCHECKED ACCESS! On purpose unchecked access for M2M operations
	 * Generates the HTML to display for a specific component-like page for the tab. WARNING: unchecked access !
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @param  array      $postdata  _POST data for saving edited tab content as generated with getEditTab
	 * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getTabComponent( /** @noinspection PhpUnusedParameterInspection */ $tab,
									 /** @noinspection PhpUnusedParameterInspection */ $user,
									 /** @noinspection PhpUnusedParameterInspection */ $ui,
									 /** @noinspection PhpUnusedParameterInspection */ $postdata )
	{
		return null;
	}

	/**
	 * Labeller for title:
	 * Returns a profile view tab title
	 *
	 * @param  TabTable   $tab       the tab database entry
	 * @param  UserTable  $user      the user being displayed
	 * @param  int        $ui        1 for front-end, 2 for back-end
	 * @param  array      $postdata  _POST data for saving edited tab content as generated with getEditTab
	 * @return string|boolean        Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getTabTitle( $tab, $user, /** @noinspection PhpUnusedParameterInspection */ $ui, /** @noinspection PhpUnusedParameterInspection */ $postdata )
	{
		return cbReplaceVars( $tab->title, $user );
	}

	/**
	 * Methods for CB backend only (do not override):
	 */

	/**
	 * Loads XML file (backend use only!)
	 *
	 * @param  TabTable  $tab
	 * @return boolean
	 */
	private function _loadXML( $tab )
	{
		global $_PLUGINS;

		if ( $this->_xml === null ) {
			if ( ! $_PLUGINS->loadPluginGroup( null, array( (int) $tab->pluginid ), 0 ) ) {
				return false;
			}

			$this->_xml		=&	$_PLUGINS->loadPluginXML( 'editTab', $tab->pluginclass, $tab->pluginid );

			if ( $this->_xml === null ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Loads tab XML (backend use only!)
	 * (used in TabTable only)
	 *
	 * @param  TabTable          $tab
	 * @return SimpleXMLElement
	 */
	public function _loadTabXML( $tab )
	{
		if ( $this->_tabXml === null ) {
			if ( $this->_loadXML( $tab ) ) {
				$fieldTypesXML		=	$this->_xml->getElementByPath( 'tabs' );

				if ( $fieldTypesXML ) {
					$this->_tabXml	=	$fieldTypesXML->getChildByNameAttr( 'tab', 'class', $tab->pluginclass );
				}
			}
		}

		return $this->_tabXml;
	}

	/**
	 * Loads parameters editor (backend use only!)
	 *
	 * @param  TabTable                       $tab
	 * @return cbParamsEditorController|null
	 */
	private function _loadParamsEditor( $tab )
	{
		global $_PLUGINS;

		if ( $this->_loadXML( $tab ) ) {
			$plugin 		=	$_PLUGINS->getPluginObject( $tab->pluginid );
			$params			=	new cbParamsEditorController( $tab->params, $this->_xml, $this->_xml, $plugin, $tab->tabid );
			$params->setNamespaceRegistry( 'tab', $tab );
			$pluginParams	=	new Registry( $plugin->params );

			$params->setPluginParams( $pluginParams );
		} else {
			$params			=	null;
		}

		return $params;
	}

	/**
	 * Draws parameters editor of the tab paramaters (backend use only!)
	 * (used in TabTable only)
	 *
	 * @param  TabTable     $tab
	 * @param  array        $options
	 * @return null|string
	 */
	public function drawParamsEditor( $tab, $options )
	{
		$params		=	$this->_loadParamsEditor( $tab );

		if ( $params ) {
			$params->setOptions( $options );

			return $params->draw( 'params', 'tabs', 'tab', 'class', $tab->pluginclass, 'params', true, 'depends', 'div' );
		} else {
			return null;
		}
	}

	/**
	 * Private methods for inheriting classes:
	 */

	/**
	 * Internal utility method to get prefix
	 * (Should be protected, but is public for backwards compatibility with CBSubs GPL 3.0.0 cbpaidsubscriptions.php)
	 *
	 * @param  string  $postfix  Postfix for identifying multiple pagings/search/sorts (optional)
	 * @return string            Value of the tab forms&urls prefix
	 */
	public function _getPrefix( $postfix='' )
	{
		return str_replace('.','_',((strncmp($this->element, 'cb.', 3)==0)? substr($this->element,3) : $this->element).$postfix);
	}

	/**
	 * Gets an ESCAPED and urldecoded request parameter for the plugin
	 * you need to call stripslashes to remove escapes, and htmlspecialchars before displaying.
	 *
	 * @param  string  $name     name of parameter in REQUEST URL
	 * @param  string  $def      default value of parameter in REQUEST URL if none found
	 * @param  string  $postfix  postfix for identifying multiple pagings/search/sorts (optional)
	 * @return string            value of the parameter (urldecode processed for international and special chars) and ESCAPED! and ALLOW HTML!
	 */
	public function _getReqParam( $name, $def = null, $postfix = '' )
	{
		global $_GET, $_POST;

		$prefix		=	$this->_getPrefix( $postfix );

		$value		=	cbGetParam( $_POST, $prefix.$name, false );

		if ( $value !== false ) {
			$value	=	cbGetParam( $_POST, $prefix.$name, $def );
		} else {
			$value	=	cbGetParam( $_GET, $prefix.$name, $def );

			if ( $value && is_string( $value ) ) {
				$value	=	urldecode( $value );
			}
		}

		return $value;
	}

	/**
	 * Gets the name input parameter for search and other functions
	 *
	 * @param  string  $name     name of parameter of plugin
	 * @param  string  $postfix  postfix for identifying multiple pagings/search/sorts (optional)
	 * @return string            value of the name input parameter
	 */
	public function _getPagingParamName( $name='search', $postfix='' )
	{
		$prefix		=	$this->_getPrefix($postfix);

		return $prefix.$name;
	}

	/**
	 * Gives the URL of a link with plugin parameters.
	 * (Should be protected, but is public for backwards compatibility with CBSubs GPL 3.0.0)
	 *
	 * @param  array    $paramArray        array of string with key name of parameters
	 * @param  string   $task              cb task to link to (default: userProfile)
	 * @param  boolean  $sefed             TRUE to call cbSef (default), FALSE to leave URL unsefed
	 * @param  array    $excludeParamList  of string with keys of parameters to not include
	 * @param  string   $format            'html', 'raw'		(added in CB 1.2.3)
	 * @return string                      value of the parameter (htmlspecialchared)
	 */
	public function _getAbsURLwithParam( $paramArray, $task = 'userprofile', $sefed = true, $excludeParamList = null, $format = 'html' )
	{
		global $_POST, $_GET;

		if ( $excludeParamList === null ) {
			$excludeParamList	=	array();
		}

		$prefix						=	$this->_getPrefix();

		if ( $task == 'userprofile' ) {
			$Itemid					=	(int) getCBprofileItemid( 0 );
			unset( $paramArray['Itemid'] );
		} elseif ( isset( $paramArray['Itemid'] ) ) {
			$Itemid					=	(int) $paramArray['Itemid'];
			unset( $paramArray['Itemid'] );
		} elseif ( isset( $_POST['Itemid'] ) ) {
			$Itemid					=	(int) cbGetParam( $_POST, 'Itemid', 0 );
		} elseif ( isset( $_GET['Itemid'])) {
			$Itemid					=	(int) cbGetParam( $_GET, 'Itemid', 0 );
		} else {
			$Itemid					=	(int) getCBprofileItemid( 0 );
		}

		if ( ( $task == 'userprofile' ) && ! isset( $paramArray['user'] ) ) {
			if ( isset( $_POST['user'] ) ) {
				$paramArray['user']	=	urldecode(cbGetParam($_POST,'user',null));
			} else {
				$paramArray['user']	=	urldecode(cbGetParam($_GET,'user',null));
			}
		}

		if ( $task == 'pluginclass' ) {
			$plugin					=	$this->getPluginObject();
			$unsecureChars			=	array( '/', '\\', ':', ';', '{', '}', '(', ')', '"', "'", '.', ',', "\0", ' ', "\t", "\n", "\r", "\x0B" );
			$paramArray['plugin']	=	substr( str_replace( $unsecureChars, '', $plugin->element ), 0, 32 );
			$paramArray['tab']		=	null;
		} elseif ( strtolower( $task ) == 'manageconnections' ) {
			$paramArray['plugin']	=	null;
			$paramArray['tab']		=	null;
		} else {
			$paramArray['plugin']	=	null;
			if ( ! isset( $paramArray['tab'] ) ) {
				$paramArray['tab']	=	strtolower( get_class( $this ) );
			}
		}

		$uri	=	'index.php?option=com_comprofiler&amp;view=' . $task
			.	( ( isset( $paramArray['user'] ) && $paramArray['user'] ) ? '&amp;user=' . htmlspecialchars( stripslashes( $paramArray['user'] ) ) : '' )
			.	( $Itemid ? '&amp;Itemid=' . $Itemid : '' )
			.	( $paramArray['tab'] ? '&amp;tab=' . htmlspecialchars( stripslashes( $paramArray['tab'] ) ) : '' )
			.	($paramArray['plugin'] ? '&amp;plugin=' . htmlspecialchars( stripslashes( $paramArray['plugin'] ) ) : '' );

		reset( $paramArray );

		while ( list( $key, $val ) = each( $paramArray ) ) {
			if ( ! in_array( $key, array( 'Itemid', 'user', 'tab', 'plugin' ) ) && ! in_array( $key, $excludeParamList ) ) {
				if ( $val ) {
					$uri			.=	'&amp;' . htmlspecialchars( $prefix . $key ) . '=' . htmlspecialchars( stripslashes( $val ) );
				}
			}
		}

		if ( $sefed ) {
			return cbSef( $uri, true, $format );
		}

		return $uri;
	}

	/**
	 * Returns the tab description with all replacements of variables and of language strings made.
	 *
	 * @param  TabTable   $tab
	 * @param  UserTable  $user
	 * @param  string     $htmlId  div id tag for the description html div
	 * @return string
	 */
	protected function _writeTabDescription( $tab, $user, $htmlId = null )
	{
		if ( $tab->description == null ) {
			return null;
		}

		$return		=	'<div class="tab_description form-group cb_form_line clearfix cbtwolinesfield"' . ( $htmlId ? ' id="' . $htmlId . '"' : '' ) . '>'
					.		'<div class="cb_field col-sm-12">'
					.			'<div>'
					.				cbReplaceVars( cbUnHtmlspecialchars( $tab->description ), $user )
					.			'</div>'
					.		'</div>'
					.	'</div>';

		return $return;
	}

	/**
	 * Writes the html links for pages inside tabs, eg, previous 1 2 3 ... x next
	 *
	 * @param  array   $pagingParams  Paging parameters. ['limitstart'] is the record number to start dislpaying from will be ignored
	 * @param  string  $postfix       Postfix for identifying multiple pagings/search/sorts (optional)
	 * @param  int     $limit         Number of rows to display per page
	 * @param  int     $total         Total number of rows
	 * @param  string  $task          Cb task to link to (default: userProfile)
	 * @return string                 HTML text displaying paging
	 */
	public function _writePaging( $pagingParams, $postfix, $limit, $total, $task = 'userprofile' )
	{
		$base_url	=	$this->_getAbsURLwithParam( $pagingParams, $task, false, array( $postfix . 'limitstart' ) );
		$prefix		=	$this->_getPrefix( $postfix );

		return writePagesLinks( $pagingParams[$postfix . 'limitstart'], $limit, $total, $base_url, null, $prefix );
	}

	/**
	 * Gets the paging limitstart, search and sortby parameters, as well as additional parameters
	 * (Should be protected, but is public because (only) comprofiler.php manageConnections() function uses it)
	 * Do not use as public function !
	 *
	 * @param  array         $additionalParams  Array of string : keyed additional parameters as "Param-name" => "default-value"
	 * @param  array|string  $postfixArray      Array of string OR string : postfix for identifying multiple pagings/search/sorts (optional)
	 * @return array         ("limitstart" => current list-start value (default: null), "search" => search-string (default: null), "sortby" => sorting by, +additional parameters as keyed in $additionalParams)
	 */
	public function _getPaging( $additionalParams = array(), $postfixArray = array() )
	{
		$return				=	array();

		foreach ( (array) $postfixArray as $postfix ) {
			$limitstart							=	$this->_getReqParam( 'limitstart', null, $postfix );
			$return[$postfix . 'limitstart']	=	( $limitstart === null ? null : (int) $limitstart );
			$return[$postfix . 'search']		=	$this->_getReqParam( 'search', null, $postfix );
			$return[$postfix . 'sortby']		=	$this->_getReqParam( 'sortby', null, $postfix );
		}
		foreach ( $additionalParams as $k => $p ) {
			$return[$k]		=	$this->_getReqParam($k, $p);
		}

		return $return;
	}
}

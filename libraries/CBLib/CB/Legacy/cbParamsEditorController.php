<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:46 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\AhaWow\Controller\RegistryEditController;
use CBLib\Database\Table\Table;
use CBLib\Database\Table\TableInterface;
use CBLib\Registry\Registry;
use CBLib\Xml\SimpleXMLElement;
use CB\Database\Table\PluginTable;

defined('CBLIB') or die();

/**
 * cbParamsEditorController Class implementation
 * CB 1.x Parameters handler Controller
 *
 * @deprecated 2.0 Use CBLib\AhaWow\Controller\RegistryEditController class But new way ( //TODO: To be documented)
 * @see \CBLib\AhaWow\Controller\RegistryEditController
 */
/** @noinspection PhpDeprecationInspection */
class cbParamsEditorController extends cbParamsBase
{
	/** @var cbObject */
	public $_params = null;
//	/** @var string The raw params string */
//	public $_raw = null;

	/** The main enclosing tag name
	 *  @var string */
	public $_maintagname = null;

	/** The attribute name of setup file
	 *  @var string */
	public $_attrname = null;

	/** The attribute value of setup file
	 *  @var string */
	public $_attrvalue = null;

	/**
	 * plugin object
	 *  @var PluginTable
	 */
	public $_pluginObject = null;

	/** @var int */
	public $_tabId = null;

	/** The xml plugin root element
	 *  @var SimpleXMLElement */
	public $_xml = null;

	/** The xml params element
	 *  @var SimpleXMLElement */
	public $_xmlElem = null;

	/** The xml actions element
	 *  @var SimpleXMLElement */
	public $_actions = null;

	/** The xml types element
	 *  @var SimpleXMLElement */
	public $_types = null;

	/** The xml views element
	 *  @var SimpleXMLElement */
	public $_views = null;

	/** Options from url REQUEST
	 *  @var array */
	public $_options = null;

	/** Extending view parser
	 *  @var SimpleXMLElement */
	public $_extendViewParser = null;

	/** CB plugin parameters
	 *  @var cbParamsBase */
	public $_pluginParams = null;

	/**
	 * @var RegistryEditController
	 */
	protected $registryEditController;

	/**
	 * Constructor
	 *
	 * @param  string            $paramsValues  The string raw parms text
	 * @param  SimpleXMLElement  $xmlElement    The element in XML corresponding to the parameters
	 * @param  SimpleXMLElement  $xml           The root element
	 * @param  PluginTable       $pluginObject  The plugin object
	 * @param  int               $tabId         The tab id (if there is one)
	 * @param  string            $maintagname   The main name of the tag pf the file
	 * @param  string            $attrname      The attribute name to test for $attrvalue
	 * @param  string            $attrvalue     The attribute value to be tested
	 */
	public function __construct( $paramsValues, $xmlElement, $xml, &$pluginObject, $tabId=null, $maintagname='cbinstall', $attrname='type', $attrvalue='plugin'  )
	{
		global $_CB_database;

		$input							=	Application::Input();

		$this->registryEditController   =   new RegistryEditController( $input, $_CB_database, new Registry( $paramsValues ), $xmlElement, $xml, $pluginObject, $tabId, $maintagname, $attrname, $attrvalue );

		foreach ( array_keys( get_object_vars( $this ) ) as $k ) {
			if ( isset( $this->registryEditController->$k ) ) {
				$this->$k	=&	$this->registryEditController->$k;
			}
		}

		$this->_params      =   $this->registryEditController->getEditedParams();
	}

	/**
	 * Sets parameters for this editor
	 *
	 * @param  cbObject|Table  $object  The data object
	 * @return void
	 */
	public function setAllParams( $object )
	{
		$array				=	array();

		$isStorage			=	( $object instanceof TableInterface || $object instanceof comprofilerDBTable );

		if ( $isStorage ) {
			$keys			=	$object->getPublicProperties();

			foreach ( $keys as $k ) {
				$array[$k]	=	$object->$k;
			}
		} else {
			$array			=	(array) $object;
		}

		$registry			=	new Registry( $array );

		if ( $isStorage ) {
			$registry->setStorage( $object );
		}

		$this->registryEditController->setRegistry( $registry );
	}

	/**
	 * Sets the parameters of the plugin
	 *
	 * @param  Registry  $pluginParams  The parameters of the plugin
	 * @return void
	 */
	public function setPluginParams( $pluginParams )
	{
		if ( $pluginParams instanceof Registry ) {
			$this->registryEditController->setPluginParams( $pluginParams );

			return;
		}

		/** @noinspection PhpDeprecationInspection */
		if ( $pluginParams instanceof cbParamsBase ) {
			/** @noinspection PhpDeprecationInspection */
			$pluginParams	=	$pluginParams->toParamsArray();
		}

		$this->registryEditController->setPluginParams( new Registry( $pluginParams ) );
	}

	/**
	 * Sets the namespace registry of this editor
	 *
	 * @param string $name
	 * @param object $object
	 */
	public function setNamespaceRegistry( $name, $object )
	{
		$registry	=	new Registry();

		$registry->load( $object );
		$registry->setStorage( $object );

		$this->registryEditController->getEditedParams()->setNamespaceRegistry( $name, $registry );
	}

	/**
	 * Sets the input request options
	 *
	 * @param  string[]  $options  The Input request options
	 * @return void
	 */
	public function setOptions( $options )
	{
		$this->registryEditController->setOptions( $options );
	}

	/**
	 * Sets an extended viewer/parser
	 *
	 * @param  SimpleXMLElement  $extendedViewParser  The extended view parser
	 * @return void
	 */
	public function setExtendedViewParser( &$extendedViewParser )
	{
		$this->registryEditController->setExtendedViewParser( $extendedViewParser );
	}

	/**
	 * Converts the parameters received as POST array into the raw parms text ALWAYS ESCAPED
	 *
	 * @param  mixed   $params   $_POST array or string escaped only if MAGIC_QUOTES are ON
	 * @return string            The raw parms text always addslash-ESCAPED
	 */
	public function getRawParams( $params )
	{
		/** @noinspection PhpDeprecationInspection */
		return addslashes( self::getRawParamsUnescaped( $params, true ) );
	}

	/**
	 * Converts the parameters received as POST array into the raw parms text ALWAYS ESCAPED
	 *
	 * @param  mixed   $params   $_POST array or string escaped only if MAGIC_QUOTES are ON
	 * @return string            The raw parms text always addslash-ESCAPED
	 */
	public static function getRawParamsMagicgpcEscaped( $params )
	{
		/** @noinspection PhpDeprecationInspection */
		$ret					=	self::getRawParamsUnescaped( $params, true );
		if ( get_magic_quotes_gpc() ) {
			return addslashes( $ret );
		} else {
			return $ret;
		}
	}
	/**
	 * Converts the parameters received as request array into the raw parms text NEVER ESCAPED
	 *
	 * @param  mixed    $params             Request array or string escaped only if MAGIC_QUOTES are ON
	 * @param  boolean  $checkMagicSlashes  TRUE: if magic_quotes are ON, remove slashes, FALSE: never remove slashes
	 * @return string                       The raw parms text NEVER addslash-ESCAPED
	 */
	public static function getRawParamsUnescaped( $params, $checkMagicSlashes )
	{
		if( is_array( $params ) ) {
			$txt				=	array();

			foreach ( $params as $k => $v ) {
				if ( is_array( $v ) ) {
					if ( isset( $v[0] ) ) {
						$v		=	implode("|*|", $v);
					} else {
						$r		=	'';

						foreach ( $v as $kk => $vv ) {
							$r	.=	'|**|' . $kk . '=' . $vv;
						}
						$v		=	$r;
					}
				}

				if ( $checkMagicSlashes && get_magic_quotes_gpc() ) {
					$v			=	stripslashes( $v );
				}

				$txt[]			=	"$k=$v";
			}
			/** @noinspection PhpDeprecationInspection */
			$ret				=	cbEditRowView::textareaHandling( $txt );
		} else {
			if ( $checkMagicSlashes && get_magic_quotes_gpc() ) {
				$ret			=	stripslashes( $params );
			} else {
				$ret			=	$params;
			}
		}

		return $ret;
	}

	/**
	 * Converts the parameters received as POST array into the |*| and CBparams formats
	 *
	 * @param  array  $params  MODIFIED BY THIS CALL: POST array
	 * @return void
	 */
	public static function fixMultiSelects( &$params )
	{
		if ( is_array( $params ) ) {
			foreach ( $params as $k => $v ) {
				if ( is_array( $v ) ) {
					if ( isset( $v[0] ) ) {
						$params[$k]		=	implode( "|*|", $v );
					} else {
						/** @noinspection PhpDeprecationInspection */
						$params[$k]		=	self::getRawParamsMagicgpcEscaped( $v );
					}
				}
			}
		}
	}

	/**
	 * Draws the control, or the default text area if a setup file is not found
	 *
	 * @param  string   $tag_path           The XML path to the params (by default 'params')
	 * @param  string   $grand_parent_path  [optional] First find as grand-parent that node (if exists)
	 * @param  string   $parent_tag         [optional] Then find as parent that node (if exists)
	 * @param  string   $parent_attr        [optional] but parent with that attribute having
	 * @param  string   $parent_attrvalue   [optional] that value
	 * @param  string   $control_name       The control name (by default 'params')
	 * @param  boolean  $paramstextarea     If there are no params XML descriptor should params be represented just as a textarea of the raw params (to avoid loosing them) ?
	 * @param  string   $viewType           View type ( 'view', 'param', 'depends': means: <param> tag => param, <field> tag => view )
	 * @param  string   $htmlFormatting     HTML formatting type for params ( 'table', 'td', 'none', 'fieldsListArray' )
	 * @return string                       HTML
	 */
	public function draw( $tag_path = 'params', $grand_parent_path = null, $parent_tag = null, $parent_attr = null, $parent_attrvalue = null,
						  $control_name = 'params', $paramstextarea = true, $viewType = 'depends', $htmlFormatting = 'table'  )
	{
		return $this->registryEditController->draw( $tag_path, $grand_parent_path, $parent_tag, $parent_attr, $parent_attrvalue, $control_name, $paramstextarea, $viewType, $htmlFormatting );
	}
}

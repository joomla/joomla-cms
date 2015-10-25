<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 11/12/13 4:03 PM $
* @package CBLib\AhaWow\Controller
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\AhaWow\Controller;

use CBLib\AhaWow\View\RegistryEditView;
use CBLib\Database\DatabaseDriverInterface;
use CBLib\Input\InputInterface;
use CBLib\Registry\RegistryInterface;
use CBLib\Xml\SimpleXMLElement;
use CB\Database\Table\PluginTable;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\Controller\RegistryEditController Class implementation
 * 
 */
class RegistryEditController {

	/**
	 * The corresponding registry (Model)
	 * @var RegistryInterface
	 */
	private $registry;

	/**
	 * The main enclosing tag name
	 * @var string */
	var $_maintagname = null;

	/**
	 * The attribute name of setup file
	 * @var string */
	var $_attrname = null;

	/**
	 * The attribute value of setup file
	 * @var string */
	var $_attrvalue = null;

	/**
	 * plugin object
	 * @var PluginTable */
	var $_pluginObject = null;

	/**
	 * tab id
	 * @var int
	 */
	var $_tabId = null;

	/**
	 * The xml plugin root element
	 * @var SimpleXMLElement */
	var $_xml = null;

	/**
	 * The xml params element
	 * @var SimpleXMLElement */
	var $_xmlElem = null;

	/**
	 * The xml actions element
	 * @var SimpleXMLElement */
	var $_actions = null;

	/**
	 * The xml types element
	 * @var SimpleXMLElement */
	var $_types = null;

	/**
	 * The xml views element
	 * @var SimpleXMLElement */
	var $_views = null;

	/**
	 * Options from url REQUEST
	 * @var array */
	var $_options = null;

	/**
	 * Extending view parser
	 * @var SimpleXMLElement */
	var $_extendViewParser = null;

	/**
	 * CB plugin parameters
	 * @var RegistryInterface */
	var $_pluginParams = null;

	/**
	 * @var InputInterface
	 */
	protected $input			=	null;

	/**
	 * @var DatabaseDriverInterface
	 */
	protected $_db;

	/**
	 * Constructor
	 *
	 * @param  InputInterface           $input         The Input
	 * @param  DatabaseDriverInterface  $db            The user form input
	 * @param  RegistryInterface        $registry      The string raw parms text
	 * @param  SimpleXMLElement         $xmlElement    The element in XML corresponding to the parameters
	 * @param  SimpleXMLElement         $xml           The root element
	 * @param  PluginTable              $pluginObject  The plugin object
	 * @param  int                      $tabId         The tab id (if there is one)
	 * @param  string                   $maintagname   The main name of the tag pf the file
	 * @param  string                   $attrname      The attribute name to test for $attrvalue
	 * @param  string                   $attrvalue     The attribute value to be tested
	 */
	function __construct( InputInterface $input, DatabaseDriverInterface $db, RegistryInterface $registry, SimpleXMLElement $xmlElement = null, SimpleXMLElement $xml = null, PluginTable $pluginObject = null, $tabId = null, $maintagname = 'cbinstall', $attrname = 'type', $attrvalue = 'plugin'  )
	{
		$this->input			=	$input;
		$this->_db				=	$db;
		$this->setRegistry( $registry );
		$this->_xml				=	$xmlElement;
		if ( $xml ) {
			$this->_actions		=	$xml->getElementByPathOrNull( 'actions' );
			$this->_types		=	$xml->getElementByPathOrNull( 'types' );
			$this->_views		=	$xml->getElementByPathOrNull( 'views' );
		}
		$this->_pluginObject	=	$pluginObject;
		$this->_tabId			=	$tabId;
		$this->_maintagname		=	$maintagname;
		$this->_attrname		=	$attrname;
		$this->_attrvalue		=	$attrvalue;
	}

	/**
	 * Sets parameters for this editor
	 *
	 * @param  RegistryInterface  $registry  The data object
	 * @return self                          For chaining
	 */
	function setRegistry( RegistryInterface $registry ) {
		$this->registry			=	$registry;
		return $this;
	}

	/**
	 * Sets the parameters of the plugin
	 *
	 * @param  RegistryInterface  $pluginParams  The parameters of the plugin
	 * @return self                          For chaining
	 */
	function setPluginParams( RegistryInterface $pluginParams ) {
		$this->_pluginParams	=	$pluginParams;
		return $this;
	}

	/**
	 * Sets the input request options
	 *
	 * @param  array  $options  The Input request options
	 * @return self             For chaining
	 */
	function setOptions( $options ) {
		$this->_options			=	$options;
		return $this;
	}

	/**
	 * Sets an extended viewer/parser
	 *
	 * @param  SimpleXMLElement  $extendedViewParser  The extended view parser
	 * @return self                          For chaining
	 */
	function setExtendedViewParser( SimpleXMLElement $extendedViewParser ) {
		$this->_extendViewParser =	$extendedViewParser;
		return $this;
	}

	/**
	 * Gets the edited parameters for saving
	 *
	 * @return RegistryInterface  The updated params
	 */
	function getEditedParams( ) {
		return $this->registry;
	}

	/**
	 * Converts the parameters received as POST array into the raw parms text ALWAYS ESCAPED
	 * @deprecated CB 2.0.10 (unused since CB 2.0)
	 *
	 * @param  mixed   $params   POST array or string escaped only if MAGIC_QUOTES are ON
	 * @return string            The raw parms text always addslash-ESCAPED
	 */
	function getRawParams( $params ) {
		return addslashes( self::getRawParamsUnescaped( $params, true ) );
	}

	/**
	 * Converts the parameters received as POST array into the raw parms text ALWAYS ESCAPED
	 * @deprecated CB 2.0.10 (unused since CB 2.0)
	 *
	 * @param  mixed   $params   POST array or string escaped only if MAGIC_QUOTES are ON
	 * @return string            The raw parms text always addslash-ESCAPED
	 */
	public static function getRawParamsMagicgpcEscaped( $params ) {
		$ret					=	self::getRawParamsUnescaped( $params, true );
		if ( get_magic_quotes_gpc() ) {
			return addslashes( $ret );
		} else {
			return $ret;
		}
	}
	/**
	 * Converts the parameters received as request array into the raw parms text NEVER ESCAPED
	 * @deprecated CB 2.0.10 (unused since CB 2.0)
	 *
	 * @param  mixed    $params             Request array or string escaped only if MAGIC_QUOTES are ON
	 * @param  boolean  $checkMagicSlashes  TRUE: if magic_quotes are ON, remove slashes, FALSE: never remove slashes
	 * @return string                       The raw parms text NEVER addslash-ESCAPED
	 */
	public static function getRawParamsUnescaped( $params, $checkMagicSlashes ) {
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
			$ret				=	RegistryEditView::textareaHandling( $txt );
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
	 * @return string|array                 HTML or values if $htmlFormatting = 'fieldsListArray'
	 */
	function draw( $tag_path='params', $grand_parent_path=null, $parent_tag=null, $parent_attr=null, $parent_attrvalue=null, $control_name='params', $paramstextarea=true, $viewType = 'depends', $htmlFormatting = 'table'  ) {

		if ( $this->_xml ) {
			$element					=	$this->_xml;
			if ( $element && $element->getName() == $this->_maintagname && $element->attributes( $this->_attrname ) == $this->_attrvalue) {
				if ( $grand_parent_path != null ) {
					$element			=	$element->getElementByPath( $grand_parent_path );
					if ( ! $element ) {
						return null;
					}
				}
				if ( $parent_tag != null && $parent_attr != null && $parent_attrvalue != null ) {
					$element			=	$element->getChildByNameAttr( $parent_tag, $parent_attr, $parent_attrvalue );
					if ( $element ) {
						if ( $tag_path ) {
							/** @var SimpleXMLElement $element */
							$element	=	$element->getElementByPath( $tag_path );
						}
						if ( $element !== false ) {
							$this->_xmlElem =& $element;
						}
					}
				} else {
					$element			=	$element->getElementByPath( $tag_path );
					if ( $element !== false ) {
						$this->_xmlElem	=	$element;
					}
				}
			} elseif ( ! $tag_path ) {
				$this->_xmlElem			=	$element;
			}
		}

		if ( $this->_xmlElem !== null ) {

			$controllerView		=	new DrawController( $this->input, $this->_xmlElem, $this->_actions, $this->_options );
			$controllerView->setControl_name( $control_name );

			$editRowView		=	new RegistryEditView( $this->input, $this->_db, $this->_pluginParams, $this->_types, $this->_actions, $this->_views, $this->_pluginObject, $this->_tabId );
			$modelOfDataRows	=	$this->registry->getStorage();
			$editRowView->setModelOfDataRows( $modelOfDataRows );
			if ( $this->_extendViewParser ) {
				$editRowView->setExtendedViewParser( $this->_extendViewParser );
			}
			return $editRowView->renderEditRowView( $this->_xmlElem, $this->registry, $controllerView, $this->_options, $viewType, $htmlFormatting );
		} else {
			if ($paramstextarea) {
				return "<textarea name=\"$control_name\" cols=\"40\" rows=\"10\" class=\"text_area\">".htmlspecialchars( $this->registry->asJson() )."</textarea>";
			} else {
				return null;
			}
		}
	}
}

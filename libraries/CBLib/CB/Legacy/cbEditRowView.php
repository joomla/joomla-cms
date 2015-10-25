<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:46 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\AhaWow\Controller\DrawController;
use CBLib\AhaWow\View\RegistryEditView;
use CBLib\Registry\RegistryInterface;
use CBLib\Registry\Registry;
use CBLib\Xml\SimpleXMLElement;
use CB\Database\Table\PluginTable;

defined('CBLIB') or die();

/**
 * cbEditRowView Class implementation
 * Allows to render the view of a row
 *
 * @deprecated 2.0 Use CBLib\AhaWow\View\RegistryEditView (BUT actually, you should not use this class directly)
 * @see \CBLib\AhaWow\View\RegistryEditView
 */
class cbEditRowView
{
	/**
	 * @var RegistryEditView
	 */
	private $registryEditView;

	/**
	 * Constructor (must stay old-named for compatibility with CBSubs GPL 3.0.0)
	 *
	 * @param  Registry          $pluginParams  The parameters of the plugin
	 * @param  SimpleXMLElement  $types         The types definitions in XML
	 * @param  SimpleXMLElement  $actions       The actions definitions in XML
	 * @param  SimpleXMLElement  $views         The views definitions in XML
	 * @param  PluginTable       $pluginObject  The plugin object
	 * @param  int               $tabId         The tab id (if there is one)
	 */
	public function cbEditRowView( $pluginParams, $types, $actions, $views, $pluginObject, $tabId = null )
	{
		global $_CB_database;

		$input						=	Application::Input();

		/** @noinspection PhpDeprecationInspection */
		if ( $pluginParams instanceof cbParamsBase ) {
			// Backwards-compatibility:
			/** @noinspection PhpDeprecationInspection */
			$pluginParams			=	new Registry( $pluginParams->toParamsArray() );
		}

		$this->registryEditView		=	new RegistryEditView( $input, $_CB_database, $pluginParams, $types, $actions, $views, $pluginObject, $tabId );

		foreach ( array_keys( get_object_vars( $this->registryEditView ) ) as $k ) {
			$this->$k	=&	$this->registryEditView->$k;
		}
	}

	/**
	 * Sets the parent view for an extended view parser
	 *
	 * @param  SimpleXMLElement  $modelView  The model view of the parent viewer
	 * @return void
	 */
	public function setParentView( &$modelView )
	{
		$this->registryEditView->setParentView( $modelView );
	}

	/**
	 * Pushes the current model of data onto the stack and sets a new model of data $modelOfData
	 *
	 * @param  RegistryInterface  $modelOfData  The model data
	 * @return void
	 */
	public function pushModelOfData( &$modelOfData )
	{
		$this->registryEditView->pushModelOfData( $modelOfData );
	}

	/**
	 * Returns the model of data
	 *
	 * @return RegistryInterface  The model of the data
	 */
	public function getModelOfData( )
	{
		return $this->registryEditView->getModelOfData();
	}

	/**
	 * Sets the model of data rows (the other rows of the current model (useful for list views controls)
	 *
	 * @param  RegistryInterface[]  $modelOfDataRows  The models of all data rows that are displayed around the current row
	 * @return void
	 */
	public function setModelOfDataRows( &$modelOfDataRows )
	{
		$this->registryEditView->setModelOfDataRows( $modelOfDataRows );
	}

	/**
	 * Sets the row number for current model
	 *
	 * @param  int  $i  row index number
	 * @return void
	 */
	public function setModelOfDataRowsNumber( $i )
	{
		$this->registryEditView->setModelOfDataRowsNumber( $i );
	}

	/**
	 * Sets an extended view parser
	 * This method is experimental and not part of CB API.
	 *
	 * @param  SimpleXMLElement  $extendedViewParserElement  An Object of class className (where className is from an xml element like <extendparser class="className" /> where className extends RegistryEditView
	 * @return void
	 */
	public function setExtendedViewParser( &$extendedViewParserElement )
	{
		$this->registryEditView->setExtendedViewParser( $extendedViewParserElement );
	}

	/**
	 * Gets Selected Values
	 *
	 * @param  SimpleXMLElement  $node  The node to get the values for
	 * @return array                      The values currently selected
	 */
	public function & _getSelectValues( &$node )
	{
		return $this->registryEditView->_getSelectValues( $node );
	}

	/**
	 * Renders as ECHO HTML code of a table
	 *
	 * @param SimpleXMLElement   $modelOfView     The model of the view
	 * @param RegistryInterface  $modelOfData     The data of the model ( $row object )
	 * @param DrawController     $controllerView  The controller that will be drawing the view
	 * @param array              $options         The input request options
	 * @param string             $viewType        The view type ( 'view', 'param', 'depends': means: <param> tag => param, <field> tag => view )
	 * @param string             $htmlFormatting  The HTML/array formatting to do ( 'table', 'td', 'none', 'fieldsListArray' )
	 * @return array|string                       array if $htmlFormatting == 'fieldsListArray', otherwise html string
	 */
	public function renderEditRowView( &$modelOfView, &$modelOfData, &$controllerView, $options, $viewType = 'depends', $htmlFormatting = 'table' )
	{
		return $this->registryEditView->renderEditRowView( $modelOfView, $modelOfData, $controllerView, $options, $viewType, $htmlFormatting );
	}

	/**
	 * Gets the data from the model for a field $key
	 * @param  string  $key      The name of the field
	 * @param  mixed   $default  The default value if not found
	 * @return string
	 */
	public function get( $key, $default=null )
	{
		return $this->registryEditView->get( $key, $default );
	}

	/**
	 * Returns the "html-dom-id" if it exists based on $element attribute 'name' and $control_name
	 *
	 * @param  string              $control_name  The control name
	 * @param  SimpleXMLElement  $element       The element to get the id for
	 * @return string|null
	 */
	public function _htmlId( $control_name, $element )
	{
		return $this->registryEditView->_htmlId( $control_name, $element );
	}

	/**
	 * Returns an array-written parameter name as "$control_name[$name]" if $control_name is set, otherwise '$name'
	 * (static version)
	 *
	 * @param  string  $control_name  The control name of the controlling-array
	 * @param  string  $name          The name of the param
	 * @return string                 The form input parameter name
	 */
	public static function control_name_static( $control_name, $name )
	{
		return RegistryEditView::control_name_static( $control_name, $name );
	}

	/**
	 * Returns an array-written parameter name as "$control_name[$name]" if $control_name is set, otherwise '$name'
	 * (object method version)
	 *
	 * @param  string  $control_name  The control name of the controlling-array
	 * @param  string  $name          The name of the param
	 * @return string                 The form input parameter name
	 */
	public function control_name( $control_name, $name )
	{
		return $this->registryEditView->control_name( $control_name, $name );
	}

	/**
	 * Returns the HTML id for a $control_name and a $name
	 *
	 * @param  string  $control_name  The control name of the controlling-array
	 * @param  string  $name          The name of the param
	 * @return string                 The HTML id
	 */
	public function control_id( $control_name, $name )
	{
		return $this->registryEditView->control_id( $control_name, $name );
	}

	/**
	 * Renders all parameters (including inheritance magic)
	 *
	 * @param  SimpleXMLElement  $xmlParentElement  The parent XML node for which to render all child node parameters
	 * @param  string            $control_name      The control name
	 * @param  cbTabs            $tabs              The CB tab (if applicable)
	 * @param  string            $viewType          The view type ( 'view', 'param', 'depends': means: <param> tag => param, <field> tag => view )
	 * @param  string            $htmlFormatting    The html formatting type ( 'table', 'td', 'div', 'span', 'none', 'fieldsListArray' )
	 * @return string|array                         HTML or values depending on $htmlFormatting
	 */
	public function renderAllParams( &$xmlParentElement, $control_name='params', $tabs=null, $viewType = 'depends', $htmlFormatting = 'table' )
	{
		return $this->registryEditView->renderAllParams( $xmlParentElement, $control_name, $tabs, $viewType, $htmlFormatting );
	}

	/**
	 * special handling for textarea param in the textarea rendering
	 *
	 * @param  string[]  $txt  The texts to handle
	 * @return string          The cleaned text as parameter
	 */
	public static function textareaHandling( &$txt )
	{
		$total = count( $txt );
		for( $i=0; $i < $total; $i++ ) {
			if ( strstr( $txt[$i], "\n" ) ) {
				$txt[$i] = str_replace( array( "\\", "\n", "\r" ), array( "\\\\", '\n', '\r'  ) , $txt[$i] );
			} else {
				$txt[$i] = str_replace( "\\", "\\\\" , $txt[$i] );
			}
		}
		$ret = implode( "\n", $txt );
		return $ret;
	}
}

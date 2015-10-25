<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/18/14 2:35 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Registry\Registry;
use CBLib\Xml\SimpleXMLElement;
use CB\Database\Table\FieldTable;
use CB\Database\Table\PluginTable;

defined('CBLIB') or die();

/**
 * cbFieldParamsHandler Class implementation
 * Field Class for handling the CB field api
 */
class cbFieldParamsHandler
{
	/**
	 * Plugin id
	 * @var int
	 */
	protected $_pluginid	=	null;
	/**
	 * field object
	 * @var FieldTable
	 */
	protected $_field		=	null;
	/**
	 * XML of the Plugin of this field
	 * @var SimpleXMLElement
	 */
	protected $_xml			=	null;
	/**
	 * XML element for the params for this field
	 * @var SimpleXMLElement
	 */
	protected $_fieldXml	=	null;
	/**
	 * params are specific to one particular field type
	 * @var boolean
	 */
	protected $_specific	=	false;

	/**
	 * Constructor
	 *
	 * @param  int         $pluginId   Id of plugin with params for other fields
	 * @param  FieldTable  $field      Field to handle parameters for
	 */
	public function __construct( $pluginId, $field )
	{
		$this->_pluginid	=	$pluginId;
		$this->_field		=	$field;
	}

	/**
	 * Private methods:
	 */

	/**
	 * Loads XML file (backend use only!)
	 *
	 * @return boolean  TRUE if success, FALSE if failed
	 */
	protected function _loadXML( )
	{
		global $_PLUGINS;

		if ( $this->_xml === null ) {
			if ( ! $_PLUGINS->loadPluginGroup( null, array( (int) $this->_pluginid ), 0 ) ) {

				return false;
			}
			$this->_xml	=&	$_PLUGINS->loadPluginXML( 'editField', 'cbfields_params', $this->_pluginid );
			if ( $this->_xml === null ) {

				return false;
			}
		}

		return true;
	}

	/**
	 * Loads fields-params XML (backend use only!)
	 * also sets $this->_fieldXML and $this->_specific
	 *
	 * @return boolean              TRUE if success, FALSE if not existant
	 */
	protected function _loadFieldParamsXML( )
	{
		if ( $this->_fieldXml === null ) {
			if ( $this->_loadXML() ) {
				$fieldsParamsXML				=	$this->_xml->getElementByPath( 'fieldsparams' );

				if ( $fieldsParamsXML ) {
					$fieldTypeSpecific			=	$fieldsParamsXML->getChildByNameAttr( 'field', 'type', $this->_field->type );

					if ( $fieldTypeSpecific ) {
						// <fieldsparams><field type="date"><params><param ....
						$this->_fieldXml		=&	$fieldTypeSpecific;
						$this->_specific		=	true;
					} else {
						// <fieldsparams><field type="other_types"><params><param ....
						$nonSpecific			=	$fieldsParamsXML->getChildByNameAttr( 'field', 'type', 'other_types' );

						if ( $nonSpecific ) {
							$this->_fieldXml	=&	$nonSpecific;
							$this->_specific	=	false;
						}
					}
				}
			}
		}
		return ( $this->_fieldXml !== null );
	}

	/**
	 * Loads parameters editor (backend use only!)
	 *
	 * @return cbParamsEditorController|null  null if not existant
	 */
	protected function _loadParamsEditor( )
	{
		global $_PLUGINS;

		if ( ! $this->_loadFieldParamsXML() ) {
			$params		=	null;

			return $params;
		}

		$plugin 		=	$_PLUGINS->getPluginObject( $this->_pluginid );

		$params			=	new cbParamsEditorController( $this->_field->params, $this->_xml, $this->_xml, $plugin );

		if ( $this instanceof cbTabParamsHandler ) {
			$params->setNamespaceRegistry( 'tab', $this->_field );
		} elseif ( $this instanceof cbFieldParamsHandler ) {
			$params->setNamespaceRegistry( 'field', $this->_field );
		}

		$pluginParams	=	new Registry( $plugin->params );

		$params->setPluginParams( $pluginParams );

		return $params;
	}

	/**
	 * Methods for CB backend only (do not override):
	 */

	/**
	 * Draws parameters editor of the field paramaters (backend use only!)
	 * Used by FieldTable and TabTable only
	 *
	 * @param  array                $options
	 * @return string  HTML if editor available, or NULL
	 */
	public function drawParamsEditor( $options )
	{
		$params		=	$this->_loadParamsEditor();

		if ( ! $params ) {
			return null;
		}

		$params->setOptions( $options );

		if ( $this->_specific ) {
			return $params->draw( 'params', 'fieldsparams', 'field', 'type', $this->_field->type, 'params', true, 'depends', 'div' );
		}

		return $params->draw( 'params', 'fieldsparams', 'field', 'type', 'other_types', 'params', true, 'depends', 'div' );
	}

	/**
	 * Returns full label of the type of the field (backend use only!)
	 *
	 * @return string  plugin name: label (found in xml file)
	 */
	public function getFieldsParamsLabel( )
	{
		global $_PLUGINS;

		$plugin 				=	$_PLUGINS->getPluginObject( $this->_pluginid );

		if ( $this->_fieldXml ) {
			return $plugin->name . ': ' . $this->_fieldXml->attributes( 'label' );
		}

		return $plugin->name . ': ' . "specific field-parameters";
	}
}

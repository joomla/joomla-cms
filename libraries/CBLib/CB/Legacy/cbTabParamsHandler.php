<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/18/14 2:57 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\TabTable;

defined('CBLIB') or die();

/**
 * cbTabParamsHandler Class implementation
 * 
 */
class cbTabParamsHandler extends cbFieldParamsHandler
{
	/**
	 * Tab object
	 * @var TabTable
	 */
	protected $_field	=	null;

	/**
	 * Draws parameters editor of the tab paramaters (backend use only!)
	 *
	 * @param  array        $options
	 * @return string|null            HTML if editor available, or NULL
	 */
	public function drawParamsEditor( $options )
	{
		$params		=	$this->_loadParamsEditor();
		if ( $params ) {
			$params->setOptions( $options );
			if ( $this->_specific ) {
				return $params->draw( 'params', 'tabsparams', 'tab', 'type', $this->_field->pluginclass, 'params', true, 'depends', 'div' );
			} else {
				return $params->draw( 'params', 'tabsparams', 'tab', 'type', 'other_types', 'params', true, 'depends', 'div' );
			}
		} else {
			return null;
		}
	}

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
			$this->_xml	=&	$_PLUGINS->loadPluginXML( 'editTab', 'cbtabs_params', $this->_pluginid );
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
				$fieldsParamsXML				=	$this->_xml->getElementByPath( 'tabsparams' );
				if ( $fieldsParamsXML ) {
					$fieldTypeSpecific			=	$fieldsParamsXML->getChildByNameAttr( 'tab', 'type', $this->_field->pluginclass );
					if ( $fieldTypeSpecific ) {
						// <tabsparams><tab type="date"><params><param ....
						$this->_fieldXml		=&	$fieldTypeSpecific;
						$this->_specific		=	true;
					} else {
						// <tabsparams><tab type="other_types"><params><param ....
						$nonSpecific			=&	$fieldsParamsXML->getChildByNameAttr( 'tab', 'type', 'other_types' );
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
}

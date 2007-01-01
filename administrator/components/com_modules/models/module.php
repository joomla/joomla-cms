<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/**
 * @package		Joomla
 * @subpackage	Modules
 */
class ModulesModelModule extends JModel
{
	var $_xml;

	function &getModule()
	{
		static $instance;
		
		if (!$instance)
		{
			$instance = $this->getTable( 'Module', 'JTable' );
			if ($id = $this->getState( 'id' )) {
				$instance->load( (int) $id );
			}
		}
		return $instance;
	}

	function &_getXML()
	{
		if (!$this->_xml)
		{
			$clientId	= $this->getState( 'clientId', 0 );
			$path		= ($clientId == 1) ? 'mod1_xml' : 'mod0_xml';
			$module		= &$this->getModule();
	
			if ($module->module == 'custom') {
				$xmlpath = JApplicationHelper::getPath( $path, 'mod_custom' );
			} else {
				$xmlpath = JApplicationHelper::getPath( $path, $module->module );
			}
	
			if (file_exists($xmlpath))
			{
				$xml =& JFactory::getXMLParser('Simple');
				if ($xml->loadFile($xmlpath)) {
					$this->_xml = &$xml;
				}
			}
		}
		return $this->_xml;
	}

	function &getParams( $nodeName='params' )
	{
		// Get the state parameters
		$module	=& $this->getModule();
		$params	= new JParameter($module->params);

		if ($xml =& $this->_getXML())
		{
			if (is_a($xml, 'JSimpleXML'))
			{
				$document =& $xml->document;
				$sp =& $document->getElementByPath($nodeName);
				$params->setXML($sp);
			}
		}
		return $params;
	}
}

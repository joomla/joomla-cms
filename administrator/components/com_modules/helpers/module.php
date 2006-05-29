<?php
/**
 * @version $Id: admin.menus.php 3504 2006-05-15 05:25:43Z eddieajau $
 * @package Joomla
 * @subpackage Modules
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * @package Joomla
 * @subpackage Menus
 * @author Andrew Eddie
 */
class JModuleEditHelper extends JObject {
	// TODO: Move to library, derive a parent class to use for both module and components

	/**
	 * @var string The component file name
	 */
	var $_module;

	var $_client_id;

	var $_metadata;

	/**
	 * Constructor
	 */
	function __construct( $module, $client_id = 0 )
	{
		// clean the option
		$module = preg_replace( '#\W#', '', $module );
		$module = str_replace( 'mod_', '', $module );

		$this->_module		= $module;
		$this->_client_id	= $client_id ? 1 : 0;

		// load the xml metadata
		$this->_metadata = null;

		$path = JPATH_SITE . '/modules/mod_' . $this->_module . '/metadata.xml';
		if (file_exists( $path ))
		{
			$xml = & JFactory::getXMLParser('Simple');

			if ($xml->loadFile($path))
			{
				$this->_metadata = &$xml;
			}
		}
	}

	/**
	 * @access private
	 */
	function &_getMetadataDoc()
	{
		$result = null;
		if (isset( $this->_metadata->document ))
		{
			$result = &$this->_metadata->document;
		}
		return $result;
	}

	function hasControlParams()
	{
		return (boolean) $this->_getMetadataDoc();
	}

	/**
	 * @param string A params string
	 * @param string The option
	 */
	function &getControlParams( $params, $path='' )
	{
		$params = new JParameter( $params );

		if ($xmlDoc =& $this->_getMetadataDoc())
		{
			if (isset( $xmlDoc->control[0] ))
			{
				if (isset( $xmlDoc->control[0]->params[0] ))
				{
					$params->setXML( $xmlDoc->control[0]->params[0] );
				}
			}
		}
		return $params;
	}

	/**
	 * Allows for the parameter handling to be overridden
	 * if the component supports new parameter types
	 * @param string A params string
	 * @param string The option
	 * @return object A
	 */
	function &getViewParams( $ini, $control )
	{
		if ($this->_metadata == null)
		{
			// Check for component metadata.xml file
			$path = JApplicationHelper::getPath( 'mod'.$this->_client_id.'_xml', 'mod_' . $this->_module );
			$params = new JParameter( $ini, $path );
		}
		else
		{
			$params = new JParameter( $ini );

			$viewName = $control->get( 'view_name' );
			if ($viewName && $xmlDoc =& $this->_getMetadataDoc())
			{
				$eViews = &$xmlDoc->getElementByPath( 'control/views' );
				if ($eViews)
				{
					// we have a views element
					$eParams = &$eViews->getElementByPath( $viewName . '/params' );
					if ($eParams)
					{
						// we have a params element in the metadata
						$params->setXML( $eParams );
					}
					else
					{
						// check for a different source
						$source = $eViews->attributes( 'source' );
						if ($source)
						{
							// TODO: check for injection
							$path = JPATH_SITE . str_replace( '{VIEW_NAME}', $viewName, $source );
							if (file_exists( $path ))
							{
								// load the metadata file local to the view
								$xml = & JFactory::getXMLParser('Simple');
								if ($xml->loadFile($path))
								{
									$eParams = &$xml->document->getElementByPath( 'params' );
									$params->setXML( $eParams );
								}
							}
						}
					}
				}
			}
		}
		return $params;
	}
}
?>
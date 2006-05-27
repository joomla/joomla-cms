<?php
/**
 * @version $Id: admin.menus.php 3504 2006-05-15 05:25:43Z eddieajau $
 * @package Joomla
 * @subpackage Menus
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
class JMenuHelper extends JObject {
	// TODO: Move to library, derive similar class for module support

	/**
	 * @var string The component file name
	 */
	var $_option = null;

	var $_metadata;

	/**
	 * Constructor
	 */
	function __construct( $option )
	{
		// clean the option
		$option = preg_replace( '#\W#', '', $option );
		$option = str_replace( 'com_', '', $option );

		$this->_option = $option;

		// load the xml metadata
		$this->_metadata = null;

		$path = JPATH_SITE . '/components/com_' . $this->_option . '/metadata.xml';

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
			$path = JApplicationHelper::getPath( 'com_xml', 'com_' . $this->_option );
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

	/**
	 * @return boolean True if the component supports controllers
	 */
	function hasMVCRT()
	{
		return $this->hasControllers()
			| $this->hasViews()
			| $this->hasRenderers()
			| $this->hasTemplates();
	}

	/**
	 * @return boolean True if the component supports controllers
	 */
	function hasControllers()
	{
		return false;
	}

	/**
	 * @return boolean True if the component supports views
	 */
	function hasViews()
	{
		return false;
	}

	/**
	 * @return boolean True if the component supports templates
	 */
	function hasRenderers()
	{
		return false;
	}

	/**
	 * @return boolean True if the component supports templates
	 */
	function hasTemplates()
	{
		return false;
	}

	function getControllersFolder()
	{
		return JPATH_SITE . DS . 'components' . DS . 'com_' . $this->_option . DS . $this->_controllersFolder  . DS;
	}

	function getViewsFolder()
	{
		return JPATH_SITE . DS . 'components' . DS . 'com_' . $this->_option . DS . $this->_viewsFolder  . DS;
	}

	/**
	 * Gets a list of the available views
	 */
	function getControllerList() {
		jimport( 'joomla.filesystem.folder');

		$folderName = $this->getControllersFolder();
		if (!is_dir( $folderName ))
		{
			return array();
		}

		$files = JFolder::files( $folderName, '\.php$' );

		$result = array();
		$xml = JFactory::getXMLParser( 'Simple' );

		foreach ($files as $file)
		{
			$file = preg_replace( '#\.php$#', '', $file );
			$text = $file;

			$metaDataFile = $folderName . $file . '.xml';
			if (file_exists( $metaDataFile ))
			{
		 		$xml = new JSimpleXML;
				if ($xml->loadFile( $metaDataFile ))
				{
					if (isset( $xml->document->name ))
					{
						$text = $xml->document->name[0]->data();
					}
				}
			}

			$result[] = array(
				'value' => $file,
				'text' => $text
			);
		}

		return $result;
	}
	/**
	 * Gets a list of the available views
	 */
	function getViewList() {
		jimport( 'joomla.filesystem.folder');

		$folderName = $this->getViewsFolder();
		if (!is_dir( $folderName ))
		{
			return array();
		}

		$folders = JFolder::folders( $folderName, '.' );

		$result = array();

		foreach ($folders as $folder)
		{
			$text = $folder;

			$metaDataFile = $folderName . $folder . DS . 'metadata.xml';
			if (file_exists( $metaDataFile ))
			{
		 		$xml = new JSimpleXML;
				if ($xml->loadFile( $metaDataFile ))
				{
					if (isset( $xml->document->name ))
					{
						$text = $xml->document->name[0]->data();
					}
				}
			}

			$result[] = array(
				'value' => $folder,
				'text' => $text
			);
		}

		return $result;
	}

	function getContollerParams( $controller_name, $paramValues )
	{
		$folderName = $this->getControllersFolder();
		$fileName = $folderName . $controller_name . '.xml';

		if (file_exists( $fileName ))
		{
			$result = new JParameter( $paramValues, $fileName );
		}
		else
		{
			$result = new JParameter( $paramValues );
		}
		return $result;
	}

	/**
	 * Loads files required for menu items
	 * @param string Item type
	 */
	function menuItem( $item ) {
		$path = JPATH_ADMINISTRATOR .'/components/com_menus/'. $item .'/';
		include_once( $path . $item .'.class.php' );
		include_once( $path . $item .'.menu.html.php' );
	}
}
?>
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
 * @author Louis Landry <louis.landry@joomla.org>
 */
class JMenuHelperComponent extends JObject
{
	/**
	 * @var string The component file name
	 */
	var $_option = null;

	var $_parent = null;

	function __construct(&$parent)
	{
		$this->_parent =& $parent;
		$app =& $this->_parent->getApplication();
		$option = $app->getUserStateFromRequest('menuwizard.component.option', 'component', 'content');
		$this->setOption($option);
	}

	/**
	 * Set model state
	 */
	function setOption( $option )
	{
		// clean the option
		$option = preg_replace( '#\W#', '', $option );
		$option = str_replace( 'com_', '', $option );

		$this->_option = $option;
	}

	function loadXML()
	{
		$path = JPATH_ROOT.'/components/com_'.$this->_option.'/metadata.xml';
		$this->_parent->_wizard->loadXML($path, 'control');
	}

	/**
	 * Returns the option
	 * @return string
	 */
	function getOption()
	{
		return $this->_option;
	}

	/**
	 * @param string A params string
	 * @param string The option
	 */
	function &getFinalized( &$vals, $step )
	{
		$final = new stdClass();
		$final->values =& $vals;
		$final->message = null;
		$final->menutype = 'component';
		$final->link = $this->_buildLink($vals);
		$final->type = null;
		$final->componentid = null;
		$final->params =& $vals;
		$final->mvcrt = 0;

		return $final;
	}

	function getControllersFolder()
	{
		return JPATH_ROOT.DS.'components'.DS.'com_'.$this->_option.DS.$this->_controllersFolder.DS;
	}

	function getViewsFolder()
	{
		return JPATH_ROOT.DS.'components'.DS.'com_'.$this->_option.DS.$this->_viewsFolder.DS;
	}

	/**
	 * Gets a list of the available views
	 */
	function getControllerList() {
		jimport( 'joomla.filesystem.folder');

		$folderName = $this->getControllersFolder();
		if (!is_dir( $folderName )) {
			return array();
		}

		$files = JFolder::files( $folderName, '\.php$' );

		$result = array();
		$xml = JFactory::getXMLParser( 'Simple' );

		foreach ($files as $file) {
			$file = preg_replace( '#\.php$#', '', $file );
			$text = $file;

			$metaDataFile = $folderName.$file.'.xml';
			if (file_exists( $metaDataFile )) {
		 		$xml = new JSimpleXML;
				if ($xml->loadFile( $metaDataFile )) {
					if (isset( $xml->document->name )) {
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
		if (!is_dir( $folderName )) {
			return array();
		}

		$folders = JFolder::folders( $folderName, '.' );

		$result = array();

		foreach ($folders as $folder) {
			$text = $folder;

			$metaDataFile = $folderName.$folder.DS.'metadata.xml';
			if (file_exists( $metaDataFile )) {
		 		$xml = new JSimpleXML;
				if ($xml->loadFile( $metaDataFile )) {
					if (isset( $xml->document->name )) {
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
		$fileName = $folderName.$controller_name.'.xml';

		if (file_exists( $fileName )) {
			$result = new JParameter( $paramValues, $fileName );
		} else {
			$result = new JParameter( $paramValues );
		}
		return $result;
	}
}
?>
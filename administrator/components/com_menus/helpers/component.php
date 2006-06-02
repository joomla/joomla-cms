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

jimport('joomla.presentation.wizard');

/**
 * @package Joomla
 * @subpackage Menus
 * @author Louis Landry <louis.landry@joomla.org>
 */
class JMenuHelperComponent extends JWizardHelper
{
	var $_helperContext	= 'menu';

	var $_helperName	= 'component';

	/**
	 * @var string The component file name
	 */
	var $_type = null;

	var $_option = null;

	/**
	 * Initializes the helper class with the wizard object and loads the wizard xml.
	 * 
	 * @param object JWizard
	 */
	function init(&$wizard)
	{
		$app =& $this->_parent->getApplication();
		$option = $app->getUserStateFromRequest('menuwizard.component.option', 'component', 'content');
		$this->_type = $app->getUserStateFromRequest('menuwizard.menutype', 'menutype');
		$this->setOption($option);
		// init last because of special option handling
		parent::init( $wizard );
	}

	function loadXML()
	{
		$path = JPATH_ROOT.'/components/com_'.$this->_option.'/metadata.xml';
		$this->_wizard->loadXML($path, 'control');
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
	 * Set model state
	 */
	function setOption( $option )
	{
		// clean the option
		$option = preg_replace( '#\W#', '', $option );
		$option = str_replace( 'com_', '', $option );
		$this->_option = $option;
	}

	/**
	 * Returns the wizard name
	 * @return string
	 */
	function getWizardName()
	{
		$name = parent::getWizardName();
		if ($this->_option) {
			$name .= '.'.$this->_option;
		}
		return $name;
	}

	/**
	 * @param string A params string
	 * @param string The option
	 */
	function &getConfirmation()
	{
		$values	=& $this->_wizard->getConfirmation();

		$final['type']		= 'component';
		$final['component']	= $this->_option;
		$final['menu_type']	= $this->_type;
		$final['control']	= $values;

		return $final;
	}

	function getDetails()
	{
		$item =& $this->_parent->getItem();
		if ($cid = $item->componentid) {
			$db =& $this->_parent->getDBO();
			$query = "SELECT `name`, `option`" .
					"\n FROM `#__components`" .
					"\n WHERE `id` = $cid";
			$db->setQuery($query);
			$result = $db->loadResultArray();
			$name	= $result[0];
			$option = $result[1];
		} else {
			$name	= ucfirst(JRequest::getVar('component', 'content'));
			$option = 'com_'.JRequest::getVar('component', 'content');
		}

		$details[] = array('label' => JText::_('Type'), 'name' => JText::_('Component'), 'key' => 'type', 'value' => 'component');
		$details[] = array('label' => JText::_('Component'), 'name' => $name, 'key' => 'component', 'value' => $option);

		return $details;
	}

	function getStateXML()
	{
		$item =& $this->_parent->getItem();

		if ($cid = $item->componentid) {
			$db =& $this->_parent->getDBO();
			$query = "SELECT `option`" .
					"\n FROM `#__components`" .
					"\n WHERE `id` = $cid";
			$db->setQuery($query);
			$option = $db->loadResult();
		} else {
			$option = 'com_'.JRequest::getVar('component', 'content');
		}

		// load the xml metadata
		$path = JPATH_ROOT.'/components/'.$option.'/metadata.xml';
		$xpath = 'state';
		return array('path' => $path, 'xpath' => $xpath);
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
<?php
/**
* @version $Id: templates.class.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Templates
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Template factory class
 * @package Joomla
 * @subpackage Templates
 */
class mosTemplateFactory {
	/**
	 * @return object A template installer object
	 */
	function &createInstaller() {
		mosFS::load( '/administrator/components/com_installer/installer.class.php' );
		mosFS::load( '/administrator/components/com_templates/templates.installer.php' );
		return new mosTemplateInstaller();
	}
}

/**
 * @package Joomla
 * @subpackage Templates
 */
class mosTemplate {
	/**
	 * @param int The client number
	 * @param boolean Add trailing slash to path
	 */
	function getBasePath( $client, $addTrailingSlash=true ) {
		global $mosConfig_absolute_path;

		switch ($client) {
			case '1':
				$dir =  '/administrator/templates';
				break;
			default:
				$dir = '/templates';
				break;
		}

		return mosFS::getNativePath( $mosConfig_absolute_path . $dir, $addTrailingSlash );
	}
}

/**
 * Class mosTemplates_menu
 * @package Joomla
 * @subpackage Templates
 */
class mosTemplatesMenu extends mosDBTable {
	/** @var string The template name */
	var $template;
	/** @var int The menu id (foreign key) */
	var $menuid;
	/** @var int The client identifier */
	var $client_id;

	/**
	 * Constructor
	 */
	function mosTemplatesMenu( &$db ) {
		$this->mosDBTable( '#__templates_menu', '', $db );
	}

	/**
	 * Gets the current template
	 * @param int The client id
	 * @param int The menu id
	 */
	function getCurrent( $client=null, $menuid=null ) {
		if (is_null( $client )) {
			$client = $this->client_id;
		}
		if (is_null( $menuid )) {
			$menuid = $this->menuid;
		}

		$query = '
			SELECT template
			FROM ' . $this->_tbl . '
			WHERE client_id=' . $this->_db->Quote( $client ) . ' AND menuid=' . $this->_db->Quote( $menuid );
		$this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}

	/**
	 * Gets an array of menu ids associated with the template
	 */
	function getMenus( $client=null, $template=null ) {
		if (is_null( $client )) {
			$client = $this->client_id;
		}
		if (is_null( $template )) {
			$template = $this->template;
		}

		$query = '
			SELECT menuid
			FROM ' . $this->_tbl . '
			WHERE client_id=' . $this->_db->Quote( $client ) . ' AND template=' . $this->_db->Quote( $template );
		$this->_db->setQuery( $query );

		return $this->_db->loadAssocList();
	}

	/**
	 * Set the default template
	 */
	function setDefault() {
		if (trim( $this->template )) {
			$query = 'DELETE FROM #__templates_menu' .
					' WHERE client_id='.$this->_db->Quote( $this->client_id ) .' AND menuid=' . $this->_db->Quote( '0' );
			$this->_db->setQuery( $query );
			$this->_db->query();

			$query = 'INSERT INTO #__templates_menu' .
					' SET client_id='.$this->_db->Quote( $this->client_id ) .',' .
					' template='.$this->_db->Quote( $this->template ) .',' .
					' menuid=' . $this->_db->Quote( '0' );
			$this->_db->setQuery( $query );
			$this->_db->query();
		}
	}

}

/**
 * @package Joomla
 * @subpackage Templates
 */
class mosTemplatePosition extends mosDBTable {
	/** @var int Primary key */
	var $id=null;
	/** @var string The position name */
	var $position=null;
	/** @var string An optional description (used in lists) */
	var $description=null;

	/** Constructor */
	function mosTemplatePosition( &$db ) {
		$this->mosDBTable( '#__template_positions', 'id', $db );
	}

	/**
	 * Select records
	 * @param string The name of the view
	 * @return array A list of selected records
	 */
	function select( $view='' ) {
		switch ($view) {
			default:
				$query = "SELECT * FROM #__template_positions ORDER BY ID";
				break;
		}
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	/**
	 * Clears the table
	 * @return boolean
	 */
	function clear() {
		$query = 'DELETE FROM #__template_positions';
		$this->_db->setQuery( $query );
		return $this->_db->query();
	}

	/**
	 * Inserts a data row
	 */
	function insert() {
		return $this->_db->insertObject( $this->_tbl, $this );
	}
}
?>
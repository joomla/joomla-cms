<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Languages
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Languages Component Languages Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Languages
 * @since 1.6
 */
class LanguagesModelLanguages extends JModel
{
	/**
	 * Category ata array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Category total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Client object
	 *
	 * @var object
	 */
	var $_client = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		global $mainframe, $option;

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$this->_client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', 0, '', 'int'));
	}

	/**
	 * Method to get Languagess item data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$this->_loadData();
		}

		return $this->_data;
	}

	/**
	 * Method to get the total number of Languages items
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$this->getData();
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the Languagess
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to get the client object
	 *
	 * @access public
	 * @return object
	 */
	function getClient()
	{
		return $this->_client;
	}

	function _loadData()
	{
		// Initialize some variables
		$rows	= array ();
		$rowid = -1;
		$rowstart = $this->getState('limitstart') + 0;
		$rowend = $rowstart + $this->getState('limit') - 1;

		//load folder filesystem class
		jimport('joomla.filesystem.folder');
		$path = JLanguage::getLanguagePath($this->_client->path);
		$dirs = JFolder::folders( $path );

		foreach ($dirs as $dir)
		{
			$files = JFolder::files( $path.DS.$dir, '^([-_A-Za-z]*)\.xml$' );
			foreach ($files as $file)
			{
				$rowid++;
				// Only include the current page
				if ($rowid < $rowstart) {
					continue;
				}

				if ($rowid > $rowend) {
					continue;
				}

				$data = JApplicationHelper::parseXMLLangMetaFile($path.DS.$dir.DS.$file);

				$row 			= new StdClass();
				$row->id 		= $rowid;
				$row->language 	= substr($file,0,-4);

				if (!is_array($data)) {
					continue;
				}
				foreach($data as $key => $value) {
					$row->$key = $value;
				}

				// if current than set published
				$params = JComponentHelper::getParams('com_languages');
				if ( $params->get($this->_client->name, 'en-GB') == $row->language) {
					$row->published	= 1;
				} else {
					$row->published = 0;
				}

				$row->checked_out = 0;
				$row->mosname = JString::strtolower( str_replace( " ", "_", $row->name ) );
				$rows[] = $row;
			}
		}
		$this->_data = $rows;
		$this->_total = $rowid+1;
	}
}
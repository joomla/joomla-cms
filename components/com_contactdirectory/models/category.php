<?php
/**
 * @version		$Id: category.php
 * @package		Joomla
 * @subpackage	ContactDirectory
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * @package		Joomla
 * @subpackage	ContactDirectory
 */
class ContactdirectoryModelCategory extends JModel
{
	var $_id = null;
	var $_data = null;
	var $_total = null;
	var $_category = null;
	var $_fields = null;
	var $_pagination = null;
	var $_alphabet = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		global $mainframe;
		$config = JFactory::getConfig();

		// Get the pagination request variables
		$this->setState('limit', $mainframe->getUserStateFromRequest('com_contactdirectory.limit', 'limit', $config->getValue('config.list_limit'), 'int'));
		$this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));

		// In case limit has been changed, adjust limitstart accordingly
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));

		$this->_id	= JRequest::getVar('catid', 0, '', 'int');
	}

	/**
	 * Method to get contact item data for the current category
	 *
	 * @param	int	$state	The content state to pull from for the current
	 * category
	 * @since 1.5
	 */
	function getData()
	{
		// Load the Category data
		if ($this->_loadCategory() && $this->_loadData())
		{
			// Initialize some variables
			$user	=& JFactory::getUser();

			// Make sure the category is published
			if (!$this->_category->published)
			{
				JError::raiseError(404,  JText::_('RESOURCE NOT FOUND'));
				return false;
			}

			// check whether category access level allows access
			if ($this->_category->access > $user->get('aid', 0))
			{
				JError::raiseError(403, JText::_('ALERTNOTAUTH'));
				return false;
			}
		}
		return $this->_data;
	}

	function getFields()
	{
		if(!$this->_fields){
			$this->getData();
			for($i=0; $i<count($this->_data); $i++) {
				$id = $this->_data[$i]->id;
				$query = " SELECT f.id, f.title, d.data, f.pos, f.type, d.show_directory AS show_field, f.params, f.access "
						." FROM #__contactdirectory_fields f "
						." LEFT JOIN #__contactdirectory_details d ON d.field_id = f.id "
						." WHERE f.published = 1 AND d.contact_id = $id"
						." ORDER BY f.pos, f.ordering ";
				$this->_db->setQuery($query);
				$this->_fields[] = $this->_db->loadObjectList();
			}
		}
		return $this->_fields;
	}

	/**
	 * Method to get the total number of contact items
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	/**
	 * Method to get category data for the current category
	 *
	 * @since 1.5
	 */
	function getCategory()
	{
		// Load the Category data
		if ($this->_loadCategory())
		{
			// Initialize some variables
			$user = &JFactory::getUser();

			// Make sure the category is published
			if (!$this->_category->published) {
				JError::raiseError(404,  JText::_('RESOURCE NOT FOUND'));
				return false;
			}
			// check whether category access level allows access
			if ($this->_category->access > $user->get('aid', 0)) {
				JError::raiseError(403, JText::_('ALERTNOTAUTH'));
				return false;
			}
		}
		return $this->_category;
	}

	/**
	 * Method to load category data if it doesn't exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadCategory()
	{
		if (empty($this->_category))
		{
			// Lets get the information for the current category
			$query =  'SELECT *, '
							.' CASE WHEN CHAR_LENGTH(alias) '
							.' THEN CONCAT_WS(\':\', id, alias) ELSE id END AS slug '
							.' FROM #__categories WHERE id = '. (int) $this->_id;
			$this->_db->setQuery($query);
			$this->_category = $this->_db->loadObject();
		}
		return true;
	}

	/**
	 * Method to load contact item data for items in the category if they don't
	 * exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadData()
	{
		if (empty($this->_category)) {
			return false;
		}

		// Lets load the contact data if they don't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$rows = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

			if($rows != null){
				foreach($rows as $row){
					$row->slug = $row->id.':'.$row->alias;
				}
			}

			$this->_data = $rows;
		}
		return true;
	}

	function _buildQuery()
	{
		global $mainframe;
		// Get the page/component configuration
		$params = &$mainframe->getParams();

		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT c.*, cat.title AS category, v.name AS user'
			. ' FROM #__contactdirectory_contacts AS c '
			. ' LEFT JOIN #__users AS v ON v.id = c.user_id '
			. ' LEFT JOIN #__contactdirectory_con_cat_map AS map ON map.contact_id = c.id '
			. ' LEFT JOIN #__categories AS cat ON cat.id = map.category_id '.
			$where.
			$orderby;

		return $query;
	}


	function _buildContentOrderBy()
	{
		global $mainframe;
		// Get the page/component configuration
		$params = &$mainframe->getParams();

		$orderby = ' ORDER BY ';

		$orderby_params	= $params->def('orderby', 'order');
		switch ($orderby_params) {
			case 'alpha' :
				$orderby_params = 'c.name ';
				break;
			case 'ralpha' :
				$orderby_params = 'c.name DESC ';
				break;
			case 'order' :
				$orderby_params = 'map.ordering ';
				break;
			default :
				$orderby_params = '';
				break;
		}
		$orderby .= $orderby_params;

		return $orderby;
	}

	function _buildContentWhere()
	{
		global $mainframe, $option;

		$user		=& JFactory::getUser();
		$gid		= $user->get('aid', 0);
		$db					=& JFactory::getDBO();

		$alphabet	= $mainframe->getUserStateFromRequest( $option.'alphabet', 	'alphabet',	'',	'string' );
		$search		= $mainframe->getUserStateFromRequest( $option.'search',		'search',	'',	'string' );
		$search		= JString::strtolower( $search );

		// Get the page/component configuration
		$params = &$mainframe->getParams();

		$where = ' WHERE 1';

		// Does the user have access to view the items?
		$where .= ' AND c.access <= '.(int) $gid;

		// First thing we need to do is assert that the contacts are in the current category
		if ($this->_id) {
			$where .= ' AND map.category_id = '.(int) $this->_id;
		}

		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		 * for the filter onto the WHERE clause of the contact item query.
		 */
		if ($params->get('search')) {
			if ($search) {
				// clean filter variable
				$search = JString::strtolower($search);
				$search	= $this->_db->Quote( '%'.$this->_db->getEscaped( $search, true ).'%', false );

				$where .= ' AND LOWER( c.name ) LIKE '.$search;
			}
		}
		if ($params->get('alphabet')) 	{
			if ($alphabet) {
				// clean filter variable
				$alphabet = JString::strtolower($alphabet);
				$alphabet	= $this->_db->Quote( $this->_db->getEscaped( $alphabet, true ).'%', false );

				$where .= ' AND LOWER( c.name ) LIKE '.$alphabet;
			}
		}
		return $where;
	}

	function getAlphabet($category)
	{
		$user		=& JFactory::getUser();
		$gid		= $user->get('aid', 0);

		$query = ' SELECT DISTINCT ucase(substr(c.name,1,1)) AS active '
				.' FROM #__contactdirectory_contacts AS c '
				.' LEFT JOIN #__contactdirectory_con_cat_map AS map ON map.contact_id = c.id '
				.' WHERE c.published = 1 AND c.access <= '.(int) $gid
				.' AND map.category_id = '.$category->id;

		$this->_db->setQuery($query);
		$this->_alphabet = $this->_db->loadResultArray();

		return $this->_alphabet;
	}
}

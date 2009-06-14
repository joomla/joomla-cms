<?php
/**
 * @version		$Id: category.php 11838 2009-05-27 22:07:20Z eddieajau $
 * @package		Joomla.Administrator
 * @subpackage	Categories
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Categories Component Category Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Categories
 * @since 1.5
 */
class CategoriesModelCategory extends JModel
{
	/**
	 * Category id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Category data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid', array(0), '', 'array');
		$edit	= JRequest::getVar('edit',true);
		if ($edit)
			$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the category identifier
	 *
	 * @access	public
	 * @param	int Category identifier
	 */
	function setId($id)
	{
		// Set category id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a category
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the category data
		if (!$this->_loadData())
			$this->_initData();

		return $this->_data;
	}

	/**
	 * Method to get the group form.
	 *
	 * @access	public
	 * @return	mixed	JXForm object on success, false on failure.
	 * @since	1.0
	 */
	function &getForm()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$false	= false;

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT.'/models/fields');
		$form = &JForm::getInstance('jform', 'category', true, array('array' => true));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return $false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_categories.edit.category.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}
	
	/**
	 * Tests if category is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.5
	 */
	function isCheckedOut($uid=0)
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		}
	}

	/**
	 * Method to load category data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT s.* '.
					' FROM #__categories AS s' .
					' WHERE s.id = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the category data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$category = new stdClass();
			$category->id				= 0;
			$category->parent			= -1;
			$category->name				= null;
			$category->alias			= null;
			$category->title			= null;
			$category->extension		= JRequest::getCmd('extension', 'com_content');
			$category->description		= null;
			$category->count			= 0;
			$category->params			= null;
			$category->published		= 0;
			$category->checked_out		= 0;
			$category->checked_out_time	= 0;
			$category->archived			= 0;
			$category->approved			= 0;
			$category->categories		= 0;
			$category->active			= 0;
			$category->access			= 0;
			$category->trash			= 0;
			$this->_data				= $category;
			return (boolean) $this->_data;
		}
		return true;
	}
}

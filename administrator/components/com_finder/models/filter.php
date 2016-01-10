<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Filter model class for Finder.
 *
 * @since  2.5
 */
class FinderModelFilter extends JModelAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $text_prefix = 'COM_FINDER';

	/**
	 * Model context string.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'com_finder.filter';

	/**
	 * Custom clean cache method.
	 *
	 * @param   string   $group      The component name. [optional]
	 * @param   integer  $client_id  The client ID. [optional]
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function cleanCache($group = 'com_finder', $client_id = 1)
	{
		parent::cleanCache($group, $client_id);
	}

	/**
	 * Method to get the filter data.
	 *
	 * @return  mixed  The filter data.
	 *
	 * @since   2.5
	 */
	public function getFilter()
	{
		$filter_id = (int) $this->getState('filter.id');

		// Get a FinderTableFilter instance.
		$filter = $this->getTable();

		// Attempt to load the row.
		$return = $filter->load($filter_id);

		// Check for a database error.
		if ($return === false && $filter->getError())
		{
			$this->setError($filter->getError());

			return false;
		}

		// Process the filter data.
		if (!empty($filter->data))
		{
			$filter->data = explode(',', $filter->data);
		}
		elseif (empty($filter->data))
		{
			$filter->data = array();
		}

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		return $filter;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form. [optional]
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not. [optional]
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   2.5
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_finder.filter', 'filter', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Returns a JTable object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate. [optional]
	 * @param   string  $prefix  A prefix for the table class name. [optional]
	 * @param   array   $config  Configuration array for model. [optional]
	 *
	 * @return  JTable  A database object
	 *
	 * @since   2.5
	 */
	public function getTable($type = 'Filter', $prefix = 'FinderTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   2.5
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_finder.edit.filter.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_finder.filter', $data);

		return $data;
	}

	/**
	 * Method to get the total indexed items
	 *
	 * @return  number the number of indexed items
	 *
	 * @since  3.5
	 */
	public function getTotal()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('MAX(link_id)')
			->from('#__finder_links');
		$db->setQuery($query);
		$total = $db->loadResult();

		return $total;
	}
}

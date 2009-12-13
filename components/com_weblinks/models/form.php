<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.database.query');

/**
 * Weblink Component Weblink Model
 *
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since 1.6
 */
class WeblinksModelForm extends JModelForm
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	 protected $_context = 'com_weblinks.edit.weblink';

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app = &JFactory::getApplication();

		// Load state from the request.
		if (!($pk = (int) $app->getUserState($this->_context.'.id'))) {
			$pk = (int) JRequest::getInt('id');
		}
		$this->setState('weblink.id', $pk);

		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);
	}

	/**
	 * Returns a reference to the a Table object, always creating it
	 *
	 * @param	type 	$type 	 The table type to instantiate
	 * @param	string 	$prefix	 A prefix for the table class name. Optional.
	 * @param	array	$options Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function &getTable($type = 'Weblink', $prefix = 'WeblinksTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a form object.
	 *
	 * @access	public
	 * @param	string		$xml		The form data. Can be XML string if file flag is set to false.
	 * @param	array		$options	Optional array of parameters.
	 * @param	boolean		$clear		Optional argument to force load a new form.
	 * @return	mixed		JForm object on success, False on error.
	 */
	public function &getForm($xml = 'weblink', $name = 'com_weblinks.weblink', $options = array(), $clear = false)
	{
		$options += array('array' => 'jform', 'event' => 'onPrepareForm');

		$form = parent::getForm($xml, $name, $options);

		return $form;
	}

	/**
	 * Method to get article data.
	 *
	 * @param	integer	The id of the article.
	 *
	 * @return	mixed	Content item data object on success, false on failure.
	 */
	public function getItem($itemId = null)
	{
		// Initialise variables.
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('weblink.id');

		// Get a row instance.
		$table = &$this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return false;
		}

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');

		return $value;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @access	public
	 * @param	object		$form		The form to validate against.
	 * @param	array		$data		The data to validate.
	 * @return	mixed		Array of filtered data if valid, false otherwise.
	 */
	public function validate($form, $data)
	{
		$this->_setAccessFilters($form, $data);

		return parent::validate($form, $data);
	}

	protected function _setAccessFilters(&$form, $data)
	{
		$user = JFactory::getUser();

		if (!$user->authorise('core.edit.state', 'com_weblinks')) {
			$form->setFieldAttribute('state', 'filter', 'unset');
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
		// Initialise variables
		$dispatcher = &JDispatcher::getInstance();
		$table		= &$this->getTable();
		$form		= &$this->getForm();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState('weblink.id');
		$isNew		= true;

		if (!$form) {
			JError::raiseError(500, $model->getError());
			return false;
		}

		// Validate the posted data.
		$data	= $this->validate($form, $data);
		if ($data === false) {
			return false;
		}

		// Load the row if saving an existing item.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Prepare the row for saving
		$this->_prepareTable($table);

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		// Clean the cache.
		$cache = &JFactory::getCache('com_weblinks');
		$cache->clean();

		$this->setState('weblink.id', $table->id);

		return true;
	}

	protected function _prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= JFilterOutput::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = JFilterOutput::stringURLSafe($table->title);
		}

		if (empty($table->id)) {
			// Set the values
			$table->date	= $date->toMySQL();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__weblinks WHERE catid = '.(int)$table->catid);
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
		else {
			// Set the values
			//$table->modified	= $date->toMySQL();
			//$table->modified_by	= $user->get('id');
		}
	}
}
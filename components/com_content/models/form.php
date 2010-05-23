<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Content Component Article Model
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.5
 */
class ContentModelForm extends JModelForm
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_content.edit.article';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = &JFactory::getApplication();

		// Load state from the request.
		if (!($pk = (int) $app->getUserState($this->_context.'.id'))) {
			$pk = (int) JRequest::getInt('id');
		}
		$this->setState('article.id', $pk);

		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);
	}

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Content', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the login form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_content.article', 'article', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

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
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('article.id');

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

		$value->text = $value->introtext;
		if (!empty($value->fulltext)) {
			$value->text .= '<hr id="system-readmore" />'.$value->fulltext;
		}


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

		if (!$user->authorise('core.edit.state', 'com_content')) {
			$form->setFieldAttribute('state', 'filter', 'unset');
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_content.edit.article.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
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
		$dispatcher = JDispatcher::getInstance();
		$table		= $this->getTable();
		$form		= $this->getForm($data, false);
		$pk			= (!empty($data['id'])) ? $data['id'] : (int) $this->getState('article.id');
		$isNew		= true;

		if (!$form) {
			JError::raiseError(500, $this->getError());
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
			$this->setError($table->getError());
			return false;
		}

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Set the publish date to now
		if($table->state == 1 && intval($table->publish_up) == 0) {
			$table->publish_up = JFactory::getDate()->toMySQL();
		}
		
		// Increment the content version number
		$table->version++;

		// Include the content plugins for the onSave events.
		JPluginHelper::importPlugin('content');

		$result = $dispatcher->trigger('onContentBeforeSave', array('com_content.article', &$table, $isNew));
		if (in_array(false, $result, true)) {
			JError::raiseError(500, $table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		// Clean the cache.
		$cache = &JFactory::getCache('com_content');
		$cache->clean();

		$dispatcher->trigger('onContentAfterSave', array('com_content.article', &$table, $isNew));

		$this->setState('article.id', $table->id);

		return true;
	}

	/**
	 * Method to checkin a row.
	 *
	 * @param	integer	$pk The numeric id of a row
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function checkin($pk = null)
	{
		// Initialise variables.
		$pk	= (!empty($pk)) ? $pk : (int) $this->getState('article.id');

		// Only attempt to check the row in if it exists.
		if ($pk)
		{
			$user	= &JFactory::getUser();

			// Get an instance of the row to checkin.
			$table = &$this->getTable();
			if (!$table->load($pk)) {
				$this->setError($table->getError());
				return false;
			}

			// Check if this is the user having previously checked out the row.
			if ($table->checked_out > 0 && $table->checked_out != $user->get('id')) {
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'));
				return false;
			}

			// Attempt to check the row in.
			if (!$table->checkin($pk)) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param	int		$pk	The numeric id of the row to check-out.
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function checkout($pk = null)
	{
		// Initialise variables.
		$pk		= (!empty($pk)) ? $pk : (int) $this->getState('article.id');

		// Only attempt to check the row in if it exists.
		if ($pk)
		{
			// Get a row instance.
			$table = &$this->getTable();

			// Get the current user object.
			$user = &JFactory::getUser();

			// Attempt to check the row out.
			if (!$table->checkout($user->get('id'), $pk)) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}
}
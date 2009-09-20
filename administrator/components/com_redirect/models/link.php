<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.application.component.model');

/**
 * The Redirect Link Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @version		1.6
 */
class RedirectModelLink extends JModel
{
	/**
	 * Flag to indicate model state initialization.
	 *
	 * @var	boolean
	 */
	protected $__state_set = false;

	/**
	 * Array of items for memory caching.
	 *
	 * @var	array
	 */
	protected $_items = array();

	/**
	 * Overridden method to get model state variables.
	 *
	 * @access	public
	 * @param	string	$property	Optional parameter name.
	 * @return	object	The property where specified, the state object where omitted.
	 * @since	1.6
	 */
	function getState($property = null, $default = null)
	{
		if (!$this->__state_set)
		{
			// Get the application object.
			$app = & JFactory::getApplication();

			// Attempt to auto-load the link id.
			if (!$linkId = (int) $app->getUserState('redirect.edit.link.id')) {
				$linkId = (int) JRequest::getInt('l_id');
			}

			// Only set the link id if there is a value.
			if ($linkId) {
				$this->setState('link.id', $linkId);
			}

			// Set the model state set flat to true.
			$this->__state_set = true;
		}

		$value = parent::getState($property);
		return (is_null($value) ? $default : $value);
	}

	/**
	 * Method to get a link item.
	 *
	 * @access	public
	 * @param	integer	The id of the link to get.
	 * @return	mixed	Link data object on success, false on failure.
	 * @since	1.6
	 */
	function & getItem($linkId = null)
	{
		// Initialize variables.
		$linkId = (!empty($linkId)) ? $linkId : (int) $this->getState('link.id');
		$false	= false;

		// Get a link row instance.
		$table = & $this->getTable('Link', 'RedirectTable');

		// Attempt to load the row.
		$return = $table->load($linkId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->serError($table->getError());
			return $false;
		}

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return $false;
		}

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');
		return $value;
	}

	/**
	 * Method to get the link form.
	 *
	 * @access	public
	 * @return	mixed	JForm object on success, false on failure.
	 * @since	1.6
	 */
	function & getForm()
	{
		// Initialize variables.
		$app	= & JFactory::getApplication();
		$false	= false;

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
		$form = & JForm::getInstance('link', 'com_redirect.link', true, array('array' => true));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return $false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('redirect.edit.link.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to publish links.
	 *
	 * @access	public
	 * @param	array	The ids of the items to publish.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	function publish($linkId)
	{
		// Sanitize the ids.
		$linkId = (array) $linkId;
		JArrayHelper::toInteger($linkId);

		// Get the current user object.
		$user = & JFactory::getUser();

		// Get a link row instance.
		$table = & $this->getTable('Link', 'RedirectTable');

		// Attempt to publish the items.
		if (!$table->publish($linkId, 1, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to unpublish links.
	 *
	 * @access	public
	 * @param	array	The ids of the items to unpublish.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	function unpublish($linkId)
	{
		// Sanitize the ids.
		$linkId = (array) $linkId;
		JArrayHelper::toInteger($linkId);

		// Get the current user object.
		$user = & JFactory::getUser();

		// Get a link row instance.
		$table = & $this->getTable('Link', 'RedirectTable');

		// Attempt to unpublish the items.
		if (!$table->publish($linkId, 0, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to archive links.
	 *
	 * @access	public
	 * @param	array	The ids of the items to unpublish.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	function archive($linkId)
	{
		// Sanitize the ids.
		$linkId = (array) $linkId;
		JArrayHelper::toInteger($linkId);

		// Get the current user object.
		$user = & JFactory::getUser();

		// Get a link row instance.
		$table = & $this->getTable('Link', 'RedirectTable');

		// Attempt to unpublish the items.
		if (!$table->publish($linkId, 2, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to delete links.
	 *
	 * @access	public
	 * @param	array	An array of link ids.
	 * @return	boolean	Returns true on success, false on failure.
	 * @since	1.6
	 */
	function delete($linkId)
	{
		// Sanitize the ids.
		$linkId = (array) $linkId;
		JArrayHelper::toInteger($linkId);

		// Get a link row instance.
		$table = & $this->getTable('Link', 'RedirectTable');

		// Iterate the links to delete each one.
		foreach ($linkId as $id)
		{
			$table->delete($id);
		}

		return true;
	}

	/**
	 * Method to activate links.
	 *
	 * @access	public
	 * @param	array	An array of link ids.
	 * @param	string	The new URL to set for the redirect.
	 * @param	string	A comment for the redirect links.
	 * @return	boolean	Returns true on success, false on failure.
	 * @since	1.6
	 */
	function activate($linkId, $url, $comment=null)
	{
		// Sanitize the ids.
		$linkId = (array) $linkId;
		JArrayHelper::toInteger($linkId);

		// Populate default comment if necessary.
		$comment = (!empty($comment)) ? $comment : JText::sprintf('Redirect_Redirected_On', JHtml::date(time()));

		if (!empty($linkId)) {

			// Implode the link ids.
			$linkId = implode(' OR `id` = ', $linkId);

			// Update the link rows.
			$this->_db->setQuery(
				'UPDATE `#__redirect_links`' .
				' SET `new_url` = '.$this->_db->Quote($url).', `published` = 1, `comment` = '.$this->_db->Quote($comment) .
				' WHERE `id` ='.$linkId
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @access	public
	 * @param	array	The form data.
	 * @return	mixed	Array of filtered data if valid, false otherwise.
	 * @since	1.6
	 */
	function validate($data)
	{
		// Get the form.
		$form = & $this->getForm();

		// Check for an error.
		if ($form === false) {
			return false;
		}

		// Filter and validate the form data.
		$data	= $form->filter($data);
		$return	= $form->validate($data);

		// Check for an error.
		if (JError::isError($return)) {
			$this->setError($return->getMessage());
			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @access	public
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	function save($data)
	{
		$linkId = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('link.id');
		$isNew	= true;

		// Get a link row instance.
		$table = & $this->getTable('Link', 'RedirectTable');

		// Load the row if saving an existing item.
		if ($linkId > 0) {
			$table->load($linkId);
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

		// Store the data.
		if (!$table->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return $table->id;
	}
}

<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');
jimport('joomla.plugin.helper');

/**
 * Profile model class for Members.
 *
 * @package		Joomla.Site
 * @subpackage	com_members
 * @since		1.6
 */
class MembersModelProfile extends JModelItem
{
	/**
	 */
	protected function _populateState($property = null, $default = null)
	{
		// Get the application object.
		$app	= &JFactory::getApplication();
		$user	= &JFactory::getUser();
		$params	= &$app->getParams('com_members');

		// Get the member id.
		$memberId = JRequest::getInt('member_id', $app->getUserState('com_members.edit.profile.id'));
		$memberId = !empty($memberId) ? $memberId : (int)$user->get('id');

		// Set the member id.
		$this->setState('member.id', $memberId);

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for members plugins to extend the form with extra fields.
	 *
	 * @return	mixed		JForm object on success, false on failure.
	 */
	public function &getForm()
	{
		$false = false;

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.DS.'models'.DS.'forms');
		$form = &JForm::getInstance('jform', 'profile', true, array('array' => true));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return $false;
		}

		// Get the dispatcher and load the members plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('user');

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onPrepareUserProfileForm', array($this->getState('member.id'), &$form));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			$this->setError($dispatcher->getError());
			return $false;
		}

		return $form;
	}

	/**
	 * Method to get the profile form data.
	 *
	 * The base form data is loaded and then an event is fired
	 * for members plugins to extend the data.
	 *
	 * @return	mixed		Data object on success, false on failure.
	 */
	public function &getData()
	{
		$app	= &JFactory::getApplication();
		$false	= false;

		// Add the table include path and then initialize the table with JUser.
		$table	= &JTable::getInstance('User');
		$data	= &JUser::getInstance($this->getState('member.id'));

		// Set the base user data.
		$data->email1 = $data->get('email');
		$data->email2 = $data->get('email');

		// Override the base user data with any data in the session.
		$temp = (array)$app->getUserState('com_members.edit.profile.data', array());
		foreach ($temp as $k => $v) {
			$data->$k = $v;
		}

		// Unset the passwords.
		unset($data->password1);
		unset($data->password2);

		// Get the dispatcher and load the members plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('user');

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onPrepareUserProfileData', array($this->getState('member.id'), &$data));

		// Check for errors encountered while preparing the data.
		if (count($results) && in_array(false, $results, true)) {
			$this->setError($dispatcher->getError());
			return $false;
		}

		return $data;
	}

	public function &getProfile()
	{
		$false	= false;
		$data	= array();

		// Get the dispatcher and load the members plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('user');

		// Trigger the profile preparation event.
		$results = $dispatcher->trigger('onPrepareUserProfile', array($this->getState('member.id'), &$data));

		// Check for errors encountered while preparing the profile.
		if (count($results) && in_array(false, $results, true)) {
			$this->setError($dispatcher->getError());
			return $false;
		}

		return $data;
	}

	/**
	 * Method to check in a member.
	 *
	 * @param	integer		$memberId		The id of the row to check out.
	 * @return	boolean		True on success, false on failure.
	 */
	public function checkin($memberId = null)
	{
		// Get the member id.
		$memberId = (!empty($memberId)) ? $memberId : (int)$this->getState('member.id');

		if ($memberId)
		{
			// Add the table include path and then get the table with JUser.
			$table = JTable::getInstance('User');

			// Get the current user object.
			$user = &JFactory::getUser();

			// Attempt to check the row in.
			if (!$table->checkin($memberId)) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to check out a member for editing.
	 *
	 * @param	integer		$memberId		The id of the row to check out.
	 * @return	boolean		True on success, false on failure.
	 */
	public function checkout($memberId = null)
	{
		// Get the member id.
		$memberId = (!empty($memberId)) ? $memberId : (int)$this->getState('member.id');

		if ($memberId)
		{
			// Add the table include path and then get the table with JUser.
			$table = JTable::getInstance('User');

			// Get the current user object.
			$user = &JFactory::getUser();

			// Attempt to check the row out.
			if (!$table->checkout($user->get('id'), $memberId)) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array		$data		The form data.
	 * @return	mixed		The user id on success, false on failure.
	 */
	public function save($data)
	{
		$memberId = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('member.id');

		// Add the table include path and then initialize the table with JUser.
		JTable::getInstance('User');
		$user = JUser::getInstance($memberId);

		// Prepare the data for the user object.
		$data['email']		= $data['email1'];
		$data['password']	= $data['password1'];

		// Bind the data.
		if (!$user->bind($data)) {
			$this->setError(JText::sprintf('JError_Bind_Failed', $user->getError()));
			return false;
		}

		// Load the members plugin group.
		JPluginHelper::importPlugin('user');

		// Store the data.
		if (!$user->save()) {
			$this->setError($user->getError());
			return false;
		}

		return $user->id;
	}
}
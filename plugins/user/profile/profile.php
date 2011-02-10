<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * An example custom profile plugin.
 *
 * @package		Joomla.Plugins
 * @subpackage	user.profile
 * @version		1.6
 */
class plgUserProfile extends JPlugin
{
	/**
	 * @param	string	$context	The context for the data
	 * @param	int		$data		The user id
	 * @param	object
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile','com_users.user', 'com_users.registration', 'com_admin.profile'))) {
			return true;
		}

		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			// Load the profile data from the database.
			$db = JFactory::getDbo();
			$db->setQuery(
				'SELECT profile_key, profile_value FROM #__user_profiles' .
				' WHERE user_id = '.(int) $userId." AND profile_key LIKE 'profile.%'" .
				' ORDER BY ordering'
			);
			$results = $db->loadRowList();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				$this->_subject->setError($db->getErrorMsg());
				return false;
			}

			// Merge the profile data.
			$data->profile = array();

			foreach ($results as $v)
			{
				$k = str_replace('profile.', '', $v[0]);
				$data->profile[$k] = $v[1];
			}
			if (!JHtml::isRegistered('users.url')) {
				JHtml::register('users.url', array(__CLASS__, 'url'));
			}
			if (!JHtml::isRegistered('users.calendar')) {
				JHtml::register('users.calendar', array(__CLASS__, 'calendar'));
			}
			if (!JHtml::isRegistered('users.tos')) {
				JHtml::register('users.tos', array(__CLASS__, 'tos'));
			}
		}

		return true;
	}

	public static function url($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			$value = htmlspecialchars($value);
			if(substr ($value, 0, 4) == "http") {
				return '<a href="'.$value.'">'.$value.'</a>';
			}
			else {
				return '<a href="http://'.$value.'">'.$value.'</a>';
			}
		}
	}

	public static function calendar($value)
	{
		if (empty($value)) {
			return JHtml::_('users.value', $value);
		} else {
			return JHtml::_('date', $value);
		}
	}

	public static function tos($value)
	{
		if ($value) {
			return JText::_('JYES');
		}
		else {
			return JText::_('JNO');
		}
	}

	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareForm($form, $data)
	{
		// Load user_profile plugin language
		$lang = JFactory::getLanguage();
		$lang->load('plg_user_profile', JPATH_ADMINISTRATOR);

		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		// Check we are manipulating a valid form.
		if (!in_array($form->getName(), array('com_admin.profile','com_users.user', 'com_users.registration','com_users.profile'))) {
			return true;
		}

		// Add the registration fields to the form.
		JForm::addFormPath(dirname(__FILE__).'/profiles');
		$form->loadFile('profile', false);

		// Toggle whether the address1 field is required.
		if ($this->params->get('register-require_address1', 1) > 0) {
			$form->setFieldAttribute('address1', 'required', $this->params->get('register-require_address1') == 2, 'profile');
		}
		else {
			$form->removeField('address1', 'profile');
		}

		// Toggle whether the address2 field is required.
		if ($this->params->get('register-require_address2', 1) > 0) {
			$form->setFieldAttribute('address2', 'required', $this->params->get('register-require_address2') == 2, 'profile');
		}
		else {
			$form->removeField('address2', 'profile');
		}

		// Toggle whether the city field is required.
		if ($this->params->get('register-require_city', 1) > 0) {
			$form->setFieldAttribute('city', 'required', $this->params->get('register-require_city') == 2, 'profile');
		}
		else {
			$form->removeField('city', 'profile');
		}

		// Toggle whether the region field is required.
		if ($this->params->get('register-require_region', 1) > 0) {
			$form->setFieldAttribute('region', 'required', $this->params->get('register-require_region') == 2, 'profile');
		}
		else {
			$form->removeField('region', 'profile');
		}

		// Toggle whether the country field is required.
		if ($this->params->get('register-require_country', 1) > 0) {
			$form->setFieldAttribute('country', 'required', $this->params->get('register-require_country') == 2, 'profile');
		}
		else {
			$form->removeField('country', 'profile');
		}

		// Toggle whether the postal code field is required.
		if ($this->params->get('register-require_postal_code', 1) > 0) {
			$form->setFieldAttribute('postal_code', 'required', $this->params->get('register-require_postal_code') == 2, 'profile');
		}
		else {
			$form->removeField('postal_code', 'profile');
		}

		// Toggle whether the phone field is required.
		if ($this->params->get('register-require_phone', 1) > 0) {
			$form->setFieldAttribute('phone', 'required', $this->params->get('register-require_phone') == 2, 'profile');
		}
		else {
			$form->removeField('phone', 'profile');
		}

		// Toggle whether the website field is required.
		if ($this->params->get('register-require_website', 1) > 0) {
			$form->setFieldAttribute('website', 'required', $this->params->get('register-require_website') == 2, 'profile');
		}
		else {
			$form->removeField('website', 'profile');
		}

		// Toggle whether the favoritebook field is required.
		if ($this->params->get('register-require_favoritebook', 1) > 0) {
			$form->setFieldAttribute('favoritebook', 'required', $this->params->get('register-require_favoritebook') == 2, 'profile');
		}
		else {
			$form->removeField('favoritebook', 'profile');
		}

		// Toggle whether the aboutme field is required.
		if ($this->params->get('register-require_aboutme', 1) > 0) {
			$form->setFieldAttribute('aboutme', 'required', $this->params->get('register-require_aboutme') == 2, 'profile');
		}
		else {
			$form->removeField('aboutme', 'profile');
		}

		// Toggle whether the tos field is required.
		if ($this->params->get('register-require_tos', 1) > 0) {
			$form->setFieldAttribute('tos', 'required', $this->params->get('register-require_tos') == 2, 'profile');
		}
		else {
			$form->removeField('tos', 'profile');
		}

		// Toggle whether the dob field is required.
		if ($this->params->get('register-require_dob', 1) > 0) {
			$form->setFieldAttribute('dob', 'required', $this->params->get('register-require_dob') == 2, 'profile');
		}
		else {
			$form->removeField('dob', 'profile');
		}

		return true;
	}

	function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId	= JArrayHelper::getValue($data, 'id', 0, 'int');

		if ($userId && $result && isset($data['profile']) && (count($data['profile'])))
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = '.$userId .
					" AND profile_key LIKE 'profile.%'"
				);

				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}

				$tuples = array();
				$order	= 1;

				foreach ($data['profile'] as $k => $v)
				{
					$tuples[] = '('.$userId.', '.$db->quote('profile.'.$k).', '.$db->quote($v).', '.$order++.')';
				}

				$db->setQuery('INSERT INTO #__user_profiles VALUES '.implode(', ', $tuples));

				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}

			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}

	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user		Holds the user data
	 * @param	boolean		$success	True if user was succesfully stored in the database
	 * @param	string		$msg		Message
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success) {
			return false;
		}

		$userId	= JArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = '.$userId .
					" AND profile_key LIKE 'profile.%'"
				);

				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}
}

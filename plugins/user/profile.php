<?php
/**
* @version		$Id$
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

defined('JPATH_BASE') or die('Restricted Access');

$lang = &JFactory::getLanguage();
$lang->load('plg_user_profile');

/**
 * An exmample custom profile plugin.
 *
 * @package		Joomla.Plugins
 * @subpackage	user.profile
 * @version		1.6
 */
class plgUserProfile extends JPlugin
{
	function onPrepareUserProfileForm($userId, &$form)
	{
		// Add the profile fields to the form.
		JForm::addFormPath(dirname(__FILE__).DS.'profile');
		$form->load('profile', true, false);

		// Toggle whether the address1 field is required.
		if ($this->params->get('profile-require_address1', 1) > 0) {
			$form->setFieldAttribute('address1', 'required', $this->params->get('profile-require_address1') == 2, 'profile');
		} else {
			$form->removeField('address1', 'profile');
		}

		// Toggle whether the address2 field is required.
		if ($this->params->get('profile-require_address2', 1) > 0) {
			$form->setFieldAttribute('address2', 'required', $this->params->get('profile-require_address2') == 2, 'profile');
		} else {
			$form->removeField('address2', 'profile');
		}

		// Toggle whether the city field is required.
		if ($this->params->get('profile-require_city', 1) > 0) {
			$form->setFieldAttribute('city', 'required', $this->params->get('profile-require_address1') == 2, 'profile');
		} else {
			$form->removeField('city', 'profile');
		}

		// Toggle whether the region field is required.
		if ($this->params->get('profile-require_region', 1) > 0) {
			$form->setFieldAttribute('region', 'required', $this->params->get('profile-require_address1') == 2, 'profile');
		} else {
			$form->removeField('region', 'profile');
		}

		// Toggle whether the country field is required.
		if ($this->params->get('profile-require_country', 1) > 0) {
			$form->setFieldAttribute('country', 'required', $this->params->get('profile-require_address1') == 2, 'profile');
		} else {
			$form->removeField('country', 'profile');
		}

		// Toggle whether the postal code field is required.
		if ($this->params->get('profile-require_postal_code', 1) > 0) {
			$form->setFieldAttribute('postal_code', 'required', $this->params->get('profile-require_address1') == 2, 'profile');
		} else {
			$form->removeField('postal_code', 'profile');
		}

		// Toggle whether the phone field is required.
		if ($this->params->get('profile-require_phone', 1) > 0) {
			$form->setFieldAttribute('phone', 'required', $this->params->get('profile-require_address1') == 2, 'profile');
		} else {
			$form->removeField('phone', 'profile');
		}

		// Toggle whether the website field is required.
		if ($this->params->get('profile-require_website', 1) > 0) {
			$form->setFieldAttribute('website', 'required', $this->params->get('profile-require_address1') == 2, 'profile');
		} else {
			$form->removeField('website', 'profile');
		}

		return true;
	}

	function onPrepareUserProfileData($userId, &$data)
	{
		// Load the profile data from the database.
		$db = &JFactory::getDBO();
		$db->setQuery(
			'SELECT profile_key, profile_value FROM #__user_profiles' .
			' WHERE user_id = '.(int)$userId .
			' ORDER BY ordering'
		);
		$results = $db->loadRowList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->_subject->setError($db->getErrorMsg());
			return false;
		}

		// Merge the profile data.
		foreach ($results as $v) {
			$k = str_replace('profile.', '', $v[0]);
			$data->profile->$k = $v[1];
		}

		return true;
	}

	function onPrepareUserProfile($userId, &$data)
	{
		// Load the profile data from the database.
		$db = &JFactory::getDBO();
		$db->setQuery(
			'SELECT profile_key, profile_data FROM #__user_profiles' .
			' WHERE user_id = '.(int)$userId .
			' ORDER BY ordering'
		);
		$results = $db->loadAssoc('profile_key');

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->_subject->setError($db->getErrorMsg());
			return false;
		}

		// Push in the profile data to display.
		$data['location']	= $results['city'].', '.$results['region'].', '.$results['country'];
		$data['website']	= $results['website'];

		return true;
	}

	function onAfterStoreUser($data, $isNew, $result, $error)
	{
		$userId	= JArrayHelper::getValue($data, 'id', 0, 'int');

		if ($userId && $result && isset($data['profile']) && (count($data['profile'])))
		{
			try
			{
				$db = &JFactory::getDBO();
				$db->setQuery('DELETE FROM #__user_profiles WHERE user_id = '.$userId);
				$db->query();

				$tuples = array();
				$order	= 1;
				foreach ($data['profile'] as $k => $v) {
					$tuples[] = '('.$userId.', '.$db->quote('profile.'.$k).', '.$db->quote($v).', '.$order++.')';
				}

				$db->setQuery('INSERT INTO #__user_profile VALUES '.implode(', ', $tuples));
				$db->query();
			}
			catch (JException $e) {
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}
}
<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  11.1
 */
class JFormRuleUsername extends JFormRule
{
	/**
	 * Method to test the username for uniqueness, minimum size, maximum size, and character set compliant.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		// Default value
		$result = true;
		
		// Get the database object and a new query object.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Build the query.
		$query->select('COUNT(*)')
			->from('#__users')
			->where('username = ' . $db->quote($value));

		// Get the extra field check attribute.
		$userId = ($form instanceof JForm) ? $form->getValue('id') : '';
		$query->where($db->quoteName('id') . ' <> ' . (int) $userId);

		// Set and query the database.
		$db->setQuery($query);
		$duplicate = (bool) $db->loadResult();

		if ($duplicate)
		{
			return false;
		}
		
		// Get the config params for username
		$params = JComponentHelper::getParams('com_users');

		// CHECK MINIMUM CHARACTER'S NUMBER
		
		// Get the number of characters in $username
		$usernameLenght = StringHelper::strlen($value);
		
		// Get the minimum number of characters
		$minNumChars = $params->get('minimum_length_username');
		
		// If is set minNumChars and $usernameLenght does't achieve minimum lenght
		if (($minNumChars) && ($usernameLenght < $minNumChars)){
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_MINNUMCHARS_REQUIRED', $minNumChars, $value), 'warning');
			$result = false;
		}
		
		// CHECK MAXIMUM CHARACTER'S NUMBER
		
		// Get the minimum number of characters
		$maxNumChars = $params->get('maximum_length_username');
		
		// If is set maxNumChars and $usernameLenght surpass maximum lenght
		if (($maxNumChars) && ($usernameLenght > $maxNumChars)){
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_MAXNUMCHARS_REQUIRED', $maxNumChars, $value), 'warning');
			$result = false;
		}
		
		// CHECK IF USERNAME SPELLING IN ALLOWED CHARACTER SET
		
		// Get preset option
		$allowed_preset = $params->get('allowed_chars_username_preset');
		
		if ($allowed_preset) {
			
			// Get the username
			$uname = array_unique(StringHelper::str_split($value));
			
			switch($allowed_preset) {
				case 1: 
					// Get the charset specified in the CUSTOM parameter
					$allowedCharsUsername = array_unique(StringHelper::str_split($params->get('allowed_chars_username')));
					break;
				case 2:
					// ALPHANUMERIC
					if (!ctype_alnum($value)) {
						// Enqueue error message and return false
						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_ALPHANUMERIC_REQUIRED'), 'warning');
						return false;
					}
				case 3:
					// EMAIL
					jimport('joomla.mail.helper');
					if ( ! JMailHelper::isEmailAddress($value) ) {
						// Enqueue error message and return false
						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_EMAIL_REQUIRED', implode(' ', $invalid_chars)), 'warning');
						return false;
					}
				default:
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_NOOPTION'), 'warning');
					return false;
			}

			// Get the valid chars
			$invalid_chars = array_diff($uname, $allowedCharsUsername);
			
			// Check if all the $uname chars are valid chars
			if (!empty($invalid_chars)) {
				JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_CHARSET_REQUIRED', implode(' ', $invalid_chars)), 'warning');
				$result = false;
			}
		}

		return $result;
	}
}

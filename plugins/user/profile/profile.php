<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * An example custom profile plugin.
 *
 * @since  1.6
 */
class PlgUserProfile extends JPlugin
{
	/**
	 * Date of birth.
	 *
	 * @var    string
	 * @since  3.1
	 */
	private $date = '';

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since   1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		JFormHelper::addFieldPath(__DIR__ . '/field');
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string  $context  The context for the data
	 * @param   object  $data     An object containing the data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile')))
		{
			return true;
		}

		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->profile) and $userId > 0)
			{
				// Load the profile data from the database.
				$db = JFactory::getDbo();
				$db->setQuery(
					'SELECT profile_key, profile_value FROM #__user_profiles'
						. ' WHERE user_id = ' . (int) $userId . " AND profile_key LIKE 'profile.%'"
						. ' ORDER BY ordering'
				);

				try
				{
					$results = $db->loadRowList();
				}
				catch (RuntimeException $e)
				{
					$this->_subject->setError($e->getMessage());

					return false;
				}

				// Merge the profile data.
				$data->profile = array();

				foreach ($results as $v)
				{
					$k = str_replace('profile.', '', $v[0]);
					$data->profile[$k] = json_decode($v[1], true);

					if ($data->profile[$k] === null)
					{
						$data->profile[$k] = $v[1];
					}
				}
			}

			if (!JHtml::isRegistered('users.url'))
			{
				JHtml::register('users.url', array(__CLASS__, 'url'));
			}

			if (!JHtml::isRegistered('users.calendar'))
			{
				JHtml::register('users.calendar', array(__CLASS__, 'calendar'));
			}

			if (!JHtml::isRegistered('users.tos'))
			{
				JHtml::register('users.tos', array(__CLASS__, 'tos'));
			}

			if (!JHtml::isRegistered('users.dob'))
			{
				JHtml::register('users.dob', array(__CLASS__, 'dob'));
			}
		}

		return true;
	}

	/**
	 * Returns an anchor tag generated from a given value
	 *
	 * @param   string  $value  url to use
	 *
	 * @return mixed|string
	 */
	public static function url($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			// Convert website url to utf8 for display
			$value = JStringPunycode::urlToUTF8(htmlspecialchars($value));

			if (substr($value, 0, 4) == "http")
			{
				return '<a href="' . $value . '">' . $value . '</a>';
			}
			else
			{
				return '<a href="http://' . $value . '">' . $value . '</a>';
			}
		}
	}

	/**
	 * Returns html markup showing a date picker
	 *
	 * @param   string  $value  valid date string
	 *
	 * @return  mixed
	 */
	public static function calendar($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			return JHtml::_('date', $value, null, null);
		}
	}

	/**
	 * Returns the date of birth formatted and calculated using server timezone.
	 *
	 * @param   string  $value  valid date string
	 *
	 * @return  mixed
	 */
	public static function dob($value)
	{
		if (!$value)
		{
			return '';
		}

		return JHtml::_('date', $value, JText::_('DATE_FORMAT_LC1'), false);
	}

	/**
	 * Return the translated strings yes or no depending on the value
	 *
	 * @param   boolean  $value  input value
	 *
	 * @return string
	 */
	public static function tos($value)
	{
		if ($value)
		{
			return JText::_('JYES');
		}
		else
		{
			return JText::_('JNO');
		}
	}

	/**
	 * Adds additional fields to the user editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();

		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration')))
		{
			return true;
		}

		// Add the registration fields to the form.
		JForm::addFormPath(__DIR__ . '/profiles');
		$form->loadFile('profile', false);

		$fields = array(
			'address1',
			'address2',
			'city',
			'region',
			'country',
			'postal_code',
			'phone',
			'website',
			'favoritebook',
			'aboutme',
			'dob',
			'tos',
		);

		// Change fields description when displayed in frontend or backend profile editing
		$app = JFactory::getApplication();

		if ($app->isSite() || $name == 'com_users.user' || $name == 'com_admin.profile')
		{
			$form->setFieldAttribute('address1', 'description', 'PLG_USER_PROFILE_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('address2', 'description', 'PLG_USER_PROFILE_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('city', 'description', 'PLG_USER_PROFILE_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('region', 'description', 'PLG_USER_PROFILE_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('country', 'description', 'PLG_USER_PROFILE_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('postal_code', 'description', 'PLG_USER_PROFILE_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('phone', 'description', 'PLG_USER_PROFILE_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('website', 'description', 'PLG_USER_PROFILE_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('favoritebook', 'description', 'PLG_USER_PROFILE_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('aboutme', 'description', 'PLG_USER_PROFILE_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('dob', 'description', 'PLG_USER_PROFILE_FILL_FIELD_DESC_SITE', 'profile');
			$form->setFieldAttribute('tos', 'description', 'PLG_USER_PROFILE_FIELD_TOS_DESC_SITE', 'profile');
		}

		$tosarticle = $this->params->get('register_tos_article');
		$tosenabled = $this->params->get('register-require_tos', 0);

		// We need to be in the registration form and field needs to be enabled
		if ($name != 'com_users.registration' || !$tosenabled)
		{
			// We only want the TOS in the registration form
			$form->removeField('tos', 'profile');
		}
		else
		{
			// Push the TOS article ID into the TOS field.
			$form->setFieldAttribute('tos', 'article', $tosarticle, 'profile');
		}

		foreach ($fields as $field)
		{
			// Case using the users manager in admin
			if ($name == 'com_users.user')
			{
				// Remove the field if it is disabled in registration and profile
				if ($this->params->get('register-require_' . $field, 1) == 0
					&& $this->params->get('profile-require_' . $field, 1) == 0)
				{
					$form->removeField($field, 'profile');
				}
			}
			// Case registration
			elseif ($name == 'com_users.registration')
			{
				// Toggle whether the field is required.
				if ($this->params->get('register-require_' . $field, 1) > 0)
				{
					$form->setFieldAttribute($field, 'required', ($this->params->get('register-require_' . $field) == 2) ? 'required' : '', 'profile');
				}
				else
				{
					$form->removeField($field, 'profile');
				}
			}
			// Case profile in site or admin
			elseif ($name == 'com_users.profile' || $name == 'com_admin.profile')
			{
				// Toggle whether the field is required.
				if ($this->params->get('profile-require_' . $field, 1) > 0)
				{
					$form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'profile');
				}
				else
				{
					$form->removeField($field, 'profile');
				}
			}
		}

		return true;
	}

	/**
	 * Method is called before user data is stored in the database
	 *
	 * @param   array    $user   Holds the old user data.
	 * @param   boolean  $isnew  True if a new user is stored.
	 * @param   array    $data   Holds the new user data.
	 *
	 * @return    boolean
	 *
	 * @since   3.1
	 * @throws    InvalidArgumentException on invalid date.
	 */
	public function onUserBeforeSave($user, $isnew, $data)
	{
		// Check that the date is valid.
		if (!empty($data['profile']['dob']))
		{
			try
			{
				$date = new JDate($data['profile']['dob']);
				$this->date = $date->format('Y-m-d H:i:s');
			}
			catch (Exception $e)
			{
				// Throw an exception if date is not valid.
				throw new InvalidArgumentException(JText::_('PLG_USER_PROFILE_ERROR_INVALID_DOB'));
			}
			if (JDate::getInstance('now') < $date)
			{
				// Throw an exception if dob is greather than now.
				throw new InvalidArgumentException(JText::_('PLG_USER_PROFILE_ERROR_INVALID_DOB'));
			}
		}
		// Check that the tos is checked if required ie only in registration from frontend.
		$task       = JFactory::getApplication()->input->getCmd('task');
		$option     = JFactory::getApplication()->input->getCmd('option');
		$tosarticle = $this->params->get('register_tos_article');
		$tosenabled = ($this->params->get('register-require_tos', 0) == 2);

		if (($task == 'register') && ($tosenabled) && ($tosarticle) && ($option == 'com_users'))
		{
			// Check that the tos is checked.
			if ((!($data['profile']['tos'])))
			{
				throw new InvalidArgumentException(JText::_('PLG_USER_PROFILE_FIELD_TOS_DESC_SITE'));
			}
		}

		return true;
	}

	/**
	 * Saves user profile data
	 *
	 * @param   array    $data    entered user data
	 * @param   boolean  $isNew   true if this is a new user
	 * @param   boolean  $result  true if saving the user worked
	 * @param   string   $error   error message
	 *
	 * @return bool
	 */
	public function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId = ArrayHelper::getValue($data, 'id', 0, 'int');

		if ($userId && $result && isset($data['profile']) && (count($data['profile'])))
		{
			try
			{
				// Sanitize the date
				$data['profile']['dob'] = $this->date;

				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__user_profiles'))
					->where($db->quoteName('user_id') . ' = ' . (int) $userId)
					->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('profile.%'));
				$db->setQuery($query);
				$db->execute();

				$tuples = array();
				$order = 1;

				foreach ($data['profile'] as $k => $v)
				{
					$tuples[] = '(' . $userId . ', ' . $db->quote('profile.' . $k) . ', ' . $db->quote(json_encode($v)) . ', ' . ($order++) . ')';
				}

				$db->setQuery('INSERT INTO #__user_profiles VALUES ' . implode(', ', $tuples));
				$db->execute();
			}
			catch (RuntimeException $e)
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
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was succesfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  boolean
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId = ArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = ' . $userId
						. " AND profile_key LIKE 'profile.%'"
				);

				$db->execute();
			}
			catch (Exception $e)
			{
				$this->_subject->setError($e->getMessage());

				return false;
			}
		}

		return true;
	}
}

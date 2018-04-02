<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.privacyconsent
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * An example custom privacyconsent plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgUserPrivacyconsent extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  1.0
	 */
	protected $app;
	
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		JFormHelper::addFieldPath(__DIR__ . '/field');
	}

	/**
	 * Adds additional fields to the user editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		// Check we are manipulating a valid form - we do not display this in the admin users form.
		$name = $form->getName();

		if (!in_array($name, array('com_admin.profile', 'com_users.profile', 'com_users.registration')))
		{
			return true;
		}

		// Add the registration fields to the form.
		JForm::addFormPath(__DIR__ . '/privacyconsent');
		$form->loadFile('privacyconsent');

		$fields = array(
			'privacy',
		);

		$privacyarticle = $this->params->get('privacy_article');

		// Push the privacy article ID into the privacy field.
		$form->setFieldAttribute('privacy', 'article', $privacyarticle, 'privacyconsent');
	}

	/**
	 * Method is called before user data is stored in the database
	 *
	 * @param   array    $user   Holds the old user data.
	 * @param   boolean  $isNew  True if a new user is stored.
	 * @param   array    $data   Holds the new user data.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  InvalidArgumentException on missing required data.
	 */
	public function onUserBeforeSave($user, $isNew, $data)
	{
		// Check that the privacy is checked if required ie only in registration from frontend.
		$task       	= JFactory::getApplication()->input->getCmd('task');
		$option     	= JFactory::getApplication()->input->getCmd('option');
		$privacyarticle = $this->params->get('privacy_article');

		if ($this->app->isClient('site') && (!$data['privacyconsent']['privacy']))
		{
			throw new InvalidArgumentException(JText::_('PLG_USER_PRIVACY_FIELD_ERROR'));
		}

		return true;
	}

	/**
	 * Saves user privacy confirmation and note
	 *
	 * @param   array    $data    entered user data
	 * @param   boolean  $isNew   true if this is a new user
	 * @param   boolean  $result  true if saving the user worked
	 * @param   string   $error   error message
	 *
	 * @return  boolean
	 */
	public function onUserAfterSave($data, $isNew, $result, $error)
	{
		// Only create an entry on front-end user creation/update
		if ($this->app->isClient('administrator'))
		{
			return;
		}

		// Get the user's ID
		$userId = ArrayHelper::getValue($data, 'id', 0, 'int');

		// Get the user's IP address
		$ip = $this->app->input->server->get('REMOTE_ADDR');

		// Get the user agent string
		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		// Get the date in DB format
		$now = JFactory::getDate()->toSql();

		// Create the user note
		$userNote = (object) array(
			'user_id'         => $userId,
			'catid'           => 0,
			'subject'         => JText::_('PLG_USER_PRIVACY_SUBJECT'),
			'body'            => JText::sprintf('PLG_USER_PRIVACY_BODY', $ip, $user_agent),
			'state'           => 1,
			'created_user_id' => 42,
			'created_time'    => $now
		);

		try
		{
			$result = JFactory::getDbo()->insertObject('#__user_notes', $userNote);
		}
		catch (Exception $e)
		{
			// Do nothing if the save fails
		}

		// Create the consent confirmation
		$confirm = (object) array(
			'user_id'		=> $userId,
			'profile_key'	=> 'consent',
			'profile_value'	=> 1
		);

		try
		{
			$result = JFactory::getDbo()->insertObject('#__user_profiles', $confirm);
		}
		catch (Exception $e)

		{
			// Do nothing if the save fails
		}
		return true;
	}

	/**
	 * Remove all user privacy consent information for the given user ID
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
			// Remove any user notes
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_notes WHERE user_id = ' . $userId
				);

				$db->execute();
			}
			catch (Exception $e)
			{
				$this->_subject->setError($e->getMessage());

				return false;
			}

			// Remove any user profile fields
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = ' . $userId
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

	/**
	 * Check if a user has already consented when they login.
	 * If not will load the edit profile
	 *
	 * @param   array  $options  Array holding options
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 */
	public function onUserAfterLogin($options)
	{
		// Run this in frontend only
		if ($this->app->isClient('administrator'))
		{
			return;
		}

		$userId = JFactory::getUser()->id;
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('1')
			->from($db->qn('#__user_profiles'))
			->where($db->qn('user_id') . ' = ' . (int) $userId)
			->where($db->qn('profile_key') . ' = ' . $db->q('consent'));
		$db->setQuery($query);

		$consent = $db->loadObjectList();

		if (count($consent) != 0)
		{
			return;
		}

		// If the count of $consent is 0 then redirect to com_users profile edit
		$this->app->enqueueMessage($this->getRedirectMessage(), 'notice');
		$this->app->redirect(\JRoute::_('index.php?option=com_users&view=profile&layout=edit', false));
	}

	/**
	 * Returns the configured redirect message and falls back to the default version.
	 *
	 * @return  string  redirect message
	 *
	 * @since   1.0
	 */

	 private function getRedirectMessage()
	{
		$messageOnRedirect = trim($this->params->get('messageOnRedirect', ''));
		if (empty($messageOnRedirect))
		{
			return \JText::_('PLG_USER_PRIVACY_REDIRECT_MESSAGE_DEFAULT');
		}

		return $messageOnRedirect;
	}
}

<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.privacyconsent
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * An example custom privacyconsent plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemPrivacyconsent extends JPlugin
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
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(&$subject, $config)
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

		// Check we are manipulating a valid form - we only display this on user registration form and user profile form.
		$name = $form->getName();

		if (!in_array($name, array('com_users.profile', 'com_users.registration')))
		{
			return true;
		}

		// We only display this if user has not consented before
		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if ($userId > 0 && $this->isUserConsented($userId))
			{
				return true;
			}
		}

		// Add the privacy policy fields to the form.
		JForm::addFormPath(__DIR__ . '/privacyconsent');
		$form->loadFile('privacyconsent');

		$privacyarticle = $this->params->get('privacy_article');
		$privacynote    = $this->params->get('privacy_note');

		// Push the privacy article ID into the privacy field.
		$form->setFieldAttribute('privacy', 'article', $privacyarticle, 'privacyconsent');
		$form->setFieldAttribute('privacy', 'note', $privacynote, 'privacyconsent');
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
		// // Only check for front-end user creation/update profile
		if ($this->app->isClient('administrator'))
		{
			return true;
		}

		$userId = ArrayHelper::getValue($user, 'id', 0, 'int');

		// User already consented before, no need to check it further
		if ($userId > 0 && $this->isUserConsented($userId))
		{
			return true;
		}

		// Check that the privacy is checked if required ie only in registration from frontend.
		$option = $this->app->input->getCmd('option');
		$task   = $this->app->input->get->getCmd('task');
		$form   = $this->app->input->post->get('jform', array(), 'array');

		if ($option == 'com_users' && in_array($task, array('registration.register', 'profile.save'))
			&& empty($form['privacyconsent']['privacy']))
		{
			throw new InvalidArgumentException(Text::_('PLG_SYSTEM_PRIVACYCONSENT_FIELD_ERROR'));
		}

		return true;
	}

	/**
	 * Saves user privacy confirmation
	 *
	 * @param   array    $data    entered user data
	 * @param   boolean  $isNew   true if this is a new user
	 * @param   boolean  $result  true if saving the user worked
	 * @param   string   $error   error message
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserAfterSave($data, $isNew, $result, $error)
	{
		// Only create an entry on front-end user creation/update profile
		if ($this->app->isClient('administrator'))
		{
			return true;
		}

		// Get the user's ID
		$userId = ArrayHelper::getValue($data, 'id', 0, 'int');

		// If user already consented before, no need to check it further
		if ($userId > 0 && $this->isUserConsented($userId))
		{
			return true;
		}

		$option = $this->app->input->getCmd('option');
		$task   = $this->app->input->get->getCmd('task');
		$form   = $this->app->input->post->get('jform', array(), 'array');

		if ($option == 'com_users'
			&&in_array($task, array('registration.register', 'profile.save'))
			&& !empty($form['privacyconsent']['privacy']))
		{

			$userId = ArrayHelper::getValue($data, 'id', 0, 'int');

			// Get the user's IP address
			$ip = $this->app->input->server->get('REMOTE_ADDR', '', 'string');

			// Get the user agent string
			$userAgent = $this->app->input->server->get('HTTP_USER_AGENT', '', 'string');

			// Create the user note
			$userNote = (object) array(
				'user_id' => $userId,
				'subject' => Text::_('PLG_SYSTEM_PRIVACYCONSENT_SUBJECT'),
				'body'    => Text::sprintf('PLG_SYSTEM_PRIVACYCONSENT_BODY', $ip, $userAgent),
				'created' => Factory::getDate()->toSql(),
			);

			try
			{
				$this->db->insertObject('#__privacy_consent', $userNote);
			}
			catch (Exception $e)
			{
				// Do nothing if the save fails
			}
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
	 *
	 * @since   __DEPLOY_VERSION__
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
			// Remove user's consent
			try
			{
				$query = $this->db->getQuery(true)
					->delete($this->db->quoteName('#__privacy_consent'))
					->where($this->db->quoteName('user_id') . ' = ' . (int) $userId);
				$this->db->setQuery($query);
				$this->db->execute();
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
	 * If logged in users haven't agreed to privacy consent, redirect them to profile edit page, ask them to agree to
	 * privacy consent before allowing access to any other pages
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterRoute()
	{
		// Run this in frontend only
		if ($this->app->isClient('administrator'))
		{
			return;
		}

		$userId = Factory::getUser()->id;

		// Check to see whether user already consented, if not, redirect to user profile page
		if ($userId > 0)
		{
			// If user consented before, no need to check it further
			if ($this->isUserConsented($userId))
			{
				return;
			}

			$option = $this->app->input->getCmd('option');
			$task   = $this->app->input->get('task');
			$view   = $this->app->input->getString('view', '');
			$layout = $this->app->input->getString('layout', '');

			// If user is already on edit profile screen or press update button, do nothing to avoid infinite redirect
			if ($task == 'profile.save' || ($option == 'com_users' && $view == 'profile' && $layout == 'edit'))
			{
				return;
			}

			// Redirect to com_users profile edit
			$this->app->enqueueMessage($this->getRedirectMessage(), 'notice');
			$link = 'index.php?option=com_users&view=profile&layout=edit';
			$this->app->redirect(\JRoute::_($link, false));
		}
	}

	/**
	 * Returns the configured redirect message and falls back to the default version.
	 *
	 * @return  string  redirect message
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getRedirectMessage()
	{
		$messageOnRedirect = trim($this->params->get('messageOnRedirect', ''));

		if (empty($messageOnRedirect))
		{
			return Text::_('PLG_SYSTEM_PRIVACYCONSENT_REDIRECT_MESSAGE_DEFAULT');
		}

		return $messageOnRedirect;
	}

	/**
	 * Method to check if the given user has consented yet
	 *
	 * @param   int  $userId  ID of uer to check
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function isUserConsented($userId)
	{
		$query = $this->db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__privacy_consent')
			->where('user_id = ' . (int) $userId);
		$this->db->setQuery($query);

		return (int) $this->db->loadResult() > 0 ? true: false;
	}
}
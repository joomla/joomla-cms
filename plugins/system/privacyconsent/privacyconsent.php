<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.privacyconsent
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

/**
 * An example custom privacyconsent plugin.
 *
 * @since  3.9.0
 */
class PlgSystemPrivacyconsent extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.9.0
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since   3.9.0
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
	 * @since   3.9.0
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

		$privacyArticleId = $this->getPrivacyArticleId();
		$privacynote      = $this->params->get('privacy_note');

		// Push the privacy article ID into the privacy field.
		$form->setFieldAttribute('privacy', 'article', $privacyArticleId, 'privacyconsent');
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
	 * @since   3.9.0
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
	 * @since   3.9.0
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
				'subject' => 'PLG_SYSTEM_PRIVACYCONSENT_SUBJECT',
				'body'    => Text::sprintf('PLG_SYSTEM_PRIVACYCONSENT_BODY', $ip, $userAgent),
				'created' => Factory::getDate()->toSql(),
			);

			try
			{
				$this->db->insertObject('#__privacy_consents', $userNote);
			}
			catch (Exception $e)
			{
				// Do nothing if the save fails
			}

			$userId = ArrayHelper::getValue($data, 'id', 0, 'int');

			$message = array(
				'action'      => 'consent',
				'id'          => $userId,
				'title'       => $data['name'],
				'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $userId,
				'userid'      => $userId,
				'username'    => $data['username'],
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			);

			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

			/* @var ActionlogsModelActionlog $model */
			$model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
			$model->addLog(array($message), 'PLG_SYSTEM_PRIVACYCONSENT_CONSENT', 'plg_system_privacyconsent', $userId);
		}

		return true;
	}

	/**
	 * Remove all user privacy consent information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was successfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
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
					->delete($this->db->quoteName('#__privacy_consents'))
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
	 * @since   3.9.0
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
			$id     = $this->app->input->getInt('id');

			$privacyArticleId = $this->getPrivacyArticleId();

			/*
			 * If user is already on edit profile screen or view privacy article
			 * or press update/apply button, or logout, do nothing to avoid infinite redirect
			 */
			if ($option == 'com_users' && in_array($task, array('profile.save', 'profile.apply', 'user.logout', 'user.menulogout'))
				|| ($option == 'com_content' && $view == 'article' && $id == $privacyArticleId)
				|| ($option == 'com_users' && $view == 'profile' && $layout == 'edit'))
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
	 * Event to specify whether a privacy policy has been published.
	 *
	 * @param   array  &$policy  The privacy policy status data, passed by reference, with keys "published", "editLink" and "articlePublished".
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function onPrivacyCheckPrivacyPolicyPublished(&$policy)
	{
		// If another plugin has already indicated a policy is published, we won't change anything here
		if ($policy['published'])
		{
			return;
		}

		$articleId = $this->params->get('privacy_article');

		if (!$articleId)
		{
			return;
		}

		// Check if the article exists in database and is published
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'state')))
			->from($this->db->quoteName('#__content'))
			->where($this->db->quoteName('id') . ' = ' . (int) $articleId);
		$this->db->setQuery($query);

		$article = $this->db->loadObject();

		// Check if the article exists
		if (!$article)
		{
			return;
		}

		// Check if the article is published
		if ($article->state == 1)
		{
			$policy['articlePublished'] = true;
		}

		$policy['published'] = true;
		$policy['editLink']  = JRoute::_('index.php?option=com_content&task=article.edit&id=' . $articleId);
	}

	/**
	 * Returns the configured redirect message and falls back to the default version.
	 *
	 * @return  string  redirect message
	 *
	 * @since   3.9.0
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
	 * @param   integer  $userId  ID of uer to check
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	private function isUserConsented($userId)
	{
		$query = $this->db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__privacy_consents')
			->where('user_id = ' . (int) $userId)
			->where('subject = ' . $this->db->quote('PLG_SYSTEM_PRIVACYCONSENT_SUBJECT'))
			->where('state = 1');
		$this->db->setQuery($query);

		return (int) $this->db->loadResult() > 0;
	}

	/**
	 * Get privacy article ID. If the site is a multilingual website and there is associated article for the
	 * current language, ID of the associated article will be returned
	 *
	 * @return  integer
	 *
	 * @since   3.9.0
	 */
	private function getPrivacyArticleId()
	{
		$privacyArticleId = $this->params->get('privacy_article');

		if ($privacyArticleId > 0 && JLanguageAssociations::isEnabled())
		{
			$privacyAssociated = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $privacyArticleId);
			$currentLang = JFactory::getLanguage()->getTag();

			if (isset($privacyAssociated[$currentLang]))
			{
				$privacyArticleId = $privacyAssociated[$currentLang]->id;
			}
		}

		return $privacyArticleId;
	}

	/**
	 * The privacy consent expiration check code is triggered after the page has fully rendered.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function onAfterRender()
	{
		if (!$this->params->get('enabled', 0))
		{
			return;
		}

		$cacheTimeout = (int) $this->params->get('cachetimeout', 30);
		$cacheTimeout = 24 * 3600 * $cacheTimeout;

		// Do we need to run? Compare the last run timestamp stored in the plugin's options with the current
		// timestamp. If the difference is greater than the cache timeout we shall not execute again.
		$now  = time();
		$last = (int) $this->params->get('lastrun', 0);

		if ((abs($now - $last) < $cacheTimeout))
		{
			return;
		}

		// Update last run status
		$this->params->set('lastrun', $now);
		$db    = $this->db;
		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . ' = ' . $db->quote($this->params->toString('JSON')))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('element') . ' = ' . $db->quote('privacyconsent'));

		try
		{
			// Lock the tables to prevent multiple plugin executions causing a race condition
			$db->lockTable('#__extensions');
		}
		catch (Exception $e)
		{
			// If we can't lock the tables it's too risky to continue execution
			return;
		}

		try
		{
			// Update the plugin parameters
			$result = $db->setQuery($query)->execute();
			$this->clearCacheGroups(array('com_plugins'), array(0, 1));
		}
		catch (Exception $exc)
		{
			// If we failed to execute
			$db->unlockTables();
			$result = false;
		}

		try
		{
			// Unlock the tables after writing
			$db->unlockTables();
		}
		catch (Exception $e)
		{
			// If we can't lock the tables assume we have somehow failed
			$result = false;
		}

		// Abort on failure
		if (!$result)
		{
			return;
		}

		// Delete the expired privacy consents
		$this->invalidateExpiredConsents();

		// Remind for privacy consents near to expire
		$this->remindExpiringConsents();

	}

	/**
	 * Method to send the remind for privacy consents renew
	 *
	 * @return  integer
	 *
	 * @since   3.9.0
	 */
	private function remindExpiringConsents()
	{
		// Load the parameters.
		$expire = (int) $this->params->get('consentexpiration', 365);
		$remind = (int) $this->params->get('remind', 30);
		$now    = JFactory::getDate()->toSql();
		$period = '-' . ($expire - $remind);

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName(array('r.id', 'r.user_id', 'u.email')))
			->from($db->quoteName('#__privacy_consents', 'r'))
			->leftJoin($db->quoteName('#__users', 'u') . ' ON u.id = r.user_id')
			->where($db->quoteName('subject') . ' = ' . $db->quote('PLG_SYSTEM_PRIVACYCONSENT_SUBJECT'))
			->where($db->quoteName('remind') . ' = 0');
		$query->where($query->dateAdd($db->quote($now), $period, 'DAY') . ' > ' . $db->quoteName('created'));

		try
		{
			$users = $db->setQuery($query)->loadObjectList();
		}
		catch (JDatabaseException $exception)
		{
			return false;
		}

		$app      = JFactory::getApplication();
		$linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

		foreach ($users as $user)
		{
			$token       = JApplicationHelper::getHash(JUserHelper::genRandomPassword());
			$hashedToken = JUserHelper::hashPassword($token);

			// The mail
			try
			{
				$substitutions = array(
					'[SITENAME]' => $app->get('sitename'),
					'[URL]'      => JUri::root(),
					'[TOKENURL]' => JRoute::link('site', 'index.php?option=com_privacy&view=remind&remind_token=' . $token, false, $linkMode, true),
					'[FORMURL]'  => JRoute::link('site', 'index.php?option=com_privacy&view=remind', false, $linkMode, true),
					'[TOKEN]'    => $token,
					'\\n'        => "\n",
				);

				$emailSubject = JText::_('PLG_SYSTEM_PRIVACYCONSENT_EMAIL_REMIND_SUBJECT');
				$emailBody = JText::_('PLG_SYSTEM_PRIVACYCONSENT_EMAIL_REMIND_BODY');

				foreach ($substitutions as $k => $v)
				{
					$emailSubject = str_replace($k, $v, $emailSubject);
					$emailBody    = str_replace($k, $v, $emailBody);
				}

				$mailer = JFactory::getMailer();
				$mailer->setSubject($emailSubject);
				$mailer->setBody($emailBody);
				$mailer->addRecipient($user->email);

				$mailResult = $mailer->Send();

				if ($mailResult instanceof JException)
				{
					return false;
				}
				elseif ($mailResult === false)
				{
					return false;
				}

				// Update the privacy_consents item to not send the reminder again
				$query->clear()
					->update($db->quoteName('#__privacy_consents'))
					->set($db->quoteName('remind') . ' = 1 ')
					->set($db->quoteName('token') . ' = ' . $db->quote($hashedToken))
					->where($db->quoteName('id') . ' = ' . (int) $user->id);
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					return false;
				}
			}
			catch (phpmailerException $exception)
			{
				return false;
			}
		}
	}

	/**
	 * Method to delete the expired privacy consents
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	private function invalidateExpiredConsents()
	{
		// Load the parameters.
		$expire = (int) $this->params->get('consentexpiration', 365);
		$now    = JFactory::getDate()->toSql();
		$period = '-' . $expire;

		$db    = $this->db;
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'user_id')))
			->from($db->quoteName('#__privacy_consents'))
			->where($query->dateAdd($db->quote($now), $period, 'DAY') . ' > ' . $db->quoteName('created'))
			->where($db->quoteName('subject') . ' = ' . $db->quote('PLG_SYSTEM_PRIVACYCONSENT_SUBJECT'))
			->where($db->quoteName('state') . ' = 1');
		$db->setQuery($query);

		try
		{
			$users = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		// Do not process further if no expired consents found
		if (empty($users))
		{
			return true;
		}

		// Push a notification to the site's super users
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_messages/models', 'MessagesModel');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_messages/tables');
		/** @var MessagesModelMessage $messageModel */
		$messageModel = JModelLegacy::getInstance('Message', 'MessagesModel');

		foreach ($users as $user)
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__privacy_consents'))
				->set('state = 0')
				->where($db->quoteName('id') . ' = ' . (int) $user->id);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				return false;
			}

			$messageModel->notifySuperUsers(
				JText::_('PLG_SYSTEM_PRIVACYCONSENT_NOTIFICATION_USER_PRIVACY_EXPIRED_SUBJECT'),
				JText::sprintf('PLG_SYSTEM_PRIVACYCONSENT_NOTIFICATION_USER_PRIVACY_EXPIRED_MESSAGE', JFactory::getUser($user->user_id)->username)
			);
		}

		return true;
	}
	/**
	 * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
	 *
	 * @param   array  $clearGroups   The cache groups to clean
	 * @param   array  $cacheClients  The cache clients (site, admin) to clean
	 *
	 * @return  void
	 *
	 * @since    3.9.0
	 */
	private function clearCacheGroups(array $clearGroups, array $cacheClients = array(0, 1))
	{
		$conf = JFactory::getConfig();

		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $client_id)
			{
				try
				{
					$options = array(
						'defaultgroup' => $group,
						'cachebase'    => $client_id ? JPATH_ADMINISTRATOR . '/cache' :
							$conf->get('cache_path', JPATH_SITE . '/cache')
					);

					$cache = JCache::getInstance('callback', $options);
					$cache->clean();
				}
				catch (Exception $e)
				{
					// Ignore it
				}
			}
		}
	}
}

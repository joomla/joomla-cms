<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

/**
 * Joomla! Users Actions Logging Plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgActionlogJoomla extends JPlugin
{
	/**
	 * Array of loggable extensions.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $loggableExtensions = array();

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
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$params = ComponentHelper::getComponent('com_actionlogs')->getParams();

		$this->loggableExtensions = $params->get('loggable_extensions', array());
	}

	/**
	 * After save content logging method
	 * This method adds a record to #__action_logs contains (message, date, context, user)
	 * Method is called right after the content is saved
	 *
	 * @param   string   $context  The context of the content passed to the plugin
	 * @param   object   $article  A JTableContent object
	 * @param   boolean  $isNew    If the content is just about to be created
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
		$option = $this->app->input->getCmd('option');

		if (!$this->checkLoggable($option))
		{
			return;
		}

		$params = ActionlogsHelper::getLogContentTypeParams($context);

		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		$user             = JFactory::getUser();
		$contentTypeTitle = strtoupper($params->type_title);
		list(, $contentType) = explode('.', $params->type_alias);

		if ($isNew)
		{
			$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentTypeTitle . '_ADDED');
			$defaultLanguageKey = strtoupper('PLG_SYSTEM_ACTIONLOGS_CONTENT_ADDED');

			$action = 'add';
		}
		else
		{
			$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentTypeTitle . '_UPDATED');
			$defaultLanguageKey = strtoupper('PLG_SYSTEM_ACTIONLOGS_CONTENT_UPDATED');

			$action = 'update';
		}

		// If the content type doesn't has it own language key, use default language key
		if (!JFactory::getLanguage()->hasKey($messageLanguageKey))
		{
			$messageLanguageKey = $defaultLanguageKey;
		}

		$id = empty($params->id_holder) ? 0 : $article->get($params->id_holder);

		$message = array(
			'action'      => $action,
			'type'        => strtoupper($params->text_prefix . '_TYPE_' . $contentTypeTitle),
			'id'          => $id,
			'title'       => $article->get($params->title_holder),
			'itemlink'    => ActionlogsHelper::getContentTypeLink($option, $contentType, $id),
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	/**
	 * After delete content logging method
	 * This method adds a record to #__action_logs contains (message, date, context, user)
	 * Method is called right after the content is deleted
	 *
	 * @param   string  $context  The context of the content passed to the plugin
	 * @param   object  $article  A JTableContent object
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentAfterDelete($context, $article)
	{
		$option = $this->app->input->get('option');

		if (!$this->checkLoggable($option))
		{
			return;
		}

		$params = ActionlogsHelper::getLogContentTypeParams($context);

		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		$language         = JFactory::getLanguage();
		$user             = JFactory::getUser();
		$contentTypeTitle = strtoupper($params->type_title);

		// If the content type has it own language key, use it, otherwise, use default language key
		if ($language->hasKey(strtoupper($params->text_prefix . '_' . $contentTypeTitle . '_DELETED')))
		{
			$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentTypeTitle . '_DELETED');
		}
		else
		{
			$messageLanguageKey = strtoupper('PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED');
		}

		$id = empty($params->id_holder) ? 0 : $article->get($params->id_holder);

		$message = array(
			'action'      => 'delete',
			'type'        => strtoupper($params->text_prefix . '_TYPE_' . $contentTypeTitle),
			'id'          => $id,
			'title'       => $article->get($params->title_holder),
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	/**
	 * On content change status logging method
	 * This method adds a record to #__action_logs contains (message, date, context, user)
	 * Method is called when the status of the article is changed
	 *
	 * @param   string   $context  The context of the content passed to the plugin
	 * @param   array    $pks      An array of primary key ids of the content that has changed state.
	 * @param   integer  $value    The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		$option = $this->app->input->getCmd('option');

		if (!$this->checkLoggable($option))
		{
			return;
		}

		$params = ActionlogsHelper::getLogContentTypeParams($context);

		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		$user             = JFactory::getUser();
		$contentTypeTitle = strtoupper($params->type_title);
		list(, $contentType) = explode('.', $params->type_alias);

		switch ($value)
		{
			case 0:
				$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentTypeTitle . '_UNPUBLISHED');
				$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UNPUBLISHED';
				$action             = 'unpublish';
				break;
			case 1:
				$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentTypeTitle . '_PUBLISHED');
				$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_PUBLISHED';
				$action             = 'publish';
				break;
			case 2:
				$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentTypeTitle . '_ARCHIVED');
				$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_ARCHIVED';
				$action             = 'archive';
				break;
			case -2:
				$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentTypeTitle . '_TRASHED');
				$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_TRASHED';
				$action             = 'trash';
				break;
			default:
				$messageLanguageKey = '';
				$defaultLanguageKey = '';
				$action             = '';
				break;
		}

		// If the content type doesn't has it own language key, use default language key
		if (!JFactory::getLanguage()->hasKey($messageLanguageKey))
		{
			$messageLanguageKey = $defaultLanguageKey;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array($params->title_holder, $params->id_holder)))
			->from($db->quoteName($params->table_name))
			->where($db->quoteName($params->id_holder) . ' IN (' . implode(',', ArrayHelper::toInteger($pks)) . ')');
		$db->setQuery($query);

		try
		{
			$items = $db->loadObjectList($params->id_holder);
		}
		catch (RuntimeException $e)
		{
			$items = array();
		}

		$messages = array();

		foreach ($pks as $pk)
		{
			$message = array(
				'action'      => $action,
				'type'        => strtoupper($params->text_prefix . '_TYPE_' . $params->type_title),
				'id'          => $pk,
				'title'       => $items[$pk]->{$params->title_holder},
				'itemlink'    => ActionlogsHelper::getContentTypeLink($option, $contentType, $pk),
				'userid'      => $user->id,
				'username'    => $user->username,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			);

			$messages[] = $message;
		}

		$this->addLog($messages, $messageLanguageKey, $context);
	}

	/**
	 * On Saving application configuration logging method
	 * Method is called when the application config is being saved
	 *
	 * @param   JRegistry  $config  JRegistry object with the new config
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onApplicationAfterSave($config)
	{
		$option = $this->app->input->getCmd('option');

		if (!$this->checkLoggable($option))
		{
			return;
		}

		$messageLanguageKey = strtoupper('PLG_ACTIONLOG_JOOMLA_APPLICATION_CONFIG_UPDATED');
		$action             = 'update';

		$user = JFactory::getUser();

		$message = array(
			'action'         => $action,
			'type'           => strtoupper('PLG_ACTIONLOG_JOOMLA_TYPE_APPLICATION_CONFIG'),
			'extension_name' => 'com_config.application',
			'itemlink'       => 'index.php?option=com_config',
			'userid'         => $user->id,
			'username'       => $user->username,
			'accountlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, 'com_config.application');
	}

	/**
	 * On installing extensions logging method
	 * This method adds a record to #__action_logs contains (message, date, context, user)
	 * Method is called when an extension is installed
	 *
	 * @param   JInstaller  $installer  Installer object
	 * @param   integer     $eid        Extension Identifier
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterInstall($installer, $eid)
	{
		$context = $this->app->input->get('option');

		if (!$this->checkLoggable($context))
		{
			return;
		}

		$language      = JFactory::getLanguage();
		$user          = JFactory::getUser();
		$manifest      = $installer->get('manifest');
		$extensionType = $manifest->attributes()->type;

		// If the extension type has it own language key, use it, otherwise, use default language key
		if ($language->hasKey(strtoupper('PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_INSTALLED')))
		{
			$messageLanguageKey = strtoupper('PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_INSTALLED');
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_EXTENSION_INSTALLED';
		}

		$message = array(
			'action'         => 'install',
			'type'           => strtoupper('PLG_ACTIONLOG_JOOMLA_TYPE_' . $extensionType),
			'id'             => $eid,
			'name'           => (string) $manifest->name,
			'extension_name' => (string) $manifest->name,
			'userid'         => $user->id,
			'username'       => $user->username,
			'accountlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	/**
	 * On uninstalling extensions logging method
	 * This method adds a record to #__action_logs contains (message, date, context, user)
	 * Method is called when an extension is uninstalled
	 *
	 * @param   JInstaller  $installer  Installer instance
	 * @param   integer     $eid        Extension id
	 * @param   integer     $result     Installation result
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterUninstall($installer, $eid, $result)
	{
		$context = $this->app->input->get('option');

		if (!$this->checkLoggable($context))
		{
			return;
		}

		// If the process failed, we don't have manifest data, stop process to avoid fatal error
		if ($result === false)
		{
			return;
		}

		$language      = JFactory::getLanguage();
		$user          = JFactory::getUser();
		$manifest      = $installer->get('manifest');
		$extensionType = $manifest->attributes()->type;

		// If the extension type has it own language key, use it, otherwise, use default language key
		if ($language->hasKey(strtoupper('PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_UNINSTALLED')))
		{
			$messageLanguageKey = strtoupper('PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_UNINSTALLED');
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_EXTENSION_UNINSTALLED';
		}

		$message = array(
			'action'         => 'install',
			'type'           => strtoupper('PLG_ACTIONLOG_JOOMLA_TYPE_' . $extensionType),
			'id'             => $eid,
			'name'           => (string) $manifest->name,
			'extension_name' => (string) $manifest->name,
			'userid'         => $user->id,
			'username'       => $user->username,
			'accountlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	/**
	 * On updating extensions logging method
	 * This method adds a record to #__action_logs contains (message, date, context, user)
	 * Method is called when an extension is updated
	 *
	 * @param   JInstaller  $installer  Installer instance
	 * @param   integer     $eid        Extension id
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterUpdate($installer, $eid)
	{
		$context = $this->app->input->get('option');

		if (!$this->checkLoggable($context))
		{
			return;
		}

		$language      = JFactory::getLanguage();
		$user          = JFactory::getUser();
		$manifest      = $installer->get('manifest');
		$extensionType = $manifest->attributes()->type;

		// If the extension type has it own language key, use it, otherwise, use default language key
		if ($language->hasKey(strtoupper('PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_UPDATED')))
		{
			$messageLanguageKey = strtoupper('PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_UPDATED');
		}
		else
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_EXTENSION_UPDATED';
		}

		$message = array(
			'action'         => 'update',
			'type'           => strtoupper('PLG_ACTIONLOG_JOOMLA_TYPE_' . $extensionType),
			'id'             => $eid,
			'name'           => (string) $manifest->name,
			'extension_name' => (string) $manifest->name,
			'userid'         => $user->id,
			'username'       => $user->username,
			'accountlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	/**
	 * On Saving extensions logging method
	 * Method is called when an extension is being saved
	 *
	 * @param   string   $context  The extension
	 * @param   JTable   $table    DataBase Table object
	 * @param   boolean  $isNew    If the extension is new or not
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterSave($context, $table, $isNew)
	{
		$option = $this->app->input->getCmd('option');

		if (!$this->checkLoggable($option))
		{
			return;
		}

		$params = ActionlogsHelper::getLogContentTypeParams($context);

		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		$extensionType = $params->type_title;
		list(, $contentType) = explode('.', $params->type_alias);

		if ($isNew)
		{
			$messageLanguageKey = strtoupper('PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_ADDED');
			$defaultLanguageKey = strtoupper('PLG_SYSTEM_ACTIONLOGS_CONTENT_ADDED');
			$action             = 'add';
		}
		else
		{
			$messageLanguageKey = strtoupper('PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_UPDATED');
			$defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UPDATED';
			$action             = 'update';
		}

		// If the extension type doesn't have it own language key, use default language key
		if (!JFactory::getLanguage()->hasKey($messageLanguageKey))
		{
			$messageLanguageKey = $defaultLanguageKey;
		}

		$user = JFactory::getUser();

		$message = array(
			'action'         => $action,
			'type'           => strtoupper('PLG_ACTIONLOG_JOOMLA_TYPE_' . $extensionType),
			'id'             => $table->get($params->id_holder),
			'title'          => $table->get($params->title_holder),
			'extension_name' => $table->get($params->title_holder),
			'itemlink'       => ActionlogsHelper::getContentTypeLink($option, $contentType, $table->get($params->id_holder), $params->id_holder),
			'userid'         => $user->id,
			'username'       => $user->username,
			'accountlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	/**
	 * On Deleting extensions logging method
	 * Method is called when an extension is being deleted
	 *
	 * @param   string  $context  The extension
	 * @param   JTable  $table    DataBase Table object
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterDelete($context, $table)
	{
		if (!$this->checkLoggable($this->app->input->get('option')))
		{
			return;
		}

		$params = ActionlogsHelper::getLogContentTypeParams($context);

		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		$messageLanguageKey = strtoupper('PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED');
		$user               = JFactory::getUser();

		$message = array(
			'action'      => 'delete',
			'type'        => strtoupper('PLG_ACTIONLOG_JOOMLA_TYPE_' . $params->type_title),
			'title'       => $table->get($params->title_holder),
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	/**
	 * On saving user data logging method
	 *
	 * Method is called after user data is stored in the database.
	 * This method logs who created/edited any user's data
	 *
	 * @param   array    $user     Holds the new user data.
	 * @param   boolean  $isnew    True if a new user is stored.
	 * @param   boolean  $success  True if user was succesfully stored in the database.
	 * @param   string   $msg      Message.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		$context = $this->app->input->get('option');

		if (!$this->checkLoggable($context))
		{
			return;
		}

		$jUser = JFactory::getUser();

		if (!$jUser->id)
		{
			$messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_USER_REGISTERED';
			$action             = 'register';
		}
		elseif ($isnew)
		{
			$messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_ADDED';
			$action             = 'add';
		}
		else
		{
			$messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UPDATED';
			$action             = 'update';
		}

		$userId = $jUser->id ?: $user['id'];
		$username = $jUser->username ?: $user['username'];

		$message = array(
			'action'      => $action,
			'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER',
			'id'          => $user['id'],
			'title'       => $user['name'],
			'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user['id'],
			'userid'      => $userId,
			'username'    => $username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $userId);
	}

	/**
	 * On deleting user data logging method
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was succesfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		$context = $this->app->input->get('option');

		if (!$this->checkLoggable($context))
		{
			return;
		}

		$messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED';
		$jUser              = JFactory::getUser();

		$message = array(
			'action'      => 'delete',
			'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER',
			'id'          => $user['id'],
			'title'       => $user['name'],
			'userid'      => $jUser->id,
			'username'    => $jUser->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $jUser->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	/**
	 * On after save user group data logging method
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   string   $context  The context
	 * @param   JTable   $table    DataBase Table object
	 * @param   boolean  $isNew    Is new or not
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserAfterSaveGroup($context, $table, $isNew)
	{
		$context = $this->app->input->get('option');

		if (!$this->checkLoggable($context))
		{
			return;
		}

		if ($isNew)
		{
			$messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_ADDED';
			$action             = 'add';
		}
		else
		{
			$messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UPDATED';
			$action             = 'update';
		}

		$user = JFactory::getUser();

		$message = array(
			'action'      => $action,
			'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER_GROUP',
			'id'          => $table->id,
			'title'       => $table->title,
			'itemlink'    => 'index.php?option=com_users&task=group.edit&id=' . $table->id,
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	/**
	 * On deleting user group data logging method
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $group    Holds the group data
	 * @param   boolean  $success  True if user was succesfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserAfterDeleteGroup($group, $success, $msg)
	{
		$context = $this->app->input->get('option');

		if (!$this->checkLoggable($context))
		{
			return;
		}

		$user = JFactory::getUser();

		$messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED';

		$message = array(
			'action'      => 'delete',
			'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER_GROUP',
			'id'          => $group['id'],
			'title'       => $group['title'],
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	/**
	 * Method to log user login success action
	 *
	 * @param   array  $options  Array holding options (user, responseType)
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserAfterLogin($options)
	{
		$context = 'com_users';

		if (!$this->checkLoggable($context))
		{
			return;
		}

		$loggedInUser       = $options['user'];
		$messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_USER_LOGGED_IN';

		$message = array(
			'action'      => 'login',
			'username'    => $loggedInUser->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $loggedInUser->id,
			'app'         => strtoupper('PLG_ACTIONLOG_JOOMLA_APPLICATION_' . $this->app->getName()),
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $loggedInUser->id);
	}

	/**
	 * Method to log user login failed action
	 *
	 * @param   array  $response  Array of response data.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserLoginFailure($response)
	{
		$context = 'com_users';

		if (!$this->checkLoggable($context))
		{
			return;
		}

		$loggedInUser       = JUser::getInstance($response['username']);

		// Not a valid user, return
		if (!$loggedInUser->id)
		{
			return;
		}

		$messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_USER_LOGIN_FAILED';

		$message = array(
			'action'      => 'login',
			'id'          => $loggedInUser->id,
			'username'    => $loggedInUser->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $loggedInUser->id,
			'app'         => strtoupper('PLG_ACTIONLOG_JOOMLA_APPLICATION_' . $this->app->getName()),
		);

		$this->addLog(array($message), $messageLanguageKey, $context, $loggedInUser->id);
	}

	/**
	 * Method to log user's logout action
	 *
	 * @param   array  $user     Holds the user data
	 * @param   array  $options  Array holding options (remember, autoregister, group)
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserLogout($user, $options = array())
	{
		$context = 'com_users';

		if (!$this->checkLoggable($context))
		{
			return;
		}

		$loggedOutUser      = JUser::getInstance($user['id']);
		$messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_USER_LOGGED_OUT';

		$message = array(
			'action'      => 'logout',
			'id'          => $loggedOutUser->id,
			'username'    => $loggedOutUser->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $loggedOutUser->id,
			'app'         => strtoupper('PLG_ACTIONLOG_JOOMLA_APPLICATION_' . $this->app->getName()),
		);

		$this->addLog(array($message), $messageLanguageKey, $context);
	}

	/**
	 * Proxy for ActionlogsModelUserlog addLog method
	 *
	 * This method adds a record to #__action_logs contains (message_language_key, message, date, context, user)
	 *
	 * @param   array   $messages            The contents of the messages to be logged
	 * @param   string  $messageLanguageKey  The language key of the message
	 * @param   string  $context             The context of the content passed to the plugin
	 * @param   int     $userId              ID of user perform the action, usually ID of current logged in user
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addLog($messages, $messageLanguageKey, $context, $userId = null)
	{
		JLoader::register('ActionlogsModelActionlog', JPATH_ADMINISTRATOR . '/components/com_actionlogs/models/actionlog.php');

		/* @var ActionlogsModelActionlog $model */
		$model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog($messages, $messageLanguageKey, $context, $userId);
	}

	/**
	 * Function to check if a component is loggable or not
	 *
	 * @param   string  $extension  The extension that triggered the event
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function checkLoggable($extension)
	{
		return in_array($extension, $this->loggableExtensions);
	}
}

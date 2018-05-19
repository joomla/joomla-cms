<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.userlogs
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

JLoader::register('UserlogsHelper', JPATH_ADMINISTRATOR . '/components/com_userlogs/helpers/userlogs.php');

/**
 * Joomla! Users Actions Logging Plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgActionlogsJoomla extends JPlugin
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
	 * @var    bool
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

		$params = ComponentHelper::getComponent('com_userlogs')->getParams();

		if (is_array($params->get('loggable_extensions')))
		{
			$this->loggableExtensions = $params->get('loggable_extensions');
		}
		else
		{
			$this->loggableExtensions = explode(',', $params->get('loggable_extensions'));
		}
	}

	/**
	 * After save content logging method
	 * This method adds a record to #__user_logs contains (message, date, context, user)
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

		$params = UserlogsHelper::getLogContentTypeParams($context);

		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		$user        = JFactory::getUser();
		$contentType = strtoupper($params->type_title);

		if ($isNew)
		{
			$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentType . '_ADDED');
			$defaultLanguageKey = strtoupper($params->text_prefix . '_CONTENT_ADDED');

			$action = 'add';
		}
		else
		{
			$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentType . '_UPDATED');
			$defaultLanguageKey = strtoupper($params->text_prefix . '_CONTENT_UPDATED');

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
			'type'        => strtoupper($params->text_prefix . '_TYPE_' . $contentType),
			'id'          => $id,
			'title'       => $article->get($params->title_holder),
			'itemlink'    => UserlogsHelper::getContentTypeLink($option, $params->type_title, $id),
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLogsToDb(array($message), $messageLanguageKey, $context);
	}

	/**
	 * After delete content logging method
	 * This method adds a record to #__user_logs contains (message, date, context, user)
	 * Method is called right after the content is deleted
	 *
	 * @param   string   $context  The context of the content passed to the plugin
	 * @param   object   $article  A JTableContent object
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

		$params = UserlogsHelper::getLogContentTypeParams($context);

		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		$language    = JFactory::getLanguage();
		$user        = JFactory::getUser();
		$contentType = strtoupper($params->type_title);

		// If the content type has it own language key, use it, otherwise, use default language key
		if ($language->hasKey(strtoupper($params->text_prefix . '_' . $contentType . '_DELETED')))
		{
			$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentType . '_DELETED');
		}
		else
		{
			$messageLanguageKey = strtoupper($params->text_prefix . '_CONTENT_DELETED');
		}

		$id = empty($params->id_holder) ? 0 : $article->get($params->id_holder);

		$message = array(
			'action'      => 'delete',
			'type'        => strtoupper($params->text_prefix . '_TYPE_' . $contentType),
			'id'          => $id,
			'title'       => $article->get($params->title_holder),
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLogsToDb(array($message), $messageLanguageKey, $context);
	}

	/**
	 * On content change status logging method
	 * This method adds a record to #__user_logs contains (message, date, context, user)
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

		$params = UserlogsHelper::getLogContentTypeParams($context);

		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		$user        = JFactory::getUser();
		$contentType = strtoupper($params->type_title);

		switch ($value)
		{
			case 0:
				$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentType . '_UNPUBLISHED');
				$defaultLanguageKey = strtoupper($params->text_prefix . '_CONTENT_UNPUBLISHED');
				$action             = 'unpublish';
				break;
			case 1:
				$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentType . '_PUBLISHED');
				$defaultLanguageKey = strtoupper($params->text_prefix . '_CONTENT_PUBLISHED');
				$action             = 'publish';
				break;
			case 2:
				$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentType . '_ARCHIVED');
				$defaultLanguageKey = strtoupper($params->text_prefix . '_CONTENT_ARCHIVED');
				$action             = 'archive';
				break;
			case -2:
				$messageLanguageKey = strtoupper($params->text_prefix . '_' . $contentType . '_TRASHED');
				$defaultLanguageKey = strtoupper($params->text_prefix . '_CONTENT_TRASHED');
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

		$items = UserlogsHelper::getDataByPks($pks, $params->title_holder, $params->id_holder, $params->table_name);

		$messages = array();

		foreach ($pks as $pk)
		{
			$message = array(
				'action'      => $action,
				'type'        => strtoupper($params->text_prefix . '_TYPE_' . $params->type_title),
				'id'          => $pk,
				'title'       => $items[$pk]->{$params->title_holder},
				'itemlink'    => UserlogsHelper::getContentTypeLink($option, $params->type_title, $pk),
				'userid'      => $user->id,
				'username'    => $user->username,
				'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			);

			$messages[] = $message;
		}

		$this->addLogsToDb($messages, $messageLanguageKey, $context);
	}

	/**
	 * On installing extensions logging method
	 * This method adds a record to #__user_logs contains (message, date, context, user)
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
		if ($language->hasKey(strtoupper('PLG_SYSTEM_USERLOGS_' . $extensionType . '_INSTALLED')))
		{
			$messageLanguageKey = strtoupper('PLG_SYSTEM_USERLOGS_' . $extensionType . '_INSTALLED');
		}
		else
		{
			$messageLanguageKey = 'PLG_SYSTEM_USERLOGS_EXTENSION_INSTALLED';
		}

		$message = array(
			'action'         => 'install',
			'type'           => strtoupper('PLG_SYSTEM_USERLOGS_TYPE_' . $extensionType),
			'id'             => $eid,
			'name'           => (string) $manifest->name,
			'extension_name' => (string) $manifest->name,
			'userid'         => $user->id,
			'username'       => $user->username,
			'accountlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLogsToDb(array($message), $messageLanguageKey, $context);
	}

	/**
	 * On uninstalling extensions logging method
	 * This method adds a record to #__user_logs contains (message, date, context, user)
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

		$language      = JFactory::getLanguage();
		$user          = JFactory::getUser();
		$manifest      = $installer->get('manifest');
		$extensionType = $manifest->attributes()->type;

		// If the extension type has it own language key, use it, otherwise, use default language key
		if ($language->hasKey(strtoupper('PLG_SYSTEM_USERLOGS_' . $extensionType . '_UNINSTALLED')))
		{
			$messageLanguageKey = strtoupper('PLG_SYSTEM_USERLOGS_' . $extensionType . '_UNINSTALLED');
		}
		else
		{
			$messageLanguageKey = 'PLG_SYSTEM_USERLOGS_EXTENSION_UNINSTALLED';
		}

		$message = array(
			'action'         => 'install',
			'type'           => strtoupper('PLG_SYSTEM_USERLOGS_TYPE_' . $extensionType),
			'id'             => $eid,
			'name'           => (string) $manifest->name,
			'extension_name' => (string) $manifest->name,
			'userid'         => $user->id,
			'username'       => $user->username,
			'accountlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLogsToDb(array($message), $messageLanguageKey, $context);
	}

	/**
	 * On updating extensions logging method
	 * This method adds a record to #__user_logs contains (message, date, context, user)
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
		if ($language->hasKey(strtoupper('PLG_SYSTEM_USERLOGS_' . $extensionType . '_UPDATED')))
		{
			$messageLanguageKey = strtoupper('PLG_SYSTEM_USERLOGS_' . $extensionType . '_UPDATED');
		}
		else
		{
			$messageLanguageKey = 'PLG_SYSTEM_USERLOGS_EXTENSION_UPDATED';
		}

		$message = array(
			'action'         => 'update',
			'type'           => strtoupper('PLG_SYSTEM_USERLOGS_TYPE_' . $extensionType),
			'id'             => $eid,
			'name'           => (string) $manifest->name,
			'extension_name' => (string) $manifest->name,
			'userid'         => $user->id,
			'username'       => $user->username,
			'accountlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLogsToDb(array($message), $messageLanguageKey, $context);
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

		$params = UserlogsHelper::getLogContentTypeParams($context);

		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		$extensionType = $params->type_title;

		if ($isNew)
		{
			$messageLanguageKey = strtoupper('PLG_SYSTEM_USERLOGS_' . $extensionType . '_ADDED');
			$defaultLanguageKey = strtoupper('PLG_SYSTEM_USERLOGS_CONTENT_ADDED');
			$action             = 'add';
		}
		else
		{

			$messageLanguageKey = strtoupper('PLG_SYSTEM_USERLOGS_' . $extensionType . '_UPDATED');
			$defaultLanguageKey = 'PLG_SYSTEM_USERLOGS_CONTENT_UPDATED';
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
			'type'           => strtoupper('PLG_SYSTEM_USERLOGS_TYPE_' . $extensionType),
			'id'             => $table->get($params->id_holder),
			'title'          => $table->get($params->title_holder),
			'extension_name' => $table->get($params->title_holder),
			'itemlink'       => UserlogsHelper::getContentTypeLink($option, $params->type_title, $table->get($params->id_holder), $params->id_holder),
			'userid'         => $user->id,
			'username'       => $user->username,
			'accountlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLogsToDb(array($message), $messageLanguageKey, $context);
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

		$params = UserlogsHelper::getLogContentTypeParams($context);

		// Not found a valid content type, don't process further
		if ($params === null)
		{
			return;
		}

		$messageLanguageKey = strtoupper('PLG_SYSTEM_USERLOGS_CONTENT_DELETED');
		$user               = JFactory::getUser();

		$message = array(
			'action'      => 'delete',
			'type'        => strtoupper('PLG_SYSTEM_USERLOGS_TYPE_' . $params->type_title),
			'title'       => $table->get($params->title_holder),
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLogsToDb(array($message), $messageLanguageKey, $context);
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

		if ($isnew)
		{
			$messageLanguageKey = 'PLG_SYSTEM_USERLOGS_CONTENT_ADDED';
			$action             = 'add';
		}
		else
		{
			$messageLanguageKey = 'PLG_SYSTEM_USERLOGS_CONTENT_UPDATED';
			$action             = 'update';
		}

		$message = array(
			'action'      => $action,
			'type'        => 'PLG_SYSTEM_USERLOGS_TYPE_USER',
			'id'          => $user['id'],
			'title'       => $user['name'],
			'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user['id'],
			'userid'      => $jUser->id,
			'username'    => $jUser->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $jUser->id,
		);

		$this->addLogsToDb(array($message), $messageLanguageKey, $context);
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

		$messageLanguageKey = 'PLG_SYSTEM_USERLOGS_CONTENT_DELETED';
		$jUser              = JFactory::getUser();

		$message = array(
			'action'      => 'delete',
			'type'        => 'PLG_SYSTEM_USERLOGS_TYPE_USER',
			'id'          => $user['id'],
			'title'       => $user['name'],
			'userid'      => $jUser->id,
			'username'    => $jUser->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $jUser->id,
		);

		$this->addLogsToDb(array($message), $messageLanguageKey, $context);
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
			$messageLanguageKey = 'PLG_SYSTEM_USERLOGS_CONTENT_ADDED';
			$action             = 'add';
		}
		else
		{
			$messageLanguageKey = 'PLG_SYSTEM_USERLOGS_CONTENT_UPDATED';
			$action             = 'update';
		}

		$user = JFactory::getUser();

		$message = array(
			'action'      => $action,
			'type'        => 'PLG_SYSTEM_USERLOGS_TYPE_USER_GROUP',
			'id'          => $table->id,
			'title'       => $table->title,
			'itemlink'    => 'index.php?option=com_users&task=group.edit&id=' . $table->id,
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLogsToDb(array($message), $messageLanguageKey, $context);
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

		$messageLanguageKey = 'PLG_SYSTEM_USERLOGS_CONTENT_DELETED';

		$message = array(
			'action'      => 'delete',
			'type'        => 'PLG_SYSTEM_USERLOGS_TYPE_USER_GROUP',
			'id'          => $group['id'],
			'title'       => $group['title'],
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		$this->addLogsToDb(array($message), $messageLanguageKey, $context);
	}

	/**
	 * Proxy for UserlogsModelUserlog addLogsToDb method
	 *
	 * This method adds a record to #__user_logs contains (message_language_key, message, date, context, user)
	 *
	 * @param   array    $messages            The contents of the messages to be logged
	 * @param   string   $messageLanguageKey  The language key of the message
	 * @param   string   $context             The context of the content passed to the plugin
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addLogsToDb($messages, $messageLanguageKey, $context)
	{
		JLoader::register('UserlogsModelUserlog', JPATH_ADMINISTRATOR . '/components/com_userlogs/models/userlog.php');

		/* @var UserlogsModelUserlog $model */
		$model = JModelLegacy::getInstance('Userlog', 'UserlogsModel');
		$model->addLogsToDb($messages, $messageLanguageKey, $context);
	}

	/**
	 * Function to check if a component is loggable or not
	 *
	 * @param   string   $extension  The extension that triggered the event
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

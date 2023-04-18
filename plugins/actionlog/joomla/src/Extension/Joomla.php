<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Actionlog.joomla
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Actionlog\Joomla\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Component\Actionlogs\Administrator\Plugin\ActionLogPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Event\DispatcherInterface;
use Joomla\Utilities\ArrayHelper;
use RuntimeException;
use stdClass;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Users Actions Logging Plugin.
 *
 * @since  3.9.0
 */
final class Joomla extends ActionLogPlugin
{
    use DatabaseAwareTrait;

    /**
     * Array of loggable extensions.
     *
     * @var    array
     * @since  3.9.0
     */
    protected $loggableExtensions = [];

    /**
     * Context aliases
     *
     * @var    array
     * @since  3.9.0
     */
    protected $contextAliases = ['com_content.form' => 'com_content.article'];

    /**
     * Flag for loggable Api.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $loggableApi = false;

    /**
     * Array of loggable verbs.
     *
     * @var    array
     * @since  4.0.0
     */
    protected $loggableVerbs = [];

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher  The dispatcher
     * @param   array                $config      An optional associative array of configuration settings
     *
     * @since   3.9.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config)
    {
        parent::__construct($dispatcher, $config);

        $params = ComponentHelper::getComponent('com_actionlogs')->getParams();

        $this->loggableExtensions = $params->get('loggable_extensions', []);

        $this->loggableApi        = $params->get('loggable_api', 0);

        $this->loggableVerbs      = $params->get('loggable_verbs', []);
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
     * @since   3.9.0
     */
    public function onContentAfterSave($context, $article, $isNew): void
    {
        if (isset($this->contextAliases[$context])) {
            $context = $this->contextAliases[$context];
        }

        $params = $this->getActionLogParams($context);

        // Not found a valid content type, don't process further
        if ($params === null) {
            return;
        }

        list($option, $contentType) = explode('.', $params->type_alias);

        if (!$this->checkLoggable($option)) {
            return;
        }

        if ($isNew) {
            $messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_ADDED';
            $defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_ADDED';
        } else {
            $messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_UPDATED';
            $defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UPDATED';
        }

        // If the content type doesn't have its own language key, use default language key
        if (!$this->getApplication()->getLanguage()->hasKey($messageLanguageKey)) {
            $messageLanguageKey = $defaultLanguageKey;
        }

        $id = empty($params->id_holder) ? 0 : $article->get($params->id_holder);

        $message = [
            'action'   => $isNew ? 'add' : 'update',
            'type'     => $params->text_prefix . '_TYPE_' . $params->type_title,
            'id'       => $id,
            'title'    => $article->get($params->title_holder),
            'itemlink' => ActionlogsHelper::getContentTypeLink($option, $contentType, $id, $params->id_holder, $article),
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
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
     * @since   3.9.0
     */
    public function onContentAfterDelete($context, $article): void
    {
        $option = $this->getApplication()->getInput()->get('option');

        if (!$this->checkLoggable($option)) {
            return;
        }

        $params = $this->getActionLogParams($context);

        // Not found a valid content type, don't process further
        if ($params === null) {
            return;
        }

        // If the content type has its own language key, use it, otherwise, use default language key
        if ($this->getApplication()->getLanguage()->hasKey(strtoupper($params->text_prefix . '_' . $params->type_title . '_DELETED'))) {
            $messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_DELETED';
        } else {
            $messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED';
        }

        $id = empty($params->id_holder) ? 0 : $article->get($params->id_holder);

        $message = [
            'action' => 'delete',
            'type'   => $params->text_prefix . '_TYPE_' . $params->type_title,
            'id'     => $id,
            'title'  => $article->get($params->title_holder),
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
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
     * @since   3.9.0
     */
    public function onContentChangeState($context, $pks, $value)
    {
        $option = $this->getApplication()->getInput()->getCmd('option');

        if (!$this->checkLoggable($option)) {
            return;
        }

        $params = $this->getActionLogParams($context);

        // Not found a valid content type, don't process further
        if ($params === null) {
            return;
        }

        list(, $contentType) = explode('.', $params->type_alias);

        switch ($value) {
            case 0:
                $messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_UNPUBLISHED';
                $defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UNPUBLISHED';
                $action             = 'unpublish';
                break;
            case 1:
                $messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_PUBLISHED';
                $defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_PUBLISHED';
                $action             = 'publish';
                break;
            case 2:
                $messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_ARCHIVED';
                $defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_ARCHIVED';
                $action             = 'archive';
                break;
            case -2:
                $messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_TRASHED';
                $defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_TRASHED';
                $action             = 'trash';
                break;
            default:
                $messageLanguageKey = '';
                $defaultLanguageKey = '';
                $action             = '';
                break;
        }

        // If the content type doesn't have its own language key, use default language key
        if (!$this->getApplication()->getLanguage()->hasKey($messageLanguageKey)) {
            $messageLanguageKey = $defaultLanguageKey;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName([$params->title_holder, $params->id_holder]))
            ->from($db->quoteName($params->table_name))
            ->whereIn($db->quoteName($params->id_holder), ArrayHelper::toInteger($pks));
        $db->setQuery($query);

        try {
            $items = $db->loadObjectList($params->id_holder);
        } catch (RuntimeException $e) {
            $items = [];
        }

        $messages = [];

        foreach ($pks as $pk) {
            $message = [
                'action'   => $action,
                'type'     => $params->text_prefix . '_TYPE_' . $params->type_title,
                'id'       => $pk,
                'title'    => $items[$pk]->{$params->title_holder},
                'itemlink' => ActionlogsHelper::getContentTypeLink($option, $contentType, $pk, $params->id_holder, null),
            ];

            $messages[] = $message;
        }

        $this->addLog($messages, $messageLanguageKey, $context);
    }

    /**
     * On Saving application configuration logging method
     * Method is called when the application config is being saved
     *
     * @param   \Joomla\Registry\Registry  $config  Registry object with the new config
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onApplicationAfterSave($config): void
    {
        $option = $this->getApplication()->getInput()->getCmd('option');

        if (!$this->checkLoggable($option)) {
            return;
        }

        $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_APPLICATION_CONFIG_UPDATED';
        $action             = 'update';

        $message = [
            'action'         => $action,
            'type'           => 'PLG_ACTIONLOG_JOOMLA_TYPE_APPLICATION_CONFIG',
            'extension_name' => 'com_config.application',
            'itemlink'       => 'index.php?option=com_config',
        ];

        $this->addLog([$message], $messageLanguageKey, 'com_config.application');
    }

    /**
     * On installing extensions logging method
     * This method adds a record to #__action_logs contains (message, date, context, user)
     * Method is called when an extension is installed
     *
     * @param   Installer   $installer  Installer object
     * @param   integer     $eid        Extension Identifier
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onExtensionAfterInstall($installer, $eid)
    {
        $context = $this->getApplication()->getInput()->get('option');

        if (!$this->checkLoggable($context)) {
            return;
        }

        $manifest = $installer->get('manifest');

        if ($manifest === null) {
            return;
        }

        $extensionType = $manifest->attributes()->type;

        // If the extension type has its own language key, use it, otherwise, use default language key
        if ($this->getApplication()->getLanguage()->hasKey(strtoupper('PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_INSTALLED'))) {
            $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_INSTALLED';
        } else {
            $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_EXTENSION_INSTALLED';
        }

        $message = [
            'action'         => 'install',
            'type'           => 'PLG_ACTIONLOG_JOOMLA_TYPE_' . $extensionType,
            'id'             => $eid,
            'name'           => (string) $manifest->name,
            'extension_name' => (string) $manifest->name,
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * On uninstalling extensions logging method
     * This method adds a record to #__action_logs contains (message, date, context, user)
     * Method is called when an extension is uninstalled
     *
     * @param   Installer  $installer  Installer instance
     * @param   integer    $eid        Extension id
     * @param   integer    $result     Installation result
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onExtensionAfterUninstall($installer, $eid, $result)
    {
        $context = $this->getApplication()->getInput()->get('option');

        if (!$this->checkLoggable($context)) {
            return;
        }

        // If the process failed, we don't have manifest data, stop process to avoid fatal error
        if ($result === false) {
            return;
        }

        $manifest = $installer->get('manifest');

        if ($manifest === null) {
            return;
        }

        $extensionType = $manifest->attributes()->type;

        // If the extension type has its own language key, use it, otherwise, use default language key
        if ($this->getApplication()->getLanguage()->hasKey(strtoupper('PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_UNINSTALLED'))) {
            $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_UNINSTALLED';
        } else {
            $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_EXTENSION_UNINSTALLED';
        }

        $message = [
            'action'         => 'install',
            'type'           => 'PLG_ACTIONLOG_JOOMLA_TYPE_' . $extensionType,
            'id'             => $eid,
            'name'           => (string) $manifest->name,
            'extension_name' => (string) $manifest->name,
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * On updating extensions logging method
     * This method adds a record to #__action_logs contains (message, date, context, user)
     * Method is called when an extension is updated
     *
     * @param   Installer  $installer  Installer instance
     * @param   integer    $eid        Extension id
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onExtensionAfterUpdate($installer, $eid)
    {
        $context = $this->getApplication()->getInput()->get('option');

        if (!$this->checkLoggable($context)) {
            return;
        }

        $manifest = $installer->get('manifest');

        if ($manifest === null) {
            return;
        }

        $extensionType = $manifest->attributes()->type;

        // If the extension type has its own language key, use it, otherwise, use default language key
        if ($this->getApplication()->getLanguage()->hasKey('PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_UPDATED')) {
            $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_' . $extensionType . '_UPDATED';
        } else {
            $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_EXTENSION_UPDATED';
        }

        $message = [
            'action'         => 'update',
            'type'           => 'PLG_ACTIONLOG_JOOMLA_TYPE_' . $extensionType,
            'id'             => $eid,
            'name'           => (string) $manifest->name,
            'extension_name' => (string) $manifest->name,
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * On Saving extensions logging method
     * Method is called when an extension is being saved
     *
     * @param   string   $context  The extension
     * @param   Table    $table    DataBase Table object
     * @param   boolean  $isNew    If the extension is new or not
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onExtensionAfterSave($context, $table, $isNew): void
    {
        $option = $this->getApplication()->getInput()->getCmd('option');

        if ($table->get('module') != null) {
            $option = 'com_modules';
        }

        if (!$this->checkLoggable($option)) {
            return;
        }

        $params = $this->getActionLogParams($context);

        // Not found a valid content type, don't process further
        if ($params === null) {
            return;
        }

        list(, $contentType) = explode('.', $params->type_alias);

        if ($isNew) {
            $messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_ADDED';
            $defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_ADDED';
        } else {
            $messageLanguageKey = $params->text_prefix . '_' . $params->type_title . '_UPDATED';
            $defaultLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UPDATED';
        }

        // If the extension type doesn't have it own language key, use default language key
        if (!$this->getApplication()->getLanguage()->hasKey($messageLanguageKey)) {
            $messageLanguageKey = $defaultLanguageKey;
        }

        $message = [
            'action'         => $isNew ? 'add' : 'update',
            'type'           => 'PLG_ACTIONLOG_JOOMLA_TYPE_' . $params->type_title,
            'id'             => $table->get($params->id_holder),
            'title'          => $table->get($params->title_holder),
            'extension_name' => $table->get($params->title_holder),
            'itemlink'       => ActionlogsHelper::getContentTypeLink($option, $contentType, $table->get($params->id_holder), $params->id_holder),
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * On Deleting extensions logging method
     * Method is called when an extension is being deleted
     *
     * @param   string  $context  The extension
     * @param   Table   $table    DataBase Table object
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onExtensionAfterDelete($context, $table): void
    {
        if (!$this->checkLoggable($this->getApplication()->getInput()->get('option'))) {
            return;
        }

        $params = $this->getActionLogParams($context);

        // Not found a valid content type, don't process further
        if ($params === null) {
            return;
        }

        $messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED';

        $message = [
            'action' => 'delete',
            'type'   => 'PLG_ACTIONLOG_JOOMLA_TYPE_' . $params->type_title,
            'title'  => $table->get($params->title_holder),
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * On saving user data logging method
     *
     * Method is called after user data is stored in the database.
     * This method logs who created/edited any user's data
     *
     * @param   array    $user     Holds the new user data.
     * @param   boolean  $isnew    True if a new user is stored.
     * @param   boolean  $success  True if user was successfully stored in the database.
     * @param   string   $msg      Message.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterSave($user, $isnew, $success, $msg): void
    {
        $context = $this->getApplication()->getInput()->get('option');
        $task    = $this->getApplication()->getInput()->get('task');

        if (!$this->checkLoggable($context)) {
            return;
        }

        if ($task === 'request') {
            return;
        }

        if ($task === 'complete') {
            return;
        }

        $jUser = Factory::getUser();

        if (!$jUser->id) {
            $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_USER_REGISTERED';
            $action             = 'register';

            // Registration Activation
            if ($task === 'activate') {
                $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_USER_REGISTRATION_ACTIVATE';
                $action             = 'activaterequest';
            }
        } elseif ($isnew) {
            $messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_ADDED';
            $action             = 'add';
        } else {
            $messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UPDATED';
            $action             = 'update';
        }

        $userId   = $jUser->id ?: $user['id'];
        $username = $jUser->username ?: $user['username'];

        $message = [
            'action'      => $action,
            'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER',
            'id'          => $user['id'],
            'title'       => $user['name'],
            'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user['id'],
            'userid'      => $userId,
            'username'    => $username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
        ];

        $this->addLog([$message], $messageLanguageKey, $context, $userId);
    }

    /**
     * On deleting user data logging method
     *
     * Method is called after user data is deleted from the database
     *
     * @param   array    $user     Holds the user data
     * @param   boolean  $success  True if user was successfully stored in the database
     * @param   string   $msg      Message
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterDelete($user, $success, $msg): void
    {
        $context = $this->getApplication()->getInput()->get('option');

        if (!$this->checkLoggable($context)) {
            return;
        }

        $messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED';

        $message = [
            'action' => 'delete',
            'type'   => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER',
            'id'     => $user['id'],
            'title'  => $user['name'],
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * On after save user group data logging method
     *
     * Method is called after user group is stored into the database
     *
     * @param   string   $context  The context
     * @param   Table    $table    DataBase Table object
     * @param   boolean  $isNew    Is new or not
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterSaveGroup($context, $table, $isNew): void
    {
        // Override context (com_users.group) with the component context (com_users) to pass the checkLoggable
        $context = $this->getApplication()->getInput()->get('option');

        if (!$this->checkLoggable($context)) {
            return;
        }

        if ($isNew) {
            $messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_ADDED';
            $action             = 'add';
        } else {
            $messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_UPDATED';
            $action             = 'update';
        }

        $message = [
            'action'   => $action,
            'type'     => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER_GROUP',
            'id'       => $table->id,
            'title'    => $table->title,
            'itemlink' => 'index.php?option=com_users&task=group.edit&id=' . $table->id,
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * On deleting user group data logging method
     *
     * Method is called after user group is deleted from the database
     *
     * @param   array    $group    Holds the group data
     * @param   boolean  $success  True if user was successfully stored in the database
     * @param   string   $msg      Message
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterDeleteGroup($group, $success, $msg): void
    {
        $context = $this->getApplication()->getInput()->get('option');

        if (!$this->checkLoggable($context)) {
            return;
        }

        $messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED';

        $message = [
            'action' => 'delete',
            'type'   => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER_GROUP',
            'id'     => $group['id'],
            'title'  => $group['title'],
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * Method to log user login success action
     *
     * @param   array  $options  Array holding options (user, responseType)
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterLogin($options)
    {
        if ($options['action'] === 'core.login.api') {
            return;
        }

        $context = 'com_users';

        if (!$this->checkLoggable($context)) {
            return;
        }

        $loggedInUser       = $options['user'];
        $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_USER_LOGGED_IN';

        $message = [
            'action'      => 'login',
            'userid'      => $loggedInUser->id,
            'username'    => $loggedInUser->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $loggedInUser->id,
            'app'         => 'PLG_ACTIONLOG_JOOMLA_APPLICATION_' . $this->getApplication()->getName(),
        ];

        $this->addLog([$message], $messageLanguageKey, $context, $loggedInUser->id);
    }

    /**
     * Method to log user login failed action
     *
     * @param   array  $response  Array of response data.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserLoginFailure($response)
    {
        $context = 'com_users';

        if (!$this->checkLoggable($context)) {
            return;
        }

        // Get the user id for the given username
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'username']))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('username') . ' = :username')
            ->bind(':username', $response['username']);
        $db->setQuery($query);

        try {
            $loggedInUser = $db->loadObject();
        } catch (ExecutionFailureException $e) {
            return;
        }

        // Not a valid user, return
        if (!isset($loggedInUser->id)) {
            return;
        }

        $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_USER_LOGIN_FAILED';

        $message = [
            'action'      => 'login',
            'id'          => $loggedInUser->id,
            'userid'      => $loggedInUser->id,
            'username'    => $loggedInUser->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $loggedInUser->id,
            'app'         => 'PLG_ACTIONLOG_JOOMLA_APPLICATION_' . $this->getApplication()->getName(),
        ];

        $this->addLog([$message], $messageLanguageKey, $context, $loggedInUser->id);
    }

    /**
     * Method to log user's logout action
     *
     * @param   array  $user     Holds the user data
     * @param   array  $options  Array holding options (remember, autoregister, group)
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserLogout($user, $options = [])
    {
        $context = 'com_users';

        if (!$this->checkLoggable($context)) {
            return;
        }

        $loggedOutUser = User::getInstance($user['id']);

        if ($loggedOutUser->block) {
            return;
        }

        $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_USER_LOGGED_OUT';

        $message = [
            'action'      => 'logout',
            'id'          => $loggedOutUser->id,
            'userid'      => $loggedOutUser->id,
            'username'    => $loggedOutUser->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $loggedOutUser->id,
            'app'         => 'PLG_ACTIONLOG_JOOMLA_APPLICATION_' . $this->getApplication()->getName(),
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * Function to check if a component is loggable or not
     *
     * @param   string  $extension  The extension that triggered the event
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    protected function checkLoggable($extension)
    {
        return in_array($extension, $this->loggableExtensions);
    }

    /**
     * On after Remind username request
     *
     * Method is called after user request to remind their username.
     *
     * @param   array  $user  Holds the user data.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterRemind($user)
    {
        $context = $this->getApplication()->getInput()->get('option');

        if (!$this->checkLoggable($context)) {
            return;
        }

        $message = [
            'action'      => 'remind',
            'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER',
            'id'          => $user->id,
            'title'       => $user->name,
            'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'userid'      => $user->id,
            'username'    => $user->name,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
        ];

        $this->addLog([$message], 'PLG_ACTIONLOG_JOOMLA_USER_REMIND', $context, $user->id);
    }

    /**
     * On after Check-in request
     *
     * Method is called after user request to check-in items.
     *
     * @param   array  $table  Holds the table name.
     *
     * @return  void
     *
     * @since   3.9.3
     */
    public function onAfterCheckin($table)
    {
        $context = 'com_checkin';
        $user    = Factory::getUser();

        if (!$this->checkLoggable($context)) {
            return;
        }

        $message = [
            'action'      => 'checkin',
            'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER',
            'id'          => $user->id,
            'title'       => $user->username,
            'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'userid'      => $user->id,
            'username'    => $user->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'table'       => str_replace($this->getDatabase()->getPrefix(), '#__', $table),
        ];

        $this->addLog([$message], 'PLG_ACTIONLOG_JOOMLA_USER_CHECKIN', $context, $user->id);
    }

    /**
     * On after log action purge
     *
     * Method is called after user request to clean action log items.
     *
     * @param   array  $group  Holds the group name.
     *
     * @return  void
     *
     * @since   3.9.4
     */
    public function onAfterLogPurge($group = '')
    {
        $context = $this->getApplication()->getInput()->get('option');
        $user    = Factory::getUser();
        $message = [
            'action'      => 'actionlogs',
            'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER',
            'id'          => $user->id,
            'title'       => $user->username,
            'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'userid'      => $user->id,
            'username'    => $user->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
        ];
        $this->addLog([$message], 'PLG_ACTIONLOG_JOOMLA_USER_LOG', $context, $user->id);
    }

    /**
     * On after log export
     *
     * Method is called after user request to export action log items.
     *
     * @param   array  $group  Holds the group name.
     *
     * @return  void
     *
     * @since   3.9.4
     */
    public function onAfterLogExport($group = '')
    {
        $context = $this->getApplication()->getInput()->get('option');
        $user    = Factory::getUser();
        $message = [
            'action'      => 'actionlogs',
            'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER',
            'id'          => $user->id,
            'title'       => $user->username,
            'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'userid'      => $user->id,
            'username'    => $user->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
        ];
        $this->addLog([$message], 'PLG_ACTIONLOG_JOOMLA_USER_LOGEXPORT', $context, $user->id);
    }

    /**
     * On after Cache purge
     *
     * Method is called after user request to clean cached items.
     *
     * @param   string  $group  Holds the group name.
     *
     * @return  void
     *
     * @since   3.9.4
     */
    public function onAfterPurge($group = 'all')
    {
        $context = $this->getApplication()->getInput()->get('option');
        $user    = Factory::getUser();

        if (!$this->checkLoggable($context)) {
            return;
        }

        $message = [
            'action'      => 'cache',
            'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER',
            'id'          => $user->id,
            'title'       => $user->username,
            'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'userid'      => $user->id,
            'username'    => $user->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'group'       => $group,
        ];
        $this->addLog([$message], 'PLG_ACTIONLOG_JOOMLA_USER_CACHE', $context, $user->id);
    }

    /**
     * On after Api dispatched
     *
     * Method is called after user perform an API request better on onAfterDispatch.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAfterDispatch()
    {
        if (!$this->getApplication()->isClient('api')) {
            return;
        }

        if ($this->loggableApi === 0) {
            return;
        }

        $verb = $this->getApplication()->getInput()->getMethod();

        if (!in_array($verb, $this->loggableVerbs)) {
            return;
        }

        $context = $this->getApplication()->getInput()->get('option');

        if (!$this->checkLoggable($context)) {
            return;
        }

        $user    = $this->getApplication()->getIdentity();
        $message = [
            'action'      => 'API',
            'verb'        => $verb,
            'username'    => $user->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'url'         => htmlspecialchars(urldecode($this->getApplication()->get('uri.route')), ENT_QUOTES, 'UTF-8'),
        ];
        $this->addLog([$message], 'PLG_ACTIONLOG_JOOMLA_API', $context, $user->id);
    }

    /**
     * On after CMS Update
     *
     * Method is called after user update the CMS.
     *
     * @param   string  $oldVersion  The Joomla version before the update
     *
     * @return  void
     *
     * @since   3.9.21
     */
    public function onJoomlaAfterUpdate($oldVersion = null)
    {
        $context = $this->getApplication()->getInput()->get('option');
        $user    = Factory::getUser();

        if (empty($oldVersion)) {
            $oldVersion = $this->getApplication()->getLanguage()->_('JLIB_UNKNOWN');
        }

        $message = [
            'action'      => 'joomlaupdate',
            'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER',
            'id'          => $user->id,
            'title'       => $user->username,
            'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'userid'      => $user->id,
            'username'    => $user->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'version'     => JVERSION,
            'oldversion'  => $oldVersion,
        ];
        $this->addLog([$message], 'PLG_ACTIONLOG_JOOMLA_USER_UPDATE', $context, $user->id);
    }

    /**
     * Returns the action log params for the given context.
     *
     * @param   string  $context  The context of the action log
     *
     * @return  stdClass  The params
     *
     * @since   4.2.0
     */
    private function getActionLogParams($context): ?stdClass
    {
        $component = $this->getApplication()->bootComponent('actionlogs');

        if (!$component instanceof MVCFactoryServiceInterface) {
            return null;
        }

        return $component->getMVCFactory()->createModel('ActionlogConfig', 'Administrator')->getLogContentTypeParams($context);
    }

    /**
     * On after Reset password request
     *
     * Method is called after user request to reset their password.
     *
     * @param   array  $user  Holds the user data.
     *
     * @return  void
     *
     * @since   4.2.9
     */
    public function onUserAfterResetRequest($user)
    {
        $context = $this->getApplication()->input->get('option');

        if (!$this->checkLoggable($context)) {
            return;
        }

        $message = [
            'action'      => 'reset',
            'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER',
            'id'          => $user->id,
            'title'       => $user->name,
            'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'userid'      => $user->id,
            'username'    => $user->name,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
        ];

        $this->addLog([$message], 'PLG_ACTIONLOG_JOOMLA_USER_RESET_REQUEST', $context, $user->id);
    }

    /**
     * On after Completed reset request
     *
     * Method is called after user complete the reset of their password.
     *
     * @param   array  $user  Holds the user data.
     *
     * @return  void
     *
     * @since   4.2.9
     */
    public function onUserAfterResetComplete($user)
    {
        $context = $this->getApplication()->input->get('option');

        if (!$this->checkLoggable($context)) {
            return;
        }

        $message = [
            'action'      => 'complete',
            'type'        => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER',
            'id'          => $user->id,
            'title'       => $user->name,
            'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'userid'      => $user->id,
            'username'    => $user->name,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
        ];

        $this->addLog([$message], 'PLG_ACTIONLOG_JOOMLA_USER_RESET_COMPLETE', $context, $user->id);
    }
}

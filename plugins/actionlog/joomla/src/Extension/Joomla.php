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
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Event\Application;
use Joomla\CMS\Event\Cache;
use Joomla\CMS\Event\Checkin;
use Joomla\CMS\Event\Extension;
use Joomla\CMS\Event\Model;
use Joomla\CMS\Event\User;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Component\Actionlogs\Administrator\Plugin\ActionLogPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Users Actions Logging Plugin.
 *
 * @since  3.9.0
 */
final class Joomla extends ActionLogPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

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
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentAfterSave'        => 'onContentAfterSave',
            'onContentAfterDelete'      => 'onContentAfterDelete',
            'onContentChangeState'      => 'onContentChangeState',
            'onApplicationAfterSave'    => 'onApplicationAfterSave',
            'onExtensionAfterInstall'   => 'onExtensionAfterInstall',
            'onExtensionAfterUninstall' => 'onExtensionAfterUninstall',
            'onExtensionAfterUpdate'    => 'onExtensionAfterUpdate',
            'onExtensionAfterSave'      => 'onExtensionAfterSave',
            'onExtensionAfterDelete'    => 'onExtensionAfterDelete',
            'onUserAfterSave'           => 'onUserAfterSave',
            'onUserAfterDelete'         => 'onUserAfterDelete',
            'onUserAfterSaveGroup'      => 'onUserAfterSaveGroup',
            'onUserAfterDeleteGroup'    => 'onUserAfterDeleteGroup',
            'onUserAfterLogin'          => 'onUserAfterLogin',
            'onUserLoginFailure'        => 'onUserLoginFailure',
            'onUserLogout'              => 'onUserLogout',
            'onUserAfterRemind'         => 'onUserAfterRemind',
            'onAfterCheckin'            => 'onAfterCheckin',
            'onAfterLogPurge'           => 'onAfterLogPurge',
            'onAfterLogExport'          => 'onAfterLogExport',
            'onAfterPurge'              => 'onAfterPurge',
            'onAfterDispatch'           => 'onAfterDispatch',
            'onJoomlaAfterUpdate'       => 'onJoomlaAfterUpdate',
            'onUserAfterResetRequest'   => 'onUserAfterResetRequest',
            'onUserAfterResetComplete'  => 'onUserAfterResetComplete',
            'onUserBeforeSave'          => 'onUserBeforeSave',
            'onBeforeTourSaveUserState' => 'onBeforeTourSaveUserState',
        ];
    }

    /**
     * After save content logging method
     * This method adds a record to #__action_logs contains (message, date, context, user)
     * Method is called right after the content is saved
     *
     * @param   Model\AfterSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onContentAfterSave(Model\AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $article = $event->getItem();
        $isNew   = $event->getIsNew();

        if (isset($this->contextAliases[$context])) {
            $context = $this->contextAliases[$context];
        }

        $params = $this->getActionLogParams($context);

        // Not found a valid content type, don't process further
        if ($params === null) {
            return;
        }

        [$option, $contentType] = explode('.', $params->type_alias);

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

        $id = empty($params->id_holder) ? 0 : $article->{$params->id_holder};

        $message = [
            'action'   => $isNew ? 'add' : 'update',
            'type'     => $params->text_prefix . '_TYPE_' . $params->type_title,
            'id'       => $id,
            'title'    => $article->{$params->title_holder} ?? '',
            'itemlink' => ActionlogsHelper::getContentTypeLink($option, $contentType, $id, $params->id_holder, $article),
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * After delete content logging method
     * This method adds a record to #__action_logs contains (message, date, context, user)
     * Method is called right after the content is deleted
     *
     * @param   Model\AfterDeleteEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onContentAfterDelete(Model\AfterDeleteEvent $event): void
    {
        $context = $event->getContext();
        $article = $event->getItem();
        $option  = $this->getApplication()->getInput()->get('option');

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

        $id = empty($params->id_holder) ? 0 : $article->{$params->id_holder};

        $message = [
            'action' => 'delete',
            'type'   => $params->text_prefix . '_TYPE_' . $params->type_title,
            'id'     => $id,
            'title'  => $article->{$params->title_holder} ?? '',
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * On content change status logging method
     * This method adds a record to #__action_logs contains (message, date, context, user)
     * Method is called when the status of the article is changed
     *
     * @param   Model\AfterChangeStateEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onContentChangeState(Model\AfterChangeStateEvent $event): void
    {
        $context = $event->getContext();
        $pks     = $event->getPks();
        $value   = $event->getValue();
        $option  = $this->getApplication()->getInput()->getCmd('option');

        if (!$this->checkLoggable($option)) {
            return;
        }

        $params = $this->getActionLogParams($context);

        // Not found a valid content type, don't process further
        if ($params === null) {
            return;
        }

        [, $contentType] = explode('.', $params->type_alias);

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
        } catch (\RuntimeException) {
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
     * On Saving application configuration logging method.
     * Method is called when the application config is being saved
     *
     * @param   Application\AfterSaveConfigurationEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onApplicationAfterSave(Application\AfterSaveConfigurationEvent $event): void
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
     * @param   Extension\AfterInstallEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onExtensionAfterInstall(Extension\AfterInstallEvent $event): void
    {
        $installer = $event->getInstaller();
        $eid       = $event->getEid();
        $context   = $this->getApplication()->getInput()->get('option');

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
     * @param   Extension\AfterUninstallEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onExtensionAfterUninstall(Extension\AfterUninstallEvent $event): void
    {
        $installer = $event->getInstaller();
        $eid       = $event->getEid();
        $result    = $event->getRemoved();
        $context   = $this->getApplication()->getInput()->get('option');

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
     * @param   Extension\AfterUpdateEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onExtensionAfterUpdate(Extension\AfterUpdateEvent $event): void
    {
        $installer = $event->getInstaller();
        $eid       = $event->getEid();
        $context   = $this->getApplication()->getInput()->get('option');

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
     * On Saving extensions logging method.
     * Method is called when an extension is being saved
     *
     * @param   Model\AfterSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onExtensionAfterSave(Model\AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $table   = $event->getItem();
        $isNew   = $event->getIsNew();

        [$option] = explode('.', $context);

        if (!$this->checkLoggable($option)) {
            return;
        }

        $params = $this->getActionLogParams($context);

        // Not found a valid content type, don't process further
        if ($params === null) {
            return;
        }

        [, $contentType] = explode('.', $params->type_alias);

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

        $id_holder    = $params->id_holder;
        $title_holder = $params->title_holder;
        $message      = [
            'action'         => $isNew ? 'add' : 'update',
            'type'           => 'PLG_ACTIONLOG_JOOMLA_TYPE_' . $params->type_title,
            'id'             => $table->$id_holder,
            'title'          => $table->$title_holder,
            'extension_name' => $table->$title_holder,
            'itemlink'       => ActionlogsHelper::getContentTypeLink($option, $contentType, $table->$id_holder, $id_holder),
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * On Deleting extensions logging method.
     * Method is called when an extension is being deleted
     *
     * @param   Model\AfterDeleteEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onExtensionAfterDelete(Model\AfterDeleteEvent $event): void
    {
        $context = $event->getContext();
        $table   = $event->getItem();

        if (!$this->checkLoggable($this->getApplication()->getInput()->get('option'))) {
            return;
        }

        $params = $this->getActionLogParams($context);

        // Not found a valid content type, don't process further
        if ($params === null) {
            return;
        }

        $messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED';

        $title_holder = $params->title_holder;
        $message      = [
            'action' => 'delete',
            'type'   => 'PLG_ACTIONLOG_JOOMLA_TYPE_' . $params->type_title,
            'title'  => $table->$title_holder,
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * On saving user data logging method
     *
     * Method is called after user data is stored in the database.
     * This method logs who created/edited any user's data
     *
     * @param   User\AfterSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterSave(User\AfterSaveEvent $event): void
    {
        $user  = $event->getUser();
        $isnew = $event->getIsNew();

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

        $jUser = $this->getApplication()->getIdentity();

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

        // Check if block / unblock comes from Actions on list
        if ($task === 'block' || $task === 'unblock') {
            $messageLanguageKey = $task === 'block' ? 'PLG_ACTIONLOG_JOOMLA_USER_BLOCK' : 'PLG_ACTIONLOG_JOOMLA_USER_UNBLOCK';
            $message['action']  = $task;
        }

        $this->addLog([$message], $messageLanguageKey, $context, $userId);

        // Check if on save a block / unblock has changed
        if ($action === 'update') {
            $session = $this->getApplication()->getSession();
            $data    = $session->get('block', null);

            if ($data !== null) {
                $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_USER_UNBLOCK';
                $action             = 'unblock';
                if ($data === 'block') {
                    $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_USER_BLOCK';
                    $action             = 'block';
                }

                $message['action'] = $action;
                $this->addLog([$message], $messageLanguageKey, $context, $userId);
            }
        }
    }

    /**
     * On deleting user data logging method
     *
     * Method is called after user data is deleted from the database
     *
     * @param   User\AfterDeleteEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterDelete(User\AfterDeleteEvent $event): void
    {
        $user    = $event->getUser();
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
     * @param   Model\AfterSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterSaveGroup(Model\AfterSaveEvent $event): void
    {
        $table = $event->getItem();
        $isNew = $event->getIsNew();

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
     * @param   Model\AfterDeleteEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterDeleteGroup(Model\AfterDeleteEvent $event): void
    {
        $group   = $event->getItem();
        $context = $this->getApplication()->getInput()->get('option');

        if (!$this->checkLoggable($context)) {
            return;
        }

        $messageLanguageKey = 'PLG_SYSTEM_ACTIONLOGS_CONTENT_DELETED';

        $message = [
            'action' => 'delete',
            'type'   => 'PLG_ACTIONLOG_JOOMLA_TYPE_USER_GROUP',
            'id'     => $group->id,
            'title'  => $group->title,
        ];

        $this->addLog([$message], $messageLanguageKey, $context);
    }

    /**
     * Method to log user login success action
     *
     * @param   User\AfterLoginEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterLogin(User\AfterLoginEvent $event): void
    {
        $options = $event->getOptions();

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
     * @param   User\LoginFailureEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserLoginFailure(User\LoginFailureEvent $event): void
    {
        $response = $event->getAuthenticationResponse();
        $context  = 'com_users';

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
        } catch (ExecutionFailureException) {
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
     * @param   User\LogoutEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserLogout(User\LogoutEvent $event): void
    {
        $user    = $event->getParameters();
        $context = 'com_users';

        if (!$this->checkLoggable($context)) {
            return;
        }

        $loggedOutUser = $this->getUserFactory()->loadUserById($user['id']);

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
        return \in_array($extension, $this->loggableExtensions);
    }

    /**
     * On after Remind username request
     *
     * Method is called after user request to remind their username.
     *
     * @param   User\AfterRemindEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterRemind(User\AfterRemindEvent $event): void
    {
        $user    = $event->getUser();
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
     * @param   Checkin\AfterCheckinEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.3
     */
    public function onAfterCheckin(Checkin\AfterCheckinEvent $event): void
    {
        $table   = $event->getTableName();
        $context = 'com_checkin';
        $user    = $this->getApplication()->getIdentity();

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
     * @return  void
     *
     * @since   3.9.4
     */
    public function onAfterLogPurge(): void
    {
        $context = $this->getApplication()->getInput()->get('option');
        $user    = $this->getApplication()->getIdentity();
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
     * @return  void
     *
     * @since   3.9.4
     */
    public function onAfterLogExport(): void
    {
        $context = $this->getApplication()->getInput()->get('option');
        $user    = $this->getApplication()->getIdentity();
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
     * @param   Cache\AfterPurgeEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.4
     */
    public function onAfterPurge(Cache\AfterPurgeEvent $event): void
    {
        $group   = $event->getGroup() ?: 'all';
        $context = $this->getApplication()->getInput()->get('option');
        $user    = $this->getApplication()->getIdentity();

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
    public function onAfterDispatch(): void
    {
        if (!$this->getApplication()->isClient('api')) {
            return;
        }

        if ($this->loggableApi === 0) {
            return;
        }

        $verb = $this->getApplication()->getInput()->getMethod();

        if (!\in_array($verb, $this->loggableVerbs)) {
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
     * @param   Event $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.21
     *
     * @TODO: Update to use a real event class
     */
    public function onJoomlaAfterUpdate(Event $event): void
    {
        $arguments  = array_values($event->getArguments());
        $oldVersion = $arguments[0] ?? '';

        $context = $this->getApplication()->getInput()->get('option');
        $user    = $this->getApplication()->getIdentity();

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
     * @return  ?\stdClass  The params
     *
     * @since   4.2.0
     */
    private function getActionLogParams($context): ?\stdClass
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
     * @param   User\AfterResetRequestEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   4.2.9
     */
    public function onUserAfterResetRequest(User\AfterResetRequestEvent $event): void
    {
        $user    = $event->getUser();
        $context = $this->getApplication()->getInput()->get('option');

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
     * @param   User\AfterResetCompleteEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   4.2.9
     */
    public function onUserAfterResetComplete(User\AfterResetCompleteEvent $event)
    {
        $user    = $event->getUser();
        $context = $this->getApplication()->getInput()->get('option');

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

    /**
     * Method is called before user data is stored in the database
     *
     * @param   User\BeforeSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function onUserBeforeSave(User\BeforeSaveEvent $event): void
    {
        $user = $event->getUser();
        $new  = $event->getData();

        $session = $this->getApplication()->getSession();
        $session->set('block', null);

        if ($user['block'] !== (int) $new['block']) {
            $blockunblock = $new['block'] === '1' ? 'block' : 'unblock';
            $session->set('block', $blockunblock);
        }
    }

    /**
     * Method is called when a user cancels, completes or skips a tour
     *
     * @param   AbstractEvent $event The event instance.
     *
     * @return  void
     *
     * @since  5.2.0
     */
    public function onBeforeTourSaveUserState(AbstractEvent $event): void
    {
        $option = $this->getApplication()->getInput()->get('option');

        if (!$this->checkLoggable($option)) {
            return;
        }

        $tourId     = $event->getArgument('tourId');
        $state      = $event->getArgument('actionState');
        $stepNumber = $event->getArgument('stepNumber');

        switch ($state) {
            case 'skipped':
                $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_GUIDEDTOURS_TOURSKIPPED';
                break;
            case 'completed':
                $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_GUIDEDTOURS_TOURCOMPLETED';
                break;
            default:
                $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_GUIDEDTOURS_TOURDELAYED';
        }

        // Get the tour from the model to fetch the translated title of the tour
        $factory   = $this->getApplication()->bootComponent('com_guidedtours')->getMVCFactory();
        $tourModel = $factory->createModel(
            'Tour',
            'Administrator',
            ['ignore_request' => true]
        );

        $tour = $tourModel->getItem($tourId);

        $message = [
            'id'    => $tourId,
            'title' => $tour->title_translation,
            'state' => $state,
            'step'  => $stepNumber,
        ];

        $this->addLog([$message], $messageLanguageKey, 'com_guidedtours.state');
    }
}

<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\User\BeforeDelete\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\Folder;
use Joomla\Plugin\User\BeforeDelete\BeforeDeleteUserInterface;
use Joomla\Registry\Registry;
use RuntimeException;

/**
 * Guided Tours plugin to add interactive tours to the administrator interface.
 *
 * @since  __DEPLOY_VERSION__
 */
final class BeforeDeleteUser extends CMSPlugin implements SubscriberInterface
{
    /**
     * Global database object
     *
     * @var    DatabaseDriver
     * @since  __DEPLOY_VERSION__
     */
    protected $db = null;

    /**
     * Global application object
     *
     * @var     CMSApplication
     * @since  __DEPLOY_VERSION__
     */
    protected $app = null;

    /**
     * Load the language file on instantiation.
     *
     * @var     boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Array of extensions class
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private static $extensions = array();

    /**
     * Array of extensions class
     *
     * @var    Registry
     * @since  __DEPLOY_VERSION__
     */
    private static $componentParams = null;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface   $dispatcher   The dispatcher
     * @param   array                 $config       An optional associative array of configuration settings
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(DispatcherInterface $dispatcher, array $config)
    {
        parent::__construct($dispatcher, $config);

        self::$componentParams = ComponentHelper::getParams('com_users');
    }

    /**
     * function for getSubscribedEvents : new Joomla 4 feature
     *
     * @return array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents()
    : array
    {
        return [
            'onUserBeforeDelete'    => 'onUserBeforeDelete',
        ];
    }

    /**
     * Checks if a user exists.
     *
     * @param   int  $userId
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private function isUserExists($userId)
    {
        $userTable = Table::getInstance('user');

        return $userTable->load((int) $userId) === true;
    }

    /**
     * Event triggered before the user is deleted.
     *
     * @param   Event  $event
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onUserBeforeDelete(Event $event)
    {
        /** @var   array $user The user to be altered. */
        [$user] = $event->getArguments();

        $this->validateFallbackUser($user['id']);

        $this->changeUser($user);
    }

    /**
     * Check if the fallback user is set and should not be deleted.
     *
     * @param   int  $userIdToDelete
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function validateFallbackUser($userIdToDelete)
    {
        $componentParams = self::$componentParams;
        $fallbackUserId  = $componentParams->get('fallbackUserIdOnDelete');
        $tabName         = Text::_('COM_USERS_CONFIG_BEFORE_DELETE_USER');
        $tabId           = strtolower(str_replace(' ', '_', $tabName));
        $error           = false;

        if (empty($fallbackUserId)) {
            $this->app->enqueueMessage(
                Text::sprintf(
                    'PLG_USER_BEFOREDELETE_ERROR_FALLBACK_USER_NOT_SET_MSG',
                    $tabName
                ),
                'warning'
            );

            $error = true;
        }

        if (!$error && $userIdToDelete == $fallbackUserId) {
            $this->app->enqueueMessage(
                Text::sprintf(
                    'PLG_USER_BEFOREDELETE_ERROR_FALLBACK_USER_CONNECTED_MSG',
                    $tabName
                ),
                'warning'
            );

            $error = true;
        }

        if (!$error && !$this->isUserExists($fallbackUserId)) {
            $this->app->enqueueMessage(
                Text::sprintf(
                    'PLG_USER_BEFOREDELETE_ERROR_FALLBACK_USER_ID_NOT_EXISTS_MSG',
                    $fallbackUserId,
                    $tabName
                ),
                'warning'
            );

            $error = true;
        }

        if ($error) {
            $this->app->enqueueMessage(
                Text::_('PLG_USER_BEFOREDELETE_ERROR_USER_NOT_DELETED_MSG'),
                'error'
            );

            $url = Route::_('/administrator/index.php?option=com_config&view=component&component=com_users#' . $tabId);
            $this->app->redirect($url, 200);
        }
    }

    /**
     * Changes the user in all registered extensions before deleting them.
     *
     * @param   array  $user
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function changeUser($user)
    {
        $userId                = $user['id'];
        $aliasName             = $user['name'];
        $componentParams       = self::$componentParams;
        $fallbackUserId        = $componentParams->get('fallbackUserIdOnDelete');
        $setAliasOnDelete      = $componentParams->get('setAliasOnDelete', '1');
        $overrideAliasOnDelete = $componentParams->get('overrideAliasOnDelete', '0');

        if (empty($extensions = $this->getExtensionClass())) {
            // TODO: Add error handling and/or message and return it
            return;
        }

        foreach ($extensions as $extensionBaseContext => $extensionClass) {
            /** @var BeforeDeleteUserInterface $extensionClass */
            $columsToChangeUserId = $extensionClass->getColumsToChange();

            foreach ($columsToChangeUserId as $table) {
                $tableName       = $table['tableName'] ?? false;
                $uniqueId        = $table['uniqueId'] ?? false;
                $authorColumn    = $table['author'] ?? false;
                $aliasColumn     = $table['alias'] ?? false;
                $aliasChanged    = false;
                $infoAuthorAlias = '';

                if ($tableName && $authorColumn) {
                    $selectQuery = $this->db->getQuery(true);

                    $selectQuery->select($this->db->quoteName($uniqueId))
                        ->from($tableName)
                        ->where(
                            $this->db->quoteName($authorColumn) . ' = ' . $this->db->quote((int) $userId)
                        )
                        ->set('FOR UPDATE');

                    $updateQuery = $this->db->getQuery(true);

                    $updateQuery->update($this->db->quoteName($tableName))
                        ->set($this->db->quoteName($authorColumn) . ' = ' . $this->db->quote((int) $fallbackUserId));

                    if ($setAliasOnDelete && $aliasColumn) {
                        if ($overrideAliasOnDelete) {
                            $aliasChanged = true;

                            $updateQuery->set(
                                $this->db->quoteName($aliasColumn) . ' = ' . $this->db->quote($aliasName)
                            );
                        } else {
                            $updateQuery->set(
                                $this->db->quoteName($aliasColumn)
                                . ' = CASE WHEN ' . $this->db->quoteName($aliasColumn) . ' IS NULL'
                                . ' OR CHAR_LENGTH(TRIM(' . $this->db->quoteName($aliasColumn) . ')) = 0'
                                . ' THEN '. $this->db->quote($aliasName)
                                . ' ELSE ' . $this->db->quoteName($aliasColumn)
                                . ' END'
                            );
                        }
                    }

                    $updateQuery->where(
                        $this->db->quoteName($authorColumn) . ' = ' . $this->db->quote((int) $userId)
                    );
                }

                try {
                    // Get the entries to update.
                    $selectResult = $this->db->setQuery($selectQuery)->loadColumn();

                    if (!empty($selectResult)) {
                        // Update the entries found.
                        $this->db->setQuery($updateQuery)->execute();

                        $elementList = implode(', ', $selectResult);

                        if ($setAliasOnDelete && $aliasColumn) {
                            if ($aliasChanged) {
                                $infoAuthorAlias = Text::sprintf(
                                    'PLG_USER_BEFOREDELETE_USER_CHANGED_FALLBACK_ALIAS_MSG',
                                    $aliasName
                                );
                            } else {
                                $infoAuthorAlias = Text::sprintf(
                                    'PLG_USER_BEFOREDELETE_USER_CHANGED_FALLBACK_ALIAS_IF_NOT_EMPTY_MSG',
                                    $aliasName
                                );
                            }
                        }

                        // Load extension language files
                        $this->app->getLanguage()->load($extensionBaseContext);
                        $this->app->getLanguage()->load($extensionBaseContext . '.sys');

                        $this->app->enqueueMessage(
                            Text::sprintf(
                                'PLG_USER_BEFOREDELETE_USER_DELETED_MSG',
                                Text::_($extensionClass->getExtensionRealNameLanguageString()),
                                $elementList,
                                (int) $userId,
                                (int) $fallbackUserId,
                                $infoAuthorAlias
                            ),
                            'info'
                        );
                    }
                } catch (RuntimeException $e) {
                    $this->app->enqueueMessage(
                        Text::_('PLG_USER_BEFOREDELETE_ERROR_USER_NOT_DELETED_MSG'),
                        'error'
                    );

                    $this->app->enqueueMessage(
                        $e->getMessage(),
                        'error'
                    );

                    $url = Uri::getInstance()->toString(array('path', 'query', 'fragment'));
                    $this->app->redirect($url, 500);
                }
            }
        }
    }

    /**
     * Initialize the extensions class array.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function initExtensions()
    {
        $extensionList = Folder::folders(JPATH_PLUGINS . '/beforedeleteuser');

        foreach ($extensionList as $extension) {
            /** @var BeforeDeleteUserInterface|null $extensionClass */
            $extensionClass = $this->loadExtensionClass($extension);

            if (!$extensionClass instanceof BeforeDeleteUserInterface) {
                continue;
            }

            // Get the extension base context.
            if (empty($extensionBaseContext = $extensionClass->getExtensionBaseContext())) {
                continue;
            }

            if (!isset(self::$extensions[$extensionBaseContext])) {
                self::$extensions[$extensionBaseContext] = $extensionClass;
            }
        }
    }

    /**
     * Load the extension class.
     *
     * @param   string  $extensionName
     *
     * @return  BeforeDeleteUserInterface|void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function loadExtensionClass($extensionName)
    {
        $error = false;

        if (PluginHelper::isEnabled('beforedeleteuser', $extensionName) === false) {
            return;
        }

        try {
            /** @var BeforeDeleteUserInterface $extensionClass */
            $extensionClass = Factory::getApplication()->bootPlugin($extensionName, 'beforedeleteuser');
        } catch (\Throwable $e) {
            $error = true;
        }

        if ($error) {
            $this->app->enqueueMessage(
                Text::sprintf(
                    'PLG_USER_BEFOREDELETE_ERROR_LOADING_CLASS_MSG',
                    Text::_('PLG_USER_BEFOREDELETE'),
                    ucfirst($extensionName),
                    JPATH_PLUGINS . '/beforedeleteuser/' . $extensionName,
                    $e->getFile(),
                    $e->getLine()
                ),
                'error'
            );

            return;
        }

        return $extensionClass;
    }

    /**
     * Get the extension class by the context.
     *
     * @param   string  $context
     *
     * @return  BeforeDeleteUserInterface|array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getExtensionClass($context = null)
    {
        if (empty(self::$extensions)) {
            $this->initExtensions();
        }

        if (is_null($context)) {
            return self::$extensions;
        }

        list($extensionBaseContext, $unused) = explode('.', $context, 2);

        return self::$extensions[$extensionBaseContext] ?? array();
    }
}

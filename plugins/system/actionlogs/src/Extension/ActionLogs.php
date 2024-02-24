<?php

/**
 * @package     Joomla.Plugins
 * @subpackage  System.actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\ActionLogs\Extension;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Users Actions Logging Plugin.
 *
 * @since  3.9.0
 */
final class ActionLogs extends CMSPlugin
{
    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher   The dispatcher
     * @param   array                $config       An optional associative array of configuration settings
     *
     * @since   3.9.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config)
    {
        parent::__construct($dispatcher, $config);

        // Import actionlog plugin group so that these plugins will be triggered for events
        PluginHelper::importPlugin('actionlog');
    }

    /**
     * Listener for the `onAfterInitialise` event
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAfterInitialise()
    {
        // Load plugin language files.
        $this->loadLanguage();
    }

    /**
     * Adds additional fields to the user editing form for logs e-mail notifications
     *
     * @param   Form   $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  boolean
     *
     * @since   3.9.0
     *
     * @throws  \Exception
     */
    public function onContentPrepareForm(Form $form, $data)
    {
        $formName = $form->getName();

        $allowedFormNames = [
            'com_users.profile',
            'com_users.user',
        ];

        if (!in_array($formName, $allowedFormNames, true)) {
            return true;
        }

        /**
         * We only allow users who have Super User permission to change this setting for themselves or for other
         * users who have the same Super User permission
         */
        $user = $this->getApplication()->getIdentity();

        if (!$user || !$user->authorise('core.admin')) {
            return true;
        }

        // If we are on the save command, no data is passed to $data variable, we need to get it directly from request
        $jformData = $this->getApplication()->getInput()->get('jform', [], 'array');

        if ($jformData && !$data) {
            $data = $jformData;
        }

        if (is_array($data)) {
            $data = (object) $data;
        }

        if (empty($data->id) || !$this->getUserFactory()->loadUserById($data->id)->authorise('core.admin')) {
            return true;
        }

        Form::addFormPath(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms');

        if ((!PluginHelper::isEnabled('actionlog', 'joomla')) && ($this->getApplication()->isClient('administrator'))) {
            $form->loadFile('information', false);

            return true;
        }

        if (!PluginHelper::isEnabled('actionlog', 'joomla')) {
            return true;
        }

        $form->loadFile('actionlogs', false);

        return true;
    }

    /**
     * Runs on content preparation
     *
     * @param   string  $context  The context for the data
     * @param   object  $data     An object containing the data for the form.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function onContentPrepareData($context, $data)
    {
        if (!in_array($context, ['com_users.profile', 'com_users.user'])) {
            return true;
        }

        if (is_array($data)) {
            $data = (object) $data;
        }

        if (!$this->getUserFactory()->loadUserById($data->id)->authorise('core.admin')) {
            return true;
        }

        $db = $this->getDatabase();
        $id = (int) $data->id;

        $query = $db->getQuery(true)
            ->select($db->quoteName(['notify', 'extensions']))
            ->from($db->quoteName('#__action_logs_users'))
            ->where($db->quoteName('user_id') . ' = :userid')
            ->bind(':userid', $id, ParameterType::INTEGER);

        try {
            $values = $db->setQuery($query)->loadObject();
        } catch (ExecutionFailureException $e) {
            return false;
        }

        if (!$values) {
            return true;
        }

        $data->actionlogs                       = new \stdClass();
        $data->actionlogs->actionlogsNotify     = $values->notify;
        $data->actionlogs->actionlogsExtensions = $values->extensions;

        if (!HTMLHelper::isRegistered('users.actionlogsNotify')) {
            HTMLHelper::register('users.actionlogsNotify', [__CLASS__, 'renderActionlogsNotify']);
        }

        if (!HTMLHelper::isRegistered('users.actionlogsExtensions')) {
            HTMLHelper::register('users.actionlogsExtensions', [__CLASS__, 'renderActionlogsExtensions']);
        }

        return true;
    }

    /**
     * Runs after the HTTP response has been sent to the client and delete log records older than certain days
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onAfterRespond()
    {
        $daysToDeleteAfter = (int) $this->params->get('logDeletePeriod', 0);

        if ($daysToDeleteAfter <= 0) {
            return;
        }

        // The delete frequency will be once per day
        $deleteFrequency = 3600 * 24;

        // Do we need to run? Compare the last run timestamp stored in the plugin's options with the current
        // timestamp. If the difference is greater than the cache timeout we shall not execute again.
        $now  = time();
        $last = (int) $this->params->get('lastrun', 0);

        if (abs($now - $last) < $deleteFrequency) {
            return;
        }

        // Update last run status
        $this->params->set('lastrun', $now);

        $db     = $this->getDatabase();
        $params = $this->params->toString('JSON');
        $query  = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = :params')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('actionlogs'))
            ->bind(':params', $params);

        try {
            // Lock the tables to prevent multiple plugin executions causing a race condition
            $db->lockTable('#__extensions');
        } catch (\Exception $e) {
            // If we can't lock the tables it's too risky to continue execution
            return;
        }

        try {
            // Update the plugin parameters
            $result = $db->setQuery($query)->execute();

            $this->clearCacheGroups(['com_plugins'], [0, 1]);
        } catch (\Exception $exc) {
            // If we failed to execute
            $db->unlockTables();
            $result = false;
        }

        try {
            // Unlock the tables after writing
            $db->unlockTables();
        } catch (\Exception $e) {
            // If we can't lock the tables assume we have somehow failed
            $result = false;
        }

        // Stop on failure
        if (!$result) {
            return;
        }

        $daysToDeleteAfter = (int) $this->params->get('logDeletePeriod', 0);
        $now               = Factory::getDate()->toSql();

        if ($daysToDeleteAfter > 0) {
            $days = -1 * $daysToDeleteAfter;

            $query->clear()
                ->delete($db->quoteName('#__action_logs'))
                ->where($db->quoteName('log_date') . ' < ' . $query->dateAdd($db->quote($now), $days, 'DAY'));

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                // Ignore it
                return;
            }
        }
    }

    /**
     * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
     *
     * @param   array  $clearGroups   The cache groups to clean
     * @param   array  $cacheClients  The cache clients (site, admin) to clean
     *
     * @return  void
     *
     * @since   3.9.0
     */
    private function clearCacheGroups(array $clearGroups, array $cacheClients = [0, 1])
    {
        foreach ($clearGroups as $group) {
            foreach ($cacheClients as $clientId) {
                try {
                    $options = [
                        'defaultgroup' => $group,
                        'cachebase'    => $clientId ? JPATH_ADMINISTRATOR . '/cache' :
                            $this->getApplication()->get('cache_path', JPATH_SITE . '/cache'),
                    ];

                    $cache = Cache::getInstance('callback', $options);
                    $cache->clean();
                } catch (\Exception $e) {
                    // Ignore it
                }
            }
        }
    }

    /**
     * Utility method to act on a user after it has been saved.
     *
     * @param   array    $user     Holds the new user data.
     * @param   boolean  $isNew    True if a new user is stored.
     * @param   boolean  $success  True if user was successfully stored in the database.
     * @param   string   $msg      Message.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterSave($user, $isNew, $success, $msg): void
    {
        if (!$success) {
            return;
        }

        // Clear access rights in case user groups were changed.
        $userObject = $this->getUserFactory()->loadUserById($user['id']);
        $userObject->clearAccessRights();

        $authorised = $userObject->authorise('core.admin');
        $userid     = (int) $user['id'];
        $db         = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__action_logs_users'))
            ->where($db->quoteName('user_id') . ' = :userid')
            ->bind(':userid', $userid, ParameterType::INTEGER);

        try {
            $exists = (bool) $db->setQuery($query)->loadResult();
        } catch (ExecutionFailureException $e) {
            return;
        }

        $query->clear();

        // If preferences don't exist, insert.
        if (!$exists && $authorised && isset($user['actionlogs'])) {
            $notify  = (int) $user['actionlogs']['actionlogsNotify'];
            $values  = [':userid', ':notify'];
            $bind    = [$userid, $notify];
            $columns = ['user_id', 'notify'];

            $query->bind($values, $bind, ParameterType::INTEGER);

            if (isset($user['actionlogs']['actionlogsExtensions'])) {
                $values[]  = ':extension';
                $columns[] = 'extensions';
                $extension = json_encode($user['actionlogs']['actionlogsExtensions']);
                $query->bind(':extension', $extension);
            }

            $query->insert($db->quoteName('#__action_logs_users'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
        } elseif ($exists && $authorised && isset($user['actionlogs'])) {
            // Update preferences.
            $notify = (int) $user['actionlogs']['actionlogsNotify'];
            $values = [$db->quoteName('notify') . ' = :notify'];

            $query->bind(':notify', $notify, ParameterType::INTEGER);

            if (isset($user['actionlogs']['actionlogsExtensions'])) {
                $values[]  = $db->quoteName('extensions') . ' = :extension';
                $extension = json_encode($user['actionlogs']['actionlogsExtensions']);
                $query->bind(':extension', $extension);
            }

            $query->update($db->quoteName('#__action_logs_users'))
                ->set($values)
                ->where($db->quoteName('user_id') . ' = :userid')
                ->bind(':userid', $userid, ParameterType::INTEGER);
        } elseif ($exists && !$authorised) {
            // Remove preferences if user is not authorised.
            $query->delete($db->quoteName('#__action_logs_users'))
                ->where($db->quoteName('user_id') . ' = :userid')
                ->bind(':userid', $userid, ParameterType::INTEGER);
        } else {
            return;
        }

        try {
            $db->setQuery($query)->execute();
        } catch (ExecutionFailureException $e) {
            // Do nothing.
        }
    }

    /**
     * Removes user preferences
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
        if (!$success) {
            return;
        }

        $db     = $this->getDatabase();
        $userid = (int) $user['id'];

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__action_logs_users'))
            ->where($db->quoteName('user_id') . ' = :userid')
            ->bind(':userid', $userid, ParameterType::INTEGER);

        try {
            $db->setQuery($query)->execute();
        } catch (ExecutionFailureException $e) {
            // Do nothing.
        }
    }

    /**
     * Method to render a value.
     *
     * @param   integer|string  $value  The value (0 or 1).
     *
     * @return  string  The rendered value.
     *
     * @since   3.9.16
     */
    public static function renderActionlogsNotify($value)
    {
        return Text::_($value ? 'JYES' : 'JNO');
    }

    /**
     * Method to render a list of extensions.
     *
     * @param   array|string  $extensions  Array of extensions or an empty string if none selected.
     *
     * @return  string  The rendered value.
     *
     * @since   3.9.16
     */
    public static function renderActionlogsExtensions($extensions)
    {
        // No extensions selected.
        if (!$extensions) {
            return Text::_('JNONE');
        }

        foreach ($extensions as &$extension) {
            // Load extension language files and translate extension name.
            ActionlogsHelper::loadTranslationFiles($extension);
            $extension = Text::_($extension);
        }

        return implode(', ', $extensions);
    }
}

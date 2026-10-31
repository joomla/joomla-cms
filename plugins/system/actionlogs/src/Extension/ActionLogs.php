<?php

/**
 * @package     Joomla.Plugins
 * @subpackage  System.actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\ActionLogs\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Application\AfterInitialiseEvent;
use Joomla\CMS\Event\Model;
use Joomla\CMS\Event\User;
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
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Users Actions Logging Plugin.
 *
 * @since  3.9.0
 */
final class ActionLogs extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise'    => ['onAfterInitialise', Priority::ABOVE_NORMAL],
            'onContentPrepareForm' => 'onContentPrepareForm',
            'onContentPrepareData' => 'onContentPrepareData',
            'onUserAfterSave'      => 'onUserAfterSave',
            'onUserAfterDelete'    => 'onUserAfterDelete',
            'onExtensionAfterSave' => 'onExtensionAfterSave',
        ];
    }

    /**
     * After initialise listener.
     *
     * @param   AfterInitialiseEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   5.3.0
     */
    public function onAfterInitialise(AfterInitialiseEvent $event): void
    {
        // Import actionlog plugin group so that these plugins will be triggered for events
        PluginHelper::importPlugin('actionlog', null, true, $event->getApplication()->getDispatcher());
    }

    /**
     * Adds additional fields to the user editing form for logs e-mail notifications
     *
     * @param   Model\PrepareFormEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     *
     * @throws  \Exception
     */
    public function onContentPrepareForm(Model\PrepareFormEvent $event): void
    {
        $form     = $event->getForm();
        $data     = $event->getData();
        $formName = $form->getName();

        $allowedFormNames = [
            'com_users.profile',
            'com_users.user',
        ];

        if (!\in_array($formName, $allowedFormNames, true)) {
            return;
        }

        /**
         * We only allow users who have Super User permission to change this setting for themselves or for other
         * users who have the same Super User permission
         */
        $user = $this->getApplication()->getIdentity();

        if (!$user || !$user->authorise('core.admin')) {
            return;
        }

        // Load plugin language files.
        $this->loadLanguage();

        // If we are on the save command, no data is passed to $data variable, we need to get it directly from request
        $jformData = $this->getApplication()->getInput()->get('jform', [], 'array');

        if ($jformData && !$data) {
            $data = $jformData;
        }

        if (\is_array($data)) {
            $data = (object) $data;
        }

        if (empty($data->id) || !$this->getUserFactory()->loadUserById($data->id)->authorise('core.admin')) {
            return;
        }

        Form::addFormPath(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms');

        if ((!PluginHelper::isEnabled('actionlog', 'joomla')) && ($this->getApplication()->isClient('administrator'))) {
            $form->loadFile('information', false);

            return;
        }

        if (!PluginHelper::isEnabled('actionlog', 'joomla')) {
            return;
        }

        $form->loadFile('actionlogs', false);
    }

    /**
     * Runs on content preparation
     *
     * @param   Model\PrepareDataEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onContentPrepareData(Model\PrepareDataEvent $event): void
    {
        $context = $event->getContext();

        if (!\in_array($context, ['com_users.profile', 'com_users.user'])) {
            return;
        }

        $data = $event->getData();

        if (\is_array($data)) {
            $data = (object) $data;
        }

        if (empty($data->id) || !$this->getUserFactory()->loadUserById($data->id)->authorise('core.admin')) {
            return;
        }

        $db = $this->getDatabase();
        $id = (int) $data->id;

        $query = $db->createQuery()
            ->select($db->quoteName(['notify', 'extensions']))
            ->from($db->quoteName('#__action_logs_users'))
            ->where($db->quoteName('user_id') . ' = :userid')
            ->bind(':userid', $id, ParameterType::INTEGER);

        try {
            $values = $db->setQuery($query)->loadObject();
        } catch (ExecutionFailureException) {
            return;
        }

        if (!$values) {
            return;
        }

        // Load plugin language files.
        $this->loadLanguage();

        $data->actionlogs                       = new \stdClass();
        $data->actionlogs->actionlogsNotify     = $values->notify;
        $data->actionlogs->actionlogsExtensions = $values->extensions;

        if (!HTMLHelper::isRegistered('users.actionlogsNotify')) {
            HTMLHelper::register('users.actionlogsNotify', [__CLASS__, 'renderActionlogsNotify']);
        }

        if (!HTMLHelper::isRegistered('users.actionlogsExtensions')) {
            HTMLHelper::register('users.actionlogsExtensions', [__CLASS__, 'renderActionlogsExtensions']);
        }
    }

    /**
     * Utility method to act on a user after it has been saved.
     *
     * @param   User\AfterSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterSave(User\AfterSaveEvent $event): void
    {
        if (!$event->getSavingResult()) {
            return;
        }

        $user = $event->getUser();

        // Clear access rights in case user groups were changed.
        $userObject = $this->getUserFactory()->loadUserById($user['id']);
        $userObject->clearAccessRights();

        $authorised = $userObject->authorise('core.admin');
        $userid     = (int) $user['id'];
        $db         = $this->getDatabase();

        $query = $db->createQuery()
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
        } catch (ExecutionFailureException) {
            // Do nothing.
        }
    }

    /**
     * Removes user preferences
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
        if (!$event->getDeletingResult()) {
            return;
        }

        $user   = $event->getUser();
        $db     = $this->getDatabase();
        $userid = (int) $user['id'];

        $query = $db->createQuery()
            ->delete($db->quoteName('#__action_logs_users'))
            ->where($db->quoteName('user_id') . ' = :userid')
            ->bind(':userid', $userid, ParameterType::INTEGER);

        try {
            $db->setQuery($query)->execute();
        } catch (ExecutionFailureException) {
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

    /**
     * On Saving extensions logging method.
     * Method is called when an extension is being saved
     *
     * @param   Model\AfterSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function onExtensionAfterSave(Model\AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $table   = $event->getItem();

        if ($context !== 'com_config.component' || $table->name !== 'com_actionlogs') {
            return;
        }

        $params    = ComponentHelper::getParams('com_actionlogs');
        $globalExt = (array) $params->get('loggable_extensions', []);

        $db = $this->getDatabase();

        $query = $db->createQuery()
            ->select($db->quoteName(['user_id', 'notify', 'extensions']))
            ->from($db->quoteName('#__action_logs_users'));

        try {
            $values = $db->setQuery($query)->loadObjectList();
        } catch (ExecutionFailureException) {
            return;
        }

        foreach ($values as $item) {
            $userExt = substr($item->extensions, 2);
            $userExt = substr($userExt, 0, -2);
            $user    = explode('","', $userExt);
            $common  = array_intersect($globalExt, $user);

            $extension = json_encode(array_values($common));

            $query->clear()
                ->update($db->quoteName('#__action_logs_users'))
                ->set($db->quoteName('extensions') . ' = :extension')
                ->where($db->quoteName('user_id') . ' = :userid')
                ->bind(':userid', $item->user_id, ParameterType::INTEGER)
                ->bind(':extension', $extension);

            try {
                $db->setQuery($query)->execute();
            } catch (ExecutionFailureException) {
                // Do nothing.
            }
        }
    }
}

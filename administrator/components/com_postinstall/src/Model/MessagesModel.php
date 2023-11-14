<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Postinstall\Administrator\Model;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Postinstall\Administrator\Helper\PostinstallHelper;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class to manage postinstall messages
 *
 * @since  3.2
 */
class MessagesModel extends BaseDatabaseModel
{
    /**
     * Method to auto-populate the state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the
     * configuration flag to ignore the request is set.
     *
     * @return  void
     *
     * @note    Calling getState in this method will result in recursion.
     * @since   4.0.0
     */
    protected function populateState()
    {
        parent::populateState();

        $eid = (int) Factory::getApplication()->getInput()->getInt('eid');

        if ($eid) {
            $this->setState('eid', $eid);
        }
    }

    /**
     * Gets an item with the given id from the database
     *
     * @param   integer  $id  The item id
     *
     * @return  Object
     *
     * @since   3.2
     */
    public function getItem($id)
    {
        $db = $this->getDatabase();
        $id = (int) $id;

        $query = $db->createQuery();
        $query->select(
            [
                $db->quoteName('postinstall_message_id'),
                $db->quoteName('extension_id'),
                $db->quoteName('title_key'),
                $db->quoteName('description_key'),
                $db->quoteName('action_key'),
                $db->quoteName('language_extension'),
                $db->quoteName('language_client_id'),
                $db->quoteName('type'),
                $db->quoteName('action_file'),
                $db->quoteName('action'),
                $db->quoteName('condition_file'),
                $db->quoteName('condition_method'),
                $db->quoteName('version_introduced'),
                $db->quoteName('enabled'),
            ]
        )
            ->from($db->quoteName('#__postinstall_messages'))
            ->where($db->quoteName('postinstall_message_id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        $db->setQuery($query);

        $result = $db->loadObject();

        return $result;
    }

    /**
     * Unpublishes specified post-install message
     *
     * @param   integer  $id  The message id
     *
     * @return   void
     */
    public function unpublishMessage($id)
    {
        $db = $this->getDatabase();
        $id = (int) $id;

        $query = $db->createQuery();
        $query
            ->update($db->quoteName('#__postinstall_messages'))
            ->set($db->quoteName('enabled') . ' = 0')
            ->where($db->quoteName('postinstall_message_id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();
        Factory::getCache()->clean('com_postinstall');
    }

    /**
     * Archives specified post-install message
     *
     * @param    integer  $id  The message id
     *
     * @return   void
     *
     * @since    4.2.0
     */
    public function archiveMessage($id)
    {
        $db = $this->getDatabase();
        $id = (int) $id;

        $query = $db->createQuery();
        $query
            ->update($db->quoteName('#__postinstall_messages'))
            ->set($db->quoteName('enabled') . ' = 2')
            ->where($db->quoteName('postinstall_message_id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();
        Factory::getCache()->clean('com_postinstall');
    }

    /**
     * Republishes specified post-install message
     *
     * @param    integer  $id  The message id
     *
     * @return   void
     *
     * @since    4.2.0
     */
    public function republishMessage($id)
    {
        $db = $this->getDatabase();
        $id = (int) $id;

        $query = $db->createQuery();
        $query
            ->update($db->quoteName('#__postinstall_messages'))
            ->set($db->quoteName('enabled') . ' = 1')
            ->where($db->quoteName('postinstall_message_id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();
        Factory::getCache()->clean('com_postinstall');
    }

    /**
     * Returns a list of messages from the #__postinstall_messages table
     *
     * @return  array
     *
     * @since   3.2
     */
    public function getItems()
    {
        // Add a forced extension filtering to the list
        $eid = (int) $this->getState('eid', $this->getJoomlaFilesExtensionId());

        // Build a cache ID for the resulting data object
        $cacheId = 'postinstall_messages.' . $eid;

        $db    = $this->getDatabase();
        $query = $db->createQuery();
        $query->select(
            [
                $db->quoteName('postinstall_message_id'),
                $db->quoteName('extension_id'),
                $db->quoteName('title_key'),
                $db->quoteName('description_key'),
                $db->quoteName('action_key'),
                $db->quoteName('language_extension'),
                $db->quoteName('language_client_id'),
                $db->quoteName('type'),
                $db->quoteName('action_file'),
                $db->quoteName('action'),
                $db->quoteName('condition_file'),
                $db->quoteName('condition_method'),
                $db->quoteName('version_introduced'),
                $db->quoteName('enabled'),
            ]
        )
            ->from($db->quoteName('#__postinstall_messages'));
        $query->where($db->quoteName('extension_id') . ' = :eid')
            ->bind(':eid', $eid, ParameterType::INTEGER);

        // Force filter only enabled messages
        $query->whereIn($db->quoteName('enabled'), [1, 2]);
        $db->setQuery($query);

        try {
            /** @var CallbackController $cache */
            $cache = $this->getCacheControllerFactory()->createCacheController('callback', ['defaultgroup' => 'com_postinstall']);

            $result = $cache->get([$db, 'loadObjectList'], [], md5($cacheId), false);
        } catch (\RuntimeException $e) {
            $app = Factory::getApplication();
            $app->getLogger()->warning(
                Text::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $e->getMessage()),
                ['category' => 'jerror']
            );

            return [];
        }

        $this->onProcessList($result);

        return $result;
    }

    /**
     * Returns a count of all enabled messages from the #__postinstall_messages table
     *
     * @return  integer
     *
     * @since   4.0.0
     */
    public function getItemsCount()
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery();
        $query->select(
            [
                $db->quoteName('language_extension'),
                $db->quoteName('language_client_id'),
                $db->quoteName('condition_file'),
                $db->quoteName('condition_method'),
            ]
        )
            ->from($db->quoteName('#__postinstall_messages'));

        // Force filter only enabled messages
        $query->where($db->quoteName('enabled') . ' = 1');
        $db->setQuery($query);

        try {
            /** @var CallbackController $cache */
            $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
                ->createCacheController('callback', ['defaultgroup' => 'com_postinstall']);

            // Get the resulting data object for cache ID 'all.1' from com_postinstall group.
            $result = $cache->get([$db, 'loadObjectList'], [], md5('all.1'), false);
        } catch (\RuntimeException $e) {
            $app = Factory::getApplication();
            $app->getLogger()->warning(
                Text::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $e->getMessage()),
                ['category' => 'jerror']
            );

            return 0;
        }

        $this->onProcessList($result);

        return \count($result);
    }

    /**
     * Returns the name of an extension, as registered in the #__extensions table
     *
     * @param   integer  $eid  The extension ID
     *
     * @return  string  The extension name
     *
     * @since   3.2
     */
    public function getExtensionName($eid)
    {
        // Load the extension's information from the database
        $db  = $this->getDatabase();
        $eid = (int) $eid;

        $query = $db->createQuery()
            ->select(
                [
                    $db->quoteName('name'),
                    $db->quoteName('element'),
                    $db->quoteName('client_id'),
                ]
            )
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('extension_id') . ' = :eid')
            ->bind(':eid', $eid, ParameterType::INTEGER)
            ->setLimit(1);

        $db->setQuery($query);

        $extension = $db->loadObject();

        if (!\is_object($extension)) {
            return '';
        }

        // Load language files
        $basePath = JPATH_ADMINISTRATOR;

        if ($extension->client_id == 0) {
            $basePath = JPATH_SITE;
        }

        $lang = Factory::getApplication()->getLanguage();
        $lang->load($extension->element, $basePath);

        // Return the localised name
        return Text::_(strtoupper($extension->name));
    }

    /**
     * Resets all messages for an extension
     *
     * @param   integer  $eid  The extension ID whose messages we'll reset
     *
     * @return  mixed  False if we fail, a db cursor otherwise
     *
     * @since   3.2
     */
    public function resetMessages($eid)
    {
        $db  = $this->getDatabase();
        $eid = (int) $eid;

        $query = $db->createQuery()
            ->update($db->quoteName('#__postinstall_messages'))
            ->set($db->quoteName('enabled') . ' = 1')
            ->where($db->quoteName('extension_id') . ' = :eid')
            ->bind(':eid', $eid, ParameterType::INTEGER);
        $db->setQuery($query);

        $result = $db->execute();
        Factory::getCache()->clean('com_postinstall');

        return $result;
    }

    /**
     * Hides all messages for an extension
     *
     * @param   integer  $eid  The extension ID whose messages we'll hide
     *
     * @return  mixed  False if we fail, a db cursor otherwise
     *
     * @since   3.8.7
     */
    public function hideMessages($eid)
    {
        $db  = $this->getDatabase();
        $eid = (int) $eid;

        $query = $db->createQuery()
            ->update($db->quoteName('#__postinstall_messages'))
            ->set($db->quoteName('enabled') . ' = 0')
            ->where($db->quoteName('extension_id') . ' = :eid')
            ->bind(':eid', $eid, ParameterType::INTEGER);
        $db->setQuery($query);

        $result = $db->execute();
        Factory::getCache()->clean('com_postinstall');

        return $result;
    }

    /**
     * List post-processing. This is used to run the programmatic display
     * conditions against each list item and decide if we have to show it or
     * not.
     *
     * Do note that this a core method of the RAD Layer which operates directly
     * on the list it's being fed. A little touch of modern magic.
     *
     * @param   array  &$resultArray  A list of items to process
     *
     * @return  void
     *
     * @since   3.2
     */
    protected function onProcessList(&$resultArray)
    {
        $unset_keys          = [];
        $language_extensions = [];

        // Order the results DESC so the newest is on the top.
        $resultArray = array_reverse($resultArray);

        foreach ($resultArray as $key => $item) {
            // Filter out messages based on dynamically loaded programmatic conditions.
            if (!empty($item->condition_file) && !empty($item->condition_method)) {
                $helper = new PostinstallHelper();
                $file   = $helper->parsePath($item->condition_file);

                if (is_file($file)) {
                    require_once $file;

                    $result = \call_user_func($item->condition_method);

                    if ($result === false) {
                        $unset_keys[] = $key;
                    }
                }
            }

            // Load the necessary language files.
            if (!empty($item->language_extension)) {
                $hash = $item->language_client_id . '-' . $item->language_extension;

                if (!\in_array($hash, $language_extensions)) {
                    $language_extensions[] = $hash;
                    Factory::getApplication()->getLanguage()->load($item->language_extension, $item->language_client_id == 0 ? JPATH_SITE : JPATH_ADMINISTRATOR);
                }
            }
        }

        if (!empty($unset_keys)) {
            foreach ($unset_keys as $key) {
                unset($resultArray[$key]);
            }
        }
    }

    /**
     * Get the dropdown options for the list of component with post-installation messages
     *
     * @since 3.4
     *
     * @return  array  Compatible with JHtmlSelect::genericList
     */
    public function getComponentOptions()
    {
        $db = $this->getDatabase();

        $query = $db->createQuery()
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__postinstall_messages'))
            ->group($db->quoteName('extension_id'));
        $db->setQuery($query);
        $extension_ids = $db->loadColumn();

        $options = [];

        Factory::getApplication()->getLanguage()->load('files_joomla.sys', JPATH_SITE, null, false, false);

        foreach ($extension_ids as $eid) {
            $options[] = HTMLHelper::_('select.option', $eid, $this->getExtensionName($eid));
        }

        return $options;
    }

    /**
     * Adds or updates a post-installation message (PIM) definition. You can use this in your post-installation script using this code:
     *
     * Factory::getApplication()->bootComponent('com_postinstall')
     * ->getMVCFactory()->createModel('Messages', 'Administrator', ['ignore_request' => true])
     * ->addPostInstallationMessage($options);
     *
     * The $options array contains the following mandatory keys:
     *
     * extension_id        The numeric ID of the extension this message is for (see the #__extensions table)
     *
     * type                One of message, link or action. Their meaning is:
     *                         message  Informative message. The user can dismiss it.
     *                         link     The action button links to a URL. The URL is defined in the action parameter.
     *                         action   A PHP action takes place when the action button is clicked. You need to specify the action_file
     *                                  (RAD path to the PHP file) and action (PHP function name) keys. See below for more information.
     *
     * title_key           The Text language key for the title of this PIM.
     *                     Example: COM_FOOBAR_POSTINSTALL_MESSAGEONE_TITLE
     *
     * description_key     The Text language key for the main body (description) of this PIM
     *                     Example: COM_FOOBAR_POSTINSTALL_MESSAGEONE_DESCRIPTION
     *
     * action_key          The Text language key for the action button. Ignored and not required when type=message
     *                     Example: COM_FOOBAR_POSTINSTALL_MESSAGEONE_ACTION
     *
     * language_extension  The extension name which holds the language keys used above.
     *                     For example, com_foobar, mod_something, plg_system_whatever, tpl_mytemplate
     *
     * language_client_id  Should we load the frontend (0) or backend (1) language keys?
     *
     * version_introduced  Which was the version of your extension where this message appeared for the first time?
     *                     Example: 3.2.1
     *
     * enabled             Must be 1 for this message to be enabled. If you omit it, it defaults to 1.
     *
     * condition_file      The RAD path to a PHP file containing a PHP function which determines whether this message should be shown to
     *                     the user. @see FOFTemplateUtils::parsePath() for RAD path format. Joomla! will include this file before calling
     *                     the condition_method.
     *                     Example:   admin://components/com_foobar/helpers/postinstall.php
     *
     * condition_method    The name of a PHP function which will be used to determine whether to show this message to the user. This must be
     *                     a simple PHP user function (not a class method, static method etc) which returns true to show the message and false
     *                     to hide it. This function is defined in the condition_file.
     *                     Example: com_foobar_postinstall_messageone_condition
     *
     * When type=message no additional keys are required.
     *
     * When type=link the following additional keys are required:
     *
     * action  The URL which will open when the user clicks on the PIM's action button
     *         Example:    index.php?option=com_foobar&view=tools&task=installSampleData
     *
     * When type=action the following additional keys are required:
     *
     * action_file  The RAD path to a PHP file containing a PHP function which performs the action of this PIM.
     *              Joomla! will include this file before calling the function defined in the action key below.
     *              Example:   admin://components/com_foobar/helpers/postinstall.php
     *
     * action       The name of a PHP function which will be used to run the action of this PIM. This must be a simple PHP user function
     *              (not a class method, static method etc) which returns no result.
     *              Example: com_foobar_postinstall_messageone_action
     *
     * @param   array  $options  See description
     *
     * @return  $this
     *
     * @throws  \Exception
     */
    public function addPostInstallationMessage(array $options)
    {
        // Make sure there are options set
        if (!\is_array($options)) {
            throw new \Exception('Post-installation message definitions must be of type array', 500);
        }

        // Initialise array keys
        $defaultOptions = [
            'extension_id'       => '',
            'type'               => '',
            'title_key'          => '',
            'description_key'    => '',
            'action_key'         => '',
            'language_extension' => '',
            'language_client_id' => '',
            'action_file'        => '',
            'action'             => '',
            'condition_file'     => '',
            'condition_method'   => '',
            'version_introduced' => '',
            'enabled'            => '1',
        ];

        $options = array_merge($defaultOptions, $options);

        // Array normalisation. Removes array keys not belonging to a definition.
        $defaultKeys = array_keys($defaultOptions);
        $allKeys     = array_keys($options);
        $extraKeys   = array_diff($allKeys, $defaultKeys);

        if (!empty($extraKeys)) {
            foreach ($extraKeys as $key) {
                unset($options[$key]);
            }
        }

        // Normalisation of integer values
        $options['extension_id']       = (int) $options['extension_id'];
        $options['language_client_id'] = (int) $options['language_client_id'];
        $options['enabled']            = (int) $options['enabled'];

        // Normalisation of 0/1 values
        foreach (['language_client_id', 'enabled'] as $key) {
            $options[$key] = $options[$key] ? 1 : 0;
        }

        // Make sure there's an extension_id
        if (!(int) $options['extension_id']) {
            throw new \Exception('Post-installation message definitions need an extension_id', 500);
        }

        // Make sure there's a valid type
        if (!\in_array($options['type'], ['message', 'link', 'action'])) {
            throw new \Exception('Post-installation message definitions need to declare a type of message, link or action', 500);
        }

        // Make sure there's a title key
        if (empty($options['title_key'])) {
            throw new \Exception('Post-installation message definitions need a title key', 500);
        }

        // Make sure there's a description key
        if (empty($options['description_key'])) {
            throw new \Exception('Post-installation message definitions need a description key', 500);
        }

        // If the type is anything other than message you need an action key
        if (($options['type'] != 'message') && empty($options['action_key'])) {
            throw new \Exception('Post-installation message definitions need an action key when they are of type "' . $options['type'] . '"', 500);
        }

        // You must specify the language extension
        if (empty($options['language_extension'])) {
            throw new \Exception('Post-installation message definitions need to specify which extension contains their language keys', 500);
        }

        // The action file and method are only required for the "action" type
        if ($options['type'] == 'action') {
            if (empty($options['action_file'])) {
                throw new \Exception('Post-installation message definitions need an action file when they are of type "action"', 500);
            }

            $helper    = new PostinstallHelper();
            $file_path = $helper->parsePath($options['action_file']);

            if (!@is_file($file_path)) {
                throw new \Exception('The action file ' . $options['action_file'] . ' of your post-installation message definition does not exist', 500);
            }

            if (empty($options['action'])) {
                throw new \Exception('Post-installation message definitions need an action (function name) when they are of type "action"', 500);
            }
        }

        if ($options['type'] == 'link') {
            if (empty($options['link'])) {
                throw new \Exception('Post-installation message definitions need an action (URL) when they are of type "link"', 500);
            }
        }

        // The condition file and method are only required when the type is not "message"
        if ($options['type'] != 'message') {
            if (empty($options['condition_file'])) {
                throw new \Exception('Post-installation message definitions need a condition file when they are of type "' . $options['type'] . '"', 500);
            }

            $helper    = new PostinstallHelper();
            $file_path = $helper->parsePath($options['condition_file']);

            if (!@is_file($file_path)) {
                throw new \Exception('The condition file ' . $options['condition_file'] . ' of your post-installation message definition does not exist', 500);
            }

            if (empty($options['condition_method'])) {
                throw new \Exception(
                    'Post-installation message definitions need a condition method (function name) when they are of type "'
                    . $options['type'] . '"',
                    500
                );
            }
        }

        // Check if the definition exists
        $table       = $this->getTable();
        $tableName   = $table->getTableName();
        $extensionId = (int) $options['extension_id'];

        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName($tableName))
            ->where(
                [
                    $db->quoteName('extension_id') . ' = :extensionId',
                    $db->quoteName('type') . ' = :type',
                    $db->quoteName('title_key') . ' = :titleKey',
                ]
            )
            ->bind(':extensionId', $extensionId, ParameterType::INTEGER)
            ->bind(':type', $options['type'])
            ->bind(':titleKey', $options['title_key']);

        $existingRow = $db->setQuery($query)->loadAssoc();

        // Is the existing definition the same as the one we're trying to save?
        if (!empty($existingRow)) {
            $same = true;

            foreach ($options as $k => $v) {
                if ($existingRow[$k] != $v) {
                    $same = false;
                    break;
                }
            }

            // Trying to add the same row as the existing one; quit
            if ($same) {
                return $this;
            }

            // Otherwise it's not the same row. Remove the old row before insert a new one.
            $query = $db->createQuery()
                ->delete($db->quoteName($tableName))
                ->where(
                    [
                        $db->quoteName('extension_id') . ' = :extensionId',
                        $db->quoteName('type') . ' = :type',
                        $db->quoteName('title_key') . ' = :titleKey',
                    ]
                )
                ->bind(':extensionId', $extensionId, ParameterType::INTEGER)
                ->bind(':type', $options['type'])
                ->bind(':titleKey', $options['title_key']);

            $db->setQuery($query)->execute();
        }

        // Insert the new row
        $options = (object) $options;
        $db->insertObject($tableName, $options);
        Factory::getCache()->clean('com_postinstall');

        return $this;
    }

    /**
     * Returns the library extension ID.
     *
     * @return  integer
     *
     * @since   4.0.0
     */
    public function getJoomlaFilesExtensionId()
    {
        return ExtensionHelper::getExtensionRecord('joomla', 'file')->extension_id;
    }
}

<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Jtchuserbeforedel
 *
 * @author      Guido De Gobbis <support@joomtools.de>
 * @copyright   Copyright JoomTools.de - All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

\JLoader::registerNamespace('JtChUserBeforeDel', JPATH_PLUGINS . '/system/jtchuserbeforedel/src', false, true, 'psr4');

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use JtChUserBeforeDel\JtChUserBeforeDelInterface;

/**
 * Class to replace the userid on component items on user deletion
 *
 * @since  1.0.0
 */
class PlgSystemJtchuserbeforedel extends CMSPlugin
{
    /**
     * Global database object
     *
     * @var    \JDatabaseDriver
     * @since  1.0.0
     */
    protected $db = null;

    /**
     * Global application object
     *
     * @var     CMSApplication
     * @since  1.0.0
     */
    protected $app = null;

    /**
     * Load the language file on instantiation.
     *
     * @var     boolean
     * @since  1.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Array of extensions class
     *
     * @var    array
     * @since  1.0.0
     */
    private static $extensions = array();

    /**
     * Event triggered before an extension item output is rendered.
     *
     * @param   string  $context
     * @param   object  $item
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function onContentPrepareData($context, $item)
    {
        $extensionClass = $this->getExtensionClass($context);

        if ($extensionClass instanceof JtChUserBeforeDelInterface) {
            $this->changeUserIdIfUserDoesNotExistAnymore($extensionClass, $item);
        }
    }

    /**
     * Event triggered before an extension is saved.
     *
     * @param   string  $context
     * @param   object  $item
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function onExtensionBeforeSave($context, $item)
    {
        $extensionClass = $this->getExtensionClass($context);

        if ($extensionClass instanceof JtChUserBeforeDelInterface) {
            $this->changeUserIdIfUserDoesNotExistAnymore($extensionClass, $item);
        }

        if ($context == 'com_plugins.plugin' && $item->name == 'plg_system_jtchuserbeforedel') {
            $newParams               = new Registry($item->params);
            $userIdToChangeManualy   = $newParams->get('userIdToChangeManualy', '');
            $userNameToChangeManualy = $newParams->get('userNameToChangeManualy', '');

            // Reset the fields
            $newParams->set('userIdToChangeManualy', '');
            $newParams->set('userNameToChangeManualy', '');

            $item->params = (string) $newParams;

            if (empty($userIdToChangeManualy)) {
                return;
            }

            if ($this->isUserExists($userIdToChangeManualy)) {
                $this->app->enqueueMessage(
                    Text::sprintf(
                        'PLG_SYSTEM_JTCHUSERBEFOREDEL_USER_ID_TO_CHANGE_MANUALY_EXISTS',
                        $userIdToChangeManualy
                    ),
                    'error'
                );

                return;
            }

            if (!empty($userIdToChangeManualy)) {
                $this->params = $newParams;
                $user         = array(
                    'id'   => $userIdToChangeManualy,
                    'name' => $userNameToChangeManualy,
                );

                $this->changeUser($user);
            }
        }
    }

    /**
     * Changes the user in all registered extensions if it no longer exists.
     *
     * @param   object  $extensionClass
     * @param   object  $item
     *
     * @return  void
     *
     * @since   1.0.0
     */
    private function changeUserIdIfUserDoesNotExistAnymore($extensionClass, $item)
    {
        $fallbackUserId    = $this->params->get('fallbackUser');
        $fallbackAliasName = $this->params->get('fallbackAliasName', '');

        if (empty($fallbackUserId) || !is_numeric($fallbackUserId)) {
            $fallbackUserId = Factory::getUser()->id;
        }

        foreach ($extensionClass->getColumsToChange() as $table) {
            if (is_array($table) && count($table) > 1) {
                $authorExists = true;
                $authorTable  = $table['author'] ?? false;
                $aliasTable   = $table['alias'] ?? false;

                if ($authorTable && isset($item->$authorTable)) {
                    $authorExists = $this->isUserExists($item->$authorTable);
                }

                if (!$authorExists) {
                    $this->app->enqueueMessage(
                        Text::sprintf(
                            'PLG_SYSTEM_JTCHUSERBEFOREDEL_USER_CHANGED_MSG',
                            $item->$authorTable,
                            $fallbackUserId
                        ),
                        'info'
                    );

                    $item->$authorTable = $fallbackUserId;

                    if ($aliasTable && isset($item->$aliasTable) && $this->params->get('setAlias')) {
                        if ((!$this->params->get('overrideAlias') && !empty($item->$aliasTable))
                            || (empty($item->$aliasTable) && empty($fallbackAliasName))
                        ) {
                            continue;
                        }

                        $item->$aliasTable = $fallbackAliasName;

                        $this->app->enqueueMessage(
                            Text::sprintf(
                                'PLG_SYSTEM_JTCHUSERBEFOREDEL_USER_CHANGED_FALLBACK_ALIAS_MSG',
                                $fallbackAliasName
                            ),
                            'info'
                        );
                    }
                }
            }
        }
    }

    /**
     * Checks if a user exists.
     *
     * @param   int  $userId
     *
     * @return  bool
     *
     * @since   1.0.0
     */
    private function isUserExists($userId)
    {
        $userTable = Table::getInstance('user');

        return $userTable->load((int) $userId) === true;
    }

    /**
     * Event triggered before the user is deleted.
     *
     * @param   array  $user
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function onUserBeforeDelete($user)
    {
        $fallbackUser = $this->params->get('fallbackUser');

        if ($user['id'] == $fallbackUser) {
            $this->app->enqueueMessage(
                Text::_('PLG_SYSTEM_JTCHUSERBEFOREDEL_ERROR_FALLBACK_USER_CONNECTED_MSG'),
                'error'
            );

            $url = Uri::getInstance()->toString(array('path', 'query', 'fragment'));
            $this->app->redirect($url, 500);
        }

        if (!$this->changeUser($user)) {
            $this->app->enqueueMessage(
                Text::_('PLG_SYSTEM_JTCHUSERBEFOREDEL_ERROR_USER_NOT_DELETED_MSG'),
                'error'
            );

            $url = Uri::getInstance()->toString(array('path', 'query', 'fragment'));
            $this->app->redirect($url, 500);
        }
    }

    /**
     * Changes the user in all registered extensions before deleting them.
     *
     * @param   array  $user
     *
     * @return  bool
     *
     * @since   1.0.0
     */
    private function changeUser($user)
    {
        $return         = true;
        $userId         = $user['id'];
        $aliasName      = $user['name'];
        $fallbackUserId = $this->params->get('fallbackUser');
        $setAuthorAlias = $this->params->get('setAlias');

        if (empty($fallbackUserId) || !is_numeric($fallbackUserId)) {
            $fallbackUserId = Factory::getUser()->id;
        }

        if (empty($extensions = $this->getExtensionClass())) {
            // TODO: Add error handling and/or message and return false
            return true;
        }

        foreach ($extensions as $extensionBaseContext => $extensionClass) {
            /** @var JtChUserBeforeDelInterface $extensionClass */
            $columsToChangeUserId = $extensionClass->getColumsToChange();

            foreach ($columsToChangeUserId as $table) {
                $tableName    = $table['tableName'] ?? false;
                $uniqueId     = $table['uniqueId'] ?? false;
                $authorColumn = $table['author'] ?? false;
                $aliasColumn  = $table['alias'] ?? false;

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

                    if ($setAuthorAlias && $aliasColumn) {
                        if ($this->params->get('overrideAlias')) {
                            $updateQuery->set(
                                $this->db->quoteName($aliasColumn) . ' = ' . $this->db->quote($aliasName)
                            );
                        } else {
                            $updateQuery->set(
                                $this->db->quoteName($aliasColumn)
                                . ' = COALESCE(NULLIF('
                                . $this->db->quote($aliasName)
                                . ', ""), '
                                . $this->db->quoteName($aliasColumn) . ')'
                            );
                        }
                    }

                    $updateQuery->where(
                        $this->db->quoteName($authorColumn) . ' = ' . $this->db->quote((int) $userId)
                    );
                }

                try {
                    $infoAuthorAlias = '';
                    $selectResult    = $this->db->setQuery($selectQuery)->loadColumn();

                    if (!empty($selectResult)) {
                        $elementList = implode(', ', $selectResult);

                        if ($setAuthorAlias && $aliasColumn) {
                            $infoAuthorAlias = Text::sprintf(
                                'PLG_SYSTEM_JTCHUSERBEFOREDEL_USER_CHANGED_FALLBACK_ALIAS_MSG',
                                $aliasName
                            );
                        }

                        // Load extension language files
                        $this->app->getLanguage()->load($extensionBaseContext);
                        $this->app->getLanguage()->load($extensionBaseContext . '.sys');

                        $this->db->setQuery($updateQuery)->execute();
                        $this->app->enqueueMessage(
                            Text::sprintf(
                                'PLG_SYSTEM_JTCHUSERBEFOREDEL_USER_DELETED_MSG',
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
                        $e->getMessage(),
                        'error'
                    );

                    $return = false;
                }
            }
        }

        return $return;
    }

    /**
     * Initialize the extensions class array.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    private function initExtensions()
    {
        \JLoader::registerNamespace(
            'JtChUserBeforeDel\\Extension',
            JPATH_PLUGINS . '/system/jtchuserbeforedel/src/Extension/all',
            false,
            true,
            'psr4'
        );

        if (version_compare(JVERSION, '4', 'ge')) {
            \JLoader::registerNamespace(
                'JtChUserBeforeDel\\Extension',
                JPATH_PLUGINS . '/system/jtchuserbeforedel/src/Extension/onlyJoomla4',
                false,
                true,
                'psr4'
            );
        }

        if (version_compare(JVERSION, '4', 'lt')) {
            \JLoader::registerNamespace(
                'JtChUserBeforeDel\\Extension',
                JPATH_PLUGINS . '/system/jtchuserbeforedel/src/Extension/onlyJoomla3',
                false,
                true,
                'psr4'
            );
        }

        $ns      = \JLoader::getNamespaces('psr4');
        $nsPaths = (array) $ns['JtChUserBeforeDel\\Extension'];

        foreach ($nsPaths as $nsPath) {
            $extensions = Folder::files($nsPath);

            foreach ($extensions as $extensionFileName) {
                $ext = pathinfo($extensionFileName, PATHINFO_EXTENSION);

                if ($ext != 'php') {
                    continue;
                }

                /** @var JtChUserBeforeDelInterface $extensionClass */
                $extensionClass = $this->loadExtensionClass($extensionFileName);

                if ($extensionClass instanceof JtChUserBeforeDelInterface
                    && !isset(self::$extensions[$extensionClass->getExtensionBaseContext()])
                ) {
                    self::$extensions[$extensionClass->getExtensionBaseContext()] = $extensionClass;
                }
            }
        }
    }

    /**
     * Load the extension class.
     *
     * @param   string  $extensionFileName
     *
     * @return  JtChUserBeforeDelInterface|null
     *
     * @since   1.0.0
     */
    private function loadExtensionClass($extensionFileName)
    {
        $error              = false;
        $extensionClassName = pathinfo($extensionFileName, PATHINFO_FILENAME);
        $extensionNs        = 'JtChUserBeforeDel\\Extension\\' . ucfirst($extensionClassName);

        try {
            $extensionClass = new $extensionNs;
        } catch (\Throwable $e) {
            $error = true;
        } catch (\Exception $e) {
            $error = true;
        }

        if ($error) {
            $this->app->enqueueMessage(
                Text::sprintf(
                    'PLG_SYSTEM_JTCHUSERBEFOREDEL_ERROR_LOADING_CLASS_MSG',
                    Text::_('PLG_SYSTEM_JTCHUSERBEFOREDEL'),
                    $extensionNs,
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
     * @return  JtChUserBeforeDelInterface|array
     *
     * @since   1.0.0
     */
    private function getExtensionClass($context = null)
    {
        if (empty(self::$extensions)) {
            $this->initExtensions();
        }

        if (is_null($context)) {
            return self::$extensions;
        }

        list($extensionBaseContext, $rest) = explode('.', $context, 2);

        return self::$extensions[$extensionBaseContext] ?? array();
    }
}

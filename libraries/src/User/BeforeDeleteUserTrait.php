<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use RuntimeException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for classes which require to change a user in database.
 *
 * @since  __DEPLOY_VERSION__
 */
trait BeforeDeleteUserTrait
{
    /**
     * Changes the user in all registered extensions before deleting them.
     *
     * @param   Registry  $params
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function changeUser(Registry $params)
    {
        if (!$this instanceof BeforeDeleteUserInterface) {
            // TODO: Add error handling and/or message and return it
            return;
        }

        $app                    = $this->getApplication();
        $userId                 = (int) $params->get('userId');
        $aliasName              = $params->get('userName');
        $fallbackUserId         = (int) $params->get('fallbackUserIdOnDelete');
        $setAliasOnDelete       = $params->get('setAliasOnDelete', '1');
        $tablesListToChangeUser = $this->getTablesListToChangeUser();

        foreach ($tablesListToChangeUser as $table) {
            $tableName        = $table['tableName'] ?? false;
            $userIdColumns    = (array) $table['userId'] ?? [];
            $userNameColumns  = (array) $table['userName'] ?? [];
            $infoAuthorAlias  = '';

            if ($tableName && $userIdColumns) {
                try {
                    // Get the entries to update.
                    $selectResult = $this->getDbItems($table, $params);

                    if (!empty($selectResult)) {
                        $forcedChangedAlias = $this->updateDbItems($table, $params);
                        $elementList        = implode(', ', $selectResult);

                        if ($setAliasOnDelete && $userNameColumns) {
                            if ($forcedChangedAlias) {
                                $infoAuthorAlias = Text::sprintf(
                                    'COM_USERS_BEFORE_DELETE_USER_CHANGED_FALLBACK_ALIAS_MSG',
                                    $aliasName
                                );
                            } else {
                                $infoAuthorAlias = Text::sprintf(
                                    'COM_USERS_BEFORE_DELETE_USER_CHANGED_FALLBACK_ALIAS_IF_NOT_EMPTY_MSG',
                                    $aliasName
                                );
                            }
                        }

                        // Load extension language files
                        $app->getLanguage()->load($table['baseContext']);
                        $app->getLanguage()->load($table['baseContext'] . '.sys');

                        $app->enqueueMessage(
                            Text::sprintf(
                                'COM_USERS_BEFORE_DELETE_USER_DELETED_MSG',
                                Text::_($table['realName']),
                                $elementList,
                                $userId,
                                $fallbackUserId,
                                $infoAuthorAlias
                            ),
                            'info'
                        );
                    }
                } catch (RuntimeException $e) {
                    $app->enqueueMessage(
                        Text::_('COM_USERS_BEFORE_DELETE_USER_ERROR_USER_NOT_DELETED_MSG'),
                        'error'
                    );

                    $app->enqueueMessage(
                        $e->getMessage(),
                        'error'
                    );

                    $url = Uri::getInstance()->toString(['path', 'query', 'fragment']);
                    $app->redirect($url, 500);
                }
            }
        }
    }

    /**
     * Searches the database for the occurrences of the user id.
     *
     * @param   array     $table
     * @param   Registry  $params
     *
     * @return string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getDbItems(array $table, Registry $params)
    {
        $db               = $this->getDatabase();
        $userId           = (int) $params->get('userId');
        $tableName        = $table['tableName'] ?? false;
        $primaryKeyColumn = $table['primaryKey'] ?? false;
        $userIdColumn     = (array) $table['userId'] ?? [];
        $selectQuery      = $db->getQuery(true);

        $selectQuery->select($db->quoteName($primaryKeyColumn))
            ->from($tableName);

        foreach ($userIdColumn as $column) {
            $selectQuery->where($db->quoteName($column) . ' = ' . $db->quote($userId), 'OR');
        }

        $selectQuery->set('FOR UPDATE');

        return $db->setQuery($selectQuery)->loadColumn();
    }

    /**
     * @param   array     $table
     * @param   Registry  $params
     *
     * @return  boolean  True if the alias is force changed.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function updateDbItems(array $table, Registry $params)
    {
        $db                    = $this->getDatabase();
        $userId                = (int) $params->get('userId');
        $tableName             = $table['tableName'] ?? false;
        $userIdColumns         = (array) $table['userId'] ?? [];
        $userNameColumns       = (array) $table['userName'] ?? [];
        $userName              = $params->get('userName');
        $fallbackUserId        = (int) $params->get('fallbackUserIdOnDelete');
        $setAliasOnDelete      = $params->get('setAliasOnDelete', '1');
        $overrideAliasOnDelete = $setAliasOnDelete && $params->get('overrideAliasOnDelete', '0');
        $updateQuery           = $db->getQuery(true);
        $forcedChangedAlias    = false;

        $updateQuery->update($db->quoteName($tableName));

        foreach ($userIdColumns as $userIdColumn) {
            $updateQuery->set(
                $db->quoteName($userIdColumn)
                . ' = CASE WHEN ' . $db->quoteName($userIdColumn) . ' = ' . $db->quote($userId)
                . ' THEN ' . $db->quote($fallbackUserId)
                . ' ELSE ' . $db->quoteName($userIdColumn)
                . ' END'
            );

            $updateQuery->where($db->quoteName($userIdColumn) . ' = ' . $db->quote($userId), 'OR');
        }

        if ($setAliasOnDelete && !empty($userNameColumns)) {
            foreach ($userNameColumns as $userNameColumn) {
                if ($overrideAliasOnDelete) {
                    $forcedChangedAlias = true;

                    $updateQuery->set($db->quoteName($userNameColumn) . ' = ' . $db->quote($userName));
                } else {
                    $updateQuery->set(
                        $db->quoteName($userNameColumn)
                        . ' = CASE WHEN ' . $db->quoteName($userNameColumn) . ' IS NULL'
                        . ' OR CHAR_LENGTH(TRIM(' . $db->quoteName($userNameColumn) . ')) = 0'
                        . ' THEN ' . $db->quote($userName)
                        . ' ELSE ' . $db->quoteName($userNameColumn)
                        . ' END'
                    );
                }
            }
        }


        // Update the entries found.
        $db->setQuery($updateQuery)->execute();

        return $forcedChangedAlias;
    }
}

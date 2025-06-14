<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Schema\ChangeItem;

use Joomla\CMS\Schema\ChangeItem;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Checks the database schema against one PostgreSQL DDL query to see if it has been run.
 *
 * @since  3.0
 */
class PostgresqlChangeItem extends ChangeItem
{
    /**
     * Checks a DDL query to see if it is a known type
     * If yes, build a check query to see if the DDL has been run on the database.
     * If successful, the $msgElements, $queryType, $checkStatus and $checkQuery fields are populated.
     * The $msgElements contains the text to create the user message.
     * The $checkQuery contains the SQL query to check whether the schema change has
     * been run against the current database. The $queryType contains the type of
     * DDL query that was run (for example, CREATE_TABLE, ADD_COLUMN, CHANGE_COLUMN_TYPE, ADD_INDEX).
     * The $checkStatus field is set to zero if the query is created
     *
     * If not successful, $checkQuery is empty and , and $checkStatus is -1.
     * For example, this will happen if the current line is a non-DDL statement.
     *
     * @return void
     *
     * @since  3.0
     */
    protected function buildCheckQuery()
    {
        // Initialize fields in case we can't create a check query

        // Change status to skipped
        $this->checkStatus = -1;

        $result           = null;
        $splitIntoWords   = "~'[^']*'(*SKIP)(*F)|\s+~";
        $splitIntoActions = "~'[^']*'(*SKIP)(*F)|\([^)]*\)(*SKIP)(*F)|,~";

        // Remove any newlines
        $this->updateQuery = str_replace("\n", '', $this->updateQuery);

        // Remove trailing whitespace and semicolon
        $this->updateQuery = rtrim($this->updateQuery, "; \t\n\r\0\x0B");

        // Fix up extra spaces around () and in general
        $find        = ['#((\s*)\(\s*([^)\s]+)\s*)(\))#', '#(\s)(\s*)#'];
        $replace     = ['($3)', '$1'];
        $updateQuery = preg_replace($find, $replace, $this->updateQuery);
        $wordArray   = preg_split($splitIntoWords, $updateQuery, -1, PREG_SPLIT_NO_EMPTY);

        $totalWords = \count($wordArray);

        // First, make sure we have an array of at least 6 elements
        // if not, we can't make a check query for this one
        if ($totalWords < 6) {
            // Done with method
            return;
        }

        // We can only make check queries for alter table and create table queries
        $command = strtoupper($wordArray[0] . ' ' . $wordArray[1]);

        if ($command === 'ALTER TABLE') {
            // Check only the last action
            $actions = ltrim(substr($updateQuery, strpos($updateQuery, $wordArray[2]) + \strlen($wordArray[2])));
            $actions = preg_split($splitIntoActions, $actions);

            // Get the last action
            $lastActionArray = preg_split($splitIntoWords, end($actions), -1, PREG_SPLIT_NO_EMPTY);

            // Replace all actions by the last one
            array_splice($wordArray, 3, $totalWords, $lastActionArray);

            $alterCommand = strtoupper($wordArray[3] . ' ' . $wordArray[4]);

            if ($alterCommand === 'RENAME TO') {
                $table                    = $this->fixQuote($wordArray[5]);
                $result                   = 'SELECT table_name FROM information_schema.tables WHERE table_name=' . $table;
                $this->queryType          = 'RENAME_TABLE';
                $this->checkQueryExpected = 1;
                $this->msgElements        = [$table];
            } elseif ($alterCommand === 'ADD COLUMN') {
                $result = 'SELECT column_name'
                    . ' FROM information_schema.columns'
                    . ' WHERE table_name='
                    . $this->fixQuote($wordArray[2])
                    . ' AND column_name=' . $this->fixQuote($wordArray[5]);

                $this->queryType   = 'ADD_COLUMN';
                $this->msgElements = [
                    $this->fixQuote($wordArray[2]),
                    $this->fixQuote($wordArray[5]),
                ];
            } elseif ($alterCommand === 'DROP COLUMN') {
                $result = 'SELECT column_name'
                    . ' FROM information_schema.columns'
                    . ' WHERE table_name='
                    . $this->fixQuote($wordArray[2])
                    . ' AND column_name=' . $this->fixQuote($wordArray[5]);

                $this->queryType          = 'DROP_COLUMN';
                $this->checkQueryExpected = 0;
                $this->msgElements        = [
                    $this->fixQuote($wordArray[2]),
                    $this->fixQuote($wordArray[5]),
                ];
            } elseif ($alterCommand === 'ALTER COLUMN') {
                $alterAction = strtoupper($wordArray[6]);

                if ($alterAction === 'TYPE') {
                    $type = implode(' ', \array_slice($wordArray, 7));

                    if ($pos = stripos($type, ' USING ')) {
                        $type = substr($type, 0, $pos);
                    }

                    if ($pos = strpos($type, '(')) {
                        $datatype = substr($type, 0, $pos);
                    } else {
                        $datatype = $type;
                    }

                    if ($datatype === 'varchar') {
                        $datatype = 'character varying';
                    }

                    $result = 'SELECT column_name, data_type '
                        . 'FROM information_schema.columns WHERE table_name='
                        . $this->fixQuote($wordArray[2]) . ' AND column_name='
                        . $this->fixQuote($wordArray[5])
                        . ' AND data_type=' . $this->fixQuote($datatype);

                    if ($datatype === 'character varying') {
                        $result .= ' AND character_maximum_length = ' . (int) substr($type, $pos + 1);
                    }

                    $this->queryType   = 'CHANGE_COLUMN_TYPE';
                    $this->msgElements = [
                        $this->fixQuote($wordArray[2]),
                        $this->fixQuote($wordArray[5]),
                        $type,
                    ];
                } elseif ($alterAction === 'SET') {
                    $alterType = strtoupper($wordArray[7]);

                    if ($alterType === 'NOT' && strtoupper($wordArray[8]) === 'NULL') {
                        $result = 'SELECT column_name, data_type, is_nullable'
                            . ' FROM information_schema.columns'
                            . ' WHERE table_name=' . $this->fixQuote($wordArray[2])
                            . ' AND column_name=' . $this->fixQuote($wordArray[5])
                            . ' AND is_nullable=' . $this->fixQuote('NO');

                        $this->queryType   = 'CHANGE_COLUMN_TYPE';
                        $this->msgElements = [
                            $this->fixQuote($wordArray[2]),
                            $this->fixQuote($wordArray[5]),
                            'NOT NULL',
                        ];
                    } elseif ($alterType === 'DEFAULT') {
                        $result = 'SELECT column_name, data_type, is_nullable'
                            . ' FROM information_schema.columns'
                            . ' WHERE table_name=' . $this->fixQuote($wordArray[2])
                            . ' AND column_name=' . $this->fixQuote($wordArray[5])
                            . ' AND (CASE (position(' . $this->db->quote('::') . ' in column_default))'
                            . ' WHEN 0 THEN '
                            . ' column_default = ' . $this->db->quote($wordArray[8])
                            . ' ELSE '
                            . ' substring(column_default, 1, (position(' . $this->db->quote('::')
                            . ' in column_default) -1))  = ' . $this->db->quote($wordArray[8])
                            . ' END)';

                        $this->queryType   = 'CHANGE_COLUMN_TYPE';
                        $this->msgElements = [
                            $this->fixQuote($wordArray[2]),
                            $this->fixQuote($wordArray[5]),
                            'DEFAULT ' . $wordArray[8],
                        ];
                    }
                } elseif ($alterAction === 'DROP') {
                    $alterType = strtoupper($wordArray[7]);

                    if ($alterType === 'DEFAULT') {
                        $result = 'SELECT column_name, data_type, is_nullable , column_default'
                            . ' FROM information_schema.columns'
                            . ' WHERE table_name=' . $this->fixQuote($wordArray[2])
                            . ' AND column_name=' . $this->fixQuote($wordArray[5])
                            . ' AND column_default IS NOT NULL';

                        $this->queryType          = 'CHANGE_COLUMN_TYPE';
                        $this->checkQueryExpected = 0;
                        $this->msgElements        = [
                            $this->fixQuote($wordArray[2]),
                            $this->fixQuote($wordArray[5]),
                            'NOT DEFAULT',
                        ];
                    } elseif ($alterType === 'NOT' && strtoupper($wordArray[8]) === 'NULL') {
                        $result = 'SELECT column_name, data_type, is_nullable , column_default'
                            . ' FROM information_schema.columns'
                            . ' WHERE table_name=' . $this->fixQuote($wordArray[2])
                            . ' AND column_name=' . $this->fixQuote($wordArray[5])
                            . ' AND is_nullable = ' . $this->fixQuote('NO');

                        $this->queryType          = 'CHANGE_COLUMN_TYPE';
                        $this->checkQueryExpected = 0;
                        $this->msgElements        = [
                            $this->fixQuote($wordArray[2]),
                            $this->fixQuote($wordArray[5]),
                            'NULL',
                        ];
                    }
                }
            }
        } elseif ($command === 'DROP INDEX') {
            if (strtoupper($wordArray[2] . $wordArray[3]) === 'IFEXISTS') {
                $idx = $this->fixQuote($wordArray[4]);
            } else {
                $idx = $this->fixQuote($wordArray[2]);
            }

            $result                   = 'SELECT * FROM pg_indexes WHERE indexname=' . $idx;
            $this->queryType          = 'DROP_INDEX';
            $this->checkQueryExpected = 0;
            $this->msgElements        = [$this->fixQuote($idx)];
        } elseif ($command === 'CREATE INDEX' || (strtoupper($command . $wordArray[2]) === 'CREATE UNIQUE INDEX')) {
            if ($wordArray[1] === 'UNIQUE') {
                $idx   = $this->fixQuote($wordArray[3]);
                $table = $this->fixQuote($wordArray[5]);
            } else {
                $idx   = $this->fixQuote($wordArray[2]);
                $table = $this->fixQuote($wordArray[4]);
            }

            $result                   = 'SELECT * FROM pg_indexes WHERE indexname=' . $idx . ' AND tablename=' . $table;
            $this->queryType          = 'ADD_INDEX';
            $this->checkQueryExpected = 1;
            $this->msgElements        = [$table, $idx];
        }

        if ($command === 'CREATE TABLE') {
            if (strtoupper($wordArray[2] . $wordArray[3] . $wordArray[4]) === 'IFNOTEXISTS') {
                $table = $this->fixQuote($wordArray[5]);
            } else {
                $table = $this->fixQuote($wordArray[2]);
            }

            $result                   = 'SELECT table_name FROM information_schema.tables WHERE table_name=' . $table;
            $this->queryType          = 'CREATE_TABLE';
            $this->checkQueryExpected = 1;
            $this->msgElements        = [$table];
        }

        // Set fields based on results
        if ($this->checkQuery = $result) {
            // Unchecked status
            $this->checkStatus = 0;
        } else {
            // Skipped
            $this->checkStatus = -1;
        }
    }

    /**
     * Fix up integer. Fixes problem with PostgreSQL integer descriptions.
     * If you change a column to "integer unsigned" it shows
     * as "int(10) unsigned" in the check query.
     *
     * @param   string  $type1  the column type
     * @param   string  $type2  the column attributes
     *
     * @return  string  The original or changed column type.
     *
     * @since   3.0
     */
    private function fixInteger($type1, $type2)
    {
        $result = $type1;

        if (strtolower($type1) === 'integer' && strtolower(substr($type2, 0, 8)) === 'unsigned') {
            $result = 'unsigned int(10)';
        }

        return $result;
    }

    /**
     * Fixes up a string for inclusion in a query.
     * Replaces name quote character with normal quote for literal.
     * Drops trailing semicolon. Injects the database prefix.
     *
     * @param   string  $string  The input string to be cleaned up.
     *
     * @return  string  The modified string.
     *
     * @since   3.0
     */
    private function fixQuote($string)
    {
        $string = str_replace('"', '', $string);
        $string = str_replace(';', '', $string);
        $string = str_replace('#__', $this->db->getPrefix(), $string);

        return $this->db->quote($string);
    }
}

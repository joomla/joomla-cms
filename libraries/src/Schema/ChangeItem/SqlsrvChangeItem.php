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
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Checks the database schema against one SQL Server DDL query to see if it has been run.
 *
 * @since  2.5
 *
 * @deprecated  4.3 will be removed in 6.0
 *              Will be removed without replacement
 */
class SqlsrvChangeItem extends ChangeItem
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
     * @since  2.5
     */
    protected function buildCheckQuery()
    {
        // Initialize fields in case we can't create a check query

        // Change status to skipped
        $this->checkStatus = -1;
        $result            = null;

        // Remove any newlines
        $this->updateQuery = str_replace("\n", '', $this->updateQuery);

        // Fix up extra spaces around () and in general
        $find        = ['#((\s*)\(\s*([^)\s]+)\s*)(\))#', '#(\s)(\s*)#'];
        $replace     = ['($3)', '$1'];
        $updateQuery = preg_replace($find, $replace, $this->updateQuery);
        $wordArray   = explode(' ', $updateQuery);

        // First, make sure we have an array of at least 6 elements
        // if not, we can't make a check query for this one
        if (\count($wordArray) < 6) {
            // Done with method
            return;
        }

        // We can only make check queries for alter table and create table queries
        $command = strtoupper($wordArray[0] . ' ' . $wordArray[1]);

        if ($command === 'ALTER TABLE') {
            $alterCommand = strtoupper($wordArray[3] . ' ' . $wordArray[4]);

            if ($alterCommand === 'ADD') {
                $result            = 'SELECT * FROM INFORMATION_SCHEMA.Columns ' . $wordArray[2] . ' WHERE COLUMN_NAME = ' . $this->fixQuote($wordArray[5]);
                $this->queryType   = 'ADD';
                $this->msgElements = [$this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[5])];
            } elseif ($alterCommand === 'CREATE INDEX') {
                $index             = $this->fixQuote(substr($wordArray[5], 0, strpos($wordArray[5], '(')));
                $result            = 'SELECT * FROM SYS.INDEXES ' . $wordArray[2] . ' WHERE name = ' . $index;
                $this->queryType   = 'CREATE INDEX';
                $this->msgElements = [$this->fixQuote($wordArray[2]), $index];
            } elseif (strtoupper($wordArray[3]) === 'MODIFY' || strtoupper($wordArray[3]) === 'CHANGE') {
                $result            = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS  WHERE table_name = ' . $this->fixQuote($wordArray[2]);
                $this->queryType   = 'ALTER COLUMN COLUMN_NAME =' . $this->fixQuote($wordArray[4]);
                $this->msgElements = [$this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[4])];
            }
        }

        if ($command === 'CREATE TABLE') {
            $table             = $wordArray[2];
            $result            = 'SELECT * FROM sys.TABLES WHERE NAME = ' . $this->fixQuote($table);
            $this->queryType   = 'CREATE_TABLE';
            $this->msgElements = [$this->fixQuote($table)];
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
     * Fix up integer. Fixes problem with MySQL integer descriptions.
     * If you change a column to "integer unsigned" it shows
     * as "int(10) unsigned" in the check query.
     *
     * @param   string  $type1  the column type
     * @param   string  $type2  the column attributes
     *
     * @return  string  The original or changed column type.
     *
     * @since   2.5
     */
    private function fixInteger($type1, $type2)
    {
        $result = $type1;

        if (strtolower($type1) === 'integer' && strtolower(substr($type2, 0, 8)) === 'unsigned') {
            $result = 'int';
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
     * @since   2.5
     */
    private function fixQuote($string)
    {
        $string = str_replace('[', '', $string);
        $string = str_replace(']', '', $string);
        $string = str_replace('"', '', $string);
        $string = str_replace(';', '', $string);
        $string = str_replace('#__', $this->db->getPrefix(), $string);

        return $this->db->quote($string);
    }
}

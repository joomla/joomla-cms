<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Schema
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Checks the database schema against one MySQL DDL query to see if it has been run.
 *
 * @since  2.5
 */
class JSchemaChangeitemMysql extends JSchemaChangeitem
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
		$this->checkStatus = -1; // change status to skipped
		$result = null;

		// Remove any newlines
		$this->updateQuery = str_replace("\n", '', $this->updateQuery);

		// Fix up extra spaces around () and in general
		$find = array('#((\s*)\(\s*([^)\s]+)\s*)(\))#', '#(\s)(\s*)#');
		$replace = array('($3)', '$1');
		$updateQuery = preg_replace($find, $replace, $this->updateQuery);
		$wordArray = explode(' ', $updateQuery);

		// First, make sure we have an array of at least 6 elements
		// if not, we can't make a check query for this one
		if (count($wordArray) < 6)
		{
			// Done with method
			return;
		}

		// We can only make check queries for alter table and create table queries
		$command = strtoupper($wordArray[0] . ' ' . $wordArray[1]);

		// Check for special update statement to reset utf8mb4 conversion status
		if (($command == 'UPDATE `#__UTF8_CONVERSION`'
			|| $command == 'UPDATE #__UTF8_CONVERSION')
			&& strtoupper($wordArray[2]) == 'SET'
			&& strtolower(substr(str_replace('`', '', $wordArray[3]), 0, 9)) == 'converted')
		{
			// Statement is special statement to reset conversion status
			$this->queryType = 'UTF8CNV';

			// Done with method
			return;
		}

		if ($command === 'ALTER TABLE')
		{
			$alterCommand = strtoupper($wordArray[3] . ' ' . $wordArray[4]);

			if ($alterCommand == 'ADD COLUMN')
			{
				$result = 'SHOW COLUMNS IN ' . $wordArray[2] . ' WHERE field = ' . $this->fixQuote($wordArray[5]);
				$this->queryType = 'ADD_COLUMN';
				$this->msgElements = array($this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[5]));
			}
			elseif ($alterCommand == 'ADD INDEX' || $alterCommand == 'ADD KEY')
			{
				if ($pos = strpos($wordArray[5], '('))
				{
					$index = $this->fixQuote(substr($wordArray[5], 0, $pos));
				}
				else
				{
					$index = $this->fixQuote($wordArray[5]);
				}

				$result = 'SHOW INDEXES IN ' . $wordArray[2] . ' WHERE Key_name = ' . $index;
				$this->queryType = 'ADD_INDEX';
				$this->msgElements = array($this->fixQuote($wordArray[2]), $index);
			}
			elseif ($alterCommand == 'ADD UNIQUE')
			{
				$idxIndexName = 5;

				if (isset($wordArray[6]))
				{
					$addCmdCheck = strtoupper($wordArray[5]);

					if ($addCmdCheck == 'INDEX' || $addCmdCheck == 'KEY')
					{
						$idxIndexName = 6;
					}
				}

				if ($pos = strpos($wordArray[$idxIndexName], '('))
				{
					$index = $this->fixQuote(substr($wordArray[$idxIndexName], 0, $pos));
				}
				else
				{
					$index = $this->fixQuote($wordArray[$idxIndexName]);
				}

				$result = 'SHOW INDEXES IN ' . $wordArray[2] . ' WHERE Key_name = ' . $index;
				$this->queryType = 'ADD_INDEX';
				$this->msgElements = array($this->fixQuote($wordArray[2]), $index);
			}
			elseif ($alterCommand == 'DROP INDEX' || $alterCommand == 'DROP KEY')
			{
				$index = $this->fixQuote($wordArray[5]);
				$result = 'SHOW INDEXES IN ' . $wordArray[2] . ' WHERE Key_name = ' . $index;
				$this->queryType = 'DROP_INDEX';
				$this->checkQueryExpected = 0;
				$this->msgElements = array($this->fixQuote($wordArray[2]), $index);
			}
			elseif ($alterCommand == 'DROP COLUMN')
			{
				$index = $this->fixQuote($wordArray[5]);
				$result = 'SHOW COLUMNS IN ' . $wordArray[2] . ' WHERE Field = ' . $index;
				$this->queryType = 'DROP_COLUMN';
				$this->checkQueryExpected = 0;
				$this->msgElements = array($this->fixQuote($wordArray[2]), $index);
			}
			elseif (strtoupper($wordArray[3]) == 'MODIFY')
			{
				// Kludge to fix problem with "integer unsigned"
				$type = $wordArray[5];

				if (isset($wordArray[6]))
				{
					$type = $this->fixInteger($wordArray[5], $wordArray[6]);
				}

				/**
				 * When we made the UTF8MB4 conversion then text becomes medium text - so loosen the checks to these two types
				 * otherwise (for example) the profile fields profile_value check fails - see https://github.com/joomla/joomla-cms/issues/9258
				 */
				$typeCheck = $this->fixUtf8mb4TypeChecks($type);

				$result = 'SHOW COLUMNS IN ' . $wordArray[2] . ' WHERE field = ' . $this->fixQuote($wordArray[4])
					. ' AND ' . $typeCheck;
				$this->queryType = 'CHANGE_COLUMN_TYPE';
				$this->msgElements = array($this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[4]), $type);
			}
			elseif (strtoupper($wordArray[3]) == 'CHANGE')
			{
				// Kludge to fix problem with "integer unsigned"
				$type = $wordArray[6];

				if (isset($wordArray[7]))
				{
					$type = $this->fixInteger($wordArray[6], $wordArray[7]);
				}

				/**
				 * When we made the UTF8MB4 conversion then text becomes medium text - so loosen the checks to these two types
				 * otherwise (for example) the profile fields profile_value check fails - see https://github.com/joomla/joomla-cms/issues/9258
				 */
				$typeCheck = $this->fixUtf8mb4TypeChecks($type);

				$result = 'SHOW COLUMNS IN ' . $wordArray[2] . ' WHERE field = ' . $this->fixQuote($wordArray[5])
					. ' AND ' . $typeCheck;
				$this->queryType = 'CHANGE_COLUMN_TYPE';
				$this->msgElements = array($this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[5]), $type);
			}
		}

		if ($command == 'CREATE TABLE')
		{
			if (strtoupper($wordArray[2] . $wordArray[3] . $wordArray[4]) == 'IFNOTEXISTS')
			{
				$table = $wordArray[5];
			}
			else
			{
				$table = $wordArray[2];
			}

			$result = 'SHOW TABLES LIKE ' . $this->fixQuote($table);
			$this->queryType = 'CREATE_TABLE';
			$this->msgElements = array($this->fixQuote($table));
		}

		// Set fields based on results
		if ($this->checkQuery = $result)
		{
			// Unchecked status
			$this->checkStatus = 0;
		}
		else
		{
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

		if (strtolower($type1) == "integer" && strtolower(substr($type2, 0, 8)) == 'unsigned')
		{
			$result = 'int(10) unsigned';
		}
		elseif (strtolower(substr($type2, 0, 8)) == 'unsigned')
		{
			$result = $type1 . ' unsigned';
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
		$string = str_replace('`', '', $string);
		$string = str_replace(';', '', $string);
		$string = str_replace('#__', $this->db->getPrefix(), $string);

		return $this->db->quote($string);
	}

	/**
	 * Make check query for column changes/modifications tolerant
	 * for automatic type changes of text columns, e.g. from TEXT
	 * to MEDIUMTEXT, after comnversion from utf8 to utf8mb4
	 *
	 * @param   string  $type  The column type found in the update query
	 *
	 * @return  string  The condition for type check in the check query
	 *
	 * @since   3.5
	 */
	private function fixUtf8mb4TypeChecks($type)
	{
		$fixedType = str_replace(';', '', $type);

		if ($this->db->hasUTF8mb4Support())
		{
			$uType = strtoupper($fixedType);

			if ($uType === 'TINYTEXT')
			{
				$typeCheck = 'type IN (' . $this->db->quote('TINYTEXT') . ',' . $this->db->quote('TEXT') . ')';
			}
			elseif ($uType === 'TEXT')
			{
				$typeCheck = 'type IN (' . $this->db->quote('TEXT') . ',' . $this->db->quote('MEDIUMTEXT') . ')';
			}
			elseif ($uType === 'MEDIUMTEXT')
			{
				$typeCheck = 'type IN (' . $this->db->quote('MEDIUMTEXT') . ',' . $this->db->quote('LONGTEXT') . ')';
			}
			else
			{
				$typeCheck = 'type = ' . $this->db->quote($fixedType);
			}
		}
		else
		{
			$typeCheck = 'type = ' . $this->db->quote($fixedType);
		}

		return $typeCheck;
	}
}

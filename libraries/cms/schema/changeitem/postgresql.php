<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Schema
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Checks the database schema against one PostgreSQL DDL query to see if it has been run.
 *
 * @since  3.0
 */
class JSchemaChangeitemPostgresql extends JSchemaChangeitem
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

		if ($command === 'ALTER TABLE')
		{
			$alterCommand = strtoupper($wordArray[3] . ' ' . $wordArray[4]);

			if ($alterCommand === 'ADD COLUMN')
			{
				$result = 'SELECT column_name FROM information_schema.columns WHERE table_name='
				. $this->fixQuote($wordArray[2]) . ' AND column_name=' . $this->fixQuote($wordArray[5]);

				$this->queryType = 'ADD_COLUMN';
				$this->msgElements = array($this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[5]));
			}
			elseif ($alterCommand === 'DROP COLUMN')
			{
				$result = 'SELECT column_name FROM information_schema.columns WHERE table_name='
				. $this->fixQuote($wordArray[2]) . ' AND column_name=' . $this->fixQuote($wordArray[5]);

				$this->queryType = 'DROP_COLUMN';
				$this->checkQueryExpected = 0;
				$this->msgElements = array($this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[5]));
			}
			elseif ($alterCommand === 'ALTER COLUMN')
			{
				if (strtoupper($wordArray[6]) === 'TYPE')
				{
					$type = '';

					for ($i = 7; $i < count($wordArray); $i++)
					{
						$type .= $wordArray[$i] . ' ';
					}

					if ($pos = strpos($type, '('))
					{
						$type = substr($type, 0, $pos);
					}

					if ($pos = strpos($type, ';'))
					{
						$type = substr($type, 0, $pos);
					}

					$result = 'SELECT column_name, data_type FROM information_schema.columns WHERE table_name='
						. $this->fixQuote($wordArray[2]) . ' AND column_name=' . $this->fixQuote($wordArray[5])
						. ' AND data_type=' . $this->fixQuote($type);

					$this->queryType = 'CHANGE_COLUMN_TYPE';
					$this->msgElements = array($this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[5]), $type);
				}
				elseif (strtoupper($wordArray[7] . ' ' . $wordArray[8]) === 'NOT NULL')
				{
					if (strtoupper($wordArray[6]) === 'SET')
					{
						// SET NOT NULL
						$isNullable = $this->fixQuote('NO');
					}
					else
					{
						// DROP NOT NULL
						$isNullable = $this->fixQuote('YES');
					}

					$result = 'SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name='
						. $this->fixQuote($wordArray[2]) . ' AND column_name=' . $this->fixQuote($wordArray[5])
						. ' AND is_nullable=' . $isNullable;

					$this->queryType = 'CHANGE_COLUMN_TYPE';
					$this->checkQueryExpected = 1;
					$this->msgElements = array($this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[5]), $isNullable);
				}
				elseif (strtoupper($wordArray[7]) === 'DEFAULT')
				{
					if (strtoupper($wordArray[6]) === 'SET')
					{
						$isNullDef = 'IS NOT NULL';
					}
					else
					{
						// DROP DEFAULT
						$isNullDef = 'IS NULL';
					}

					$result = 'SELECT column_name, data_type, column_default FROM information_schema.columns WHERE table_name='
						. $this->fixQuote($wordArray[2]) . ' AND column_name=' . $this->fixQuote($wordArray[5])
						. ' AND column_default ' . $isNullDef;

					$this->queryType = 'CHANGE_COLUMN_TYPE';
					$this->checkQueryExpected = 1;
					$this->msgElements = array($this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[5]), $isNullDef);
				}
			}
		}
		elseif ($command === 'DROP INDEX')
		{
			if (strtoupper($wordArray[2] . $wordArray[3]) === 'IFEXISTS')
			{
				$idx = $this->fixQuote($wordArray[4]);
			}
			else
			{
				$idx = $this->fixQuote($wordArray[2]);
			}

			$result = 'SELECT * FROM pg_indexes WHERE indexname=' . $idx;
			$this->queryType = 'DROP_INDEX';
			$this->checkQueryExpected = 0;
			$this->msgElements = array($this->fixQuote($idx));
		}
		elseif ($command === 'CREATE INDEX' || (strtoupper($command . $wordArray[2]) === 'CREATE UNIQUE INDEX'))
		{
			if ($wordArray[1] === 'UNIQUE')
			{
				$idx = $this->fixQuote($wordArray[3]);
				$table = $this->fixQuote($wordArray[5]);
			}
			else
			{
				$idx = $this->fixQuote($wordArray[2]);
				$table = $this->fixQuote($wordArray[4]);
			}

			$result = 'SELECT * FROM pg_indexes WHERE indexname=' . $idx . ' AND tablename=' . $table;
			$this->queryType = 'ADD_INDEX';
			$this->checkQueryExpected = 1;
			$this->msgElements = array($table, $idx);
		}

		if ($command === 'CREATE TABLE')
		{
			if (strtoupper($wordArray[2] . $wordArray[3] . $wordArray[4]) === 'IFNOTEXISTS')
			{
				$table = $this->fixQuote($wordArray[5]);
			}
			else
			{
				$table = $this->fixQuote($wordArray[2]);
			}

			$result = 'SELECT table_name FROM information_schema.tables WHERE table_name=' . $table;
			$this->queryType = 'CREATE_TABLE';
			$this->checkQueryExpected = 1;
			$this->msgElements = array($table);
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

		if (strtolower($type1) === 'integer' && strtolower(substr($type2, 0, 8)) === 'unsigned')
		{
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

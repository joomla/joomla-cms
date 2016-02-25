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

		if ($command === 'ALTER TABLE')
		{
			$alterCommand = strtoupper($wordArray[3] . ' ' . $wordArray[4]);

			// If UTF8MB4 is not supported is has to be changed to utf8
			$doUtf8mb4Replace = !$this->db->hasUTF8mb4Support();

			if ($alterCommand == 'ADD COLUMN')
			{
				$colName = $this->fixQuote($wordArray[5]);
				$result = 'SHOW COLUMNS IN ' . $wordArray[2]
					. ' WHERE ' . $this->db->quoteName('field') . ' = ' . $colName;
				$this->queryType = 'ADD_COLUMN';
				$this->msgElements = array($this->fixQuote($wordArray[2]), $colName);
			}
			elseif ($alterCommand == 'ADD INDEX' || $alterCommand == 'ADD KEY')
			{
				$checkArray = $this->getIndexDetails($wordArray, 5, false);

				if (count($checkArray) == 2)
				{
					$result = $checkArray[0];
					$this->queryType = 'ADD_INDEX';
					$this->msgElements = array($this->fixQuote($wordArray[2]), $checkArray[1]);
				}
			}
			elseif ($alterCommand == 'ADD UNIQUE')
			{
				$addCommand = strtoupper($wordArray[5]);

				if ((count($wordArray) > 6)
					&& ($addCommand == 'INDEX' || $addCommand == 'KEY'))
				{
					$idxKey = 6;
				}
				else
				{
					$idxKey = 5;
				}

				$checkArray = $this->getIndexDetails($wordArray, $idxKey, false);

				if (count($checkArray) == 2)
				{
					$result = $checkArray[0];
					$this->queryType = 'ADD_INDEX';
					$this->msgElements = array($this->fixQuote($wordArray[2]), $checkArray[1]);
				}
			}
			elseif ($alterCommand == 'DROP INDEX' || $alterCommand == 'DROP KEY')
			{
				$isDropOnly = true;
				$posIdx = 5;
				$posCheck = 0;

				if ($pos = strpos($wordArray[5], ','))
				{
					$len = strlen($wordArray[5]);

					if ($pos == ($len - 2))
					{
						if (isset($wordArray[6]))
						{
							if (strtoupper($wordArray[6]) == 'ADD')
							{
								$posCheck = 7;
							}
						}
					}
					elseif (($pos == ($len - 5))
						&& (strtoupper(substr($wordArray[5], -4)) == ',ADD'))
					{
						$posCheck = 6;
					}
				}
				else
				{
					if (isset($wordArray[6]))
					{
						$testWord = strtoupper($wordArray[6]);
						if ($testWord == ',ADD')
						{
							$posCheck = 7;
						}
						elseif ($testWord == ',')
						{
							if (isset($wordArray[7]))
							{
								$testWord2 = strtoupper($wordArray[7]);
								if ($testWord2 == 'ADD')
								{
									$posCheck = 8;
								}
							}
						}
					}
				}

				if ($posCheck > 0)
				{
					if (isset($wordArray[$posCheck]))
					{
						$testWord = strtoupper($wordArray[$posCheck]);

						if ($testWord == 'INDEX' || $testWord == 'KEY')
						{
							$isDropOnly = false;
							$posIdx = $posCheck + 1;
						}
						elseif ($testWord == 'UNIQUE')
						{
							$posCheck++;

							$isDropOnly = false;
							$posIdx = $posCheck;

							if (isset($wordArray[$posCheck]))
							{
								$addCommand = strtoupper($wordArray[$posCheck]);

								if ($addCommand == 'INDEX' || $addCommand == 'KEY')
								{
									$posIdx++;
								}
							}
						}
					}
				}

				$checkArray = $this->getIndexDetails($wordArray, $posIdx, $isDropOnly);

				if (count($checkArray) == 2)
				{
					$result = $checkArray[0];
					$this->msgElements = array($this->fixQuote($wordArray[2]), $checkArray[1]);

					if ($isDropOnly)
					{
						$this->queryType = 'DROP_INDEX';
						$this->checkQueryExpected = 0;
					}
					else
					{
						$this->queryType = 'ADD_INDEX';
					}
				}
			}
			elseif ($alterCommand == 'DROP COLUMN')
			{
				$colName = $this->fixQuote($wordArray[5]);
				$result = 'SHOW COLUMNS IN ' . $wordArray[2]
					. ' WHERE ' . $this->db->quoteName('field') . ' = ' . $colName;
				$this->queryType = 'DROP_COLUMN';
				$this->checkQueryExpected = 0;
				$this->msgElements = array($this->fixQuote($wordArray[2]), $colName);
			}
			elseif (($alterCommand == 'CONVERT TO') && (count($wordArray) > 9))
			{
				if ((strtoupper($wordArray[5] . $wordArray[6]) == 'CHARACTERSET')
					&& ($wordArray[8] == 'COLLATE'))
				{
					$table = $wordArray[2];
					$collat = $this->fixQuote(strtolower($wordArray[9]));

					if ($doUtf8mb4Replace)
					{
						$collat = str_replace('utf8mb4', 'utf8', $collat);
					}

					$result = 'SHOW TABLE STATUS WHERE ' . $this->db->quoteName('name')
						. ' = ' . $this->fixQuote($table)
						. ' AND ' . $this->db->quoteName('collation') . ' = ' . $collat;
					$this->queryType = 'CREATE_TABLE';
					$this->msgElements = array($this->fixQuote($table) . ' (COLLATION ' . $collat . ')');
				}
			}
			elseif (strtoupper($wordArray[3]) == 'MODIFY')
			{
				$checkArray = $this->getColumnDetails($wordArray, 5, $doUtf8mb4Replace);

				if (count($checkArray) == 2)
				{
					$result = 'SHOW FULL COLUMNS IN ' . $wordArray[2]
						. ' WHERE ' . $this->db->quoteName('field') . ' = ' . $this->fixQuote($wordArray[4])
						. $checkArray[0];
					$this->queryType = 'CHANGE_COLUMN_TYPE';
					$this->msgElements = array($this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[4]), $checkArray[1]);
				}
			}
			elseif (strtoupper($wordArray[3]) == 'CHANGE')
			{
				$checkArray = $this->getColumnDetails($wordArray, 6, $doUtf8mb4Replace);

				if (count($checkArray) == 2)
				{
					$result = 'SHOW FULL COLUMNS IN ' . $wordArray[2]
						. ' WHERE ' . $this->db->quoteName('field') . ' = ' . $this->fixQuote($wordArray[5])
						. $checkArray[0];
					$this->queryType = 'CHANGE_COLUMN_TYPE';
					$this->msgElements = array($this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[5]), $checkArray[1]);
				}
			}
		}
		elseif ($command == 'CREATE TABLE')
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
	 * Fixes up a string for inclusion in a query.
	 * Replaces name quote character with normal quote for literal.
	 * Drops trailing semi-colon. Injects the database prefix.
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
	 * Get check query conditions and msg elements for column details 
	 * from the update query's words array.
	 *
	 * @param   array    $wordArray  Array with the update query's words
	 * @param   integer  $idxStart   Index of $wordArray where to start
	 * @param   boolean  $doUtf8mb4  Flag if utf8mb4 to be replaced
	 *
	 * @return  array    Array of strings with
	 *                     [0] = condition appended to the check query,
	 *                     [1] = parameter for the message text
	 *
	 * @since   3.5
	 */
	private function getColumnDetails(array $wordArray, $idxStart, $doUtf8mb4)
	{
		if (!isset($wordArray[$idxStart]))
		{
			return array();
		}

		$idxCurr = $idxStart + 1;

		// Kludge to fix problem with "integer unsigned"
		$type = $this->fixQuote($wordArray[$idxStart]);

		if (isset($wordArray[$idxCurr]))
		{
			if (strtolower($wordArray[$idxStart]) == "integer"
				&& strtolower(substr($wordArray[$idxCurr], 0, 8)) == 'unsigned')
			{
				$type = 'int(10) unsigned';
				$idxCurr++;
			}
		}

		$condition = ' AND ' . $this->db->quoteName('type') . ' = ' . $type;
		$attrMessage = '';

		// Get optional attributes if some
		$idxEnd = count($wordArray);

		$hasNullValue = false;
		$nullValue = false;

		while ($idxCurr < $idxEnd)
		{
			if (strtoupper($wordArray[$idxCurr]) == 'CHARACTER')
			{
				$idxCollation = $idxCurr + 4;

				if ($idxCollation < $idxEnd)
				{
					if ((strtoupper($wordArray[$idxCurr + 1]) == 'SET')
						&& (strtoupper($wordArray[$idxCurr + 3]) == 'COLLATE'))
					{
						$coll = $this->fixQuote($wordArray[$idxCollation]);
						if ($doUtf8mb4)
						{
							$coll = str_replace('utf8mb4', 'utf8', $coll);
						}
						$condition = $condition
							. ' AND ' . $this->db->quoteName('collation') . ' = ' . $coll;

						$attrMessage = $attrMessage . ' COLLATION=' . $coll;

						$idxCurr = $idxCollation;
					}
				}
			}
			/* elseif (strtoupper($wordArray[$idxCurr]) == 'COMMENT')
			{
				$idxCurr++;

				if (isset($wordArray[$idxCurr]))
				{
					$com = str_replace(';', '', $wordArray[$idxCurr]);

					if ($com != '\'\'')
					{
						$com = $this->fixQuote($com);
					}

					$condition = $condition
						. ' AND ' . $this->db->quoteName('comment') . ' = ' . $com;

					$attrMessage = $attrMessage . ' COMMENT=' . $com;
				}
			}
			elseif (strtoupper($wordArray[$idxCurr]) == 'DEFAULT')
			{
				$idxCurr++;

				if (isset($wordArray[$idxCurr]))
				{
					$def = str_replace(';', '', $wordArray[$idxCurr]);

					if ($def != '\'\'')
					{
						$def = $this->fixQuote($def);
					}

					$condition = $condition
						. ' AND ' . $this->db->quoteName('default') . ' = ' . $def;

					$attrMessage = $attrMessage . ' DEFAULT=' . $def;
				}
			} */
			elseif (strtoupper($wordArray[$idxCurr]) == 'NOT')
			{
				$idxCurr++;

				if (isset($wordArray[$idxCurr]))
				{
					if (substr(strtoupper($wordArray[$idxCurr]), 0, 4) == 'NULL')
					{
						$hasNullValue = true;
						$nullValue = false;
					}
				}
			}
			elseif (substr(strtoupper($wordArray[$idxCurr]), 0, 4) == 'NULL')
			{
					$hasNullValue = true;
					$nullValue = true;
			}

			$idxCurr++;
		}

		if ($hasNullValue)
		{
			$strNullVal = ($nullValue ? 'TRUE' : 'FALSE');
			$condition = $condition . ' AND '
				. $this->db->quoteName('null') . ' = ' . $strNullVal;

			$attrMessage = $attrMessage . ' null=' . $strNullVal;
		}

		if ($attrMessage != '')
		{
			return array($condition, $type . ' ' . $attrMessage);
		}

		return array($condition, $type);
	}

	/**
	 * Get check query and msg elements for index details 
	 * from the update query's words array.
	 *
	 * @param   array    $wordArray  Array with the update query's words
	 * @param   integer  $idxStart   Index of $wordArray where to start
	 * @param   boolean  $isDrop     Flag if drop index so no details
	 *
	 * @return  array    Array of strings with
	 *                     [0] = check query,
	 *                     [1] = parameter for the message text
	 *
	 * @since   3.5
	 */
	private function getIndexDetails(array $wordArray, $idxStart, $isDrop)
	{
		if (!isset($wordArray[$idxStart]))
		{
			return array();
		}

		$firstBracketPos = 0;
		$removeFirstBracket = false;

		if ($pos = strpos($wordArray[$idxStart], '('))
		{
			$index = $this->fixQuote(substr($wordArray[$idxStart], 0, $pos));

			if (!$isDrop)
			{
				if ($pos < (strlen($wordArray[$idxStart]) - 2))
				{
					$idxListStart = $idxStart;
					$firstBracketPos = $pos;
					$removeFirstBracket = true;
				}
				else
				{
					$idxListStart = $idxStart + 1;
				}
			}
		}
		else
		{
			$index = $this->fixQuote($wordArray[$idxStart]);

			if (!$isDrop)
			{
				$idxListStart = $idxStart + 1;

				if (isset($wordArray[$idxListStart]))
				{
					if ($wordArray[$idxListStart] == '(')
					{
						$idxListStart++;
					}
					elseif (substr($wordArray[$idxListStart], 0, 1) == '(')
					{
						$removeFirstBracket = true;
					}
				}
			}
		}

		// Drop statement: No column list to be checked
		if ($isDrop)
		{
			return array('SHOW INDEXES IN ' . $wordArray[2]
				. ' WHERE ' . $this->db->quoteName('key_name') . ' = ' . $index,
				$index);
		}

		$tmpArray = array();

		$tmpStr = $wordArray[$idxListStart];
		if ($removeFirstBracket)
		{
			$tmpStr = substr($tmpStr, $firstBracketPos + 1, strlen($tmpStr));
		}
		$tmpStr = str_replace(' ', '', $tmpStr);
		$tmpStr = str_replace('`', '', $tmpStr);
		$tmpArray[] = $tmpStr;

		for ($i = $idxListStart + 1; $i < count($wordArray); $i++)
		{
			$tmpStr = $wordArray[$i];
			$tmpStr = str_replace(' ', '', $tmpStr);
			$tmpStr = str_replace('`', '', $tmpStr);
			$tmpArray[] = $tmpStr;
		}

		$colList = implode($tmpArray);

		if (substr($colList, -1) == ';')
		{
			$colList = substr($colList, 0, -1);
		}

		// Invalid column list, no closing bracket
		if (substr($colList, -1) != ')')
		{
			return array();
		}

		$colList = substr($colList, 0, strlen($colList) - 1);

		$dbname = JFactory::getApplication()->get('db');

		return array(
			'SELECT s.`index_name` FROM ('
				. 'SELECT `table_name`, `index_name`,'
				. ' GROUP_CONCAT('
					. 'LOWER(IFNULL(CONCAT(`column_name`, ' . $this->db->quote('(')
						. ' ,`sub_part`, ' . $this->db->quote(')') . '), `column_name`))'
					. ' ORDER BY seq_in_index) AS `col_list`'
				. ' FROM information_schema.statistics'
				. ' WHERE `table_schema` = ' . $this->db->quote($dbname)
				. ' AND `table_name` = ' . $this->fixQuote($wordArray[2])
				. ' AND `index_name` = ' . $index
				. ' GROUP BY `table_name`,`index_name`) AS s'
			. ' WHERE s.`col_list` = ' . $this->db->quote($colList),
			$index . '(' . $colList . ')');
	}
}

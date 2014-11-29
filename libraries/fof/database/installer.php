<?php
/**
 * @package		fof
 * @copyright	2014 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU GPL version 3 or later
 */

class FOFDatabaseInstaller
{
	/** @var  JDatabase  The database connector object */
	private $db = null;

	/**
	 * @var   FOFInput  Input variables
	 */
	protected $input = array();

	/** @var  string  The directory where the XML schema files are stored */
	private $xmlDirectory = null;

	/** @var  array  A list of the base names of the XML schema files */
	public $xmlFiles = array('mysql', 'mysqli', 'pdomysql', 'postgresql', 'sqlsrv', 'mssql');

	/**
	 * Public constructor
	 *
	 * @param   array  $config  The configuration array
	 */
	public function __construct($config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		// Get the input
		if (array_key_exists('input', $config))
		{
			if ($config['input'] instanceof FOFInput)
			{
				$this->input = $config['input'];
			}
			else
			{
				$this->input = new FOFInput($config['input']);
			}
		}
		else
		{
			$this->input = new FOFInput;
		}

		// Set the database object
		if (array_key_exists('dbo', $config))
		{
			$this->db = $config['dbo'];
		}
		else
		{
			$this->db = FOFPlatform::getInstance()->getDbo();
		}

		// Set the $name/$_name variable
		$component = $this->input->getCmd('option', 'com_foobar');

		if (array_key_exists('option', $config))
		{
			$component = $config['option'];
		}

		// Figure out where the XML schema files are stored
		if (array_key_exists('dbinstaller_directory', $config))
		{
			$this->xmlDirectory = $config['dbinstaller_directory'];
		}
		else
		{
			// Nothing is defined, assume the files are stored in the sql/xml directory inside the component's administrator section
			$directories = FOFPlatform::getInstance()->getComponentBaseDirs($component);
			$this->setXmlDirectory($directories['admin'] . '/sql/xml');
		}

		// Do we have a set of XML files to look for?
		if (array_key_exists('dbinstaller_files', $config))
		{
			$files = $config['dbinstaller_files'];

			if (!is_array($files))
			{
				$files = explode(',', $files);
			}

			$this->xmlFiles = $files;
		}

	}

	/**
	 * Sets the directory where XML schema files are stored
	 *
	 * @param   string  $xmlDirectory
	 */
	public function setXmlDirectory($xmlDirectory)
	{
		$this->xmlDirectory = $xmlDirectory;
	}

	/**
	 * Returns the directory where XML schema files are stored
	 *
	 * @return  string
	 */
	public function getXmlDirectory()
	{
		return $this->xmlDirectory;
	}

	/**
	 * Creates or updates the database schema
	 *
	 * @return  void
	 *
	 * @throws  Exception  When a database query fails and it doesn't have the canfail flag
	 */
	public function updateSchema()
	{
		// Get the schema XML file
		$xml = $this->findSchemaXml();

		if (empty($xml))
		{
			return;
		}

		// Make sure there are SQL commands in this file
		if (!$xml->sql)
		{
			return;
		}

		// Walk the sql > action tags to find all tables
		$tables = array();
		/** @var SimpleXMLElement $actions */
		$actions = $xml->sql->children();

		/** @var SimpleXMLElement $action */
		foreach ($actions as $action)
		{
			// Get the attributes
			$attributes = $action->attributes();

			// Get the table / view name
			$table = $attributes->table ? $attributes->table : '';

			if (empty($table))
			{
				continue;
			}

			// Am I allowed to let this action fail?
			$canFailAction = $attributes->canfail ? $attributes->canfail : 0;

			// Evaluate conditions
			$shouldExecute = true;

			/** @var SimpleXMLElement $node */
			foreach ($action->children() as $node)
			{
				if ($node->getName() == 'condition')
				{
					// Get the operator
					$operator = $node->attributes()->operator ? (string)$node->attributes()->operator : 'and';
					$operator = empty($operator) ? 'and' : $operator;

					$condition = $this->conditionMet($table, $node);

					switch ($operator)
					{
						case 'not':
							$shouldExecute = $shouldExecute && !$condition;
							break;

						case 'or':
							$shouldExecute = $shouldExecute || $condition;
							break;

						case 'nor':
							$shouldExecute = $shouldExecute || !$condition;
							break;

						case 'xor':
							$shouldExecute = ($shouldExecute xor $condition);
							break;

						case 'maybe':
							$shouldExecute = $condition ? true : $shouldExecute;
							break;

						default:
							$shouldExecute = $shouldExecute && $condition;
							break;
					}
				}

				if (!$shouldExecute)
				{
					break;
				}
			}

			// Make sure all conditions are met
			if (!$shouldExecute)
			{
				continue;
			}

			// Execute queries
			foreach ($action->children() as $node)
			{
				if ($node->getName() == 'query')
				{
					$canFail = $node->attributes->canfail ? $node->attributes->canfail : $canFailAction;

					$this->db->setQuery((string) $node);

					try
					{
						if (version_compare(JVERSION, '3.1', 'lt'))
						{
							$handlers = array(
								E_NOTICE 	=> JError::getErrorHandling(E_NOTICE),
								E_WARNING	=> JError::getErrorHandling(E_WARNING),
								E_ERROR		=> JError::getErrorHandling(E_ERROR),
							);
							$handlers[E_NOTICE]['options'] = isset($handlers[E_NOTICE]['options']) ? $handlers[E_NOTICE]['options'] : null;
							$handlers[E_WARNING]['options'] = isset($handlers[E_WARNING]['options']) ? $handlers[E_WARNING]['options'] : null;
							$handlers[E_ERROR]['options'] = isset($handlers[E_ERROR]['options']) ? $handlers[E_ERROR]['options'] : null;
							JError::setErrorHandling(E_NOTICE, 'ignore');
							JError::setErrorHandling(E_WARNING, 'ignore');
							JError::setErrorHandling(E_ERROR, 'ignore');
						}

						$this->db->execute();

						if (version_compare(JVERSION, '3.1', 'lt'))
						{
							JError::setErrorHandling(E_NOTICE, $handlers[E_NOTICE]['mode'], $handlers[E_NOTICE]['options']);
							JError::setErrorHandling(E_WARNING, $handlers[E_WARNING]['mode'], $handlers[E_WARNING]['options']);
							JError::setErrorHandling(E_ERROR, $handlers[E_ERROR]['mode'], $handlers[E_ERROR]['options']);
						}

						if (version_compare(JVERSION, '3.1', 'lt') && $this->db->getErrorNum())
						{
							if (!$canFail)
							{
								JError::raise(E_WARNING, $this->db->getErrorNum(), $this->db->getErrorMsg());
							}
						}
						/**/
					}
					catch (Exception $e)
					{
						// If we are not allowed to fail, throw back the exception we caught
						if (!$canFail)
						{
							throw $e;
						}
					}
				}
			}
		}
	}

	/**
	 * Uninstalls the database schema
	 *
	 * @return  void
	 */
	public function removeSchema()
	{
		// Get the schema XML file
		$xml = $this->findSchemaXml();

		if (empty($xml))
		{
			return;
		}

		// Make sure there are SQL commands in this file
		if (!$xml->sql)
		{
			return;
		}

		// Walk the sql > action tags to find all tables
		$tables = array();
		/** @var SimpleXMLElement $actions */
		$actions = $xml->sql->children();

		/** @var SimpleXMLElement $action */
		foreach ($actions as $action)
		{
			$attributes = $action->attributes();
			$tables[] = (string)$attributes->table;
		}

		// Simplify the tables list
		$tables = array_unique($tables);

		// Start dropping tables
		foreach ($tables as $table)
		{
			try
			{
				$this->db->dropTable($table);
			}
			catch (Exception $e)
			{
				// Do not fail if I can't drop the table
			}
		}
	}

	/**
	 * Find an suitable schema XML file for this database type and return the SimpleXMLElement holding its information
	 *
	 * @return  null|SimpleXMLElement  Null if no suitable schema XML file is found
	 */
	protected function findSchemaXml()
	{
		$driverType = $this->db->name;
		$xml = null;

		foreach ($this->xmlFiles as $baseName)
		{
			// Remove any accidental whitespace
			$baseName = trim($baseName);

			// Get the full path to the file
			$fileName = $this->xmlDirectory . '/' . $baseName . '.xml';

			// Make sure the file exists
			if (!@file_exists($fileName))
			{
				continue;
			}

			// Make sure the file is a valid XML document
			try
			{
				$xml = new SimpleXMLElement($fileName, LIBXML_NONET, true);
			}
			catch (Exception $e)
			{
				$xml = null;
				continue;
			}

			// Make sure the file is an XML schema file
			if ($xml->getName() != 'schema')
			{
				$xml = null;
				continue;
			}

			if (!$xml->meta)
			{
				$xml = null;
				continue;
			}

			if (!$xml->meta->drivers)
			{
				$xml = null;
				continue;
			}

			/** @var SimpleXMLElement $drivers */
			$drivers = $xml->meta->drivers;

			foreach ($drivers->children() as $driverTypeTag)
			{
				$thisDriverType = (string)$driverTypeTag;

				if ($thisDriverType == $driverType)
				{
					return $xml;
				}
			}

			$xml = null;
		}

		return $xml;
	}

	/**
	 * Checks if a condition is met
	 *
	 * @param   string            $table  The table we're operating on
	 * @param   SimpleXMLElement  $node   The condition definition node
	 *
	 * @return  bool
	 */
	protected function conditionMet($table, SimpleXMLElement $node)
	{
		static $allTables = null;

		if (empty($allTables))
		{
			$allTables = $this->db->getTableList();
		}

		// Does the table exist?
		$tableNormal = $this->db->replacePrefix($table);
		$tableExists = in_array($tableNormal, $allTables);

		// Initialise
		$condition = false;

		// Get the condition's attributes
		$attributes = $node->attributes();
		$type = $attributes->type ? $attributes->type : null;
		$value = $attributes->value ? (string) $attributes->value : null;

		switch ($type)
		{
			// Check if a table or column is missing
			case 'missing':
				$tableName = (string)$table;
				$fieldName = (string)$value;

				if (empty($fieldName))
				{
					$condition = !$tableExists;
				}
				else
				{
					$tableColumns = $this->db->getTableColumns($tableNormal, true);
					$condition = !array_key_exists($fieldName, $tableColumns);
				}
				break;

			// Check if a column type matches the "coltype" attribute
			case 'type':
				$tableColumns = $this->db->getTableColumns($table, false);
				$condition = false;

				if (array_key_exists($value, $tableColumns))
				{
					$coltype = $attributes->coltype ? $attributes->coltype : null;

					if (!empty($coltype))
					{
						$coltype = strtolower($coltype);
						$currentType = strtolower($tableColumns[$value]->Type);

						$condition = ($coltype == $currentType);
					}
				}

				break;

			// Check if the result of a query matches our expectation
			case 'equals':
				$query = (string)$node;
				$this->db->setQuery($query);

				try
				{
					$result = $this->db->loadResult();
					$condition = ($result == $value);
				}
				catch (Exception $e)
				{
					return false;
				}

				break;

			// Always returns true
			case 'true':
				return true;
				break;

			default:
				return false;
				break;
		}

		return $condition;
	}
}

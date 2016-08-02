<?php
/**
 *  @package     FrameworkOnFramework
 *  @subpackage  config
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 *  @license     GNU General Public License version 2, or later
 */

defined('FOF_INCLUDED') or die();

/**
 * Configuration parser for the tables-specific settings
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFConfigDomainTables implements FOFConfigDomainInterface
{
	/**
	 * Parse the XML data, adding them to the $ret array
	 *
	 * @param   SimpleXMLElement  $xml   The XML data of the component's configuration area
	 * @param   array             &$ret  The parsed data, in the form of a hash array
	 *
	 * @return  void
	 */
	public function parseDomain(SimpleXMLElement $xml, array &$ret)
	{
		// Initialise
		$ret['tables'] = array();

		// Parse table configuration
		$tableData = $xml->xpath('table');

		// Sanity check
		if (empty($tableData))
		{
			return;
		}

		foreach ($tableData as $aTable)
		{
			$key = (string) $aTable['name'];

			$ret['tables'][$key]['behaviors'] = (string) $aTable->behaviors;
			$ret['tables'][$key]['tablealias'] = $aTable->xpath('tablealias');
			$ret['tables'][$key]['fields'] = array();
			$ret['tables'][$key]['relations'] = array();

			$fieldData = $aTable->xpath('field');

			if (!empty($fieldData))
			{
				foreach ($fieldData as $field)
				{
					$k = (string) $field['name'];
					$ret['tables'][$key]['fields'][$k] = (string) $field;
				}
			}

			$relationsData = $aTable->xpath('relation');

			if (!empty($relationsData))
			{
				foreach ($relationsData as $relationData)
				{
					$type = (string)$relationData['type'];
					$itemName = (string)$relationData['name'];

					if (empty($type) || empty($itemName))
					{
						continue;
					}

					$tableClass		= (string)$relationData['tableClass'];
					$localKey		= (string)$relationData['localKey'];
					$remoteKey		= (string)$relationData['remoteKey'];
					$ourPivotKey	= (string)$relationData['ourPivotKey'];
					$theirPivotKey	= (string)$relationData['theirPivotKey'];
					$pivotTable		= (string)$relationData['pivotTable'];
					$default		= (string)$relationData['default'];

					$default = !in_array($default, array('no', 'false', 0));

					$relation = array(
						'type'			=> $type,
						'itemName'		=> $itemName,
						'tableClass'	=> empty($tableClass) ? null : $tableClass,
						'localKey'		=> empty($localKey) ? null : $localKey,
						'remoteKey'		=> empty($remoteKey) ? null : $remoteKey,
						'default'		=> $default,
					);

					if (!empty($ourPivotKey) || !empty($theirPivotKey) || !empty($pivotTable))
					{
						$relation['ourPivotKey']	= empty($ourPivotKey) ? null : $ourPivotKey;
						$relation['theirPivotKey']	= empty($theirPivotKey) ? null : $theirPivotKey;
						$relation['pivotTable']	= empty($pivotTable) ? null : $pivotTable;
					}

					$ret['tables'][$key]['relations'][] = $relation;
				}
			}
		}
	}

	/**
	 * Return a configuration variable
	 *
	 * @param   string  &$configuration  Configuration variables (hashed array)
	 * @param   string  $var             The variable we want to fetch
	 * @param   mixed   $default         Default value
	 *
	 * @return  mixed  The variable's value
	 */
	public function get(&$configuration, $var, $default)
	{
		$parts = explode('.', $var);

		$view = $parts[0];
		$method = 'get' . ucfirst($parts[1]);

		if (!method_exists($this, $method))
		{
			return $default;
		}

		array_shift($parts);
		array_shift($parts);

		$ret = $this->$method($view, $configuration, $parts, $default);

		return $ret;
	}

	/**
	 * Internal method to return the magic field mapping
	 *
	 * @param   string  $table           The table for which we will be fetching a field map
	 * @param   array   &$configuration  The configuration parameters hash array
	 * @param   array   $params          Extra options; key 0 defines the table we want to fetch
	 * @param   string  $default         Default magic field mapping; empty if not defined
	 *
	 * @return  array   Field map
	 */
	protected function getField($table, &$configuration, $params, $default = '')
	{
		$fieldmap = array();

		if (isset($configuration['tables']['*']) && isset($configuration['tables']['*']['fields']))
		{
			$fieldmap = $configuration['tables']['*']['fields'];
		}

		if (isset($configuration['tables'][$table]) && isset($configuration['tables'][$table]['fields']))
		{
			$fieldmap = array_merge($fieldmap, $configuration['tables'][$table]['fields']);
		}

		$map = $default;

		if (empty($params[0]))
		{
			$map = $fieldmap;
		}
		elseif (isset($fieldmap[$params[0]]))
		{
			$map = $fieldmap[$params[0]];
		}

		return $map;
	}

	/**
	 * Internal method to get table alias
	 *
	 * @param   string  $table           The table for which we will be fetching table alias
	 * @param   array   &$configuration  The configuration parameters hash array
	 * @param   array   $params          Extra options; key 0 defines the table we want to fetch
	 * @param   string  $default         Default table alias
	 *
	 * @return  string  Table alias
	 */
	protected function getTablealias($table, &$configuration, $params, $default = '')
	{
		$tablealias = $default;

		if (isset($configuration['tables']['*'])
			&& isset($configuration['tables']['*']['tablealias'])
			&& isset($configuration['tables']['*']['tablealias'][0]))
		{
			$tablealias = (string) $configuration['tables']['*']['tablealias'][0];
		}

		if (isset($configuration['tables'][$table])
			&& isset($configuration['tables'][$table]['tablealias'])
			&& isset($configuration['tables'][$table]['tablealias'][0]))
		{
			$tablealias = (string) $configuration['tables'][$table]['tablealias'][0];
		}

		return $tablealias;
	}

	/**
	 * Internal method to get table behaviours
	 *
	 * @param   string  $table           The table for which we will be fetching table alias
	 * @param   array   &$configuration  The configuration parameters hash array
	 * @param   array   $params          Extra options; key 0 defines the table we want to fetch
	 * @param   string  $default         Default table alias
	 *
	 * @return  string  Table behaviours
	 */
	protected function getBehaviors($table, &$configuration, $params, $default = '')
	{
		$behaviors = $default;

		if (isset($configuration['tables']['*'])
			&& isset($configuration['tables']['*']['behaviors']))
		{
			$behaviors = (string) $configuration['tables']['*']['behaviors'];
		}

		if (isset($configuration['tables'][$table])
			&& isset($configuration['tables'][$table]['behaviors']))
		{
			$behaviors = (string) $configuration['tables'][$table]['behaviors'];
		}

		return $behaviors;
	}

	/**
	 * Internal method to get table relations
	 *
	 * @param   string  $table           The table for which we will be fetching table alias
	 * @param   array   &$configuration  The configuration parameters hash array
	 * @param   array   $params          Extra options; key 0 defines the table we want to fetch
	 * @param   string  $default         Default table alias
	 *
	 * @return  array   Table relations
	 */
	protected function getRelations($table, &$configuration, $params, $default = '')
	{
		$relations = $default;

		if (isset($configuration['tables']['*'])
			&& isset($configuration['tables']['*']['relations']))
		{
			$relations = $configuration['tables']['*']['relations'];
		}

		if (isset($configuration['tables'][$table])
			&& isset($configuration['tables'][$table]['relations']))
		{
			$relations = $configuration['tables'][$table]['relations'];
		}

		return $relations;
	}
}

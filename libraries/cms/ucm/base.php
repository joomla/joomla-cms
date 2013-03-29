<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  UCM
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Base class for implementing UCM
 *
 * @package     Joomla.Libraries
 * @subpackage  UCM
 * @since       3.1
 */
class JUcmBase implements JUcm
{
	/**
	 * The related table object
	 *
	 * @var    JTable Object
	 * @since  13.1
	 */
	protected $table;

	/**
	 * The UCM type object
	 *
	 * @var    JUcmType Object
	 * @since  13.1
	 */
	protected $type;

	/**
	 * The alias for the content table
	 *
	 * @var    String
	 * @since  13.1
	 */
	protected $alias;

	/**
	 * The UCM data array
	 *
	 * @var    Array
	 * @since  13.1
	 */
	public $ucmData;

	/**
	 * Instantiate the UcmBase.
	 *
	 * @param   JTable    $table    The table object
	 * @param   JUcmType  $model    The type object
	 *
	 * @since  13.1
	 */
	public function __construct(JTable $table, $alias = null, JUcmType $type = null)
	{
		// Setup dependencies.
		$input = JFactory::getApplication()->input;
		$this->alias = isset($alias) ? $alias : $input->get('option') . '.' . $input->get('view');

		$this->type = isset($type) ? $type : $this->getType();

		if ($table)
		{
			$this->table = $table;
		}
		else
		{
			$tableArray = json_decode($this->type->table_array);
			$this->table->getInstance($tableArray['type'], $tableArray['prefix'], $tableArray['config']);
		}

	}

	/**
	*
	* @param   Array   $original  The original data to be saved
	* @param   Object  $type      The UCM Type object
	* @param   boolean  $corecontent  Flag that is true for data that are using #__core_content as their primary table
	*
	* @return  boolean  true
	*
	* @since   3.1
	**/
	public function save($original = null, JUcmType $type = null, $corecontent = true)
	{
		$ucmData = $original ? $this->mapData($original, $type) : $this->ucmData;

		//Store the Common fields
		$this->store($ucmData['common']);

		//Store the special fields
		if( isset($ucmData['special']))
		{
			$table = $this->table;
			$this->store($ucmData['special'], $table, $corecontent);
		}

		return true;
	}

	/**
	* Map the original content to the Core Content fields
	*
	* @param   Array     $original  The original data array
	* @param   JUcmType  $type      Type object for this data
	*
	* @return  Object   $ucmData  The mapped UCM data
	*
	* @since   3.1
	*/
	public function mapData($original, JUcmType $type = null)
	{
		$contentType = isset($type) ? $type : $this->type;

		$fields = json_decode($contentType->type->field_mappings, true);

		$ucmData = array();

		foreach ($fields['common'][0] as $i => $field)
		{
			if ($field && $field != 'null' && array_key_exists($field, $original))
			{
				$ucmData['common'][$i] = $original[$field];
			}
		}

		foreach ($fields['special'][0] as $i => $field)
		{
			if ($field && $field != 'null' && array_key_exists($field, $original))
			{
				$ucmData['special'][$i] = $original[$field];
			}
		}
		$ucmData['special']['core_content_item_id'] = $ucmData['common']['core_content_item_id'];

		$this->ucmData = $ucmData;

		return $this->ucmData;
	}

	/**
	* Get the UCM Content type.
	*
	* @return	Object	The UCM content type
	*
	* @since	13.1
	**/
	public function getType()
	{

		$type = new JUcmType($this->alias);

		return $type;
	}

	/**
	* Store data to the appropriate table
	*
	* @param   array    $data         Data to be stored
	* @param   JTable   $table        JTable Object
	* @param   boolean  $corecontent  Flag that is true for data that are using #__core_content as their primary table
	*
	* @return  Boolean  true on success
	*
	* @since   3.1
	*/
	private function store($data, JTable $table = null, $corecontent = true)
	{
		$table = $table ? $table : JTable::getInstance('Corecontent');

		if ($table instanceof JTableCorecontent)
		{
			$typeAlias = $this->getType()->type->type_alias;
			$primaryKey = self::getPrimaryKey('core_content_id', $typeAlias, $data['core_content_item_id']);

			$table->load($primaryKey);

			try
			{
				$table->bind($data);
			}
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500);
				return false;
			}

			try
			{
				$table->store();
			}
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500);
				return false;
			}
		}
		else
		{
			if (!$corecontent)
			{
				// Avoid a save() within a save() for legacy handling.
				return true;
			}
			$primaryKeyName = $table->getKeyName();

			$data[$primaryKeyName] = $data['core_content_item_id'];
			$table->load($data[$primaryKeyName]);
			try
			{
				$table->bind($data);
			}
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500);
				return false;
			}

			try
			{
				$table->store();die;
			}
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500);
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the value of the primary key from #__core_content
	 *
	 * @param   string   $primaryKeyName   Name of the primary key field
	 * @param   string   $typeAlias        The dot separated alias for the type
	 * @param   integer  $contentItemId    Value of the primary key in the legacy or secondary table
	 *
	 * @return  Boolean  true on success
	 *
	 * @since   3.1
	 */

	public function getPrimaryKey($primaryKeyName, $typeAlias, $contentItemId)
	{
		$db = JFactory::getDbo();
		$queryccid = $db->getQuery(true);
		$queryccid = $db->getQuery(true);
		$queryccid->select($db->quoteName($primaryKeyName))
		->from($db->quoteName('#__core_content'))
		->where(
			array(
					$db->quoteName('core_content_item_id') . ' = ' . $db->quote($contentItemId),
					$db->quoteName('core_type_alias') . ' = ' . $db->quote($typeAlias)
			));
		$db->setQuery($queryccid);
		$primaryKey = $db->loadResult();

		return $primaryKey;
	}
}

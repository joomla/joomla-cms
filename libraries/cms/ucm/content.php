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
class JUcmContent extends JUcmBase
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
	public $type;

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
	 * Instantiate the UcmContent.
	 *
	 * @param   JTable    $table   The table object
	 * @param   sring     $alias   The type alias
	 * @param   JUcmType  $type    The type object
	 *
	 * @since  13.1
	 */
	public function __construct(JTable $table = null, $alias = null, JUcmType $type = null)
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
	* @param   Array    $original     The original data to be saved
	* @param   Object   $type         The UCM Type object
	* @param   boolean  $corecontent  Flag that is true for data that are using #__core_content as their primary table
	*
	* @return  boolean  true
	*
	* @since   3.1
	**/
	public function save($original = null, JUcmType $type = null)
	{
		$type = $type ? $type : $this->type;
		$ucmData = $original ? $this->mapData($original, $type) : $this->ucmData;

		//Store the Common fields
		$this->store($ucmData['common']);

		//Store the special fields
		if(isset($ucmData['special']))
		{
			$table = $this->table;
			$this->store($ucmData['special'], $table,'');
		}

		return true;
	}

	/**
	* Delete content from the Core Content table
	*
	* @param	String/Array	$pk		The string/array of id's to delete
	* @param	Type Object		$type	The content type object
	*
	* @return	Boolean	True if success
	*
	* @since 3.1
	*
	*/
	public function delete($pk, JUcmType $type = null)
	{
		$db		= JFactory::getDbo();
		$type	= $type ? $type : $this->type;

		if (is_array($pk))
		{
			$pk = implode(',', $pk);
		}

		$query = $db->getQuery(true)
			->delete('#__core_content')
			->where($db->quoteName('core_type_id') . ' = ' . (int)$type->type_id)
			->where($db->quoteName('core_content_item_id') . ' IN (' . $pk . ')');

		$db->setQuery($query);
		$db->execute();

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

		$ucmData['common']['core_type_alias'] 	= $contentType->type->type_alias;
		$ucmData['common']['core_type_id']		= $contentType->type->type_id;

		if (isset($ucmData['special']))
		{
			$ucmData['special']['ucm_id'] = $ucmData['common']['ucm_id'];
		}

		$this->ucmData = $ucmData;

		return $this->ucmData;
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
	protected function store($data, JTable $table = null, $primaryKey = null)
	{
		$table = $table ? $table : JTable::getInstance('Corecontent');

		$typeId 	= $this->getType()->type->type_id;
		$primaryKey = $primaryKey ? $primaryKey : self::getPrimaryKey($typeId, $data['core_content_item_id']);

		if (!$primaryKey)
		{
			//Store the core UCM mappings
			$baseData = array();
			$baseData['ucm_type_id']		= $typeId;
			$baseData['ucm_item_id']		= $data['core_content_item_id'];
			$baseData['ucm_language_id']	= JHelperContent::getLanguageId($data['core_language']);

			if (parent::store($baseData))
			{
				$primaryKey = self::getPrimaryKey($typeId,$data['core_content_item_id']);
			}
		}


		parent::store($data, $table, $primaryKey);

		return true;
	}

	/**
	 * Get the value of the primary key from #__ucm_base
	 *
	 * @param   string   $typeId	        The ID for the type
	 * @param   integer  $contentItemId    Value of the primary key in the legacy or secondary table
	 *
	 * @return  Integer  The integer of the primary key
	 *
	 * @since   3.1
	 */

	public function getPrimaryKey($typeId, $contentItemId)
	{
		$db = JFactory::getDbo();
		$queryccid = $db->getQuery(true);
		$queryccid->select($db->quoteName('ucm_id'))
		->from($db->quoteName('#__ucm_base'))
		->where(
			array(
					$db->quoteName('ucm_item_id') . ' = ' . $db->quote($contentItemId),
					$db->quoteName('ucm_type_id') . ' = ' . $db->quote($typeId)
			));
		$db->setQuery($queryccid);
		$primaryKey = $db->loadResult();

		return $primaryKey;
	}
}
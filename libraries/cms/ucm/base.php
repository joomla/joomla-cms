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
	 * @param   JTable  	$table    The table object
	 * @param   JUcmType  	$model    The type object
	 *
	 * @since  13.1
	 */
	public function __construct(JTable $table, $alias = null, JUcmType $type = null)
	{
		// Setup dependencies.
		$this->table = $table;

		$input = JFactory::getApplication()->input;
		$this->alias = isset($alias) ? $alias : $input->get('option') . '.' . $input->get('view');

		$this->type = isset($type) ? $type : $this->getType();
	}

	/**
	*
	* @param	Array	$original	The original data to be saved
	* @param	Object	$type		The UCM Type object
	*
	* @return	boolean	true
	*
	* @since	13.1
	**/
	public function save($original = null, JUcmType $type = null)
	{
		$ucmData = $original ? $this->mapData($original, $type) : $this->ucmData;

		//Store the Common fields
		$this->store($ucmData['common']);
		
		//Store the special fields
		if( isset($ucmData['special']))
		{
			$this->store($ucmData['special'], $this->alias);
		}

		return true;
	}

	/**
	* Map the original content to the Core Content fields
	*
	* @param	Array	$original	The original data array
	* @param	
	*
	* @return	Objecct	$ucmData	The mapped UCM data
	*
	* @since 	13.1
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
	* @param	Array	$data		Data to be stored
	* @param	JTable	$table		JTable Object
	*
	* @return	Boolean	true
	*/
	private function store($data, JTable $table = null)
	{

		//If no table is set we use the core content table
		$table = $table ? $table : JTable::getInstance('Corecontent');

		$primaryKeyName = $table->getKeyName();

		if ($primaryKeyName && isset($data[$primaryKeyName]))
		{
			$table->load($data[$primaryKeyName]);
		}

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
		
		return true;
	}
}

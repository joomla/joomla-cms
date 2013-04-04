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
	 * Instantiate the UcmBase.
	 *
	 * @param   String    $alias    The alias string
	 * @param   JUcmType  $model    The type object
	 *
	 * @since  13.1
	 */
	public function __construct($alias = null, JUcmType $type = null)
	{
		// Setup dependencies.
		$input = JFactory::getApplication()->input;
		$this->alias = isset($alias) ? $alias : $input->get('option') . '.' . $input->get('view');

		$this->type = isset($type) ? $type : $this->getType();

	}

	/**
	*
	* @param   Array   $data	  The original data to be saved
	* @param   Object  $type      The UCM Type object
	*
	* @return  boolean  true
	*
	* @since   3.1
	**/
	public function save($data, JUcmType $type = null)
	{
		$type = $type ? $type : $this->type;

		if (!isset($data['ucm_type_id'])) 
		{
			$data['ucm_type_id'] = $type->id;
		}

		//Store the Common fields
		$this->store($data);

		return true;
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
	private function store($data)
	{
		$table = JTable::getInstance('Ucm');

		if (isset($data['ucm_id']) {
			$table->load($data['ucm_id']);
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

}

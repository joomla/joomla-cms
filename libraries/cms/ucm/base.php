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
	* Store data to the appropriate table
	*
	* @param   array   $data         Data to be stored
	* @param   JTable  $table        JTable Object
	* @param   string  $primaryKey   The primary key name
	*
	* @return  Boolean  true on success
	*
	* @since   3.1
	*/
	protected function store(&$data, JTable $table = null, $primaryKey = null)
	{
		if (!$table)
		{
			$table = JTable::getInstance('Ucm');
		}

		$primaryKey = $primaryKey ? $primaryKey : $data['ucm_id'];

		if (isset($primaryKey))
		{
			$table->load($primaryKey);
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
	* @return  Object  The UCM content type
	*
	* @since   3.1
	**/
	public function getType()
	{
		$type = new JUcmType($this->alias);

		return $type;
	}

	/**
	* Method to map the base ucm fields
	*
	* @return  array  Data array of UCM mappings
	*
	* @since 3.1
	**/
	public function mapBase($original, JUcmType $type = null)
	{
		$type = $type ? $type : $this->type;

		$data = array(
					'ucm_type_id' => $type->id,
					'ucm_item_id' => $original[$type->primary_key],
					'ucm_language_id' => $original['language']
				);

		return $data;
	}

}

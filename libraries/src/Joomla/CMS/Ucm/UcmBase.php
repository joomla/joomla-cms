<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Ucm;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;

/**
 * Base class for implementing UCM
 *
 * @since  3.1
 */
class UcmBase implements Ucm
{
	/**
	 * The UCM type object
	 *
	 * @var    UcmType
	 * @since  3.1
	 */
	protected $type;

	/**
	 * The alias for the content table
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $alias;

	/**
	 * Instantiate the UcmBase.
	 *
	 * @param   string   $alias  The alias string
	 * @param   UcmType  $type   The type object
	 *
	 * @since   3.1
	 */
	public function __construct($alias = null, UcmType $type = null)
	{
		// Setup dependencies.
		$input = \JFactory::getApplication()->input;
		$this->alias = isset($alias) ? $alias : $input->get('option') . '.' . $input->get('view');

		$this->type = isset($type) ? $type : $this->getType();
	}

	/**
	 * Store data to the appropriate table
	 *
	 * @param   array           $data        Data to be stored
	 * @param   TableInterface  $table       Table Object
	 * @param   string          $primaryKey  The primary key name
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 * @throws  Exception
	 */
	protected function store($data, TableInterface $table = null, $primaryKey = null)
	{
		if (!$table)
		{
			$table = Table::getInstance('Ucm');
		}

		$ucmId      = isset($data['ucm_id']) ? $data['ucm_id'] : null;
		$primaryKey = $primaryKey ?: $ucmId;

		if (isset($primaryKey))
		{
			$table->load($primaryKey);
		}

		try
		{
			$table->bind($data);
		}
		catch (\RuntimeException $e)
		{
			throw new \Exception($e->getMessage(), 500, $e);
		}

		try
		{
			$table->store();
		}
		catch (\RuntimeException $e)
		{
			throw new \Exception($e->getMessage(), 500, $e);
		}

		return true;
	}

	/**
	 * Get the UCM Content type.
	 *
	 * @return  UcmType  The UCM content type
	 *
	 * @since   3.1
	 */
	public function getType()
	{
		if (!$this->type)
		{
			$this->type = new UcmType($this->alias);
		}

		return $this->type;
	}

	/**
	 * Method to map the base ucm fields
	 *
	 * @param   array    $original  Data array
	 * @param   UcmType  $type      UCM Content Type
	 *
	 * @return  array  Data array of UCM mappings
	 *
	 * @since   3.1
	 */
	public function mapBase($original, UcmType $type = null)
	{
		$type = $type ?: $this->type;

		$data = array(
			'ucm_type_id' => $type->id,
			'ucm_item_id' => $original[$type->primary_key],
			'ucm_language_id' => ContentHelper::getLanguageId($original['language']),
		);

		return $data;
	}
}

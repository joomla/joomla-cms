<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Prototype item model.
 *
 * @package     Joomla.Model
 * @subpackage  Model
 * @since       3.4
 */
class JModelCmsitem extends JModelCmsactions implements JModelItemInterface
{
	/**
	 * An item.
	 *
	 * @var  stdClass
	 */
	protected $item = null;

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   3.4
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		return md5($id);
	}

	/**
	 * Method to get an object. This only works for content types registered in the content_type table.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($id = null)
	{
		if (empty($this->item) && $this->getTable())
		{
			$table = $this->getTable();
			$tableClassName = get_class($table);

			$this->item = false;
			$contentType = new JUcmType;

			if ($tableClassName != 'JTableCorecontent')
			{
				$type = $contentType->getTypeByTable($tableClassName);
			}
			elseif (!empty($id))
			{
				$table->load($id);
				$type = $contentType->getType($table->core_type_id);

			}
			elseif (empty($id))
			// Deal with examples where there is no row in type table?
			{
				$type = null;
			}

			if (!empty($type))
			{
				$prefix = $this->state->get($table, 'prefix');
				$typeTable = $type->table;
				$typeTable = json_decode($typeTable);

				// Check to see if special exists .. if it doesn't use common
				if (!empty($typeTable->special) && $tableClassName != 'JTableCorecontent')
				{
					$table = JTable::getInstance($typeTable->special->type, $typeTable->special->prefix);
				}
				else
				{
					if (empty($typeTable->common))
					{
						// Should there be an exception here? or should we load ucm_content?
						return;
					}
					$table = JTable::getInstance($typeTable->common->type, $typeTable->common->prefix);
					// Get the special field mapping here
				}
			}

			// Attempt to load the row.
			if ($table->load($id))
			{
				// Convert the JTable to a clean object.
				$properties = $table->getProperties(1);
				$this->item = JArrayHelper::toObject($properties);
			}
			elseif ($error = $table->getError())
			{
				$this->setError($error);
			}
		}

		return $this->item;
	}

	/**
	 * Method to increment the hit counter for the weblink
	 *
	 * @param   integer  $id  Optional ID of the weblink.
	 *
	 * @return  boolean  True on success
	 *
	 * @since  3.4
	 */
	public function hit($id = null)
	{
		if (empty($id))
		{
			$id = $this->getState($type . 'id');
		}

		$item = $this->getTable($type, $prefix);

		return $item->hit($id);
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $category_id  The id of the category.
	 * @param   string   $alias        The alias.
	 * @param   string   $title        The title.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 * @since	3.4
	 */
	protected function generateNewTitle($category_id, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();
		while ($table->load(array('alias' => $alias, 'catid' => $category_id)))
		{
			$title = JString::increment($title);
			$alias = JString::increment($alias, 'dash');
		}

		return array($title, $alias);
	}

	/**
	 * Stock method to auto-populate the state.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function populateState()
	{
		$table = $this->getTable();
		$key = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = JFactory::getApplication()->input->getInt($key);
		$this->state->set($this->name . '.id', $pk);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->state->set('params', $value);
	}
}

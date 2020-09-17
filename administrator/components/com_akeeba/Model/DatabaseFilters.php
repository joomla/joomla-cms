<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Model\Mixin\ExclusionFilter;
use Akeeba\Engine\Factory;
use Exception;
use FOF30\Container\Container;
use FOF30\Model\Model;
use Joomla\CMS\Language\Text;

/**
 * Database Filters model
 *
 * Handles the exclusion of database tables (whole or just their data)
 */
class DatabaseFilters extends Model
{
	use ExclusionFilter;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->knownFilterTypes = ['tables', 'tabledata'];
	}

	/**
	 * Returns a list of the database tables, views, procedures, functions and triggers,
	 * along with their filter status in array format, for use in the GUI
	 *
	 * @param   string  $root  The database root we're working on
	 *
	 * @return  array  Hash array. 'tables' is an array list of tables w/ metadata. 'root' is the current db root.
	 */
	public function make_listing($root)
	{
		// Get database inclusion filters
		$filters       = Factory::getFilters();
		$database_list = $filters->getInclusions('db');

		// Load the database object for the selected database
		$config         = $database_list[$root];
		$config['user'] = $config['username'];
		$db             = Factory::getDatabase($config);

		// Load the table data
		try
		{
			$table_data = $db->getTables();
		}
		catch (Exception $e)
		{
			$table_data = [];
		}

		$tableMeta = [];

		try
		{
			$db->setQuery('SHOW TABLE STATUS');

			$temp = $db->loadAssocList();

			foreach ($temp as $record)
			{
				$tableMeta[$db->getAbstract($record['Name'])] = [
					'engine'      => $record['Engine'],
					'rows'        => $record['Rows'],
					'dataLength'  => $record['Data_length'],
					'indexLength' => $record['Index_length'],
				];
			}
		}
		catch (Exception $e)
		{
		}

		// Process filters
		$tables = [];

		if (!empty($table_data))
		{
			foreach ($table_data as $table_name => $table_type)
			{
				$status = [
					'engine'      => null,
					'rows'        => null,
					'dataLength'  => null,
					'indexLength' => null,
				];

				if (array_key_exists($table_name, $tableMeta))
				{
					$status = $tableMeta[$table_name];
				}

				// Add table type
				$status['type'] = $table_type;

				// Check dbobject/all filter (exclude)
				$result           = $filters->isFilteredExtended($table_name, $root, 'dbobject', 'all', $byFilter);
				$status['tables'] = (!$result) ? 0 : (($byFilter == 'tables') ? 1 : 2);

				// Check dbobject/content filter (skip table data)
				$result              = $filters->isFilteredExtended($table_name, $root, 'dbobject', 'content', $byFilter);
				$status['tabledata'] = (!$result) ? 0 : (($byFilter == 'tabledata') ? 1 : 2);

				// We can't filter contents of views, merge tables, black holes, procedures, functions and triggers
				if ($table_type != 'table')
				{
					$status['tabledata'] = 2;
				}

				$tables[$table_name] = $status;
			}
		}

		return [
			'tables' => $tables,
			'root'   => $root,
		];
	}

	/**
	 * Returns an array containing a mapping of db root names and their human-readable representation
	 *
	 * @return  array  Array of objects; "value" contains the root name, "text" the human-readable text
	 */
	public function get_roots()
	{
		// Get database inclusion filters
		$filters       = Factory::getFilters();
		$database_list = $filters->getInclusions('db');

		$ret = [];

		foreach ($database_list as $name => $definition)
		{
			$root = $definition['host'];

			if (!empty($definition['port']))
			{
				$root .= ':' . $definition['port'];
			}

			$root .= '/' . $definition['database'];

			if ($name == '[SITEDB]')
			{
				$root = Text::_('COM_AKEEBA_DBFILTER_LABEL_SITEDB');
			}

			$ret[] = (object) [
				'value' => $name,
				'text'  => $root,
			];
		}

		return $ret;
	}

	/**
	 * Toggle a filter
	 *
	 * @param   string  $root    Database root
	 * @param   string  $item    The db entity we want to toggle the filter for
	 * @param   string  $filter  The name of the filter to apply (tables, tabledata)
	 *
	 * @return  array
	 */
	public function toggle($root, $item, $filter)
	{
		return $this->applyExclusionFilter($filter, $root, $item, 'toggle');
	}

	/**
	 * Set a filter
	 *
	 * @param   string  $root    Database root
	 * @param   string  $item    The db entity we want to toggle the filter for
	 * @param   string  $filter  The name of the filter to apply (tables, tabledata)
	 *
	 * @return  array
	 */
	public function remove($root, $item, $filter)
	{
		return $this->applyExclusionFilter($filter, $root, $item, 'remove');
	}

	/**
	 * Set a filter
	 *
	 * @param   string  $root    Database root
	 * @param   string  $item    The db entity we want to toggle the filter for
	 * @param   string  $filter  The name of the filter to apply (tables, tabledata)
	 *
	 * @return  array
	 */
	public function setFilter($root, $item, $filter)
	{
		return $this->applyExclusionFilter($filter, $root, $item, 'set');
	}

	/**
	 * Swap a filter
	 *
	 * @param   string  $root      Database root
	 * @param   string  $old_item  The db entity that used to be filtered and will no longer be
	 * @param   string  $new_item  The db entity that wasn't filtered but now will be
	 * @param   string  $filter    The name of the filter to apply (tables, tabledata)
	 *
	 * @return  array
	 */
	public function swap($root, $old_item, $new_item, $filter)
	{
		return $this->applyExclusionFilter($filter, $root, $new_item, 'swap', $old_item);
	}

	/**
	 * Retrieves the filters as an array. Used for the tabular filter editor.
	 *
	 * @param   string  $root  The root node to search filters on
	 *
	 * @return  array  An array of hash arrays containing node and type for each filtered element
	 */
	public function &get_filters($root)
	{
		return $this->getTabularFilters($root);
	}

	/**
	 * Resets all filters
	 *
	 * @param   string  $root  Root directory
	 *
	 * @return  array
	 */
	public function resetFilters($root)
	{
		$this->resetAllFilters($root);

		return $this->make_listing($root);
	}

	/**
	 * Handles a request coming in through AJAX. Basically, this is a simple proxy to the model methods.
	 *
	 * @return  array
	 */
	public function doAjax()
	{
		$action = $this->getState('action');
		$verb   = array_key_exists('verb', get_object_vars($action)) ? $action->verb : null;

		$ret_array = [];

		switch ($verb)
		{
			// Return a listing for the normal view
			case 'list':
				$ret_array = $this->make_listing($action->root);
				break;

			// Toggle a filter's state
			case 'toggle':
				$ret_array = $this->toggle($action->root, $action->node, $action->filter);
				break;

			// Set a filter (used by the editor)
			case 'set':
				$ret_array = $this->setFilter($action->root, $action->node, $action->filter);
				break;

			// Remove a filter (used by the editor)
			case 'remove':
				$ret_array = $this->remove($action->root, $action->node, $action->filter);
				break;

			// Swap a filter (used by the editor)
			case 'swap':
				$ret_array = $this->swap($action->root, $action->old_node, $action->new_node, $action->filter);
				break;

			// Tabular view
			case 'tab':
				$ret_array = $this->get_filters($action->root);
				break;

			// Reset filters
			case 'reset':
				$ret_array = $this->resetFilters($action->root);
				break;
		}

		return $ret_array;
	}
}

<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model\Mixin;

// Protect from unauthorized access
use Akeeba\Engine\Factory;

defined('_JEXEC') || die();

/**
 * Trait for handling Akeeba Engine exclusion filters in models
 */
trait ExclusionFilter
{
	protected $knownFilterTypes = [];

	/**
	 * Modifies a filter
	 *
	 * @param   string  $type     Filter type
	 * @param   string  $root     The filter's root
	 * @param   string  $node     The filter node to modify
	 * @param   string  $action   The action to take: set, remove, toggle, swap
	 * @param   string  $oldNode  Only for swap: The old node which will be swapped with $node
	 *
	 * @return  array  Array with keys success and newstate
	 */
	protected function applyExclusionFilter($type, $root, $node, $action = 'set', $oldNode = '')
	{
		$ret = [
			'success'  => false,
			'newstate' => false
		];

		$filter  = Factory::getFilterObject($type);
		$newState = null;

		switch ($action)
		{
			case 'set':
				$ret['success'] = $filter->set($root, $node);
				break;

			case 'remove':
				$ret['success'] = $filter->remove($root, $node);
				break;

			case 'toggle':
				$ret['success'] = $filter->toggle($root, $node, $newState);
				break;

			case 'swap':
				$ret['success'] = true;

				if (empty($node))
				{
					$ret['success'] = false;
				}

				if ($ret['success'] && !empty($oldNode))
				{
					$ret = $this->applyExclusionFilter($type, $root, $oldNode, 'remove');
				}

				if ($ret['success'])
				{
					$ret = $this->applyExclusionFilter($type, $root, $node, 'set');
				}
				break;
		}

		$ret['newstate'] = $newState;

		if (is_null($newState))
		{
			$ret['newstate'] = $ret['success'];
		}

		if ($ret['success'])
		{
			$filters = Factory::getFilters();
			$filters->save();
		}

		return $ret;
	}


	/**
	 * Retrieves the filters as an array. Used for the tabular filter editor.
	 *
	 * @param   string  $root  The root node to search filters on
	 *
	 * @return  array  A collection of hash arrays containing node and type for each filtered element
	 */
	protected function &getTabularFilters($root)
	{
		// A reference to the global Akeeba Engine filter object
		$filters = Factory::getFilters();

		// Initialize the return array
		$ret = array();

		foreach ($this->knownFilterTypes as $type)
		{
			$rawFilterData = $filters->getFilterData($type);

			if (array_key_exists($root, $rawFilterData))
			{
				if (!empty($rawFilterData[ $root ]))
				{
					foreach ($rawFilterData[ $root ] as $node)
					{
						$ret[] = array(
							'node' => substr($node, 0), // Make sure we get a COPY, not a reference to the original data
							'type' => $type
						);
					}
				}
			}
		}

		return $ret;
	}

	/**
	 * Resets the filters
	 *
	 * @param   string  $root  Root directory
	 *
	 * @return  array
	 */
	protected function resetAllFilters($root)
	{
		// Get a reference to the global Filters object
		$filters = Factory::getFilters();

		foreach ($this->knownFilterTypes as $filterName)
		{
			$filter = Factory::getFilterObject($filterName);
			$filter->reset($root);
		}

		$filters->save();
	}
}

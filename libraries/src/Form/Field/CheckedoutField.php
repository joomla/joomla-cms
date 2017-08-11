<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

/**
 * Form Field to load a list of users who checked items out.
 *
 * @since  __DEPLOY_VERSION__
 */
class CheckedoutField extends \JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $type = 'Checkedout';

	/**
	 * The FROM clause of the SQL query.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $from = null;

	/**
	 * The alias of the table.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $alias = null;

	/**
	 * Builds the query for the checked out list.
	 *
	 * @return  JDatabaseQuery  The query for the checked out form field.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getQuery()
	{
		$db = Factory::getDbo();
		$sql_from = (string) $this->element['sql_from'];

		// Get from clause and table alias.
		if (preg_match("%^(#__[a-z0-9_]+)\s*(?:AS\s)?\s*([a-z0-9_]*)$%i", $sql_from, $matches))
		{
			if (empty($matches[2]))
			{
				$this->from = $db->quoteName($matches[1]);
				$this->alias = $this->from;
			}
			else
			{
				$this->alias = $matches[2];
				$this->from = $db->quoteName($matches[1]). ' AS ' . $this->alias;
			}
		}
		else
		{
			throw new \UnexpectedValueException(sprintf('%s has invalid value of the sql_from attribute.', $this->name));
		}

		// Get selected id. If selected id > 0, the row with this id always adds to the result set.
		if (is_numeric($this->value))
		{
			$selectedId = (int) $this->value;
			$orIsSelectedId = ($selectedId > 0) ? ' OR uc.id = ' . $selectedId : '';
		}
		else
		{
			$orIsSelectedId = '';
		}

		// Construct the query.
		$query = $db->getQuery(true)
			->select('uc.id AS value, uc.name AS text')
			->from($this->from)
			->join('INNER', '#__users AS uc ON ' . $this->alias . '.checked_out = uc.id' . $orIsSelectedId)
			->group('uc.id, uc.name')
			->order('uc.name');

		if ($where = (string) $this->element['sql_where'])
		{
			$query->where($where);
		}

		return $query;
	}

	/**
	 * Method to get the options to populate list.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$db = Factory::getDbo();
		$db->setQuery($this->getQuery());

		// Get the result.
		try
		{
			$options = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			$options = array();
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}

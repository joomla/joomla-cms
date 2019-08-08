<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Association;

defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Association Extension Helper
 *
 * @since  3.7.0
 */
abstract class AssociationExtensionHelper  implements AssociationExtensionInterface
{
	/**
	 * The extension name
	 *
	 * @var     array  $extension
	 *
	 * @since   3.7.0
	 */
	protected $extension = 'com_??';

	/**
	 * Array of item types
	 *
	 * @var     array  $itemTypes
	 *
	 * @since   3.7.0
	 */
	protected $itemTypes = array();

	/**
	 * Has the extension association support
	 *
	 * @var     boolean  $associationsSupport
	 *
	 * @since   3.7.0
	 */
	protected $associationsSupport = false;

	/**
	 * Checks if the extension supports associations
	 *
	 * @return  boolean  Supports the extension associations
	 *
	 * @since   3.7.0
	 */
	public function hasAssociationsSupport()
	{
		return $this->associationsSupport;
	}

	/**
	 * Get the item types
	 *
	 * @return  array  Array of item types
	 *
	 * @since  3.7.0
	 */
	public function getItemTypes()
	{
		return $this->itemTypes;
	}

	/**
	 * Get the associated items for an item
	 *
	 * @param   string  $typeName  The item type
	 * @param   int     $itemId    The id of item for which we need the associated items
	 *
	 * @return   array
	 *
	 * @since    3.7.0
	 */
	public function getAssociationList($typeName, $itemId)
	{
		$items = array();

		$associations = $this->getAssociations($typeName, $itemId);

		foreach ($associations as $key => $association)
		{
			$items[$key] = ArrayHelper::fromObject($this->getItem($typeName, (int) $association->id), false);
		}

		return $items;
	}

	/**
	 * Get information about the type
	 *
	 * @param   string  $typeName  The item type
	 *
	 * @return  array  Array of item types
	 *
	 * @since   3.7.0
	 */
	public function getType($typeName = '')
	{
		$fields  = $this->getFieldsTemplate();
		$tables  = array();
		$joins   = array();
		$support = $this->getSupportTemplate();
		$title   = '';

		return array(
			'fields'  => $fields,
			'support' => $support,
			'tables'  => $tables,
			'joins'   => $joins,
			'title'   => $title
		);
	}

	/**
	 * Get information about the fields the type provides
	 *
	 * @param   string  $typeName  The item type
	 *
	 * @return  array  Array of support information
	 *
	 * @since   3.7.0
	 */
	public function getTypeFields($typeName)
	{
		return $this->getTypeInformation($typeName, 'fields');
	}

	/**
	 * Get information about the fields the type provides
	 *
	 * @param   string  $typeName  The item type
	 *
	 * @return  array  Array of support information
	 *
	 * @since   3.7.0
	 */
	public function getTypeSupport($typeName)
	{
		return $this->getTypeInformation($typeName, 'support');
	}

	/**
	 * Get information about the tables the type use
	 *
	 * @param   string  $typeName  The item type
	 *
	 * @return  array  Array of support information
	 *
	 * @since   3.7.0
	 */
	public function getTypeTables($typeName)
	{
		return $this->getTypeInformation($typeName, 'tables');
	}

	/**
	 * Get information about the table joins for the type
	 *
	 * @param   string  $typeName  The item type
	 *
	 * @return  array  Array of support information
	 *
	 * @since   3.7.0
	 */
	public function getTypeJoins($typeName)
	{
		return $this->getTypeInformation($typeName, 'joins');
	}

	/**
	 * Get the type title
	 *
	 * @param   string  $typeName  The item type
	 *
	 * @return  array  Array of support information
	 *
	 * @since   3.7.0
	 */
	public function getTypeTitle($typeName)
	{
		$type = $this->getType($typeName);

		if (!array_key_exists('title', $type))
		{
			return '';
		}

		return $type['title'];
	}

	/**
	 * Get information about the type
	 *
	 * @param   string  $typeName  The item type
	 * @param   string  $part      part of the information
	 *
	 * @return  array Array of support information
	 *
	 * @since   3.7.0
	 */
	private function getTypeInformation($typeName, $part = 'support')
	{
		$type = $this->getType($typeName);

		if (!array_key_exists($part, $type))
		{
			return array();
		}

		return $type[$part];
	}

	/**
	 * Get a table field name for a type
	 *
	 * @param   string  $typeName   The item type
	 * @param   string  $fieldName  The item type
	 *
	 * @return  string
	 *
	 * @since   3.7.0
	 */
	public function getTypeFieldName($typeName, $fieldName)
	{
		$fields = $this->getTypeFields($typeName);

		if (!array_key_exists($fieldName, $fields))
		{
			return '';
		}

		$tmp = $fields[$fieldName];
		$pos = strpos($tmp, '.');

		if ($pos === false)
		{
			return $tmp;
		}

		return substr($tmp, $pos + 1);
	}

	/**
	 * Get default values for support array
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	protected function getSupportTemplate()
	{
		return array(
			'state'    => false,
			'acl'      => false,
			'checkout' => false
		);
	}

	/**
	 * Get default values for fields array
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	protected function getFieldsTemplate()
	{
		return array(
			'id'                  => 'a.id',
			'title'               => 'a.title',
			'alias'               => 'a.alias',
			'ordering'            => 'a.ordering',
			'menutype'            => '',
			'level'               => '',
			'catid'               => 'a.catid',
			'language'            => 'a.language',
			'access'              => 'a.access',
			'state'               => 'a.state',
			'created_user_id'     => 'a.created_by',
			'checked_out'         => 'a.checked_out',
			'checked_out_time'    => 'a.checked_out_time'
		);
	}
}

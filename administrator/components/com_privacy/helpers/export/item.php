<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PrivacyExportField', __DIR__ . '/field.php');

/**
 * Data object representing a single item within a domain.
 *
 * An item is typically a single row from a database table.
 *
 * @since  __DEPLOY_VERSION__
 */
class PrivacyExportItem
{
	/**
	 * The primary identifier of this item, typically the primary key for a database row.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	public $id;

	/**
	 * The fields belonging to this item
	 *
	 * @var    PrivacyExportField[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $fields = array();

	/**
	 * Add a field to the item
	 *
	 * @param   PrivacyExportField  $field  The field to add
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function addField(PrivacyExportField $field)
	{
		$this->fields[] = $field;
	}

	/**
	 * Get the item's fields
	 *
	 * @return  PrivacyExportField[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getFields()
	{
		return $this->fields;
	}
}

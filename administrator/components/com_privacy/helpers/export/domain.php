<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PrivacyExportItem', __DIR__ . '/item.php');

/**
 * Data object representing all data contained in a domain.
 *
 * A domain is typically a single database table and the items within the domain are separate rows from the table.
 *
 * @since  3.9.0
 */
class PrivacyExportDomain
{
	/**
	 * The name of this domain
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	public $name;

	/**
	 * A short description of the data in this domain
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	public $description;

	/**
	 * The items belonging to this domain
	 *
	 * @var    PrivacyExportItem[]
	 * @since  3.9.0
	 */
	protected $items = array();

	/**
	 * Add an item to the domain
	 *
	 * @param   PrivacyExportItem  $item  The item to add
	 *
	 * @return  void
	 *
	 * @since  3.9.0
	 */
	public function addItem(PrivacyExportItem $item)
	{
		$this->items[] = $item;
	}

	/**
	 * Get the domain's items
	 *
	 * @return  PrivacyExportItem[]
	 *
	 * @since  3.9.0
	 */
	public function getItems()
	{
		return $this->items;
	}
}

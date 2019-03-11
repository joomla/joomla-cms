<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cpanel\Administrator\Entities;

defined('_JEXEC') or die;

/**
 * Class representing a section in the system view.
 *
 * @since  4.0.0
 */
class SystemHeader
{
	/**
	 * The class for an icon to display for the header
	 *
	 * @var string
	 *
	 * @since  4.0.0
	 */
	private $icon;

	/**
	 * The title of the header
	 *
	 * @var string
	 *
	 * @since  4.0.0
	 */
	private $title;

	/**
	 * The list of items in this section
	 *
	 * @var SystemItem[]
	 *
	 * @since  4.0.0
	 */
	private $items = [];

	/**
	 * SystemHeader constructor.
	 *
	 * @param   string  $title  The title of the header
	 * @param   string  $icon   The class for an icon to display for the header
	 *
	 * @since  4.0.0
	 */
	public function __construct($title, $icon)
	{
		$this->title = $title;
		$this->icon  = $icon;
	}

	/**
	 * Method to add an item to the section
	 *
	 * @param   SystemItem  $item  The item to add to this section
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	public function addItem(SystemItem $item)
	{
		$this->items[] = $item;
	}

	/**
	 * Get the icon associated with the section
	 *
	 * @return string
	 *
	 * @since  4.0.0
	 */
	public function getIcon()
	{
		return $this->icon;
	}

	/**
	 * Get the title associated with the section
	 *
	 * @return string
	 *
	 * @since  4.0.0
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Get the items added into the section
	 *
	 * @return  SystemItem[]
	 *
	 * @since  4.0.0
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * Does the section contain any items
	 *
	 * @return  boolean
	 *
	 * @since  4.0.0
	 */
	public function hasItems()
	{
		return count($this->items) !== 0;
	}
}

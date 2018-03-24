<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Menu\Node;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Menu\Node;

/**
 * A Separator type of node for MenuTree
 *
 * @see    Node
 *
 * @since  3.8.0
 */
class Separator extends Node
{
	/**
	 * Node Title
	 *
	 * @var  string
	 *
	 * @since   3.8.0
	 */
	protected $title = null;

	/**
	 * Constructor for the class.
	 *
	 * @param   string  $title  The title of the node
	 *
	 * @since   3.8.0
	 */
	public function __construct($title = null)
	{
		$this->title = trim($title, '- ') ? $title : null;

		parent::__construct();
	}

	/**
	 * Get an attribute value
	 *
	 * @param   string  $name  The attribute name
	 *
	 * @return  mixed
	 *
	 * @since   3.8.0
	 */
	public function get($name)
	{
		switch ($name)
		{
			case 'title':
				return $this->$name;
		}

		return parent::get($name);
	}
}

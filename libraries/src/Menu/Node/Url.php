<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Menu\Node;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Menu\Node;

/**
 * An external Url type of node for MenuTree
 *
 * @see    Node
 *
 * @since  3.8.0
 */
class Url extends Node
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
	 * Node Link
	 *
	 * @var  string
	 *
	 * @since   3.8.0
	 */
	protected $link = null;

	/**
	 * Link Target
	 *
	 * @var  string
	 *
	 * @since   3.8.0
	 */
	protected $target = null;

	/**
	 * Link title icon
	 *
	 * @var  string
	 *
	 * @since   3.8.0
	 */
	protected $icon = null;

	/**
	 * Constructor for the class.
	 *
	 * @param   string  $title   The title of the node
	 * @param   string  $link    The node link
	 * @param   string  $target  The link target
	 * @param   string  $class   The CSS class for the node
	 * @param   string  $id      The node id
	 * @param   string  $icon    The title icon for the node
	 *
	 * @since   3.8.0
	 */
	public function __construct($title, $link, $target = null, $class = null, $id = null, $icon = null)
	{
		$this->title  = $title;
		$this->link   =	\JFilterOutput::ampReplace($link);
		$this->target = $target;
		$this->class  = $class;
		$this->id     = $id;
		$this->icon   = $icon;

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
			case 'link':
			case 'target':
			case 'icon':
				return $this->$name;
		}

		return parent::get($name);
	}
}

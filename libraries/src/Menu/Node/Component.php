<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Menu\Node;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Menu\Node;

/**
 * A Component type of node for MenuTree
 *
 * @see    Node
 *
 * @since  3.8.0
 */
class Component extends Node
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
	 * The component name for this node link
	 *
	 * @var  string
	 *
	 * @since   3.8.0
	 */
	protected $element = null;

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
	 * @param   string  $title    The title of the node
	 * @param   string  $element  The component name
	 * @param   string  $link     The node link
	 * @param   string  $target   The link target
	 * @param   string  $class    The CSS class for the node
	 * @param   string  $id       The node id
	 * @param   string  $icon     The title icon for the node
	 *
	 * @since   3.8.0
	 */
	public function __construct($title, $element, $link, $target = null, $class = null, $id = null, $icon = null)
	{
		$this->title   = $title;
		$this->element = $element;
		$this->link    = $link ? \JFilterOutput::ampReplace($link) : 'index.php?option=' . $element;
		$this->target  = $target;
		$this->class   = $class;
		$this->id      = $id;
		$this->icon    = $icon;

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
			case 'element':
			case 'link':
			case 'target':
			case 'icon':
				return $this->$name;
		}

		return parent::get($name);
	}
}

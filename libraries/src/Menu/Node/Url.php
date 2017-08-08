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
 * @since  __DEPLOY_VERSION__
 */
class Url extends Node
{
	/**
	 * Node Title
	 *
	 * @var  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $title = null;

	/**
	 * Node Link
	 *
	 * @var  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $link = null;

	/**
	 * Link Target
	 *
	 * @var  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $target = null;

	/**
	 * Link title icon
	 *
	 * @var  string
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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

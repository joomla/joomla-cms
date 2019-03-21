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
 * Class representing a item in the system view.
 *
 * @since  4.0.0
 */
class SystemItem
{
	/**
	 * The title of the item
	 *
	 * @var string
	 */
	private $title;

	/**
	 * The link of the item
	 *
	 * @var string
	 */
	private $link;

	/**
	 * An optional type for the ajax request
	 *
	 * @var string|null
	 */
	private $type;

	/**
	 * Class constructor.
	 *
	 * @param   string  $title  The title of the item
	 * @param   string  $link   The link for the item
	 * @param   string  $type   The type, requested by the ajax request
	 *
	 * @since  4.0.0
	 */
	public function __construct($title, $link, $type = '')
	{
		$this->title = $title;
		$this->link  = $link;

		if (!empty($type))
		{
			$this->type = $type;
		}
	}

	/**
	 * The item title
	 *
	 * @return  string
	 *
	 * @since  4.0.0
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * The item link
	 *
	 * @return  string
	 *
	 * @since  4.0.0
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * The type to load in the notification badge if there is one. Else null.
	 *
	 * @return string|null
	 *
	 * @since  4.0.0
	 */
	public function getType()
	{
		return $this->type;
	}
}

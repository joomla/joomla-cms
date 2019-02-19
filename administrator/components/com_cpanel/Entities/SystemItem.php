<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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
	 * An optional badge of the item
	 *
	 * @var string|null
	 */
	private $badge;

	/**
	 * Class constructor.
	 *
	 * @param   string  $title  The title of the item
	 * @param   string  $link   The link for the item
	 * @param   string  $badge  The optional badge for the item
	 *
	 * @since  4.0.0
	 */
	public function __construct($title, $link, $badge = '')
	{
		$this->title = $title;
		$this->link  = $link;

		if (!empty($badge))
		{
			$this->badge = $badge;
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
	 * The string to display in the notification badge if there is one. Else null.
	 *
	 * @return string|null
	 *
	 * @since  4.0.0
	 */
	public function getBadge()
	{
		return $this->badge;
	}
}

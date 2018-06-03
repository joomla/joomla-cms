<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Event;

defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;

/**
 * Event object to retrieve OAuthCallbacks.
 *
 * @since  4.0.0
 */
class OAuthCallbackEvent extends AbstractEvent
{
	/**
	 * The event context.
	 *
	 * @var string
	 * @since  4.0.0
	 */
	private $context = null;

	/**
	 * The event input.
	 *
	 * @var \JInput
	 * @since  4.0.0
	 */
	private $input = null;

	/**
	 * Get the event context.
	 *
	 * @return string
	 *
	 * @since  4.0.0
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Set the event context.
	 *
	 * @param   string  $context  Event context
	 *
	 * @return void
	 *
	 * @since  4.0.0
	 */
	public function setContext($context)
	{
		$this->context = $context;
	}

	/**
	 * Get the event input.
	 *
	 * @return \JInput
	 *
	 * @since  4.0.0
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Set the event input.
	 *
	 * @param   \JInput  $input  Event input
	 *
	 * @return void
	 *
	 * @since  4.0.0
	 */
	public function setInput($input)
	{
		$this->input = $input;
	}
}

<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Event;

use Joomla\CMS\Event\AbstractEvent;

defined('_JEXEC') or die;

/**
 * Event object for retreving OAuthCallbacks
 *
 * @since  __DEPLOY_VERSION__
 */
class OAuthCallbackEvent extends AbstractEvent
{
	/**
	 * The event context
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	private $context = null;

	/**
	 * The event parameters
	 *
	 * @var array
	 * @since  __DEPLOY_VERSION__
	 */
	private $parameters = null;

	/**
	 * Get the event context
	 *
	 * @return string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Set the event context
	 *
	 * @param   string  $context  Event context
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setContext($context)
	{
		$this->context = $context;
	}

	/**
	 * Get the event parameters
	 *
	 * @return array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * Set the event parameters
	 *
	 * @param   array  $parameters  Event parameters
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setParameters($parameters)
	{
		$this->parameters = $parameters;
	}
}

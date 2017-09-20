<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Event;

defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\Component\Media\Administrator\Provider\ProviderManager;

/**
 * Event object to retrieve Media Adapters.
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaProviderEvent extends AbstractEvent
{
	/**
	 * The ProviderManager for event
	 *
	 * @var ProviderManager
	 * @since  __DEPLOY_VERSION__
	 */
	private $providerManager = null;

	/**
	 * Return the ProviderManager
	 *
	 * @return  ProviderManager
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getProviderManager()
	{
		return $this->providerManager;
	}

	/**
	 * Set the ProviderManager
	 *
	 * @param   ProviderManager  $providerManager  The Provider Manager to be set
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setProviderManager($providerManager)
	{
		$this->providerManager = $providerManager;
	}
}

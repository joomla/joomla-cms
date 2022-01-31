<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Event;

\defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\Component\Media\Administrator\Provider\ProviderManager;

/**
 * Event object to retrieve Media Adapters.
 *
 * @since  4.0.0
 */
class MediaProviderEvent extends AbstractEvent
{
	/**
	 * The ProviderManager for event
	 *
	 * @var ProviderManager
	 * @since  4.0.0
	 */
	private $providerManager = null;

	/**
	 * Return the ProviderManager
	 *
	 * @return  ProviderManager
	 *
	 * @since  4.0.0
	 */
	public function getProviderManager(): ProviderManager
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
	 * @since  4.0.0
	 */
	public function setProviderManager(ProviderManager $providerManager)
	{
		$this->providerManager = $providerManager;
	}
}

<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Event;

\defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;

/**
 * Event object to set an url.
 *
 * @since  __DEPLOY_VERSION__
 */
class FetchMediaFileUrlEvent extends AbstractEvent
{
	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  \BadMethodCallException
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($name, array $arguments = array())
	{
		parent::__construct($name, $arguments);

		// Check for required arguments
		if (!\array_key_exists('url', $arguments) || !is_string($arguments['url']))
		{
			throw new \BadMethodCallException("Argument 'url' of event $name is not of the expected type");
		}
	}

	/**
	 * Returns the event url.
	 *
	 * @return string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getUrl(): string
	{
		return $this->arguments['url'];
	}
}

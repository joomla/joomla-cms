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
 * Event object for fetch media file.
 *
 * @since  __DEPLOY_VERSION__
 */
class FetchMediaFileEvent extends AbstractEvent
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
		if (!\array_key_exists('file', $arguments) || !is_object($arguments['file']))
		{
			throw new \BadMethodCallException("Argument 'file' of event $name is not of the expected type");
		}
	}

	/**
	 * Returns the event file.
	 *
	 * @return stdClass
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getFile(): \stdClass
	{
		return $this->arguments['file'];
	}
}

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

/**
 * Event object for fetch media files.
 *
 * @since  __DEPLOY_VERSION__
 */
class FetchMediaFilesEvent extends AbstractEvent
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
		if (!\array_key_exists('files', $arguments) || !is_array($arguments['files']))
		{
			throw new \BadMethodCallException("Argument 'files' of event $name is not of the expected type");
		}
	}

	/**
	 * Returns the event files.
	 *
	 * @return array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getFiles(): array
	{
		return $this->arguments['files'];
	}
}

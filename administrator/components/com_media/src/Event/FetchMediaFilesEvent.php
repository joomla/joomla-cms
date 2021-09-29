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
 * Event object for fetch media files.
 *
 * @since  __DEPLOY_VERSION__
 */
class FetchMediaFilesEvent extends AbstractEvent
{
	/**
	 * @var array
	 * @since __DEPLOY_VERSION__
	 */
	private $files;

	/**
	 * Constructor.
	 *
	 * @param   string     $name   The event name.
	 * @param   \stdClass  $files  The files.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($name, array $files)
	{
		parent::__construct($name, []);

		$this->files = $files;
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
		return $this->files;
	}

	/**
	 * Sets the event files.
	 *
	 * @param array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setFiles(array $files)
	{
		$this->files = $files;
	}
}

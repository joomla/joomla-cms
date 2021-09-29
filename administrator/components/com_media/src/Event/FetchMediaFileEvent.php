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
	 * @var \stdClass
	 * @since __DEPLOY_VERSION__
	 */
	private $file;

	/**
	 * Constructor.
	 *
	 * @param   string     $name  The event name.
	 * @param   \stdClass  $file  The file.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($name, \stdClass $file)
	{
		parent::__construct($name, []);

		$this->file = $file;
	}

	/**
	 * Returns the event file.
	 *
	 * @return \stdClass
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getFile(): \stdClass
	{
		return $this->file;
	}

	/**
	 * Sets the event file.
	 *
	 * @param   \stdClass  $file  The file.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setFile(\stdClass $file): void
	{
		$this->file = $file;
	}
}

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
class FetchMediaUrlUrlEvent extends AbstractEvent
{
	/**
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	private $url;

	/**
	 * Constructor.
	 *
	 * @param   string  $name  The event name.
	 * @param   string  $url   The url.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($name, string $url)
	{
		parent::__construct($name, []);

		$this->url = $url;
	}

	/**
	 * Returns the event url.
	 *
	 * @return stdClass
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getUrl(): string
	{
		return $this->url;
	}

	/**
	 * Sets the event url.
	 *
	 * @param stdClass
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setUrl(string $url)
	{
		$this->url = $url;
	}
}

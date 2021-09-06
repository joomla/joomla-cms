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
 * Event object to set an url.
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaFileUrlEvent extends AbstractEvent
{
	/**
	 * The url.
	 *
	 * @var string $url
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $url;

	/**
	 * Returns the url.
	 *
	 * @return string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
    public function getUrl(): string
    {
        return $this->url;
    }

	/**
	 * Set the url.
	 *
	 * @param string $url
	 *
	 * @since  __DEPLOY_VERSION__
	 */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }
}

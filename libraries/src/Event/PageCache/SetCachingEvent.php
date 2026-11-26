<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\PageCache;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;

\defined('_JEXEC') or die;

/**
 * Class for PageCache events
 *
 * @since  5.1.0
 */
class SetCachingEvent extends PageCacheEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeBooleanAware;
}

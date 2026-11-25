<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Application;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;

\defined('_JEXEC') or die;

/**
 * Class for Application Configuration events
 *
 * @since  5.0.0
 */
class BeforeSaveConfigurationEvent extends ApplicationConfigurationEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeBooleanAware;
}

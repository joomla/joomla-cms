<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Installer;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;

\defined('_JEXEC') or die;

/**
 * Class for Installer events
 *
 * @since  5.0.0
 */
class BeforeInstallationEvent extends InstallerEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeBooleanAware;
}

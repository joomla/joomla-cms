<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\SampleData;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeObjectAware;

\defined('_JEXEC') or die;

/**
 * Cass for Sample data events
 *
 * @since  5.3.0
 */
class GetOverviewEvent extends SampleDataEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeObjectAware;
}

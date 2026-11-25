<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\CustomFields;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeMixedAware;

\defined('_JEXEC') or die;

/**
 * Class for CustomFields events
 *
 * @since  5.0.0
 */
class PrepareFieldEvent extends AbstractPrepareFieldEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeMixedAware;
}

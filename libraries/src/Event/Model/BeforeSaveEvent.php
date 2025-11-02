<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Model;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Model event.
 * Example:
 *  new BeforeSaveEvent('onEventName', ['context' => 'com_example.example', 'subject' => $itemObjectToSave, 'isNew' => $isNew, 'data' => $submittedData]);
 *
 * @since  5.0.0
 */
class BeforeSaveEvent extends SaveEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeBooleanAware;
}

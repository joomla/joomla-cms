<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\User;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;

\defined('_JEXEC') or die;

/**
 * Class for User event.
 * Example:
 *  new LoginEvent('onEventName', ['subject' => $authenticationResponseArray, 'options' => $options]);
 *
 * @since  5.0.0
 */
class LoginEvent extends AbstractLoginEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeBooleanAware;
}

<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Plugin\System\Webauthn;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;

/**
 * Concrete event class for the onAjaxWebauthnDelete event
 *
 * @since  4.2.0
 */
class AjaxDelete extends AbstractImmutableEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeBooleanAware;
}

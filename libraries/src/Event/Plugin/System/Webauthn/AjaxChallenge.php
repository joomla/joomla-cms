<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Plugin\System\Webauthn;

use InvalidArgumentException;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;

/**
 * Concrete event class for the onAjaxWebauthnChallenge event
 *
 * @since  4.2.0
 */
class AjaxChallenge extends AbstractImmutableEvent implements ResultAwareInterface
{
    use ResultAware;

    /**
     * Make sure the result is valid JSON or boolean false
     *
     * @param   mixed  $data  The data to check
     *
     * @return  void
     * @since   4.2.0
     */
    public function typeCheckResult($data): void
    {
        if ($data === false) {
            return;
        }

        if (!is_string($data) || @json_decode($data) === null) {
            throw new InvalidArgumentException(sprintf('Event %s only accepts JSON results.', $this->getName()));
        }
    }
}

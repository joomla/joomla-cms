<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface defining a factory which can create mailer objects.
 *
 * @since  4.4.0
 */
interface MailerFactoryInterface
{
    /**
     * Method to get an instance of a mailer. If the passed settings are null,
     * then the mailer does use the internal configuration.
     *
     * @param   ?Registry  $settings  The configuration
     *
     * @return  MailerInterface
     *
     * @since   4.4.0
     */
    public function createMailer(?Registry $settings = null): MailerInterface;
}

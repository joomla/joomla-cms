<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface to be implemented by classes depending on a mailer factory.
 *
 * @since  4.4.0
 */
interface MailerFactoryAwareInterface
{
    /**
     * Set the mailer factory to use.
     *
     * @param   ?MailerFactoryInterface  $mailerFactory  The mailer factory to use.
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function setMailerFactory(?MailerFactoryInterface $mailerFactory = null): void;
}

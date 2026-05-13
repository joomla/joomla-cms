<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides a common interface to send emails through an SMTP or sendmail service.
 *
 * @since  __DEPLOY_VERSION__
 */
interface TransportConfigurableMailerInterface
{
    /**
     * Use SMTP for sending the email.
     *
     * @param   string   $auth    SMTP Authentication [optional]
     * @param   string   $host    SMTP Host [optional]
     * @param   string   $user    SMTP Username [optional]
     * @param   string   $pass    SMTP Password [optional]
     * @param   string   $secure  Use secure methods
     * @param   integer  $port    The SMTP port
     *
     * @return  boolean  True on success
     *
     * @since   __DEPLOY_VERSION__
     */
    public function useSmtp($auth = null, $host = null, $user = null, $pass = null, $secure = null, $port = 25);

    /**
     * Use sendmail for sending the email.
     *
     * @return  boolean  True on success
     *
     * @since   __DEPLOY_VERSION__
     */
    public function useSendmail($sendmail = null);
}

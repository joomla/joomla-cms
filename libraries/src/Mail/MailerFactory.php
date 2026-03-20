<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Default factory for creating mailer objects.
 *
 * @since  4.4.0
 */
class MailerFactory implements MailerFactoryInterface
{
    /**
     * The default configuration.
     *
     * @var     Registry
     * @since   4.4.0
     */
    private $defaultConfiguration;

    /**
     * The MailerFactory constructor.
     *
     * @param   Registry  $defaultConfiguration  The default configuration
     */
    public function __construct(Registry $defaultConfiguration)
    {
        $this->defaultConfiguration = $defaultConfiguration;
    }

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
    public function createMailer(?Registry $settings = null): MailerInterface
    {
        $configuration = new Registry($this->defaultConfiguration);

        if ($settings) {
            $configuration->merge($settings);
        }

        $mailer = new Mail((bool) $configuration->get('throw_exceptions', true));

        $smtpauth   = $configuration->get('smtpauth') == 0 ? null : 1;
        $smtpuser   = $configuration->get('smtpuser');
        $smtppass   = $configuration->get('smtppass');
        $smtphost   = $configuration->get('smtphost');
        $smtpsecure = $configuration->get('smtpsecure');
        $smtpport   = $configuration->get('smtpport');
        $mailfrom   = $configuration->get('mailfrom');
        $fromname   = $configuration->get('fromname');
        $mailType   = $configuration->get('mailer');
        $mailsender = $configuration->get('mailsender');

        // Configure Sender (return path) when present
        $mailsender = $mailsender ? MailHelper::cleanLine($mailsender) : false;

        if ($mailsender && MailHelper::isEmailAddress($mailsender)) {
            try {
                $setResult = $mailer->setFrom($mailsender);
            } catch (\Exception) {
                $setResult = false;
            }

            if (!$setResult) {
                Log::add(__METHOD__ . '() could not set the sender data.', Log::WARNING, 'mail');
            }
        }

        // Configure From when present
        $mailfrom = $mailfrom ? MailHelper::cleanLine($mailfrom) : false;

        // Set default From without Reply-to if the mailfrom is a valid address
        if ($mailfrom && MailHelper::isEmailAddress($mailfrom)) {
            try {
                $setResult = $mailer->setFrom($mailfrom, MailHelper::cleanLine($fromname), false);
            } catch (\Exception) {
                $setResult = false;
            }

            if (!$setResult) {
                Log::add(__METHOD__ . '() could not set the mailfrom data.', Log::WARNING, 'mail');
            }
        }

        // Default mailer is to use PHP's mail function
        switch ($mailType) {
            case 'smtp':
                $mailer->useSmtp($smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport);
                break;

            case 'sendmail':
                $mailer->isSendmail();
                break;

            default:
                $mailer->isMail();
                break;
        }

        return $mailer;
    }
}

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
        $oauth2Provider = (string) ($configuration->get('oauth2_provider') ?: 'microsoft');
        $oauth2ClientId = (string) $configuration->get('oauth2_client_id');
        $oauth2ClientSecret = (string) $configuration->get('oauth2_client_secret');
        $oauth2RefreshToken = (string) $configuration->get('oauth2_refresh_token');
        $oauth2Scope = (string) $configuration->get('oauth2_scope');
        $oauth2TenantMode = (string) $configuration->get('oauth2_tenant_mode', '');
        $oauth2TenantId = (string) $configuration->get('oauth2_tenant_id', 'common');
        $oauth2TokenUrl = (string) $configuration->get('oauth2_token_url');
        $oauth2SmtpHost = (string) $configuration->get('oauth2_smtp_host');
        $oauth2SmtpPort = (int) $configuration->get('oauth2_smtp_port', 587);
        $oauth2SmtpSecure = (string) $configuration->get('oauth2_smtp_secure', 'tls');
        $oauth2Email = (string) $configuration->get('oauth2_email');

        if ($oauth2TenantMode === '') {
            $oauth2TenantMode = strtolower($oauth2TenantId) !== 'common' ? 'tenant' : 'common';
        }

        if ($oauth2TenantMode !== 'tenant') {
            $oauth2TenantId = 'common';
        } elseif ($oauth2TenantId === '') {
            $oauth2TenantId = 'common';
        }

        // Clean the email address
        $mailfrom = MailHelper::cleanLine($mailfrom);

        // Set default sender without Reply-to if the mailfrom is a valid address
        if (MailHelper::isEmailAddress($mailfrom)) {
            // Wrap in try/catch to catch Exception if it is throwing them
            try {
                // Check for a false return value if exception throwing is disabled
                if ($mailer->setFrom($mailfrom, MailHelper::cleanLine($fromname), false) === false) {
                    Log::add(__METHOD__ . '() could not set the sender data.', Log::WARNING, 'mail');
                }
            } catch (\Exception) {
                Log::add(__METHOD__ . '() could not set the sender data.', Log::WARNING, 'mail');
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

            case 'smtpoauth2':
                $oauthUser = $oauth2Email ?: ($smtpuser ?: $mailfrom);
                $tokenUrl  = '';
                $scope     = $oauth2Scope;
                $smtpHost  = '';
                $smtpPort  = 587;
                $smtpSecureMode = 'tls';

                switch ($oauth2Provider) {
                    case 'google':
                        $tokenUrl = 'https://oauth2.googleapis.com/token';
                        $scope = $scope ?: 'https://mail.google.com/';
                        $smtpHost = 'smtp.gmail.com';
                        break;

                    case 'custom':
                        $tokenUrl = $oauth2TokenUrl;
                        $smtpHost = $oauth2SmtpHost;
                        $smtpPort = $oauth2SmtpPort > 0 ? $oauth2SmtpPort : 587;
                        $smtpSecureMode = $oauth2SmtpSecure ?: 'tls';
                        break;

                    default:
                        $tokenUrl = 'https://login.microsoftonline.com/' . rawurlencode($oauth2TenantId) . '/oauth2/v2.0/token';
                        $scope = $scope ?: 'https://outlook.office.com/SMTP.Send offline_access';
                        $smtpHost = 'smtp.office365.com';
                        break;
                }

                if (!$oauthUser || !$oauth2ClientId || !$oauth2ClientSecret || !$oauth2RefreshToken || !$tokenUrl) {
                    throw new \RuntimeException('OAuth2 SMTP configuration is incomplete.');
                }

                // Configure SMTP transport directly because OAuth2/XOAUTH2 does not use a password.
                $mailer->isSMTP();
                $mailer->Host = $smtpHost;
                $mailer->Port = $smtpPort;
                $mailer->SMTPAuth = true;
                $mailer->Username = $oauthUser;
                $mailer->Password = '';
                $mailer->SMTPSecure = \in_array($smtpSecureMode, ['ssl', 'tls'], true) ? $smtpSecureMode : '';
                $mailer->AuthType = 'XOAUTH2';
                $mailer->setOAuth(
                    new SmtpOAuth2TokenProvider(
                        $tokenUrl,
                        $scope,
                        $oauth2ClientId,
                        $oauth2ClientSecret,
                        $oauth2RefreshToken,
                        $oauthUser
                    )
                );
                break;

            default:
                $mailer->isMail();
                break;
        }

        return $mailer;
    }
}

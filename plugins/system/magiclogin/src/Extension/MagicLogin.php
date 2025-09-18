<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.MagicLogin
 *
 * @copyright   Copyright (C) 2025 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\MagicLogin\Extension;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Magic Login System Plugin
 *
 * Allows users to login with magic links sent via email. When a login fails with an email address,
 * a magic link is automatically sent to the user's email for passwordless authentication.
 *
 * @since  1.0.0
 */
final class MagicLogin extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Application object
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  1.0.0
     */
    protected $app;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   1.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise' => 'onAfterInitialise',
        ];
    }

    /**
     * Handles the onAfterInitialise event
     *
     * Processes magic tokens from URLs and intercepts email-based login attempts
     * to send magic links instead of showing authentication errors.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function onAfterInitialise(): void
    {
        if ($this->app->isClient('administrator')) {
            return;
        }

        // Load plugin language
        $this->loadLanguage();

        // Check for magic token
        $token = $this->app->input->get('magic_token', '', 'ALNUM');
        if (!empty($token)) {
            Log::add('Processing magic token: ' . $token, Log::INFO, 'plg_system_magiclogin');
            $this->processMagicToken($token);
            return;
        }

        // Only process POST requests for login
        if ($this->app->input->getMethod() !== 'POST') {
            return;
        }

        // Check for login attempt with email
        $username = $this->app->input->get('username', '', 'string');
        $password = $this->app->input->get('password', '', 'string');

        Log::add('POST - Username: ' . $username . ', Has password: ' . (!empty($password) ? 'yes' : 'no'), Log::INFO, 'plg_system_magiclogin');

        // If username is email (with or without password)
        if (!empty($username) && filter_var($username, FILTER_VALIDATE_EMAIL)) {
            Log::add('Sending magic link to: ' . $username, Log::INFO, 'plg_system_magiclogin');
            $this->sendMagicLink($username);

            $this->app->enqueueMessage(
                Text::_('PLG_SYSTEM_MAGICLOGIN_EMAIL_SENT'),
                'info'
            );

            $this->app->redirect(Uri::base());
        }
    }

    /**
     * Sends a magic link to the specified email address
     *
     * Generates a secure token, stores it in the database, and sends an email
     * with a magic link that allows passwordless authentication.
     *
     * @param   string  $email  The email address to send the magic link to
     *
     * @return  void
     *
     * @since   1.0.0
     */
    private function sendMagicLink($email)
    {
        $db = $this->getDatabase();

        // Rate limiting check
        if ($this->isRateLimited($email)) {
            Log::add('Rate limit exceeded for email: ' . $email, Log::WARNING, 'plg_system_magiclogin');
            return;
        }

        // Check if user exists and is active
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'username', 'name']))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('email') . ' = :email')
            ->where($db->quoteName('block') . ' = 0')
            ->where($db->quoteName('activation') . ' = ' . $db->quote(''))
            ->bind(':email', $email);

        $user = $db->setQuery($query)->loadObject();

        if (!$user) {
            return; // Don't reveal if email exists
        }

        // Generate secure token
        $token       = $this->generateSecureToken();
        $hashedToken = $this->hashToken($token);
        $expiry      = time() + ($this->params->get('token_expiry', 15) * 60);
        $ipAddress   = $this->app->input->server->get('REMOTE_ADDR');
        $userAgent   = $this->app->input->server->get('HTTP_USER_AGENT');

        // Store hashed token with security data
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__magiclogin_tokens'))
            ->columns($db->quoteName(['user_id', 'token', 'expires', 'ip_address', 'user_agent']))
            ->values($user->id . ',' . $db->quote($hashedToken) . ',' . $db->quote(date('Y-m-d H:i:s', $expiry)) . ',' . $db->quote($ipAddress) . ',' . $db->quote($userAgent));

        $db->setQuery($query)->execute();

        // Send email using MailTemplate
        try {
            $magicLink = Uri::base() . '?magic_token=' . $token;
            $siteName  = $this->app->get('sitename');

            $mailer = new MailTemplate('plg_system_magiclogin.magiclink', $this->app->getLanguage()->getTag());
            $mailer->addRecipient($email);
            $mailer->addTemplateData([
                'SITENAME'       => $siteName,
                'USERNAME'       => $user->name,
                'MAGIC_LINK'     => $magicLink,
                'EXPIRY_MINUTES' => $this->params->get('token_expiry', 15),
            ]);
            $mailer->send();
        } catch (\Exception $e) {
            Log::add('Failed to send magic link email: ' . $e->getMessage(), Log::ERROR, 'plg_system_magiclogin');
        }
    }

    /**
     * Processes a magic token from the URL
     *
     * Validates the token, logs the user in if valid, and cleans up expired tokens.
     * Also logs the login action to the user action log.
     *
     * @param   string  $token  The magic token to process
     *
     * @return  void
     *
     * @since   1.0.0
     */
    private function processMagicToken($token)
    {
        // Security headers
        $this->app->setHeader('X-Frame-Options', 'DENY');
        $this->app->setHeader('X-Content-Type-Options', 'nosniff');

        $session = Factory::getApplication()->getSession();

        $db               = $this->getDatabase();
        $currentIp        = $this->app->input->server->get('REMOTE_ADDR');
        $currentUserAgent = $this->app->input->server->get('HTTP_USER_AGENT');

        // Clean expired tokens
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__magiclogin_tokens'))
            ->where($db->quoteName('expires') . ' < ' . $db->quote(date('Y-m-d H:i:s')));
        $db->setQuery($query)->execute();

        // Get tokens for IP and user agent validation, then verify hash
        $query = $db->getQuery(true)
            ->select($db->quoteName(['user_id', 'token']))
            ->from($db->quoteName('#__magiclogin_tokens'))
            ->where($db->quoteName('ip_address') . ' = :ip')
            ->where($db->quoteName('user_agent') . ' = :ua')
            ->bind(':ip', $currentIp)
            ->bind(':ua', $currentUserAgent);

        $tokens    = $db->setQuery($query)->loadObjectList();
        $tokenData = null;

        // Verify token hash
        foreach ($tokens as $storedToken) {
            if (password_verify($token, $storedToken->token)) {
                $tokenData = $storedToken;
                break;
            }
        }

        if (!$tokenData) {
            Log::add('Invalid token or security mismatch for IP: ' . $currentIp, Log::WARNING, 'plg_system_magiclogin');
            $this->app->enqueueMessage(Text::_('PLG_SYSTEM_MAGICLOGIN_INVALID_TOKEN'), 'error');
            return;
        }

        // Get user
        $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($tokenData->user_id);

        if ($user->id) {
            // Set user session directly
            $session->set('user', $user);

            // Update last visit date
            $user->setLastVisit();

            // Log user action using ActionLog model
            $messageLanguageKey = 'PLG_ACTIONLOG_JOOMLA_USER_LOGGED_IN';
            try {
                /** @var \Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel $model */
                $model = $this->app->bootComponent('com_actionlogs')
                    ->getMVCFactory()->createModel('Actionlog', 'Administrator', ['ignore_request' => true]);

                $message = [
                    'action'      => 'login',
                    'userid'      => $user->id,
                    'username'    => $user->username,
                    'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
                    'app'         => 'PLG_ACTIONLOG_JOOMLA_APPLICATION_SITE',
                ];

                $model->addLog([$message], $messageLanguageKey, 'plg_system_magiclogin', $user->id);
            } catch (\Exception $e) {
                // Log error but don't stop login process
                Log::add('Failed to log action: ' . $e->getMessage(), Log::WARNING, 'plg_system_magiclogin');
            }

            // Delete used token by user_id and security context
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__magiclogin_tokens'))
                ->where($db->quoteName('user_id') . ' = :user_id')
                ->where($db->quoteName('ip_address') . ' = :ip')
                ->where($db->quoteName('user_agent') . ' = :ua')
                ->bind(':user_id', $tokenData->user_id)
                ->bind(':ip', $currentIp)
                ->bind(':ua', $currentUserAgent);
            $db->setQuery($query)->execute();

            $this->app->enqueueMessage(Text::_('PLG_SYSTEM_MAGICLOGIN_LOGIN_SUCCESS'), 'success');
            $this->app->redirect(Uri::base());
        }
    }

    /**
     * Check if email is rate limited
     *
     * @param   string  $email  Email address to check
     *
     * @return  bool
     *
     * @since   1.0.0
     */
    private function isRateLimited($email): bool
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__magiclogin_tokens'))
            ->where($db->quoteName('user_id') . ' = (SELECT id FROM ' . $db->quoteName('#__users') . ' WHERE ' . $db->quoteName('email') . ' = :email)')
            ->where($db->quoteName('created') . ' > DATE_SUB(NOW(), INTERVAL 5 MINUTE)')
            ->bind(':email', $email);

        return $db->setQuery($query)->loadResult() >= 3;
    }

    /**
     * Generate cryptographically secure token
     *
     * @return  string
     *
     * @since   1.0.0
     */
    private function generateSecureToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Hash token for secure storage
     *
     * @param   string  $token  Plain token
     *
     * @return  string
     *
     * @since   1.0.0
     */
    private function hashToken(string $token): string
    {
        return password_hash($token, PASSWORD_ARGON2ID);
    }
}

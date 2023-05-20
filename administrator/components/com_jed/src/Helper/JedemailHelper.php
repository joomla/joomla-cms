<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\User\User;

use function defined;

/**
 * JED Email Helper
 *
 * @package   JED
 * @since     4.0.0
 */
class JedemailHelper
{
    /**
     * The mail engine
     *
     * @var    Mail
     * @since  4.0.0
     */


    /**
     * Send an email to the user.
     *
     * @param   string  $subject    The message subject
     * @param   string  $body       The message body
     * @param   User    $recipient  The user recipient
     * @param   string  $sender     The current JED administrator user
     *
     * @return  string
     *
     * @since   4.0.0
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function sendEmail(string $subject, string $body, User $recipient, string $sender): string
    {


        //  $sender = User::getInstance($userId);


        // Prepare the email
        $mailer = Factory::getMailer();

        $mailer->isHtml()
            ->addReplyTo('noreply@extensions.joomla.org', $sender)
            ->setFrom('noreply@extensions.joomla.org', $sender);

        // Send the email

        try {
            $result = $mailer
                ->addRecipient($recipient->email, $recipient->name)
                ->setSubject($subject)
                ->setBody($body)
                ->Send();

            if ($result === false) {
                return $mailer->ErrorInfo;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return "";
        //  $this->storeEmail($extensionId, $subject, $body, $developer, $sender);
    }
}

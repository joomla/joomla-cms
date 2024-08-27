<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use PHPMailer\PHPMailer\Exception as phpmailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Email Templating Class
 *
 * @since  4.0.0
 */
class MailTemplate
{
    /**
     * Mailer object to send the actual mail.
     *
     * @var    \Joomla\CMS\Mail\Mail
     * @since  4.0.0
     */
    protected $mailer;

    /**
     * Identifier of the mail template.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $template_id;

    /**
     * Language of the mail template.
     *
     * @var    string
     */
    protected $language;

    /**
     *
     * @var    string[]
     * @since  4.0.0
     */
    protected $data = [];

    /**
     *
     * @var    string[]
     * @since  4.4.7
     */
    protected $unsafe_tags = [];

    /**
     *
     * @var    string[]
     * @since  4.0.0
     */
    protected $attachments = [];

    /**
     * List of recipients of the email
     *
     * @var    \stdClass[]
     * @since  4.0.0
     */
    protected $recipients = [];

    /**
     * Reply To of the email
     *
     * @var    \stdClass
     * @since  4.0.0
     */
    protected $replyto;

    /**
     * Constructor for the mail templating class
     *
     * @param   string  $templateId  Id of the mail template.
     * @param   string  $language    Language of the template to use.
     * @param   Mail    $mailer      Mail object to send the mail with.
     *
     * @since   4.0.0
     */
    public function __construct($templateId, $language, Mail $mailer = null)
    {
        $this->template_id = $templateId;
        $this->language    = $language;

        if ($mailer) {
            $this->mailer = $mailer;
        } else {
            $this->mailer = Factory::getMailer();
        }
    }

    /**
     * Add an attachment to the mail
     *
     * @param   string  $name  Filename of the attachment
     * @param   string  $file  Either a filepath or filecontent
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function addAttachment($name, $file)
    {
        $attachment          = new \stdClass();
        $attachment->name    = $name;
        $attachment->file    = $file;
        $this->attachments[] = $attachment;
    }

    /**
     * Adds recipients for this mail
     *
     * @param   string  $mail  Mail address of the recipient
     * @param   string  $name  Name of the recipient
     * @param   string  $type  How should the recipient receive the mail? ('to', 'cc', 'bcc')
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function addRecipient($mail, $name = null, $type = 'to')
    {
        $recipient          = new \stdClass();
        $recipient->mail    = $mail;
        $recipient->name    = $name ?? $mail;
        $recipient->type    = $type;
        $this->recipients[] = $recipient;
    }

    /**
     * Set reply to for this mail
     *
     * @param   string  $mail  Mail address to reply to
     * @param   string  $name  Name
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function setReplyTo($mail, $name = '')
    {
        $reply         = new \stdClass();
        $reply->mail   = $mail;
        $reply->name   = $name;
        $this->replyto = $reply;
    }

    /**
     * Add data to replace in the template
     *
     * @param   array  $data  Associative array of strings to replace
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function addTemplateData($data)
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Mark tags as unsafe to ensure escaping in HTML mails
     *
     * @param   array   $tags  Tag names
     *
     * @return  void
     *
     * @since   4.4.7
     */
    public function addUnsafeTags($tags)
    {
        $this->unsafe_tags = array_merge($this->unsafe_tags, array_map('strtoupper', $tags));
    }

    /**
     * Render and send the mail
     *
     * @return  boolean  True on success
     *
     * @since   4.0.0
     * @throws  \Exception
     * @throws  MailDisabledException
     * @throws  phpmailerException
     */
    public function send()
    {
        $config = ComponentHelper::getParams('com_mails');

        $mail = self::getTemplate($this->template_id, $this->language);

        // If the Mail Template was not found in the db, we cannot send an email.
        if ($mail === null) {
            return false;
        }

        /** @var Registry $params */
        $params      = $mail->params;
        $app         = Factory::getApplication();
        $replyTo     = $app->get('replyto', '');
        $replyToName = $app->get('replytoname', '');

        if ((int) $config->get('alternative_mailconfig', 0) === 1 && (int) $params->get('alternative_mailconfig', 0) === 1) {
            if ($this->mailer->Mailer === 'smtp' || $params->get('mailer') === 'smtp') {
                $smtpauth   = ($params->get('smtpauth', $app->get('smtpauth')) == 0) ? null : 1;
                $smtpuser   = $params->get('smtpuser', $app->get('smtpuser'));
                $smtppass   = $params->get('smtppass', $app->get('smtppass'));
                $smtphost   = $params->get('smtphost', $app->get('smtphost'));
                $smtpsecure = $params->get('smtpsecure', $app->get('smtpsecure'));
                $smtpport   = $params->get('smtpport', $app->get('smtpport'));
                $this->mailer->useSmtp($smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport);
            }

            if ($params->get('mailer') === 'sendmail') {
                $this->mailer->isSendmail();
            }

            $mailfrom = $params->get('mailfrom', $app->get('mailfrom'));
            $fromname = $params->get('fromname', $app->get('fromname'));

            if (MailHelper::isEmailAddress($mailfrom)) {
                $this->mailer->setFrom(MailHelper::cleanLine($mailfrom), MailHelper::cleanLine($fromname), false);
            }

            $replyTo     = $params->get('replyto', $replyTo);
            $replyToName = $params->get('replytoname', $replyToName);
        }

        $app->triggerEvent('onMailBeforeRendering', [$this->template_id, &$this]);

        $subject = $this->replaceTags(Text::_($mail->subject), $this->data);
        $this->mailer->setSubject($subject);

        $mailStyle = $config->get('mail_style', 'plaintext');
        $plainBody = $this->replaceTags(Text::_($mail->body), $this->data);
        $htmlBody  = $this->replaceTags(Text::_($mail->htmlbody), $this->data, true);

        if ($mailStyle === 'plaintext' || $mailStyle === 'both') {
            // If the Plain template is empty try to convert the HTML template to a Plain text
            if (!$plainBody) {
                $plainBody = strip_tags(str_replace(['<br>', '<br />', '<br/>'], "\n", $htmlBody));
            }

            $this->mailer->setBody($plainBody);

            // Set alt body, use $mailer->Body directly because it was filtered by $mailer->setBody()
            if ($mailStyle === 'both') {
                $this->mailer->AltBody = $this->mailer->Body;
            }
        }

        if ($mailStyle === 'html' || $mailStyle === 'both') {
            $this->mailer->isHtml(true);

            // If HTML body is empty try to convert the Plain template to html
            if (!$htmlBody) {
                $htmlBody = nl2br($this->replaceTags(Text::_($mail->body), $this->data, true), false);
            }

            $htmlBody = MailHelper::convertRelativeToAbsoluteUrls($htmlBody);

            $this->mailer->setBody($htmlBody);
        }

        if ($config->get('copy_mails') && $params->get('copyto')) {
            $this->mailer->addBcc($params->get('copyto'));
        }

        foreach ($this->recipients as $recipient) {
            switch ($recipient->type) {
                case 'cc':
                    $this->mailer->addCc($recipient->mail, $recipient->name);
                    break;
                case 'bcc':
                    $this->mailer->addBcc($recipient->mail, $recipient->name);
                    break;
                case 'to':
                default:
                    $this->mailer->addAddress($recipient->mail, $recipient->name);
            }
        }

        if ($this->replyto) {
            $this->mailer->addReplyTo($this->replyto->mail, $this->replyto->name);
        } elseif ($replyTo) {
            $this->mailer->addReplyTo($replyTo, $replyToName);
        }

        if (trim($config->get('attachment_folder', ''))) {
            $folderPath = rtrim(Path::check(JPATH_ROOT . '/' . $config->get('attachment_folder')), \DIRECTORY_SEPARATOR);

            if ($folderPath && $folderPath !== Path::clean(JPATH_ROOT) && is_dir($folderPath)) {
                foreach ((array) json_decode($mail->attachments) as $attachment) {
                    $filePath = Path::check($folderPath . '/' . $attachment->file);

                    if (is_file($filePath)) {
                        $this->mailer->addAttachment($filePath, $this->getAttachmentName($filePath, $attachment->name));
                    }
                }
            }
        }

        foreach ($this->attachments as $attachment) {
            if (is_file($attachment->file)) {
                $this->mailer->addAttachment($attachment->file, $this->getAttachmentName($attachment->file, $attachment->name));
            } else {
                $this->mailer->addStringAttachment($attachment->file, $attachment->name);
            }
        }

        return $this->mailer->Send();
    }

    /**
     * Replace tags with their values recursively
     *
     * @param   string  $text    The template to process
     * @param   array   $tags    An associative array to replace in the template
     * @param   bool    $isHtml  Is the text an HTML text and requires escaping
     *
     * @return  string  Rendered mail template
     *
     * @since   4.0.0
     */
    protected function replaceTags($text, $tags, $isHtml = false)
    {
        foreach ($tags as $key => $value) {
            // If the value is NULL, replace with an empty string. NULL itself throws notices
            if (\is_null($value)) {
                $value = '';
            }

            if (\is_array($value)) {
                $matches = [];
                $pregKey = preg_quote(strtoupper($key), '/');

                if (preg_match_all('/{' . $pregKey . '}(.*?){\/' . $pregKey . '}/s', $text, $matches)) {
                    foreach ($matches[0] as $i => $match) {
                        $replacement = '';

                        foreach ($value as $name => $subvalue) {
                            if (\is_array($subvalue) && $name == $matches[1][$i]) {
                                $subvalue = implode("\n", $subvalue);

                                // Escape if necessary
                                if ($isHtml && \in_array(strtoupper($key), $this->unsafe_tags, true)) {
                                    $subvalue = htmlspecialchars($subvalue, ENT_QUOTES, 'UTF-8');
                                }

                                $replacement .= implode("\n", $subvalue);
                            } elseif (\is_array($subvalue)) {
                                $replacement .= $this->replaceTags($matches[1][$i], $subvalue, $isHtml);
                            } elseif (\is_string($subvalue) && $name == $matches[1][$i]) {
                                // Escape if necessary
                                if ($isHtml && \in_array(strtoupper($key), $this->unsafe_tags, true)) {
                                    $subvalue = htmlspecialchars($subvalue, ENT_QUOTES, 'UTF-8');
                                }

                                $replacement .= $subvalue;
                            }
                        }

                        $text = str_replace($match, $replacement, $text);
                    }
                }
            } else {
                // Escape if necessary
                if ($isHtml && \in_array(strtoupper($key), $this->unsafe_tags, true)) {
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }

                $text = str_replace('{' . strtoupper($key) . '}', $value, $text);
            }
        }

        return $text;
    }

    /**
     * Get a specific mail template
     *
     * @param   string  $key       Template identifier
     * @param   string  $language  Language code of the template
     *
     * @return  object|null  An object with the data of the mail, or null if the template not found in the db.
     *
     * @since   4.0.0
     */
    public static function getTemplate($key, $language)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__mail_templates'))
            ->where($db->quoteName('template_id') . ' = :key')
            ->whereIn($db->quoteName('language'), ['', $language], ParameterType::STRING)
            ->order($db->quoteName('language') . ' DESC')
            ->bind(':key', $key);
        $db->setQuery($query);
        $mail = $db->loadObject();

        if ($mail) {
            $mail->params = new Registry($mail->params);
        }

        return $mail;
    }

    /**
     * Insert a new mail template into the system
     *
     * @param   string  $key       Mail template key
     * @param   string  $subject   A default subject (normally a translatable string)
     * @param   string  $body      A default body (normally a translatable string)
     * @param   array   $tags      Associative array of tags to replace
     * @param   string  $htmlbody  A default htmlbody (normally a translatable string)
     *
     * @return  boolean  True on success, false on failure
     *
     * @since   4.0.0
     */
    public static function createTemplate($key, $subject, $body, $tags, $htmlbody = '')
    {
        $db = Factory::getDbo();

        $template              = new \stdClass();
        $template->template_id = $key;
        $template->language    = '';
        $template->subject     = $subject;
        $template->body        = $body;
        $template->htmlbody    = $htmlbody;
        $template->extension   = explode('.', $key, 2)[0] ?? '';
        $template->attachments = '';
        $params                = new \stdClass();
        $params->tags          = (array) $tags;
        $template->params      = json_encode($params);

        return $db->insertObject('#__mail_templates', $template);
    }

    /**
     * Update an existing mail template
     *
     * @param   string  $key       Mail template key
     * @param   string  $subject   A default subject (normally a translatable string)
     * @param   string  $body      A default body (normally a translatable string)
     * @param   array   $tags      Associative array of tags to replace
     * @param   string  $htmlbody  A default htmlbody (normally a translatable string)
     *
     * @return  boolean  True on success, false on failure
     *
     * @since   4.0.0
     */
    public static function updateTemplate($key, $subject, $body, $tags, $htmlbody = '')
    {
        $db = Factory::getDbo();

        $template              = new \stdClass();
        $template->template_id = $key;
        $template->language    = '';
        $template->subject     = $subject;
        $template->body        = $body;
        $template->htmlbody    = $htmlbody;
        $params                = new \stdClass();
        $params->tags          = (array) $tags;
        $template->params      = json_encode($params);

        return $db->updateObject('#__mail_templates', $template, ['template_id', 'language']);
    }

    /**
     * Method to delete a mail template
     *
     * @param   string  $key  The key of the mail template
     *
     * @return  boolean  True on success, false on failure
     *
     * @since   4.0.0
     */
    public static function deleteTemplate($key)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__mail_templates'))
            ->where($db->quoteName('template_id') . ' = :key')
            ->bind(':key', $key);
        $db->setQuery($query);

        return $db->execute();
    }

    /**
     * Check and if necessary fix the file name of an attachment so that the attached file
     * has the same extension as the source file, and not a different file extension
     *
     * @param   string  $file  Path to the file to be attached
     * @param   string  $name  The file name to be used for the attachment
     *
     * @return  string  The corrected file name for the attachment
     *
     * @since   4.0.0
     */
    protected function getAttachmentName(string $file, string $name): string
    {
        // If no name is given, do not process it further
        if (!trim($name)) {
            return '';
        }

        // Replace any placeholders.
        $name = $this->replaceTags($name, $this->data);

        // Get the file extension.
        $ext = File::getExt($file);

        // Strip off extension from $name and append extension of $file, if any
        return File::stripExt($name) . ($ext ? '.' . $ext : '');
    }
}

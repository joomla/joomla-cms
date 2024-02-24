<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Multifactorauth.email
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Multifactorauth\Email\Extension;

use Exception;
use Joomla\CMS\Encrypt\Totp;
use Joomla\CMS\Event\MultiFactor\BeforeDisplayMethods;
use Joomla\CMS\Event\MultiFactor\Captive;
use Joomla\CMS\Event\MultiFactor\GetMethod;
use Joomla\CMS\Event\MultiFactor\GetSetup;
use Joomla\CMS\Event\MultiFactor\SaveSetup;
use Joomla\CMS\Event\MultiFactor\Validate;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Users\Administrator\DataShape\CaptiveRenderOptions;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;
use Joomla\Component\Users\Administrator\DataShape\SetupRenderOptions;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Component\Users\Administrator\Table\MfaTable;
use Joomla\Event\SubscriberInterface;
use PHPMailer\PHPMailer\Exception as phpMailerException;
use RuntimeException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Multi-factor Authentication using a Validation Code sent by Email.
 *
 * Requires entering a 6-digit code sent to the user through email. These codes change automatically
 * on a frequency set in the plugin options (30 seconds to 5 minutes, default 2 minutes).
 *
 * @since 4.2.0
 */
class Email extends CMSPlugin implements SubscriberInterface
{
    use UserFactoryAwareTrait;

    /**
     * Generated OTP length. Constant: 6 numeric digits.
     *
     * @since 4.2.0
     */
    private const CODE_LENGTH = 6;

    /**
     * Length of the secret key used for generating the OTPs. Constant: 20 characters.
     *
     * @since 4.2.0
     */
    private const SECRET_KEY_LENGTH = 20;


    /**
     * Should I try to detect and register legacy event listeners, i.e. methods which accept unwrapped arguments? While
     * this maintains a great degree of backwards compatibility to Joomla! 3.x-style plugins it is much slower. You are
     * advised to implement your plugins using proper Listeners, methods accepting an AbstractEvent as their sole
     * parameter, for best performance. Also bear in mind that Joomla! 5.x onwards will only allow proper listeners,
     * removing support for legacy Listeners.
     *
     * @var    boolean
     * @since  4.2.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Implement your plugin methods accepting an AbstractEvent object
     *              Example:
     *              onEventTriggerName(AbstractEvent $event) {
     *                  $context = $event->getArgument(...);
     *              }
     */
    protected $allowLegacyListeners = false;

    /**
     * Autoload this plugin's language files
     *
     * @var    boolean
     * @since 4.2.0
     */
    protected $autoloadLanguage = true;

    /**
     * The MFA Method name handled by this plugin
     *
     * @var   string
     * @since 4.2.0
     */
    private $mfaMethodName = 'email';

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since 4.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onUserMultifactorGetMethod'            => 'onUserMultifactorGetMethod',
            'onUserMultifactorCaptive'              => 'onUserMultifactorCaptive',
            'onUserMultifactorGetSetup'             => 'onUserMultifactorGetSetup',
            'onUserMultifactorSaveSetup'            => 'onUserMultifactorSaveSetup',
            'onUserMultifactorValidate'             => 'onUserMultifactorValidate',
            'onUserMultifactorBeforeDisplayMethods' => 'onUserMultifactorBeforeDisplayMethods',
        ];
    }

    /**
     * Gets the identity of this MFA Method
     *
     * @param   GetMethod  $event  The event we are handling
     *
     * @return  void
     * @since   4.2.0
     */
    public function onUserMultifactorGetMethod(GetMethod $event): void
    {
        $event->addResult(
            new MethodDescriptor(
                [
                    'name'      => $this->mfaMethodName,
                    'display'   => Text::_('PLG_MULTIFACTORAUTH_EMAIL_LBL_DISPLAYEDAS'),
                    'shortinfo' => Text::_('PLG_MULTIFACTORAUTH_EMAIL_LBL_SHORTINFO'),
                    'image'     => 'media/plg_multifactorauth_email/images/email.svg',
                ]
            )
        );
    }

    /**
     * Returns the information which allows Joomla to render the Captive MFA page. This is the page
     * which appears right after you log in and asks you to validate your login with MFA.
     *
     * @param   Captive  $event  The event we are handling
     *
     * @return  void
     * @since   4.2.0
     */
    public function onUserMultifactorCaptive(Captive $event): void
    {
        /**
         * @var   MfaTable $record The record currently selected by the user.
         */
        $record = $event['record'];

        // Make sure we are actually meant to handle this Method
        if ($record->method != $this->mfaMethodName) {
            return;
        }

        // Load the options from the record (if any)
        $options = $this->decodeRecordOptions($record);
        $key     = $options['key'] ?? '';

        // Send an email message with a new code and ask the user to enter it.
        $user = $this->getUserFactory()->loadUserById($record->user_id);

        try {
            $this->sendCode($key, $user);
        } catch (\Exception $e) {
            return;
        }

        $event->addResult(
            new CaptiveRenderOptions(
                [
                    // Custom HTML to display above the MFA form
                    'pre_message' => Text::_('PLG_MULTIFACTORAUTH_EMAIL_LBL_PRE_MESSAGE'),
                    // How to render the MFA code field. "input" (HTML input element) or "custom" (custom HTML)
                    'field_type' => 'input',
                    // The type attribute for the HTML input box. Typically "text" or "password". Use any HTML5 input type.
                    'input_type' => 'text',
                    // The attributes for the HTML input box.
                    'input_attributes' => [
                        'pattern' => '[0-9]{6}', 'maxlength' => '6', 'inputmode' => 'numeric', 'required' => 'true', 'autocomplete' => 'one-time-code', 'aria-autocomplete' => 'none',
                    ],
                    // Placeholder text for the HTML input box. Leave empty if you don't need it.
                    'placeholder' => Text::_('PLG_MULTIFACTORAUTH_EMAIL_LBL_SETUP_PLACEHOLDER'),
                    // Label to show above the HTML input box. Leave empty if you don't need it.
                    'label' => Text::_('PLG_MULTIFACTORAUTH_EMAIL_LBL_LABEL'),
                    // Custom HTML. Only used when field_type = custom.
                    'html' => '',
                    // Custom HTML to display below the MFA form
                    'post_message' => '',
                    // Should I hide the default Submit button?
                    'hide_submit' => false,
                    // Is this MFA method validating against all configured authenticators of the same type?
                    'allowEntryBatching' => false,
                ]
            )
        );
    }

    /**
     * Returns the information which allows Joomla to render the MFA setup page. This is the page
     * which allows the user to add or modify a MFA Method for their user account. If the record
     * does not correspond to your plugin return an empty array.
     *
     * @param   GetSetup  $event  The event we are handling
     *
     * @return  void
     * @throws  \Exception
     * @since   4.2.0
     */
    public function onUserMultifactorGetSetup(GetSetup $event): void
    {
        /** @var MfaTable $record The record currently selected by the user. */
        $record = $event['record'];

        // Make sure we are actually meant to handle this Method
        if ($record->method != $this->mfaMethodName) {
            return;
        }

        // Load the options from the record (if any)
        $options           = $this->decodeRecordOptions($record);
        $key               = $options['key'] ?? '';
        $isKeyAlreadySetup = !empty($key);

        // If there's a key in the session use that instead.
        $session = $this->getApplication()->getSession();
        $session->get('plg_multifactorauth_email.emailcode.key', $key);

        // Initialize objects
        $timeStep = min(max((int) $this->params->get('timestep', 120), 30), 900);
        $totp     = new Totp($timeStep, self::CODE_LENGTH, self::SECRET_KEY_LENGTH);

        // If there's still no key in the options, generate one and save it in the session
        if (!$isKeyAlreadySetup) {
            $key = $totp->generateSecret();

            $session->set('plg_multifactorauth_email.emailcode.key', $key);
            $session->set('plg_multifactorauth_email.emailcode.user_id', $record->user_id);

            $user = $this->getUserFactory()->loadUserById($record->user_id);

            $this->sendCode($key, $user);

            $event->addResult(
                new SetupRenderOptions(
                    [
                        'default_title' => Text::_('PLG_MULTIFACTORAUTH_EMAIL_LBL_DISPLAYEDAS'),
                        'hidden_data'   => [
                            'key' => $key,
                        ],
                        'field_type'       => 'input',
                        'input_type'       => 'text',
                        'input_attributes' => [
                            'pattern' => '[0-9]{6}', 'maxlength' => '6', 'inputmode' => 'numeric', 'required' => 'true', 'autocomplete' => 'one-time-code', 'aria-autocomplete' => 'none',
                        ],
                        'input_value' => '',
                        'placeholder' => Text::_('PLG_MULTIFACTORAUTH_EMAIL_LBL_SETUP_PLACEHOLDER'),
                        'pre_message' => Text::_('PLG_MULTIFACTORAUTH_EMAIL_LBL_PRE_MESSAGE'),
                        'label'       => Text::_('PLG_MULTIFACTORAUTH_EMAIL_LBL_LABEL'),
                    ]
                )
            );
        } else {
            $event->addResult(
                new SetupRenderOptions(
                    [
                        'default_title' => Text::_('PLG_MULTIFACTORAUTH_EMAIL_LBL_DISPLAYEDAS'),
                        'input_type'    => 'hidden',
                        'html'          => '',
                    ]
                )
            );
        }
    }

    /**
     * Parse the input from the MFA setup page and return the configuration information to be saved to the database. If
     * the information is invalid throw a RuntimeException to signal the need to display the editor page again. The
     * message of the exception will be displayed to the user. If the record does not correspond to your plugin return
     * an empty array.
     *
     * @param   SaveSetup  $event  The event we are handling
     *
     * @return  void The configuration data to save to the database
     * @since   4.2.0
     */
    public function onUserMultifactorSaveSetup(SaveSetup $event): void
    {
        /**
         * @var MfaTable $record The record currently selected by the user.
         * @var Input    $input  The user input you are going to take into account.
         */
        $record = $event['record'];
        $input  = $event['input'];

        // Make sure we are actually meant to handle this Method
        if ($record->method != $this->mfaMethodName) {
            return;
        }

        // Load the options from the record (if any)
        $options           = $this->decodeRecordOptions($record);
        $key               = $options['key'] ?? '';
        $isKeyAlreadySetup = !empty($key);
        $session           = $this->getApplication()->getSession();

        // If there is no key in the options fetch one from the session
        if (empty($key)) {
            $key = $session->get('plg_multifactorauth_email.emailcode.key', null);
        }

        // If there is still no key in the options throw an error
        if (empty($key)) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        /**
         * If the code is empty but the key already existed in $options someone is simply changing the title / default
         * Method status. We can allow this and stop checking anything else now.
         */
        $code = $input->getCmd('code');

        if (empty($code) && $isKeyAlreadySetup) {
            $event->addResult($options);

            return;
        }

        // In any other case validate the submitted code
        $timeStep = min(max((int) $this->params->get('timestep', 120), 30), 900);
        $totp     = new Totp($timeStep, self::CODE_LENGTH, self::SECRET_KEY_LENGTH);
        $isValid  = $totp->checkCode((string) $key, (string) $code);

        if (!$isValid) {
            throw new \RuntimeException(Text::_('PLG_MULTIFACTORAUTH_EMAIL_ERR_INVALID_CODE'), 500);
        }

        // The code is valid. Unset the key from the session.
        $session->set('plg_multifactorauth_email.emailcode.key', null);

        // Return the configuration to be serialized
        $event->addResult(['key' => $key]);
    }

    /**
     * Validates the Multi-factor Authentication code submitted by the user in the Multi-Factor
     * Authentication page. If the record does not correspond to your plugin return FALSE.
     *
     * @param   Validate  $event  The event we are handling
     *
     * @return  void
     * @since   4.2.0
     */
    public function onUserMultifactorValidate(Validate $event): void
    {
        /**
         * @var   MfaTable    $record The MFA Method's record you're validating against
         * @var   User        $user   The user record
         * @var   string|null $code   The submitted code
         */
        $record = $event['record'];
        $user   = $event['user'];
        $code   = $event['code'];

        // Make sure we are actually meant to handle this Method
        if ($record->method != $this->mfaMethodName) {
            $event->addResult(false);

            return;
        }

        // Double check the MFA Method is for the correct user
        if ($user->id != $record->user_id) {
            $event->addResult(false);

            return;
        }

        // Load the options from the record (if any)
        $options = $this->decodeRecordOptions($record);
        $key     = $options['key'] ?? '';

        // If there is no key in the options throw an error
        if (empty($key)) {
            $event->addResult(false);

            return;
        }

        // Check the MFA code for validity
        $timeStep = min(max((int) $this->params->get('timestep', 120), 30), 900);
        $totp     = new Totp($timeStep, self::CODE_LENGTH, self::SECRET_KEY_LENGTH);

        $event->addResult($totp->checkCode($key, (string) $code));
    }

    /**
     * Executes before showing the MFA Methods for the user. Used for the Force Enable feature.
     *
     * @param   BeforeDisplayMethods  $event  The event we are handling
     *
     * @return  void
     * @throws  \Exception
     * @since   4.2.0
     */
    public function onUserMultifactorBeforeDisplayMethods(BeforeDisplayMethods $event): void
    {
        /** @var ?User $user */
        $user = $event['user'];

        // Is the forced enable feature activated?
        if ($this->params->get('force_enable', 0) != 1) {
            return;
        }

        // Get MFA Methods for this user
        $userMfaRecords = MfaHelper::getUserMfaRecords($user->id);

        // If there are no Methods go back
        if (\count($userMfaRecords) < 1) {
            return;
        }

        // If the only Method is backup codes go back
        if (\count($userMfaRecords) == 1) {
            /** @var MfaTable $record */
            $record = reset($userMfaRecords);

            if ($record->method == 'backupcodes') {
                return;
            }
        }

        // If I already have the email Method go back
        $emailRecords = array_filter(
            $userMfaRecords,
            function (MfaTable $record) {
                return $record->method == 'email';
            }
        );

        if (\count($emailRecords)) {
            return;
        }

        // Add the email Method
        try {
            /** @var MVCFactoryInterface $factory */
            $factory = $this->getApplication()->bootComponent('com_users')->getMVCFactory();
            /** @var MfaTable $record */
            $record = $factory->createTable('Mfa', 'Administrator');
            $record->reset();

            $timeStep = min(max((int) $this->params->get('timestep', 120), 30), 900);
            $totp     = new Totp($timeStep, self::CODE_LENGTH, self::SECRET_KEY_LENGTH);

            $record->save(
                [
                    'method'  => 'email',
                    'title'   => Text::_('PLG_MULTIFACTORAUTH_EMAIL_LBL_DISPLAYEDAS'),
                    'options' => [
                        'key' => ($totp)->generateSecret(),
                    ],
                    'default' => 0,
                    'user_id' => $user->id,
                ]
            );
        } catch (\Exception $event) {
            // Fail gracefully
        }
    }

    /**
     * Decodes the options from a record into an options object.
     *
     * @param   MfaTable  $record  The record to decode
     *
     * @return  array
     * @since   4.2.0
     */
    private function decodeRecordOptions(MfaTable $record): array
    {
        $options = [
            'key' => '',
        ];

        if (!empty($record->options)) {
            $recordOptions = $record->options;

            $options = array_merge($options, $recordOptions);
        }

        return $options;
    }

    /**
     * Creates a new TOTP code based on secret key $key and sends it to the user via email.
     *
     * @param   string     $key   The TOTP secret key
     * @param   User|null  $user  The Joomla! user to use
     *
     * @return  void
     * @throws  \Exception
     * @since   4.2.0
     */
    private function sendCode(string $key, ?User $user = null)
    {
        static $alreadySent = false;

        // Make sure we have a user
        if (!is_object($user) || !($user instanceof User)) {
            $user = $this->getApplication()->getIdentity() ?: $this->getUserFactory()->loadUserById(0);
        }

        if ($alreadySent) {
            return;
        }

        $alreadySent = true;

        // Get the API objects
        $timeStep = min(max((int) $this->params->get('timestep', 120), 30), 900);
        $totp     = new Totp($timeStep, self::CODE_LENGTH, self::SECRET_KEY_LENGTH);

        // Create the list of variable replacements
        $code = $totp->getCode($key);

        $replacements = [
            'code'     => $code,
            'sitename' => $this->getApplication()->get('sitename'),
            'siteurl'  => Uri::base(),
            'username' => $user->username,
            'email'    => $user->email,
            'fullname' => $user->name,
        ];

        try {
            $jLanguage = $this->getApplication()->getLanguage();
            $mailer    = new MailTemplate('plg_multifactorauth_email.mail', $jLanguage->getTag());
            $mailer->addRecipient($user->email, $user->name);
            $mailer->addTemplateData($replacements);

            $didSend = $mailer->send();
        } catch (MailDisabledException | phpMailerException $exception) {
            try {
                Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');
            } catch (\RuntimeException $exception) {
                $this->getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');
            }
        }

        try {
            // The user somehow managed to not install the mail template. I'll send the email the traditional way.
            if (isset($didSend) && !$didSend) {
                $subject = Text::_('PLG_MULTIFACTORAUTH_EMAIL_EMAIL_SUBJECT');
                $body    = Text::_('PLG_MULTIFACTORAUTH_EMAIL_EMAIL_BODY');

                foreach ($replacements as $key => $value) {
                    $subject = str_replace('{' . strtoupper($key) . '}', $value, $subject);
                    $body    = str_replace('{' . strtoupper($key) . '}', $value, $body);
                }

                $mailer = Factory::getMailer();
                $mailer->setSubject($subject);
                $mailer->setBody($body);
                $mailer->addRecipient($user->email, $user->name);

                $mailer->Send();
            }
        } catch (MailDisabledException | phpMailerException $exception) {
            try {
                Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');
            } catch (\RuntimeException $exception) {
                $this->getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');
            }
        }
    }
}

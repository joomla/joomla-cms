<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Multifactorauth.yubikey
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Multifactorauth\Yubikey\Extension;

use Joomla\CMS\Event\MultiFactor\Captive;
use Joomla\CMS\Event\MultiFactor\GetMethod;
use Joomla\CMS\Event\MultiFactor\GetSetup;
use Joomla\CMS\Event\MultiFactor\SaveSetup;
use Joomla\CMS\Event\MultiFactor\Validate;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Administrator\DataShape\CaptiveRenderOptions;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;
use Joomla\Component\Users\Administrator\DataShape\SetupRenderOptions;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Component\Users\Administrator\Table\MfaTable;
use Joomla\Event\SubscriberInterface;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Multi-factor Authentication using Yubikey Plugin
 *
 * @since 4.2.0
 */
class Yubikey extends CMSPlugin implements SubscriberInterface
{
    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.2
     */
    protected $autoloadLanguage = true;

    /**
     * The MFA Method name handled by this plugin
     *
     * @var   string
     * @since 4.2.0
     */
    private $mfaMethodName = 'yubikey';

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
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onUserMultifactorGetMethod' => 'onUserMultifactorGetMethod',
            'onUserMultifactorCaptive'   => 'onUserMultifactorCaptive',
            'onUserMultifactorGetSetup'  => 'onUserMultifactorGetSetup',
            'onUserMultifactorSaveSetup' => 'onUserMultifactorSaveSetup',
            'onUserMultifactorValidate'  => 'onUserMultifactorValidate',
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
                    'name'               => $this->mfaMethodName,
                    'display'            => Text::_('PLG_MULTIFACTORAUTH_YUBIKEY_METHOD_TITLE'),
                    'shortinfo'          => Text::_('PLG_MULTIFACTORAUTH_YUBIKEY_SHORTINFO'),
                    'image'              => 'media/plg_multifactorauth_yubikey/images/yubikey.svg',
                    'allowEntryBatching' => true,
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

        $event->addResult(
            new CaptiveRenderOptions(
                [
                    // Custom HTML to display above the MFA form
                    'pre_message' => Text::_('PLG_MULTIFACTORAUTH_YUBIKEY_CAPTIVE_PROMPT'),
                    // How to render the MFA code field. "input" (HTML input element) or "custom" (custom HTML)
                    'field_type' => 'input',
                    // The type attribute for the HTML input box. Typically "text" or "password". Use any HTML5 input type.
                    'input_type' => 'text',
                    // Placeholder text for the HTML input box. Leave empty if you don't need it.
                    'placeholder' => '',
                    // Label to show above the HTML input box. Leave empty if you don't need it.
                    'label' => Text::_('PLG_MULTIFACTORAUTH_YUBIKEY_CODE_LABEL'),
                    // Custom HTML. Only used when field_type = custom.
                    'html' => '',
                    // Custom HTML to display below the MFA form
                    'post_message' => '',
                    // Allow authentication against all entries of this MFA Method.
                    'allowEntryBatching' => 1,
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
     * @since   4.2.0
     */
    public function onUserMultifactorGetSetup(GetSetup $event): void
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
        $keyID   = $options['id'] ?? '';

        if (empty($keyID)) {
            $event->addResult(
                new SetupRenderOptions(
                    [
                        'default_title' => Text::_('PLG_MULTIFACTORAUTH_YUBIKEY_METHOD_TITLE'),
                        'pre_message'   => Text::_('PLG_MULTIFACTORAUTH_YUBIKEY_LBL_SETUP_INSTRUCTIONS'),
                        'field_type'    => 'input',
                        'input_type'    => 'text',
                        'input_value'   => $keyID,
                        'placeholder'   => Text::_('PLG_MULTIFACTORAUTH_YUBIKEY_LBL_SETUP_PLACEHOLDER'),
                        'label'         => Text::_('PLG_MULTIFACTORAUTH_YUBIKEY_LBL_SETUP_LABEL'),
                    ]
                )
            );
        } else {
            $event->addResult(
                new SetupRenderOptions(
                    [
                        'default_title' => Text::_('PLG_MULTIFACTORAUTH_YUBIKEY_METHOD_TITLE'),
                        'pre_message'   => Text::sprintf('PLG_MULTIFACTORAUTH_YUBIKEY_LBL_AFTERSETUP_INSTRUCTIONS', $keyID),
                        'input_type'    => 'hidden',
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
     * @throws  \Exception
     * @since   4.2.0
     */
    public function onUserMultifactorSaveSetup(SaveSetup $event): void
    {
        /**
         * @var   MfaTable $record The record currently selected by the user.
         * @var   Input    $input  The user input you are going to take into account.
         */
        $record = $event['record'];
        $input  = $event['input'];

        // Make sure we are actually meant to handle this Method
        if ($record->method != $this->mfaMethodName) {
            return;
        }

        // Load the options from the record (if any)
        $options           = $this->decodeRecordOptions($record);
        $keyID             = $options['id'] ?? '';
        $isKeyAlreadySetup = !empty($keyID);

        /**
         * If the submitted code is 12 characters and identical to our existing key there is no change, perform no
         * further checks.
         */
        $code = $input->getString('code');

        if ($isKeyAlreadySetup || ((\strlen($code) == 12) && ($code == $keyID))) {
            $event->addResult($options);

            return;
        }

        // If an empty code or something other than 44 characters was submitted I'm not having any of this!
        if (empty($code) || (\strlen($code) != 44)) {
            throw new \RuntimeException(Text::_('PLG_MULTIFACTORAUTH_YUBIKEY_ERR_VALIDATIONFAILED'), 500);
        }

        // Validate the code
        $isValid = $this->validateYubikeyOtp($code);

        if (!$isValid) {
            throw new \RuntimeException(Text::_('PLG_MULTIFACTORAUTH_YUBIKEY_ERR_VALIDATIONFAILED'), 500);
        }

        // The code is valid. Keep the Yubikey ID (first twelve characters)
        $keyID = substr($code, 0, 12);

        // Return the configuration to be serialized
        $event->addResult(['id' => $keyID]);
    }

    /**
     * Validates the Multi-factor Authentication code submitted by the user in the Multi-Factor
     * Authentication page. If the record does not correspond to your plugin return FALSE.
     *
     * @param   Validate  $event  The event we are handling
     *
     * @return  void
     * @throws  \Exception
     * @since   4.2.0
     */
    public function onUserMultifactorValidate(Validate $event): void
    {
        /**
         * @var   MfaTable $record The MFA Method's record you're validating against
         * @var   User     $user   The user record
         * @var   string   $code   The submitted code
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

        try {
            $records = MfaHelper::getUserMfaRecords($record->user_id);
            $records = array_filter(
                $records,
                function ($rec) use ($record) {
                    return $rec->method === $record->method;
                }
            );
        } catch (\Exception $e) {
            $records = [];
        }

        // Loop all records, stop if at least one matches
        $result = array_reduce(
            $records,
            function (bool $carry, $aRecord) use ($code) {
                return $carry || $this->validateAgainstRecord($aRecord, $code);
            },
            false
        );

        $event->addResult($result);
    }

    /**
     * Validates a Yubikey OTP against the Yubikey servers
     *
     * @param   string  $otp  The OTP generated by your Yubikey
     *
     * @return  boolean  True if it's a valid OTP
     * @throws  \Exception
     * @since   4.2.0
     */
    private function validateYubikeyOtp(string $otp): bool
    {
        // Let the user define a client ID and a secret key in the plugin's configuration
        $clientID    = $this->params->get('client_id', 1);
        $secretKey   = $this->params->get('secret', '');
        $serverQueue = trim($this->params->get('servers', ''));

        if (!empty($serverQueue)) {
            $serverQueue = explode("\r", $serverQueue);
        }

        if (empty($serverQueue)) {
            $serverQueue = [
                'https://api.yubico.com/wsapi/2.0/verify',
                'https://api2.yubico.com/wsapi/2.0/verify',
                'https://api3.yubico.com/wsapi/2.0/verify',
                'https://api4.yubico.com/wsapi/2.0/verify',
                'https://api5.yubico.com/wsapi/2.0/verify',
            ];
        }

        shuffle($serverQueue);

        $gotResponse = false;

        $http     = HttpFactory::getHttp();
        $token    = $this->getApplication()->getFormToken();
        $nonce    = md5($token . uniqid(random_int(0, mt_getrandmax())));
        $response = null;

        while (!$gotResponse && !empty($serverQueue)) {
            $server = array_shift($serverQueue);
            $uri    = new Uri($server);

            // The client ID for signing the response
            $uri->setVar('id', $clientID);

            // The OTP we read from the user
            $uri->setVar('otp', $otp);

            // This prevents a REPLAYED_OTP status if the token doesn't change after a user submits an invalid OTP
            $uri->setVar('nonce', $nonce);

            // Minimum service level required: 50% (at least 50% of the YubiCloud servers must reply positively for the
            // OTP to validate)
            $uri->setVar('sl', 50);

            // Timeout waiting for YubiCloud servers to reply: 5 seconds.
            $uri->setVar('timeout', 5);

            // Set up the optional HMAC-SHA1 signature for the request.
            $this->signRequest($uri, $secretKey);

            if ($uri->hasVar('h')) {
                $uri->setVar('h', urlencode($uri->getVar('h')));
            }

            try {
                $response = $http->get($uri->toString(), [], 6);

                if (!empty($response)) {
                    $gotResponse = true;
                } else {
                    continue;
                }
            } catch (\Exception $exc) {
                // No response, continue with the next server
                continue;
            }
        }

        if (empty($response)) {
            $gotResponse = false;
        }

        // No server replied; we can't validate this OTP
        if (!$gotResponse) {
            return false;
        }

        // Parse response
        $lines = explode("\n", $response->body);
        $data  = [];

        foreach ($lines as $line) {
            $line  = trim($line);
            $parts = explode('=', $line, 2);

            if (\count($parts) < 2) {
                continue;
            }

            $data[$parts[0]] = $parts[1];
        }

        // Validate the signature
        $h       = $data['h'] ?? null;
        $fakeUri = Uri::getInstance('http://www.example.com');
        $fakeUri->setQuery($data);
        $this->signRequest($fakeUri, $secretKey);
        $calculatedH = $fakeUri->getVar('h', null);

        if ($calculatedH != $h) {
            return false;
        }

        // Validate the response - We need an OK message reply
        if ($data['status'] !== 'OK') {
            return false;
        }

        // Validate the response - We need a confidence level over 50%
        if ($data['sl'] < 50) {
            return false;
        }

        // Validate the response - The OTP must match
        if ($data['otp'] != $otp) {
            return false;
        }

        // Validate the response - The token must match
        if ($data['nonce'] != $nonce) {
            return false;
        }

        return true;
    }

    /**
     * Sign the request to YubiCloud.
     *
     * @param   Uri     $uri     The request URI to sign
     * @param   string  $secret  The secret key to sign with
     *
     * @return  void
     * @since   4.2.0
     *
     * @link    https://developers.yubico.com/yubikey-val/Validation_Protocol_V2.0.html
     */
    private function signRequest(Uri $uri, string $secret): void
    {
        // Make sure we have an encoding secret
        $secret = trim($secret);

        if (empty($secret)) {
            return;
        }

        // I will need base64 encoding and decoding
        if (!\function_exists('base64_encode') || !\function_exists('base64_decode')) {
            return;
        }

        // I need HMAC-SHA-1 support. Therefore I check for HMAC and SHA1 support in the PHP 'hash' extension.
        if (!\function_exists('hash_hmac') || !\function_exists('hash_algos')) {
            return;
        }

        $algos = hash_algos();

        if (!\in_array('sha1', $algos)) {
            return;
        }

        // Get the parameters
        /** @var   array $vars I have to explicitly state the type because the Joomla docblock is wrong :( */
        $vars = $uri->getQuery(true);

        // 'h' is the hash and it doesn't participate in the calculation of itself.
        if (isset($vars['h'])) {
            unset($vars['h']);
        }

        // Alphabetically sort the set of key/value pairs by key order.
        ksort($vars);

        /**
         * Construct a single line with each ordered key/value pair concatenated using &, and each key and value
         * concatenated with =. Do not add any line breaks. Do not add whitespace.
         *
         * Now, if you thought I can't really write PHP code, a.k.a. why not use http_build_query, read on.
         *
         * The way YubiKey expects the query to be built is UTTERLY WRONG. They are doing string concatenation, not
         * URL query building! Therefore you cannot use http_build_query(). Instead, you need to use dumb string
         * concatenation. I kid you not. If you want to laugh (or cry) read their Auth_Yubico class. It's 1998 all over
         * again.
         */
        $stringToSign = '';

        foreach ($vars as $k => $v) {
            $stringToSign .= '&' . $k . '=' . $v;
        }

        $stringToSign = ltrim($stringToSign, '&');

        /**
         * Apply the HMAC-SHA-1 algorithm on the line as an octet string using the API key as key (remember to
         * base64decode the API key obtained from Yubico).
         */
        $decodedKey = base64_decode($secret);
        $hash       = hash_hmac('sha1', $stringToSign, $decodedKey, true);

        /**
         * Base 64 encode the resulting value according to RFC 4648, for example, t2ZMtKeValdA+H0jVpj3LIichn4=
         */
        $h = base64_encode($hash);

        /**
         * Append the value under key h to the message.
         */
        $uri->setVar('h', $h);
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
            'id' => '',
        ];

        if (!empty($record->options)) {
            $recordOptions = $record->options;

            $options = array_merge($options, $recordOptions);
        }

        return $options;
    }

    /**
     * @param   MfaTable  $record  The record to validate against
     * @param   string    $code    The code given to us by the user
     *
     * @return  boolean
     * @throws  \Exception
     * @since   4.2.0
     */
    private function validateAgainstRecord(MfaTable $record, string $code): bool
    {
        // Load the options from the record (if any)
        $options = $this->decodeRecordOptions($record);
        $keyID   = $options['id'] ?? '';

        // If there is no key in the options throw an error
        if (empty($keyID)) {
            return false;
        }

        // If the submitted code is empty throw an error
        if (empty($code)) {
            return false;
        }

        // If the submitted code length is wrong throw an error
        if (\strlen($code) != 44) {
            return false;
        }

        // If the submitted code's key ID does not match the stored throw an error
        if (substr($code, 0, 12) != $keyID) {
            return false;
        }

        // Check the OTP code for validity
        return $this->validateYubikeyOtp($code);
    }
}

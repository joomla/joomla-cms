<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Multifactorauth.fixed
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Multifactorauth\Fixed\Extension;

use Joomla\CMS\Event\MultiFactor\Captive;
use Joomla\CMS\Event\MultiFactor\GetMethod;
use Joomla\CMS\Event\MultiFactor\GetSetup;
use Joomla\CMS\Event\MultiFactor\SaveSetup;
use Joomla\CMS\Event\MultiFactor\Validate;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Administrator\DataShape\CaptiveRenderOptions;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;
use Joomla\Component\Users\Administrator\DataShape\SetupRenderOptions;
use Joomla\Component\Users\Administrator\Table\MfaTable;
use Joomla\Event\SubscriberInterface;
use Joomla\Input\Input;
use RuntimeException;

/**
 * TJoomla! Multi-factor Authentication using a fixed code.
 *
 * Requires a static string (password), different for each user. It effectively works as a second
 * password. The fixed code is stored hashed, like a regular password.
 *
 * This is NOT to be used on production sites. It serves as a demonstration plugin and as a template
 * for developers to create their own custom Multi-factor Authentication plugins.
 *
 * @since 4.2.0
 */
class Fixed extends CMSPlugin implements SubscriberInterface
{
    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  4.2.0
     */
    protected $autoloadLanguage = true;

    /**
     * The MFA Method name handled by this plugin
     *
     * @var   string
     * @since 4.2.0
     */
    private $mfaMethodName = 'fixed';

    /**
     * Should I try to detect and register legacy event listeners?
     *
     * @var   boolean
     * @since 4.2.0
     *
     * @deprecated
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
                    'name'      => $this->mfaMethodName,
                    'display'   => Text::_('PLG_MULTIFACTORAUTH_FIXED_LBL_DISPLAYEDAS'),
                    'shortinfo' => Text::_('PLG_MULTIFACTORAUTH_FIXED_LBL_SHORTINFO'),
                    'image'     => 'media/plg_multifactorauth_fixed/images/fixed.svg',
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
                    'pre_message'  => Text::_('PLG_MULTIFACTORAUTH_FIXED_LBL_PREMESSAGE'),
                    // How to render the MFA code field. "input" (HTML input element) or "custom" (custom HTML)
                    'field_type'   => 'input',
                    // The type attribute for the HTML input box. Typically "text" or "password". Use any HTML5 input type.
                    'input_type'   => 'password',
                    // Placeholder text for the HTML input box. Leave empty if you don't need it.
                    'placeholder'  => Text::_('PLG_MULTIFACTORAUTH_FIXED_LBL_PLACEHOLDER'),
                    // Label to show above the HTML input box. Leave empty if you don't need it.
                    'label'        => Text::_('PLG_MULTIFACTORAUTH_FIXED_LBL_LABEL'),
                    // Custom HTML. Only used when field_type = custom.
                    'html'         => '',
                    // Custom HTML to display below the MFA form
                    'post_message' => Text::_('PLG_MULTIFACTORAUTH_FIXED_LBL_POSTMESSAGE'),
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
        /** @var MfaTable $record The record currently selected by the user. */
        $record = $event['record'];

        // Make sure we are actually meant to handle this Method
        if ($record->method != $this->mfaMethodName) {
            return;
        }

        // Load the options from the record (if any)
        $options = $this->decodeRecordOptions($record);

        /**
         * Return the parameters used to render the GUI.
         *
         * Some MFA Methods need to display a different interface before and after the setup. For example, when setting
         * up Google Authenticator or a hardware OTP dongle you need the user to enter a MFA code to verify they are in
         * possession of a correctly configured device. After the setup is complete you don't want them to see that
         * field again. In the first state you could use the tabular_data to display the setup values, pre_message to
         * display the QR code and field_type=input to let the user enter the MFA code. In the second state do the same
         * BUT set field_type=custom, set html='' and show_submit=false to effectively hide the setup form from the
         * user.
         */
        $event->addResult(
            new SetupRenderOptions(
                [
                    'default_title' => Text::_('PLG_MULTIFACTORAUTH_FIXED_LBL_DEFAULTTITLE'),
                    'pre_message'   => Text::_('PLG_MULTIFACTORAUTH_FIXED_LBL_SETUP_PREMESSAGE'),
                    'field_type'    => 'input',
                    'input_type'    => 'password',
                    'input_value'   => $options->fixed_code,
                    'placeholder'   => Text::_('PLG_MULTIFACTORAUTH_FIXED_LBL_PLACEHOLDER'),
                    'label'         => Text::_('PLG_MULTIFACTORAUTH_FIXED_LBL_LABEL'),
                    'post_message'  => Text::_('PLG_MULTIFACTORAUTH_FIXED_LBL_SETUP_POSTMESSAGE'),
                ]
            )
        );
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
        $options = $this->decodeRecordOptions($record);

        // Merge with the submitted form data
        $code = $input->get('code', $options->fixed_code, 'raw');

        // Make sure the code is not empty
        if (empty($code)) {
            throw new RuntimeException(Text::_('PLG_MULTIFACTORAUTH_FIXED_ERR_EMPTYCODE'));
        }

        // Return the configuration to be serialized
        $event->addResult(['fixed_code' => $code]);
    }

    /**
     * Validates the Multi-factor Authentication code submitted by the user in the Multi-Factor
     * Authentication. If the record does not correspond to your plugin return FALSE.
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

        // Load the options from the record (if any)
        $options = $this->decodeRecordOptions($record);

        // Double check the MFA Method is for the correct user
        if ($user->id != $record->user_id) {
            $event->addResult(false);

            return;
        }

        // Check the MFA code for validity
        $event->addResult(hash_equals($options->fixed_code, $code ?? ''));
    }

    /**
     * Decodes the options from a record into an options object.
     *
     * @param   MfaTable  $record  The record to decode options for
     *
     * @return  object
     * @since 4.2.0
     */
    private function decodeRecordOptions(MfaTable $record): object
    {
        $options = [
            'fixed_code' => '',
        ];

        if (!empty($record->options)) {
            $recordOptions = $record->options;

            $options = array_merge($options, $recordOptions);
        }

        return (object) $options;
    }
}

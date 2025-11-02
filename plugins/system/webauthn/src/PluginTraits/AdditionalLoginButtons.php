<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Inserts Webauthn buttons into login modules
 *
 * @since   4.0.0
 */
trait AdditionalLoginButtons
{
    /**
     * Do I need to inject buttons? Automatically detected (i.e. disabled if I'm already logged
     * in).
     *
     * @var     boolean|null
     * @since   4.0.0
     */
    protected $allowButtonDisplay = null;

    /**
     * Have I already injected CSS and JavaScript? Prevents double inclusion of the same files.
     *
     * @var     boolean
     * @since   4.0.0
     */
    private $injectedCSSandJS = false;

    /**
     * Creates additional login buttons
     *
     * @param   Event  $event  The event we are handling
     *
     * @return  void
     *
     * @see     AuthenticationHelper::getLoginButtons()
     *
     * @since   4.0.0
     */
    public function onUserLoginButtons(Event $event): void
    {
        /** @var string $form The HTML ID of the form we are enclosed in */
        [$form] = array_values($event->getArguments());

        // If we determined we should not inject a button return early
        if (!$this->mustDisplayButton()) {
            return;
        }

        // Load plugin language files
        $this->loadLanguage();

        // Load necessary CSS and Javascript files
        $this->addLoginCSSAndJavascript();

        // Unique ID for this button (allows display of multiple modules on the page)
        $randomId = 'plg_system_webauthn-' .
            UserHelper::genRandomPassword(12) . '-' . UserHelper::genRandomPassword(8);

        // Get local path to image
        $image = HTMLHelper::_('image', 'plg_system_webauthn/fido-passkey-black.svg', '', '', true, true);

        // If you can't find the image then skip it
        $image = $image ? JPATH_ROOT . substr($image, \strlen(Uri::root(true))) : '';

        // Extract image if it exists
        $image = file_exists($image) ? file_get_contents($image) : '';

        $this->returnFromEvent($event, [
            [
                'label'              => 'PLG_SYSTEM_WEBAUTHN_LOGIN_LABEL',
                'tooltip'            => 'PLG_SYSTEM_WEBAUTHN_LOGIN_DESC',
                'id'                 => $randomId,
                'data-webauthn-form' => $form,
                'svg'                => $image,
                'class'              => 'plg_system_webauthn_login_button',
            ],
            ]);
    }

    /**
     * Should I allow this plugin to add a WebAuthn login button?
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    private function mustDisplayButton(): bool
    {
        // We must have a valid application
        if (!($this->getApplication() instanceof CMSApplication)) {
            return false;
        }

        // This plugin only applies to the frontend and administrator applications
        if (!$this->getApplication()->isClient('site') && !$this->getApplication()->isClient('administrator')) {
            return false;
        }

        // We must have a valid user
        if (empty($this->getApplication()->getIdentity())) {
            return false;
        }

        if (\is_null($this->allowButtonDisplay)) {
            $this->allowButtonDisplay = false;

            /**
             * Do not add a WebAuthn login button if we are already logged in
             */
            if (!$this->getApplication()->getIdentity()->guest) {
                return false;
            }

            /**
             * Only display a button on HTML output
             */
            try {
                $document = $this->getApplication()->getDocument();
            } catch (\Exception) {
                $document = null;
            }

            if (!($document instanceof HtmlDocument)) {
                return false;
            }

            /**
             * WebAuthn only works on HTTPS. This is a security-related limitation of the W3C Web Authentication
             * specification, not an issue with this plugin :)
             */
            if (!Uri::getInstance()->isSsl()) {
                return false;
            }

            // All checks passed; we should allow displaying a WebAuthn login button
            $this->allowButtonDisplay = true;
        }

        return $this->allowButtonDisplay;
    }

    /**
     * Injects the WebAuthn CSS and Javascript for frontend logins, but only once per page load.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function addLoginCSSAndJavascript(): void
    {
        if ($this->injectedCSSandJS) {
            return;
        }

        // Set the "don't load again" flag
        $this->injectedCSSandJS = true;

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = $this->getApplication()->getDocument()->getWebAssetManager();

        if (!$wa->assetExists('style', 'plg_system_webauthn.button')) {
            $wa->registerStyle('plg_system_webauthn.button', 'plg_system_webauthn/button.css');
        }

        if (!$wa->assetExists('script', 'plg_system_webauthn.login')) {
            $wa->registerScript('plg_system_webauthn.login', 'plg_system_webauthn/login.js', [], ['defer' => true], ['core', 'messages']);
        }

        $wa->useStyle('plg_system_webauthn.button')
            ->useScript('plg_system_webauthn.login');

        // Load language strings client-side
        Text::script('PLG_SYSTEM_WEBAUTHN_ERR_CANNOT_FIND_USERNAME');
        Text::script('PLG_SYSTEM_WEBAUTHN_ERR_EMPTY_USERNAME');
        Text::script('PLG_SYSTEM_WEBAUTHN_ERR_INVALID_USERNAME');

        // Store the current URL as the default return URL after login (or failure)
        $this->getApplication()->getSession()->set('plg_system_webauthn.returnUrl', Uri::current());
    }
}

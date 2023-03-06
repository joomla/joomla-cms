<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Captcha\Google\HttpBridgePostRequestMethod;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Utilities\IpHelper;
use ReCaptcha\ReCaptcha;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Recaptcha Plugin
 * Based on the official recaptcha library( https://packagist.org/packages/google/recaptcha )
 *
 * @since  2.5
 */
class PlgCaptchaRecaptcha extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Application object.
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  4.0.0
     */
    protected $app;

    /**
     * Reports the privacy related capabilities for this plugin to site administrators.
     *
     * @return  array
     *
     * @since   3.9.0
     */
    public function onPrivacyCollectAdminCapabilities()
    {
        $this->loadLanguage();

        return [
            Text::_('PLG_CAPTCHA_RECAPTCHA') => [
                Text::_('PLG_RECAPTCHA_PRIVACY_CAPABILITY_IP_ADDRESS'),
            ],
        ];
    }

    /**
     * Initialise the captcha
     *
     * @param   string  $id  The id of the field.
     *
     * @return  Boolean True on success, false otherwise
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    public function onInit($id = 'dynamic_recaptcha_1')
    {
        $pubkey = $this->params->get('public_key', '');

        if ($pubkey === '') {
            throw new \RuntimeException(Text::_('PLG_RECAPTCHA_ERROR_NO_PUBLIC_KEY'));
        }

        $apiSrc = 'https://www.google.com/recaptcha/api.js?onload=JoomlainitReCaptcha2&render=explicit&hl='
            . Factory::getLanguage()->getTag();

        // Load assets, the callback should be first
        $this->app->getDocument()->getWebAssetManager()
            ->registerAndUseScript('plg_captcha_recaptcha', 'plg_captcha_recaptcha/recaptcha.min.js', [], ['defer' => true])
            ->registerAndUseScript('plg_captcha_recaptcha.api', $apiSrc, [], ['defer' => true], ['plg_captcha_recaptcha']);

        return true;
    }

    /**
     * Gets the challenge HTML
     *
     * @param   string  $name   The name of the field. Not Used.
     * @param   string  $id     The id of the field.
     * @param   string  $class  The class of the field.
     *
     * @return  string  The HTML to be embedded in the form.
     *
     * @since  2.5
     */
    public function onDisplay($name = null, $id = 'dynamic_recaptcha_1', $class = '')
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ele = $dom->createElement('div');
        $ele->setAttribute('id', $id);

        $ele->setAttribute('class', ((trim($class) == '') ? 'g-recaptcha' : ($class . ' g-recaptcha')));
        $ele->setAttribute('data-sitekey', $this->params->get('public_key', ''));
        $ele->setAttribute('data-theme', $this->params->get('theme2', 'light'));
        $ele->setAttribute('data-size', $this->params->get('size', 'normal'));
        $ele->setAttribute('data-tabindex', $this->params->get('tabindex', '0'));
        $ele->setAttribute('data-callback', $this->params->get('callback', ''));
        $ele->setAttribute('data-expired-callback', $this->params->get('expired_callback', ''));
        $ele->setAttribute('data-error-callback', $this->params->get('error_callback', ''));

        $dom->appendChild($ele);

        return $dom->saveHTML($ele);
    }

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct
     *
     * @param   string  $code  Answer provided by user. Not needed for the Recaptcha implementation
     *
     * @return  True if the answer is correct, false otherwise
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    public function onCheckAnswer($code = null)
    {
        $input      = Factory::getApplication()->input;
        $privatekey = $this->params->get('private_key');
        $version    = $this->params->get('version', '2.0');
        $remoteip   = IpHelper::getIp();
        $response   = null;
        $spam       = false;

        switch ($version) {
            case '2.0':
                $response  = $code ?: $input->get('g-recaptcha-response', '', 'string');
                $spam      = ($response === '');
                break;
        }

        // Check for Private Key
        if (empty($privatekey)) {
            throw new \RuntimeException(Text::_('PLG_RECAPTCHA_ERROR_NO_PRIVATE_KEY'), 500);
        }

        // Check for IP
        if (empty($remoteip)) {
            throw new \RuntimeException(Text::_('PLG_RECAPTCHA_ERROR_NO_IP'), 500);
        }

        // Discard spam submissions
        if ($spam) {
            throw new \RuntimeException(Text::_('PLG_RECAPTCHA_ERROR_EMPTY_SOLUTION'), 500);
        }

        return $this->getResponse($privatekey, $remoteip, $response);
    }

    /**
     * Get the reCaptcha response.
     *
     * @param   string  $privatekey  The private key for authentication.
     * @param   string  $remoteip    The remote IP of the visitor.
     * @param   string  $response    The response received from Google.
     *
     * @return  bool True if response is good | False if response is bad.
     *
     * @since   3.4
     * @throws  \RuntimeException
     */
    private function getResponse(string $privatekey, string $remoteip, string $response)
    {
        $version = $this->params->get('version', '2.0');

        switch ($version) {
            case '2.0':
                $apiResponse = (new ReCaptcha($privatekey, new HttpBridgePostRequestMethod()))->verify($response, $remoteip);

                if (!$apiResponse->isSuccess()) {
                    foreach ($apiResponse->getErrorCodes() as $error) {
                        throw new \RuntimeException($error, 403);
                    }

                    return false;
                }

                break;
        }

        return true;
    }
}

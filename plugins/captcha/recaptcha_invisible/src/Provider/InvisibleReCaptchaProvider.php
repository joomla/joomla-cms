<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Captcha\InvisibleReCaptcha\Provider;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Captcha\CaptchaProviderInterface;
use Joomla\CMS\Captcha\Google\HttpBridgePostRequestMethod;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\Utilities\IpHelper;
use ReCaptcha\RequestMethod;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provider for Invisible Captcha
 * @since   __DEPLOY_VERSION__
 */
final class InvisibleReCaptchaProvider implements CaptchaProviderInterface
{
    /**
     * A Registry object holding the parameters for the plugin
     *
     * @var    Registry
     * @since  __DEPLOY_VERSION__
     */
    protected $params;

    /**
     * The application object
     *
     * @var    CMSApplicationInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $application;

    /**
     * The http request method
     *
     * @var    RequestMethod
     * @since  __DEPLOY_VERSION__
     */
    protected $requestMethod;

    /**
     * Class constructor
     *
     * @param   Registry                 $params
     * @param   CMSApplicationInterface  $application
     * @param   RequestMethod|null       $requestMethod  The http request method
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(Registry $params, CMSApplicationInterface $application, RequestMethod $requestMethod = null)
    {
        $this->params        = $params;
        $this->application   = $application;
        $this->requestMethod = $requestMethod;
    }

    /**
     * Return Captcha name, CMD string.
     *
     * @return string
     * @since   __DEPLOY_VERSION__
     */
    public function getName(): string
    {
        return 'recaptcha_invisible';
    }

    /**
     * Gets the challenge HTML
     *
     * @param   string  $name        Input name
     * @param   array   $attributes  The class of the field
     *
     * @return  string  The HTML to be embedded in the form
     *
     * @since   __DEPLOY_VERSION__
     *
     * @throws  \RuntimeException
     */
    public function display(string $name = '', array $attributes = []): string
    {
        $this->loadAssets();

        $id    = $attributes['id'] ?? '';
        $class = $attributes['class'] ?? '';

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ele = $dom->createElement('div');
        $ele->setAttribute('id', $id);
        $ele->setAttribute('class', (!$class ? 'g-recaptcha' : ($class . ' g-recaptcha')));
        $ele->setAttribute('data-sitekey', $this->params->get('public_key', ''));
        $ele->setAttribute('data-badge', $this->params->get('badge', 'bottomright'));
        $ele->setAttribute('data-size', 'invisible');
        $ele->setAttribute('data-tabindex', $this->params->get('tabindex', '0'));
        $ele->setAttribute('data-callback', $this->params->get('callback', ''));
        $ele->setAttribute('data-expired-callback', $this->params->get('expired_callback', ''));
        $ele->setAttribute('data-error-callback', $this->params->get('error_callback', ''));
        $dom->appendChild($ele);

        return $dom->saveHTML($ele);
    }

    /**
     * Load captcha assets
     *
     * @return void
     * @since   __DEPLOY_VERSION__
     */
    private function loadAssets()
    {
        $pubkey = $this->params->get('public_key', '');

        if ($pubkey === '') {
            throw new \RuntimeException(Text::_('PLG_RECAPTCHA_INVISIBLE_ERROR_NO_PUBLIC_KEY'));
        }

        $apiSrc = 'https://www.google.com/recaptcha/api.js?onload=JoomlainitReCaptchaInvisible&render=explicit&hl='
            . $this->application->getLanguage()->getTag();

        // Load assets, the callback should be first
        $this->application->getDocument()->getWebAssetManager()
            ->registerAndUseScript('plg_captcha_recaptchainvisible', 'plg_captcha_recaptcha_invisible/recaptcha.min.js', [], ['defer' => true])
            ->registerAndUseScript('plg_captcha_recaptchainvisible.api', $apiSrc, [], ['defer' => true], ['plg_captcha_recaptchainvisible'])
            ->registerAndUseStyle('plg_captcha_recaptchainvisible', 'plg_captcha_recaptcha_invisible/recaptcha_invisible.css');
    }

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct.
     *
     * @param   string  $code  Answer provided by user
     *
     * @return  bool    If the answer is correct, false otherwise
     *
     * @since   __DEPLOY_VERSION__
     *
     * @throws  \RuntimeException
     */
    public function checkAnswer(string $code = null): bool
    {
        $privatekey = $this->params->get('private_key');
        $remoteip   = IpHelper::getIp();

        if ($code) {
            $response = $code;
        } else {
            $response = $this->application->getInput()->get('g-recaptcha-response', '', 'string');
        }

        // Check for Private Key
        if (empty($privatekey)) {
            throw new \RuntimeException(Text::_('PLG_RECAPTCHA_INVISIBLE_ERROR_NO_PRIVATE_KEY'));
        }

        // Check for IP
        if (empty($remoteip)) {
            throw new \RuntimeException(Text::_('PLG_RECAPTCHA_INVISIBLE_ERROR_NO_IP'));
        }

        // Discard spam submissions
        if (trim($response) == '') {
            throw new \RuntimeException(Text::_('PLG_RECAPTCHA_INVISIBLE_ERROR_EMPTY_SOLUTION'));
        }

        return $this->getResponse($privatekey, $remoteip, $response);
    }

    /**
     * Method to react on the setup of a captcha field. Gives the possibility
     * to change the field and/or the XML element for the field.
     *
     * @param   FormField         $field    Captcha field instance
     * @param   \SimpleXMLElement  $element  XML form definition
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     *
     * @throws  \RuntimeException
     */
    public function setupField(FormField $field, \SimpleXMLElement $element): void
    {
        // Hide the label for the invisible recaptcha type
        $element['hiddenLabel'] = 'true';
    }

    /**
     * Get the reCaptcha response.
     *
     * @param   string  $privatekey  The private key for authentication.
     * @param   string  $remoteip    The remote IP of the visitor.
     * @param   string  $response    The response received from Google.
     *
     * @return  boolean  True if response is good | False if response is bad.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \RuntimeException
     */
    private function getResponse($privatekey, $remoteip, $response)
    {
        $reCaptcha = new \ReCaptcha\ReCaptcha($privatekey, $this->requestMethod ?? new HttpBridgePostRequestMethod());
        $response  = $reCaptcha->verify($response, $remoteip);

        if (!$response->isSuccess()) {
            foreach ($response->getErrorCodes() as $error) {
                throw new \RuntimeException($error);
            }

            return false;
        }

        return true;
    }
}

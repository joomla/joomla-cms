<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha.invisible_recaptcha
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Captcha\InvisibleReCaptcha\Extension;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Form\Field\CaptchaField;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;
use Joomla\Utilities\IpHelper;
use ReCaptcha\ReCaptcha;
use ReCaptcha\RequestMethod;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Invisible reCAPTCHA Plugin.
 *
 * @since  3.9.0
 */
final class InvisibleReCaptcha extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.9.0
     */
    protected $autoloadLanguage = true;

    /**
     * The http request method
     *
     * @var    RequestMethod
     * @since  4.3.0
     */
    private $requestMethod;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher     The dispatcher
     * @param   array                $config         An optional associative array of configuration settings
     * @param   RequestMethod        $requestMethod  The http request method
     *
     * @since   4.3.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, RequestMethod $requestMethod)
    {
        parent::__construct($dispatcher, $config);

        $this->requestMethod = $requestMethod;
    }

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
            $this->getApplication()->getLanguage()->_('PLG_CAPTCHA_RECAPTCHA_INVISIBLE') => [
                $this->getApplication()->getLanguage()->_('PLG_RECAPTCHA_INVISIBLE_PRIVACY_CAPABILITY_IP_ADDRESS'),
            ],
        ];
    }

    /**
     * Initializes the captcha
     *
     * @param   string  $id  The id of the field.
     *
     * @return  boolean True on success, false otherwise
     *
     * @since   3.9.0
     * @throws  \RuntimeException
     */
    public function onInit($id = 'dynamic_recaptcha_invisible_1')
    {
        $app = $this->getApplication();

        if (!$app instanceof CMSWebApplicationInterface) {
            return false;
        }

        $pubkey = $this->params->get('public_key', '');

        if ($pubkey === '') {
            throw new \RuntimeException($app->getLanguage()->_('PLG_RECAPTCHA_INVISIBLE_ERROR_NO_PUBLIC_KEY'));
        }

        $apiSrc = 'https://www.google.com/recaptcha/api.js?onload=JoomlainitReCaptchaInvisible&render=explicit&hl='
            . $app->getLanguage()->getTag();

        // Load assets, the callback should be first
        $app->getDocument()->getWebAssetManager()
            ->registerAndUseScript('plg_captcha_recaptchainvisible', 'plg_captcha_recaptcha_invisible/recaptcha.min.js', [], ['defer' => true])
            ->registerAndUseScript('plg_captcha_recaptchainvisible.api', $apiSrc, [], ['defer' => true], ['plg_captcha_recaptchainvisible'])
            ->registerAndUseStyle('plg_captcha_recaptchainvisible', 'plg_captcha_recaptcha_invisible/recaptcha_invisible.css');

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
     * @since  3.9.0
     */
    public function onDisplay($name = null, $id = 'dynamic_recaptcha_invisible_1', $class = '')
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ele = $dom->createElement('div');
        $ele->setAttribute('id', $id);
        $ele->setAttribute('class', ((trim($class) == '') ? 'g-recaptcha' : ($class . ' g-recaptcha')));
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
     * Calls an HTTP POST function to verify if the user's guess was correct
     *
     * @param   string  $code  Answer provided by user. Not needed for the Recaptcha implementation
     *
     * @return  boolean  True if the answer is correct, false otherwise
     *
     * @since   3.9.0
     * @throws  \RuntimeException
     */
    public function onCheckAnswer($code = null)
    {
        $input      = $this->getApplication()->getInput();
        $privatekey = $this->params->get('private_key');
        $remoteip   = IpHelper::getIp();

        $response  = $input->get('g-recaptcha-response', '', 'string');

        // Check for Private Key
        if (empty($privatekey)) {
            throw new \RuntimeException($this->getApplication()->getLanguage()->_('PLG_RECAPTCHA_INVISIBLE_ERROR_NO_PRIVATE_KEY'));
        }

        // Check for IP
        if (empty($remoteip)) {
            throw new \RuntimeException($this->getApplication()->getLanguage()->_('PLG_RECAPTCHA_INVISIBLE_ERROR_NO_IP'));
        }

        // Discard spam submissions
        if (trim($response) == '') {
            throw new \RuntimeException($this->getApplication()->getLanguage()->_('PLG_RECAPTCHA_INVISIBLE_ERROR_EMPTY_SOLUTION'));
        }

        return $this->getResponse($privatekey, $remoteip, $response);
    }

    /**
     * Method to react on the setup of a captcha field. Gives the possibility
     * to change the field and/or the XML element for the field.
     *
     * @param   CaptchaField       $field    Captcha field instance
     * @param   \SimpleXMLElement  $element  XML form definition
     *
     * @return void
     *
     * @since 3.9.0
     */
    public function onSetupField(CaptchaField $field, \SimpleXMLElement $element)
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
     * @since   3.9.0
     * @throws  \RuntimeException
     */
    private function getResponse($privatekey, $remoteip, $response)
    {
        $reCaptcha = new ReCaptcha($privatekey, $this->requestMethod);
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

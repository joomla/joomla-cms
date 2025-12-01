<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha.POWCaptcha
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Captcha\POWCaptcha\Extension;

use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\ChallengeOptions;
use AltchaOrg\Altcha\Hasher\Algorithm;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Event\Plugin\AjaxEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Proof of work captcha Plugin
 * Based on the ALTCHA captcha library
 *
 * @since __DEPLOY_VERSION__
 */
final class POWCaptcha extends CMSPlugin implements SubscriberInterface
{
    protected const int MAXNUMBER_EASY     = 50000;
    protected const int MAXNUMBER_MODERATE = 100000;
    protected const int MAXNUMBER_HARD     = 200000;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onAjaxPowcaptcha' => 'handleAjaxRequest',
        ];
    }

    /**
     * Initializes the captcha
     *
     * @param   string  $id  The id of the field.
     *
     * @return  Boolean True on success, false otherwise
     *
     * @throws  \RuntimeException
     * @since __DEPLOY_VERSION__
     */
    public function onInit($id = 'altcha_1')
    {
        $app = $this->getApplication();

        if (!$app instanceof CMSWebApplicationInterface) {
            return false;
        }

        // Load assets
        $app->getDocument()->getWebAssetManager()->usePreset('altcha');

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
     * @since __DEPLOY_VERSION__
     */
    public function onDisplay($name = null, $id = 'altcha_1', $class = '')
    {
        $htmlAttributes = [
            'name'       => $name,
            'id'         => $id,
            'class'      => $class,
            'hidefooter' => true,
            'hidelogo'   => true,
            'auto'       => $this->params->get('autosolve', 'onfocus'),
            'strings'    => htmlentities(
                json_encode(
                    [
                        'ariaLinkLabel' => Text::_('PLG_CAPTCHA_POWCAPTCHA_ARIALINKLABEL'),
                        'error'         => Text::_('PLG_CAPTCHA_POWCAPTCHA_ERROR'),
                        'expired'       => Text::_('PLG_CAPTCHA_POWCAPTCHA_EXPIRED'),
                        'footer'        => Text::_('PLG_CAPTCHA_POWCAPTCHA_FOOTER'),
                        'label'         => Text::_('PLG_CAPTCHA_POWCAPTCHA_LABEL'),
                        'verified'      => Text::_('PLG_CAPTCHA_POWCAPTCHA_VERIFIED'),
                        'verifying'     => Text::_('PLG_CAPTCHA_POWCAPTCHA_VERIFYING'),
                        'waitAlert'     => Text::_('PLG_CAPTCHA_POWCAPTCHA_WAITALERT'),
                    ]
                ),
                ENT_QUOTES,
                'UTF-8'
            ),
            'challengeurl' => Route::_(
                \sprintf(
                    "index.php?option=com_ajax&plugin=powcaptcha&group=captcha&format=raw&%s=1",
                    Session::getFormToken()
                ),
                false,
                false,
                true
            ),
        ];

        return \sprintf(
            '<altcha-widget %s></altcha-widget>',
            ArrayHelper::toString($htmlAttributes)
        );
    }

    /**
     * Verify the users answer
     *
     * @param   string  $code  Answer provided by user. Not needed for the Recaptcha implementation
     *
     * @return  bool  True if the answer is correct, false otherwise
     *
     * @throws  \RuntimeException
     * @since __DEPLOY_VERSION__
     */
    public function onCheckAnswer($code = null)
    {
        // Before we verify the actual solution, let's first verify our challenge key
        $decoded = base64_decode($code, true);

        // Check for base64 decode errors
        if (!$decoded) {
            return false;
        }

        // Check for json Errors
        try {
            $data = json_decode($decoded, true, 2, \JSON_THROW_ON_ERROR);
        } catch (\JsonException|\ValueError) {
            return null;
        }

        // Check for data errors
        if (!\is_array($data) || empty($data)) {
            return null;
        }

        // Invalid salt format
        if (empty($data['salt']) || !str_contains($data['salt'], 'challengeKey=')) {
            return null;
        }

        // Extract challengeKey
        parse_str(explode("?", $data['salt'])[1], $challengeParams);

        // Check if challengeKey is valid
        $session = $this->getApplication()->getSession();

        if (!$session->get('plg_captcha_powcaptcha.' . $challengeParams['challengeKey'], false)) {
            // Key is invalid, return
            return false;
        }

        // Key is valid, check for solution
        if (!$this->getAltcha()->verifySolution((string) $code)) {
            return false;
        }

        // Solution was valid, invalidate key
        $session->set('plg_captcha_powcaptcha.' . $challengeParams['challengeKey'], false);

        // It's valid!
        return true;
    }

    /**
     * Handles the ajax request triggered by altcha to fetch the challenge code
     *
     * @param AjaxEvent $event
     */
    public function handleAjaxRequest(AjaxEvent $event)
    {
        // Altcha expects its challenge code in a specific syntax that is not compatible with com_ajax, raw output
        @ob_end_clean();
        header('Content-Type: application/json');

        // CRSF Token check
        if (!Session::checkToken('get')) {
            echo json_encode([]);
            $this->getApplication()->close();
        }

        // Determine the max number - to be updated in future releases
        $maxNumber = match ($this->params->get('difficulty', 'moderate')) {
            "easy"     => self::MAXNUMBER_EASY,
            "moderate" => self::MAXNUMBER_MODERATE,
            "hard"     => self::MAXNUMBER_HARD,
            "custom"   => $this->params->get('maxnumber', 250000)
        };

        // Calculate expiration time
        $expiration = Date::getInstance()->add(new \DateInterval('PT' . $this->params->get('expiration', 300) . 'S'));

        // Generate a random key for the challenge - that key is stored in the session and will be checked an invalidated
        // during the verification process. That prevents challenge replay attacks.
        $challengeKey = md5(random_bytes(16));

        // Store the challenge key in the session
        $this->getApplication()->getSession('')->set('plg_captcha_powcaptcha.' . $challengeKey, true);

        $options = new ChallengeOptions(
            Algorithm::SHA512,
            $maxNumber,
            $expiration,
            [
                "challengeKey" => $challengeKey
            ]
        );

        // Generate the challenge
        $challenge = $this->getAltcha()->createChallenge($options);

        echo json_encode($challenge);

        $this->getApplication()->close();
    }

    /**
     * Initializes a new altcha instance with the site secret
     *
     * @return Altcha
     */
    protected function getAltcha(): Altcha
    {
        return new Altcha($this->getApplication()->get('secret'));
    }
}

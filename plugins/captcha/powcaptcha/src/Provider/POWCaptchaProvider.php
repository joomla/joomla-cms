<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha.POWCaptcha
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Captcha\POWCaptcha\Provider;

use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\Challenge;
use AltchaOrg\Altcha\ChallengeOptions;
use AltchaOrg\Altcha\Hasher\Algorithm;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Captcha\CaptchaProviderInterface;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Class POWCaptchaProvider
 *
 * @package  Joomla\Plugin\Captcha\POWCaptcha\Provider
 *
 * @since __DEPLOY_VERSION__
 */
final class POWCaptchaProvider implements CaptchaProviderInterface
{
    protected const int MAXNUMBER_EASY     = 50000;
    protected const int MAXNUMBER_MODERATE = 100000;
    protected const int MAXNUMBER_HARD     = 200000;

    public function __construct(
        protected Registry $params,
        protected CMSWebApplicationInterface|null $application
    ) {
    }

    /**
     * Return Captcha name, CMD string.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'powcaptcha';
    }

    /**
     * Gets the challenge HTML
     *
     * @param   string  $name        The name of the field. Not Used.
     * @param   array   $attributes  The id of the field.
     *
     * @return  string  The HTML to be embedded in the form.
     */
    public function display(string $name = '', array $attributes = []): string
    {
        if (!$this->application instanceof CMSWebApplicationInterface) {
            return '';
        }

        // Load assets
        $this->application->getDocument()->getWebAssetManager()->usePreset('altcha');

        // Prepare markup
        $htmlAttributes = [
            'name'       => $name,
            'id'         => $attributes['id'] ?? '',
            'class'      => $attributes['class'] ?? '',
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
     * @param   null|string  $code  Answer provided by user. Not needed for the Recaptcha implementation
     *
     * @return  bool  True if the answer is correct, false otherwise
     *
     * @throws  \RuntimeException
     */
    public function checkAnswer(?string $code = null): bool
    {
        if (!$this->application instanceof CMSWebApplicationInterface) {
            return false;
        }

        // Before we verify the actual solution, let's first verify our challenge key
        $decoded = base64_decode($code, true);

        // Check for base64 decode errors
        if (!$decoded) {
            return false;
        }

        // Check for json Errors
        try {
            $data = json_decode($decoded, true, 2, \JSON_THROW_ON_ERROR);
        } catch (\JsonException | \ValueError) {
            return false;
        }

        // Check for data errors
        if (!\is_array($data) || empty($data)) {
            return false;
        }

        // Invalid salt format
        if (empty($data['salt']) || !str_contains($data['salt'], 'challengeKey=')) {
            return false;
        }

        // Extract challengeKey
        parse_str(explode("?", $data['salt'])[1], $challengeParams);

        // Check if challengeKey is valid
        $session = $this->application->getSession();

        if (!$session->get('plg_captcha_powcaptcha.' . $challengeParams['challengeKey'], false)) {
            // Key is invalid, return
            return false;
        }

        // Key is valid, check for solution
        if (!(new Altcha($this->application->get('secret')))->verifySolution((string) $code)) {
            return false;
        }

        // Solution was valid, invalidate key
        $session->set('plg_captcha_powcaptcha.' . $challengeParams['challengeKey'], false);

        // It's valid!
        return true;
    }

    /**
     * Method to generate the actual altcha challenge
     *
     * @return Challenge
     */
    public function getChallenge(): Challenge
    {
        // Determine the max number - to be updated in future releases
        $maxNumber = match ($this->params->get('difficulty', 'moderate')) {
            "easy"     => self::MAXNUMBER_EASY,
            "moderate" => self::MAXNUMBER_MODERATE,
            "hard"     => self::MAXNUMBER_HARD,
            "custom"   => $this->params->get('maxnumber', 250000)
        };

        // Calculate expiration time
        $expiration = Date::getInstance()->add(new \DateInterval('PT' . $this->params->get('expiration', 300) . 'S'));

        // Generate a random key for the challenge to prevent replay attacks.
        // That key is stored in the session and will be checked and invalidated for re-use during the verification process.
        $challengeKey = md5(random_bytes(16));

        // Store the challenge key in the session
        $this->application->getSession()->set('plg_captcha_powcaptcha.' . $challengeKey, true);

        $options = new ChallengeOptions(
            Algorithm::SHA512,
            $maxNumber,
            $expiration,
            [
                "challengeKey" => $challengeKey,
            ]
        );

        // Generate the challenge
        return (new Altcha($this->application->get('secret')))->createChallenge($options);
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
     * @throws  \RuntimeException
     */
    public function setupField(FormField $field, \SimpleXMLElement $element): void
    {
    }
}

<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Cpatcha.match
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Captcha\Math\Provider;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Captcha\CaptchaProviderInterface;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Math captcha Provider
 *
 * @since  __DEPLOY_VERSION__
 */
final class MathCaptchaProvider implements CaptchaProviderInterface
{
    /**
     * @var   CMSApplicationInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $app;

    /**
     * Math formula
     *
     * @var   string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected string $formula = '';

    /**
     * Session key, to store result
     *
     * @var   string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected string $sessionKey = 'plg_captcha_math.result';

    /**
     * Class constructor
     *
     * @param CMSApplicationInterface $app
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(CMSApplicationInterface $app)
    {
        $this->app = $app;
    }

    /**
     * Return Captcha name, CMD string.
     *
     * @return string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getName(): string
    {
        return 'math';
    }

    /**
     * Render the captcha input
     *
     * @param   string  $name        Input name given in the form
     * @param   array   $attributes  The class of the field and other attributes, from the form.
     *
     * @return  string  The HTML to be embedded in the form
     *
     * @throws  \RuntimeException
     *
     * @since  __DEPLOY_VERSION__
     */
    public function display(string $name = '', array $attributes = []): string
    {
        // Prepare the numbers and store the result.
        // They are the same for all captcha on the page, because browser can submit only 1 form at a time.
        if (!$this->formula) {
            $numbers       = [rand(10, 90), rand(1, 9)];
            $this->formula = sprintf('%d + %d =', ...$numbers);

            $this->app->getSession()->set($this->sessionKey, array_sum($numbers));
        }

        return  LayoutHelper::render(
            'plugins.captcha.math.mathcaptcha',
            ['name' => $name, 'attributes' => $attributes, 'formula' => $this->formula],
            null,
            ['component' => 'none']
        );
    }

    /**
     * Validate the input data
     *
     * @param   ?string  $code  Answer provided by user
     *
     * @return  bool    If the answer is correct, false otherwise
     *
     * @throws  \RuntimeException
     *
     * @since  __DEPLOY_VERSION__
     */
    public function checkAnswer(string $code = null): bool
    {
        if (!$code) {
            return false;
        }

        // Get a real solution from session, and compare with answer
        $solution = (int) $this->app->getSession()->get($this->sessionKey);

        if (!$solution) {
            throw new \RuntimeException(Text::_('PLG_CAPTCHA_MATH_EMPTY_STORE'));
        }

        // Clean stored value to prevent F5 Form submission
        $this->app->getSession()->set($this->sessionKey, null);

        return $solution === (int) $code;
    }

    /**
     * Method to react on the setup of a captcha field. Gives the possibility
     * to change the field and/or the XML element for the field.
     *
     * @param   FormField          $field    Captcha field instance
     * @param   \SimpleXMLElement  $element  XML form definition
     *
     * @return void
     *
     * @throws  \RuntimeException
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setupField(FormField $field, \SimpleXMLElement $element): void
    {
        // Hide the label for this captcha type
        $element['hiddenLabel'] = 'true';
    }
}

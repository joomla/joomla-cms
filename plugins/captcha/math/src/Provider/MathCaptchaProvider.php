<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha.math
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
     * Index of active input
     *
     * @var  int
     *
     * @since  __DEPLOY_VERSION__
     */
    protected int $inputIdx = 0;

    /**
     * Session key, to store result
     *
     * @var   string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected string $sessionKey = 'plg_captcha_math';

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
            $this->createQuiz();
        }

        return  LayoutHelper::render(
            'plugins.captcha.math.mathcaptcha',
            [
                'name'       => $name,
                'attributes' => $attributes,
                'formula'    => $this->formula,
                'inputIdx'   => $this->inputIdx,
                'document'   => $this->app->getDocument(),
            ],
            null,
            ['component' => 'none']
        );
    }

    /**
     * Prepare the quiz
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createQuiz()
    {
        // 2 or 3
        if (rand(1, 2) === 2) {
            $numbers  = [rand(110, 980), rand(1, 9)];
            $solution = array_sum($numbers);
        } else {
            $numbers  = [rand(10, 90), rand(1, 9)];
            $solution = array_sum($numbers);
        }

        // Full or half
        if (rand(1, 2) === 2) {
            $numbers[]     = (int) substr($solution, 0, 1);
            $solution      = (int) substr($solution, 1);
            $this->formula = sprintf('%d + %d = %d', ...$numbers);
        } else {
            $this->formula = sprintf('%d + %d =', ...$numbers);
        }

        $this->inputIdx = rand(0, 2);

        $this->app->getSession()->set($this->sessionKey . '.pwd', $solution);
        $this->app->getSession()->set($this->sessionKey . '.secret', $this->inputIdx);
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
        $code = $code ? json_decode($code, true) : false;
        if (!$code) {
            return false;
        }

        // Get a real solution from session, and compare with answer
        $solution = (int) $this->app->getSession()->get($this->sessionKey . '.pwd');
        $inputIdx = (int) $this->app->getSession()->get($this->sessionKey . '.secret');

        if (!$solution || $inputIdx < 0 || $inputIdx > 2) {
            throw new \RuntimeException(Text::_('PLG_CAPTCHA_MATH_EMPTY_STORE'));
        }

        // Clean stored value to prevent repetitive form submission
        $this->app->getSession()->set($this->sessionKey, null);

        // Check for correct response
        $isOk = !empty($code[$inputIdx]) && $solution === (int) $code[$inputIdx];
        unset($code[$inputIdx]);

        foreach ($code as $r) {
            if (!$isOk) {
                break;
            }
            $isOk = $r === '';
        }

        return $isOk;
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
        // Custom message
        if (!$element['message']) {
            $element['message'] = 'PLG_CAPTCHA_MATH_WRONG_SOLUTION';
        }
    }
}

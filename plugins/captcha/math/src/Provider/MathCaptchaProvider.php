<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Captcha\Math\Provider;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Captcha\CaptchaProviderInterface;
use Joomla\CMS\Form\FormField;

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
        $id    = $attributes['id'] ?? '';
        $class = $attributes['class'] ?? '';

        return 'Enter solution for: <input type="text" value="" size="5" name="' . $name . '" id="' . $id . '" class="' . $class . '"/>';
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
        return !$code;
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
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setupField(FormField $field, \SimpleXMLElement $element): void
    {
        // Hide the label for this captcha type
        $element['hiddenLabel'] = 'true';
    }
}

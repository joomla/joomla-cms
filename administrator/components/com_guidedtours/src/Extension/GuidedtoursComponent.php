<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Extension;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Psr\Container\ContainerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component class for com_guidedtours
 *
 * @since 4.3.0
 */
class GuidedtoursComponent extends MVCComponent implements BootableExtensionInterface
{
    use HTMLRegistryAwareTrait;

    /**
     * The step type
     *
     * @since 4.3.0
     */
    public const STEP_TYPE_NAMES = [
        self::STEP_NEXT        => 'COM_GUIDEDTOURS_FIELD_VALUE_STEP_TYPE_NEXT',
        self::STEP_REDIRECT    => 'COM_GUIDEDTOURS_FIELD_VALUE_STEP_TYPE_REDIRECT',
        self::STEP_INTERACTIVE => 'COM_GUIDEDTOURS_FIELD_VALUE_STEP_TYPE_INTERACTIVE',
    ];

    /**
     * A regular step.
     *
     * @since 4.3.0
     */
    public const STEP_NEXT = 0;

    /**
     * A step that redirects to another page.
     *
     * @since 4.3.0
     */
    public const STEP_REDIRECT = 1;

    /**
     * A step that allows interactions from the user.
     *
     * @since 4.3.0
     */
    public const STEP_INTERACTIVE = 2;

    /**
     * The step interactive type names
     *
     * @since 4.3.0
     */
    public const STEP_INTERACTIVETYPE_NAMES = [
        self::STEP_INTERACTIVETYPE_FORM_SUBMIT => 'COM_GUIDEDTOURS_FIELD_VALUE_INTERACTIVESTEP_TYPE_FORM_SUBMIT',
        self::STEP_INTERACTIVETYPE_TEXT        => 'COM_GUIDEDTOURS_FIELD_VALUE_INTERACTIVESTEP_TYPE_TEXT_FIELD',
        self::STEP_INTERACTIVETYPE_BUTTON      => 'COM_GUIDEDTOURS_FIELD_VALUE_INTERACTIVESTEP_TYPE_BUTTON',
        self::STEP_INTERACTIVETYPE_OTHER       => 'COM_GUIDEDTOURS_FIELD_VALUE_INTERACTIVESTEP_TYPE_OTHER',
    ];

    /**
     * An interactive step where a user clicks on a form button.
     *
     * @since 4.3.0
     */
    public const STEP_INTERACTIVETYPE_FORM_SUBMIT = 1;

    /**
     * An interactive step where a user enters text.
     *
     * @since 4.3.0
     */
    public const STEP_INTERACTIVETYPE_TEXT = 2;

    /**
     * An interactive step where a user clicks on a button.
     *
     * @since 4.3.0
     */
    public const STEP_INTERACTIVETYPE_BUTTON = 4;

    /**
     * An interactive step for other fields.
     *
     * @since 4.3.0
     */
    public const STEP_INTERACTIVETYPE_OTHER = 3;

    /**
     * Booting the extension. This is the function to set up the environment of the extension like
     * registering new class loaders, etc.
     *
     * If required, some initial set up can be done from services of the container, eg.
     * registering HTML services.
     *
     * @param   ContainerInterface $container The container
     *
     * @return void
     *
     * @since 4.3.0
     */
    public function boot(ContainerInterface $container)
    {
    }
}

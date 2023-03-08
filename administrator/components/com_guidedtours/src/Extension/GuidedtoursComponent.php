<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Extension;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component class for com_guidedtours
 *
 * @since 4.3.0
 */
class GuidedtoursComponent extends MVCComponent
{
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
     * Loads the required assets and language strings for guided tours.
     *
     * @param   CMSWebApplicationInterface $app The app
     *
     * @return void
     *
     * @since 4.3.0
     */
    public function prepareAssets(CMSWebApplicationInterface $app)
    {
        $app->getLanguage()->load('com_guidedtours', JPATH_ADMINISTRATOR);
        Text::script('JCANCEL');
        Text::script('COM_GUIDEDTOURS_BACK');
        Text::script('COM_GUIDEDTOURS_COMPLETE');
        Text::script('COM_GUIDEDTOURS_COULD_NOT_LOAD_THE_TOUR');
        Text::script('COM_GUIDEDTOURS_NEXT');
        Text::script('COM_GUIDEDTOURS_START');
        Text::script('COM_GUIDEDTOURS_STEP_NUMBER_OF');

        $app->getDocument()->getWebAssetManager()->getRegistry()->addExtensionRegistryFile('com_guidedtours');
        $app->getDocument()->getWebAssetManager()->usePreset('com_guidedtours.guidedtours');
        $app->getDocument()->addScriptOptions('com_guidedtours.token', Session::getFormToken());
    }
}

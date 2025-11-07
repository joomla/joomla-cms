<?php

/**
 * @package         Joomla.Plugin
 * @subpackage      Media-Action.crop
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\MediaAction\Crop\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Aspect Ratio Calculator Field
 *
 * @since  6.1.0
 */
class CalculatorField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  6.1.0
     */
    protected $type = 'Calculator';

    /**
     * The layout path to use for rendering the field.
     *
     * @var    string
     * @since  6.1.0
     */
    protected $layout = 'field.calculator';

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   6.1.0
     */
    protected function getLabel(): string
    {
        return '';
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   6.1.0
     */
    protected function getInput(): string
    {
        return $this->getRenderer($this->layout)->render($this->getLayoutData());
    }

    /**
     * Get the layouts paths
     *
     * @return  array  An array of layout paths
     *
     * @since   6.1.0
     */
    protected function getLayoutPaths(): array
    {
        $template = Factory::getApplication()->getTemplate();

        return [
            JPATH_ADMINISTRATOR . '/templates/' . $template . '/html/layouts/plugins/media-action/crop',
            JPATH_PLUGINS . '/media-action/crop/layouts',
            JPATH_SITE . '/layouts',
        ];
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array  The layout data.
     *
     * @since   6.1.0
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();

        $extraData = [
            'id' => $this->id,
        ];

        return array_merge($data, $extraData);
    }
}

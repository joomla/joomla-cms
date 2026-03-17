<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Administrator Menu Presets list field.
 *
 * @since  3.8.0
 */
class MenuPresetField extends ListField
{
    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   3.8.0
     */
    protected $type = 'MenuPreset';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since  3.8.0
     */
    protected function getOptions()
    {
        $options = [];
        $presets = MenusHelper::getPresets();

        foreach ($presets as $preset) {
            $options[] = HTMLHelper::_('select.option', $preset->name, Text::_($preset->title));
        }

        return array_merge(parent::getOptions(), $options);
    }
}

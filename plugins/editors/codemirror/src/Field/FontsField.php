<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\CodeMirror\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports an HTML select list of fonts
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 * @since       3.4
 */
class FontsField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.4
     */
    protected $type = 'Fonts';

    /**
     * Method to get the list of fonts field options.
     *
     * @return  array  The field option objects.
     *
     * @since   3.4
     */
    protected function getOptions()
    {
        $fonts = json_decode(file_get_contents(JPATH_PLUGINS . '/editors/codemirror/fonts.json'));
        $options = [];

        foreach ($fonts as $key => $info) {
            $options[] = HTMLHelper::_('select.option', $key, $info->name);
        }

        // Merge any additional options in the XML definition.
        return array_merge(parent::getOptions(), $options);
    }
}

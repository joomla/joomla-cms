<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * Text Filters form field.
 *
 * @since  3.7.0
 */
class ConfigComponentsField extends ListField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   3.7.0
     */
    public $type = 'ConfigComponents';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     *
     * @since   3.7.0
     */
    protected function getOptions()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('name AS text, element AS value')
            ->from('#__extensions')
            ->where('enabled >= 1')
            ->where('type =' . $db->quote('component'));

        $items = $db->setQuery($query)->loadObjectList();

        if ($items) {
            $lang = Factory::getLanguage();

            foreach ($items as &$item) {
                // Load language
                $extension = $item->value;

                if (File::exists(JPATH_ADMINISTRATOR . '/components/' . $extension . '/config.xml')) {
                    $source = JPATH_ADMINISTRATOR . '/components/' . $extension;
                    $lang->load("$extension.sys", JPATH_ADMINISTRATOR)
                    || $lang->load("$extension.sys", $source);

                    // Translate component name
                    $item->text = Text::_($item->text);
                } else {
                    $item = null;
                }
            }

            // Sort by component name
            $items = ArrayHelper::sortObjects(array_filter($items), 'text', 1, true, true);
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $items);

        return $options;
    }
}

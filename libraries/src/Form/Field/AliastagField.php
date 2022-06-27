<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  2.5.0
 */
class AliastagField extends ListField
{
    /**
     * The field type.
     *
     * @var    string
     * @since  3.6
     */
    protected $type = 'Aliastag';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     *
     * @since   3.6
     */
    protected function getOptions()
    {
        // Get list of tag type alias
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    'DISTINCT ' . $db->quoteName('type_alias', 'value'),
                    $db->quoteName('type_alias', 'text'),
                ]
            )
            ->from($db->quoteName('#__contentitem_tag_map'));
        $db->setQuery($query);

        $options = $db->loadObjectList();

        $lang = Factory::getLanguage();

        foreach ($options as $i => $item) {
            $parts     = explode('.', $item->value);
            $extension = $parts[0];
            $lang->load($extension . '.sys', JPATH_ADMINISTRATOR)
            || $lang->load($extension, Path::clean(JPATH_ADMINISTRATOR . '/components/' . $extension));
            $options[$i]->text = Text::_(strtoupper($extension) . '_TAGS_' . strtoupper($parts[1]));
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        // Sort by language value
        usort(
            $options,
            function ($a, $b) {
                return strcmp($a->text, $b->text);
            }
        );

        return $options;
    }
}

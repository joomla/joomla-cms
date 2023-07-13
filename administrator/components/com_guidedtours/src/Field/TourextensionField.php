<?php

/**
 * @package         Joomla.Administrator
 * @subpackage      com_guidedtours
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ComponentsField;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tourextension Field class.
 * List of extensions selected in a guided tour
 *
 * @since  5.0.0
 */
class TourextensionField extends ComponentsField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  5.0.0
     */
    protected $type = 'Tourextension';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  object[]  An array of JHtml options.
     *
     * @since   2.5.0
     */
    protected function getOptions()
    {
        $db    = $this->getDatabase();
        // get distinct list of active extensions from the tours table then filter the list of matching extensions
        $query = $db->getQuery(true)
                    ->select('DISTINCT ' . $db->quoteName('extensions'))
                    ->from($db->quoteName('#__guidedtours'));

        $extensionEntries = $db->setQuery($query)->loadColumn();
        $extensions       = [];

        foreach ($extensionEntries as $extensionsEntry) {
            $extensions = array_merge($extensions, (new Registry($extensionsEntry))->toArray());
        }

        $query = $db->getQuery(true)
                    ->select(
                        [
                            $db->quoteName('name', 'text'),
                            $db->quoteName('element', 'value'),
                        ]
                    )
                    ->from($db->quoteName('#__extensions'))
                    ->where(
                        [
                            $db->quoteName('enabled') . ' >= 1',
                            $db->quoteName('type') . ' = ' . $db->quote('component'),
                            $db->quoteName( 'element' ) . ' = ' . $db->quote( 'com_yoursites' )
                        ]
                    );

        if (count($extensions)) {
            $query->whereIn($db->quoteName('element'), array_values($extensions));
        }

        $items = $db->setQuery($query)->loadObjectList();

        if ($items) {
            $lang = Factory::getLanguage();

            foreach ($items as &$item) {
                // Load language
                $extension = $item->value;

                $lang->load("$extension.sys", JPATH_ADMINISTRATOR)
                || $lang->load("$extension.sys", JPATH_ADMINISTRATOR . '/components/' . $extension);

                // Translate component name
                $item->text = Text::_($item->text);
            }

            // Sort by component name
            $items = ArrayHelper::sortObjects($items, 'text', 1, true, true);
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(ListField::getOptions(), $items);

        return $options;
    }
}

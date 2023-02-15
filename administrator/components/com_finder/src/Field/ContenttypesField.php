<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content Types Filter field for the Finder package.
 *
 * @since  3.6.0
 */
class ContenttypesField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.6.0
     */
    protected $type = 'ContentTypes';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   3.6.0
     */
    public function getOptions()
    {
        $lang    = Factory::getLanguage();
        $options = [];

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id', 'value'))
            ->select($db->quoteName('title', 'text'))
            ->from($db->quoteName('#__finder_types'));

        // Get the options.
        $db->setQuery($query);

        try {
            $contentTypes = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // Translate.
        foreach ($contentTypes as $contentType) {
            $key = LanguageHelper::branchSingular($contentType->text);
            $contentType->translatedText = $lang->hasKey($key) ? Text::_($key) : $contentType->text;
        }

        // Order by title.
        $contentTypes = ArrayHelper::sortObjects($contentTypes, 'translatedText', 1, true, true);

        // Convert the values to options.
        foreach ($contentTypes as $contentType) {
            $options[] = HTMLHelper::_('select.option', $contentType->value, $contentType->translatedText);
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}

<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content Type field.
 *
 * @since  3.1
 */
class ContenttypeField extends ListField
{
    /**
     * A flexible tag list that respects access controls
     *
     * @var    string
     * @since  3.1
     */
    public $type = 'Contenttype';

    /**
     * Method to get the field input for a list of content types.
     *
     * @return  string  The field input.
     *
     * @since   3.1
     */
    protected function getInput()
    {
        if (!\is_array($this->value)) {
            if (\is_object($this->value)) {
                $this->value = $this->value->tags;
            }

            if (\is_string($this->value)) {
                $this->value = explode(',', $this->value);
            }
        }

        return parent::getInput();
    }

    /**
     * Method to get a list of content types
     *
     * @return  array  The field option objects.
     *
     * @since   3.1
     */
    protected function getOptions()
    {
        $lang = Factory::getLanguage();
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('a.type_id', 'value'),
                    $db->quoteName('a.type_title', 'text'),
                    $db->quoteName('a.type_alias', 'alias'),
                ]
            )
            ->from($db->quoteName('#__content_types', 'a'))
            ->order($db->quoteName('a.type_title') . ' ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            return [];
        }

        foreach ($options as $option) {
            // Make up the string from the component sys.ini file
            $parts = explode('.', $option->alias);
            $comp = array_shift($parts);

            // Make sure the component sys.ini is loaded
            $lang->load($comp . '.sys', JPATH_ADMINISTRATOR)
            || $lang->load($comp . '.sys', JPATH_ADMINISTRATOR . '/components/' . $comp);

            $option->string = implode('_', $parts);
            $option->string = $comp . '_CONTENT_TYPE_' . $option->string;

            if ($lang->hasKey($option->string)) {
                $option->text = Text::_($option->string);
            }
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}

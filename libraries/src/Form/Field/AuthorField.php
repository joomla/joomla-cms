<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

/**
 * Form Field to load a list of content authors
 *
 * @since  3.2
 */
class AuthorField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
     */
    public $type = 'Author';

    /**
     * Cached array of the category items.
     *
     * @var    array
     * @since  3.2
     */
    protected static $options = [];

    /**
     * Method to get the options to populate list
     *
     * @return  array  The field option objects.
     *
     * @since   3.2
     */
    protected function getOptions()
    {
        // Accepted modifiers
        $hash = md5($this->element);

        if (!isset(static::$options[$hash])) {
            static::$options[$hash] = parent::getOptions();

            $db = $this->getDatabase();

            // Construct the query
            $query = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('u.id', 'value'),
                        $db->quoteName('u.name', 'text'),
                    ]
                )
                ->from($db->quoteName('#__users', 'u'))
                ->join('INNER', $db->quoteName('#__content', 'c'), $db->quoteName('c.created_by') . ' = ' . $db->quoteName('u.id'))
                ->group(
                    [
                        $db->quoteName('u.id'),
                        $db->quoteName('u.name'),
                    ]
                )
                ->order($db->quoteName('u.name'));

            // Setup the query
            $db->setQuery($query);

            // Return the result
            if ($options = $db->loadObjectList()) {
                static::$options[$hash] = array_merge(static::$options[$hash], $options);
            }
        }

        return static::$options[$hash];
    }
}

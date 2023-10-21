<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides a list of published content languages with home pages
 *
 * @see    \Joomla\CMS\Form\Field\LanguageField for a select list of application languages.
 * @since  3.5
 */
class FrontendlanguageField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.5
     */
    public $type = 'Frontend_Language';

    /**
     * Method to get the field options for frontend published content languages with homes.
     *
     * @return  object[]  The options the field is going to show.
     *
     * @since   3.5
     */
    protected function getOptions()
    {
        // Get the database object and a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select('a.lang_code AS value, a.title AS text')
            ->from($db->quoteName('#__languages') . ' AS a')
            ->where('a.published = 1')
            ->order('a.title');

        // Select the language home pages.
        $query->select('l.home, l.language')
            ->innerJoin($db->quoteName('#__menu') . ' AS l ON l.language=a.lang_code AND l.home=1 AND l.published=1 AND l.language <> ' . $db->quote('*'))
            ->innerJoin($db->quoteName('#__extensions') . ' AS e ON e.element = a.lang_code')
            ->where('e.client_id = 0')
            ->where('e.enabled = 1')
            ->where('e.state = 0');

        $db->setQuery($query);

        try {
            $languages = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            $languages = [];

            if ($this->getCurrentUser()->authorise('core.admin')) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        // Merge any additional options in the XML definition.
        return array_merge(parent::getOptions(), $languages);
    }
}

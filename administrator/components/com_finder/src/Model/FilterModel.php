<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Component\Finder\Administrator\Table\FilterTable;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Filter model class for Finder.
 *
 * @since  2.5
 */
class FilterModel extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  2.5
     */
    protected $text_prefix = 'COM_FINDER';

    /**
     * Model context string.
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'com_finder.filter';

    /**
     * Custom clean cache method.
     *
     * @param   string   $group     The component name. [optional]
     * @param   integer  $clientId  No longer used, will be removed without replacement
     *                              @deprecated   4.3 will be removed in 6.0
     *
     * @return  void
     *
     * @since   2.5
     */
    protected function cleanCache($group = 'com_finder', $clientId = 0)
    {
        parent::cleanCache($group);
    }

    /**
     * Method to get the filter data.
     *
     * @return  FilterTable|boolean  The filter data or false on a failure.
     *
     * @since   2.5
     */
    public function getFilter()
    {
        $filter_id = (int) $this->getState('filter.id');

        // Get a FinderTableFilter instance.
        $filter = $this->getTable();

        // Attempt to load the row.
        $return = $filter->load($filter_id);

        // Check for a database error.
        if ($return === false && $filter->getError()) {
            $this->setError($filter->getError());

            return false;
        }

        // Process the filter data.
        if (!empty($filter->data)) {
            $filter->data = explode(',', $filter->data);
        } elseif (empty($filter->data)) {
            $filter->data = [];
        }

        return $filter;
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form. [optional]
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not. [optional]
     *
     * @return  Form|boolean  A Form object on success, false on failure
     *
     * @since   2.5
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_finder.filter', 'filter', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   2.5
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_finder.edit.filter.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_finder.filter', $data);

        return $data;
    }

    /**
     * Method to get the total indexed items
     *
     * @return  integer  The count of indexed items
     *
     * @since  3.5
     */
    public function getTotal()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('MAX(link_id)')
            ->from('#__finder_links');

        return $db->setQuery($query)->loadResult();
    }

    /**
     * Method to save the form data.
     *
     * Overrides the parent to correctly handle the 'save2copy' task for Finder filters.
     *
     * @param   array   $data  The form data.
     *
     * @return  boolean        True on success, false on failure.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function save($data)
    {
        $app  = Factory::getApplication();
        $task = $app->getInput()->get('task', '', 'cmd');

        if ($task === 'save2copy') {
            $data['filter_id'] = 0;

            $title = trim((string) ($data['title'] ?? ''));
            $alias = trim((string) ($data['alias'] ?? ''));

            if ($alias === '') {
                $alias = OutputFilter::stringURLSafe($title);
            }

            // Generate unique title + alias
            list($title, $alias) = $this->generateNewTitleAndAlias($title, $alias);

            $data['title'] = $title;
            $data['alias'] = $alias;
        }

        return parent::save($data);
    }

    /**
     * Generate a new unique title and alias for a copied filter.
     * Follows the same logic as Joomla's core content models.
     *
     * @param   string  $title  The original title.
     * @param   string  $alias  The original alias.
     *
     * @return  array           Array with [newTitle, newAlias].
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function generateNewTitleAndAlias(string $title, string $alias): array
    {
        $db = $this->getDatabase();

        if (preg_match('/^(.*?)(?:\s\((\d+)\))?$/', $title, $matches)) {
            $baseTitle = trim($matches[1]);
        } else {
            $baseTitle = trim($title);
        }

        $baseAlias = trim($alias ?: OutputFilter::stringURLSafe($title));

        $likeTitle = $db->quote($db->escape($baseTitle, true) . '%', false);

        $query = $db->getQuery(true)
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__finder_filters'))
            ->where($db->quoteName('title') . ' LIKE ' . $likeTitle);

        $existingTitles = $db->setQuery($query)->loadColumn();

        $maxNum = 0;
        foreach ($existingTitles as $existing) {
            if (preg_match('/^\Q' . $baseTitle . '\E(?:\s\((\d+)\))?$/', $existing, $matches)) {
                $num = isset($matches[1]) ? (int) $matches[1] : 1;
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;

        $newTitle = $baseTitle;
        if ($nextNum > 1) {
            $newTitle .= ' (' . $nextNum . ')';
        }

        // Build a unique alias
        $newAlias = $this->getUniqueAlias($baseAlias);

        return [$newTitle, $newAlias];
    }

    /**
     * Ensure a unique alias in the table by incrementing with dash style.
     *
     *
     * @param   string  $base  The starting alias (already URL-safe).
     *
     * @return  string         A unique alias.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getUniqueAlias(string $base): string
    {
        $alias = $base !== '' ? $base : OutputFilter::stringURLSafe(uniqid('filter-', true));

        while ($this->aliasExists($alias)) {
            $alias = StringHelper::increment($alias, 'dash');
        }

        return $alias;
    }

    /**
     * Check whether an alias exists in the table.
     *
     * @param   string  $alias  The alias to test.
     *
     * @return  boolean         True if it exists, false otherwise.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function aliasExists(string $alias): bool
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__finder_filters'))
            ->where($db->quoteName('alias') . ' = :alias')
            ->bind(':alias', $alias);

        return (int) $db->setQuery($query)->loadResult() > 0;
    }
}

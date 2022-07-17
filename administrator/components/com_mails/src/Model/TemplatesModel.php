<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mails\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;

/**
 * Methods supporting a list of mail template records.
 *
 * @since  4.0.0
 */
class TemplatesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'template_id', 'a.template_id',
                'language', 'a.language',
                'subject', 'a.subject',
                'body', 'a.body',
                'htmlbody', 'a.htmlbody',
                'extension'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Load the parameters.
        $params = ComponentHelper::getParams('com_mails');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.template_id', 'asc');
    }

    /**
     * Get a list of mail templates
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getItems()
    {
        $items = parent::getItems();
        $id    = '';

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('language'))
            ->from($db->quoteName('#__mail_templates'))
            ->where($db->quoteName('template_id') . ' = :id')
            ->where($db->quoteName('language') . ' != ' . $db->quote(''))
            ->order($db->quoteName('language') . ' ASC')
            ->bind(':id', $id);

        foreach ($items as $item) {
            $id = $item->template_id;
            $db->setQuery($query);
            $item->languages = $db->loadColumn();
        }

        return $items;
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  QueryInterface
     *
     * @since   4.0.0
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                $db->quoteName('a') . '.*'
            )
        );
        $query->from($db->quoteName('#__mail_templates', 'a'))
            ->where($db->quoteName('a.language') . ' = ' . $db->quote(''));

        // Filter by search in title.
        if ($search = trim($this->getState('filter.search', ''))) {
            if (stripos($search, 'id:') === 0) {
                $search = substr($search, 3);
                $query->where($db->quoteName('a.template_id') . ' = :search')
                    ->bind(':search', $search);
            } else {
                $search = '%' . str_replace(' ', '%', $search) . '%';
                $query->where(
                    '(' . $db->quoteName('a.template_id') . ' LIKE :search1'
                    . ' OR ' . $db->quoteName('a.subject') . ' LIKE :search2'
                    . ' OR ' . $db->quoteName('a.body') . ' LIKE :search3'
                    . ' OR ' . $db->quoteName('a.htmlbody') . ' LIKE :search4)'
                )
                    ->bind([':search1', ':search2', ':search3', ':search4'], $search);
            }
        }

        // Filter on the extension.
        if ($extension = $this->getState('filter.extension')) {
            $query->where($db->quoteName('a.extension') . ' = :extension')
                ->bind(':extension', $extension);
        } else {
            // Only show mail template from enabled extensions
            $subQuery = $db->getQuery(true)
                ->select($db->quoteName('name'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('enabled') . ' = 1');

            $query->where($db->quoteName('a.extension') . ' IN(' . $subQuery . ')');
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->join(
                'INNER',
                $db->quoteName('#__mail_templates', 'b'),
                $db->quoteName('b.template_id') . ' = ' . $db->quoteName('a.template_id')
                . ' AND ' . $db->quoteName('b.language') . ' = :language'
            )
                ->bind(':language', $language);
        }

        // Add the list ordering clause
        $listOrdering  = $this->state->get('list.ordering', 'a.template_id');
        $orderDirn     = $this->state->get('list.direction', 'ASC');

        $query->order($db->escape($listOrdering) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    /**
     * Get list of extensions which are using mail templates
     *
     * @return array
     *
     * @since   4.0.0
     */
    public function getExtensions()
    {
        $db       = $this->getDatabase();
        $subQuery = $db->getQuery(true)
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('enabled') . ' = 1');

        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('extension'))
            ->from($db->quoteName('#__mail_templates'))
            ->where($db->quoteName('extension') . ' IN (' . $subQuery . ')');
        $db->setQuery($query);

        return $db->loadColumn();
    }

    /**
     * Get a list of the current content languages
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getLanguages()
    {
        return LanguageHelper::getContentLanguages(array(0,1));
    }
}

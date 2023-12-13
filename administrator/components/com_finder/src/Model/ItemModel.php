<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Index Item model class for Finder.
 *
 * @since  5.0.0
 */
class ItemModel extends BaseDatabaseModel
{
    /**
     * Stock method to auto-populate the model state.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    protected function populateState()
    {
        // Get the pk of the record from the request.
        $pk = Factory::getApplication()->getInput()->getInt('id');
        $this->setState('item.link_id', $pk);
    }

    /**
     * Get a finder link object
     *
     * @return  object
     *
     * @since   5.0.0
     */
    public function getItem()
    {
        $link_id = (int) $this->getState('item.link_id');
        $db      = $this->getDatabase();
        $query   = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__finder_links', 'l'))
            ->where($db->quoteName('l.link_id') . ' = :link_id')
            ->bind(':link_id', $link_id, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObject();
    }

    /**
     * Get terms associated with a finder link
     *
     * @return  object[]
     *
     * @since   5.0.0
     */
    public function getTerms()
    {
        $link_id = (int) $this->getState('item.link_id');
        $db      = $this->getDatabase();
        $query   = $db->getQuery(true)
            ->select('t.*, l.*')
            ->from($db->quoteName('#__finder_links_terms', 'l'))
            ->leftJoin($db->quoteName('#__finder_terms', 't') . ' ON ' . $db->quoteName('t.term_id') . ' = ' . $db->quoteName('l.term_id'))
            ->where($db->quoteName('l.link_id') . ' = :link_id')
            ->order('l.weight')
            ->bind(':link_id', $link_id, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Get taxonomies associated with a finder link
     *
     * @return  \stdClass[]
     *
     * @since   5.0.0
     */
    public function getTaxonomies()
    {
        $link_id = (int) $this->getState('item.link_id');
        $db      = $this->getDatabase();
        $query   = $db->getQuery(true)
            ->select('t.*, m.*')
            ->from($db->quoteName('#__finder_taxonomy_map', 'm'))
            ->leftJoin($db->quoteName('#__finder_taxonomy', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('m.node_id'))
            ->where($db->quoteName('m.link_id') . ' = :link_id')
            ->order('t.title')
            ->bind(':link_id', $link_id, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObjectList();
    }
}

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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Statistics model class for Finder.
 *
 * @since  2.5
 */
class StatisticsModel extends BaseDatabaseModel
{
    /**
     * Method to get the component statistics
     *
     * @return  CMSObject The component statistics
     *
     * @since   2.5
     */
    public function getData()
    {
        // Initialise
        $db    = $this->getDatabase();
        $query = $db->createQuery();
        $data  = new CMSObject();

        $query->select('COUNT(term_id)')
            ->from($db->quoteName('#__finder_terms'));
        $db->setQuery($query);
        $data->term_count = $db->loadResult();

        $query->clear()
            ->select('COUNT(link_id)')
            ->from($db->quoteName('#__finder_links'));
        $db->setQuery($query);
        $data->link_count = $db->loadResult();

        $query->clear()
            ->select('COUNT(id)')
            ->from($db->quoteName('#__finder_taxonomy'))
            ->where($db->quoteName('parent_id') . ' = 1');
        $db->setQuery($query);
        $data->taxonomy_branch_count = $db->loadResult();

        $query->clear()
            ->select('COUNT(id)')
            ->from($db->quoteName('#__finder_taxonomy'))
            ->where($db->quoteName('parent_id') . ' > 1');
        $db->setQuery($query);
        $data->taxonomy_node_count = $db->loadResult();

        $query->clear()
            ->select('t.title AS type_title, COUNT(a.link_id) AS link_count')
            ->from($db->quoteName('#__finder_links') . ' AS a')
            ->join('INNER', $db->quoteName('#__finder_types') . ' AS t ON t.id = a.type_id')
            ->group('a.type_id, t.title')
            ->order($db->quoteName('type_title') . ' ASC');
        $db->setQuery($query);
        $data->type_list = $db->loadObjectList();

        $lang    = Factory::getLanguage();
        $plugins = PluginHelper::getPlugin('finder');

        foreach ($plugins as $plugin) {
            $lang->load('plg_finder_' . $plugin->name . '.sys', JPATH_ADMINISTRATOR)
            || $lang->load('plg_finder_' . $plugin->name . '.sys', JPATH_PLUGINS . '/finder/' . $plugin->name);
        }

        return $data;
    }
}

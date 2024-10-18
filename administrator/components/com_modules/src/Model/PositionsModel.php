<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Model;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Modules Component Positions Model
 *
 * @since  1.6
 */
class PositionsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @see     \JController
     * @since   1.6
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'value',
                'templates',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'ordering', $direction = 'asc')
    {
        // Special case for the client id.
        $clientId = (int) $this->getUserStateFromRequest($this->context . '.client_id', 'client_id', 0, 'int');
        $clientId = (!\in_array((int) $clientId, [0, 1])) ? 0 : (int) $clientId;
        $this->setState('client_id', $clientId);

        // Load the parameters.
        $params = ComponentHelper::getParams('com_modules');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getItems()
    {
        if (!isset($this->items)) {
            $lang            = Factory::getLanguage();
            $search          = $this->getState('filter.search');
            $state           = $this->getState('filter.state');
            $clientId        = $this->getState('client_id');
            $filter_template = $this->getState('filter.template');
            $type            = $this->getState('filter.type');
            $ordering        = $this->getState('list.ordering');
            $direction       = $this->getState('list.direction');
            $limitstart      = $this->getState('list.start');
            $limit           = $this->getState('list.limit');
            $client          = ApplicationHelper::getClientInfo($clientId);

            if ($type != 'template') {
                $clientId = (int) $clientId;

                // Get the database object and a new query object.
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select('DISTINCT ' . $db->quoteName('position', 'value'))
                    ->from($db->quoteName('#__modules'))
                    ->where($db->quoteName('client_id') . ' = :clientid')
                    ->bind(':clientid', $clientId, ParameterType::INTEGER);

                if ($search) {
                    $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                    $query->where($db->quoteName('position') . ' LIKE :position')
                        ->bind(':position', $search);
                }

                $db->setQuery($query);

                try {
                    $positions = $db->loadObjectList('value');
                } catch (\RuntimeException $e) {
                    $this->setError($e->getMessage());

                    return false;
                }

                foreach ($positions as $value => $position) {
                    $positions[$value] = [];
                }
            } else {
                $positions = [];
            }

            // Load the positions from the installed templates.
            foreach (ModulesHelper::getTemplates($clientId) as $template) {
                $path = Path::clean($client->path . '/templates/' . $template->element . '/templateDetails.xml');

                if (file_exists($path)) {
                    $xml = simplexml_load_file($path);

                    if (isset($xml->positions[0])) {
                        $lang->load('tpl_' . $template->element . '.sys', $client->path)
                        || $lang->load('tpl_' . $template->element . '.sys', $client->path . '/templates/' . $template->element);

                        foreach ($xml->positions[0] as $position) {
                            $value = (string) $position['value'];
                            $label = (string) $position;

                            if (!$value) {
                                $value    = $label;
                                $label    = preg_replace('/[^a-zA-Z0-9_\-]/', '_', 'TPL_' . $template->element . '_POSITION_' . $value);
                                $altlabel = preg_replace('/[^a-zA-Z0-9_\-]/', '_', 'COM_MODULES_POSITION_' . $value);

                                if (!$lang->hasKey($label) && $lang->hasKey($altlabel)) {
                                    $label = $altlabel;
                                }
                            }

                            if ($type == 'user' || ($state != '' && $state != $template->enabled)) {
                                unset($positions[$value]);
                            } elseif (preg_match(\chr(1) . $search . \chr(1) . 'i', $value) && ($filter_template == '' || $filter_template == $template->element)) {
                                if (!isset($positions[$value])) {
                                    $positions[$value] = [];
                                }

                                $positions[$value][$template->name] = $label;
                            }
                        }
                    }
                }
            }

            $this->total = \count($positions);

            if ($limitstart >= $this->total) {
                $limitstart = $limitstart < $limit ? 0 : $limitstart - $limit;
                $this->setState('list.start', $limitstart);
            }

            if ($ordering == 'value') {
                if ($direction == 'asc') {
                    ksort($positions);
                } else {
                    krsort($positions);
                }
            } else {
                if ($direction == 'asc') {
                    asort($positions);
                } else {
                    arsort($positions);
                }
            }

            $this->items = \array_slice($positions, $limitstart, $limit ?: null);
        }

        return $this->items;
    }

    /**
     * Method to get the total number of items.
     *
     * @return  integer  The total number of items.
     *
     * @since   1.6
     */
    public function getTotal()
    {
        if (!isset($this->total)) {
            $this->getItems();
        }

        return $this->total;
    }
}

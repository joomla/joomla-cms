<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latestactions
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\LatestActions\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_latestactions
 *
 * @since  3.9.0
 */
class LatestActionsHelper
{
    /**
     * Get a list of logged actions.
     *
     * @param   Registry  &$params  The module parameters.
     *
     * @return  mixed  An array of action logs, or false on error.
     *
     * @since   5.1.0
     *
     * @throws  \Exception
     */
    public function getActions(&$params)
    {
        /** @var \Joomla\Component\Actionlogs\Administrator\Model\ActionlogsModel $model */
        $model = Factory::getApplication()->bootComponent('com_actionlogs')->getMVCFactory()
            ->createModel('Actionlogs', 'Administrator', ['ignore_request' => true]);

        // Set the Start and Limit
        $model->setState('list.start', 0);
        $model->setState('list.limit', $params->get('count', 5));
        $model->setState('list.ordering', 'a.id');
        $model->setState('list.direction', 'DESC');

        $rows = $model->getItems();

        // Load all actionlog plugins language files
        ActionlogsHelper::loadActionLogPluginsLanguage();

        foreach ($rows as $row) {
            $row->message = ActionlogsHelper::getHumanReadableLogMessage($row);
        }

        return $rows;
    }

    /**
     * Get the alternate title for the module
     *
     * @param   Registry  $params  The module parameters.
     *
     * @return  string    The alternate title for the module.
     *
     * @since   5.1.0
     */
    public function getModuleTitle($params)
    {
        return Text::plural('MOD_LATESTACTIONS_TITLE', $params->get('count', 5));
    }

    /**
     * Get the alternate title for the module
     *
     * @param   Registry  $params  The module parameters.
     *
     * @return  string    The alternate title for the module.
     *
     * @since   3.9.1
     *
     * @deprecated 5.1.0 will be removed in 7.0
     *             Use the non-static method getModuleTitle
     *             Example: Factory::getApplication()->bootModule('mod_latestactions', 'administrator')
     *                          ->getHelper('LatestActionsHelper')
     *                          ->getModuleTitle($params)
     */
    public static function getTitle($params)
    {
        return (new self())->getModuleTitle($params);
    }

    /**
     * Get a list of logged actions.
     *
     * @param   Registry  &$params  The module parameters.
     *
     * @return  mixed  An array of action logs, or false on error.
     *
     * @since   3.9.1
     *
     * @throws  \Exception
     *
     * @deprecated 5.1.0 will be removed in 7.0
     *             Use the non-static method getActions
     *             Example: Factory::getApplication()->bootModule('mod_latestactions', 'administrator')
     *                          ->getHelper('LatestActionsHelper')
     *                          ->getActions($params)
     */
    public static function getList(&$params)
    {
        return (new self())->getActions($params);
    }
}

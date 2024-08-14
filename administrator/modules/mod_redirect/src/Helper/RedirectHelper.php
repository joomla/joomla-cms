<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_redirect
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Redirect\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_redirect
 *
 * @since __DEPLOY_VERSION__
 */
class RedirectHelper
{
    /**
     * Get a list of redirect links.
     *
     * @param   Registry  &$params  The module parameters.
     *
     * @return  mixed  An array of redirect links, or false on error.
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public function getRedirectLinks(Registry &$params)
    {
        /** @var \Joomla\Component\Redirect\Administrator\Model\LinksModel $model */
        $model = Factory::getApplication()->bootComponent('com_redirect')->getMVCFactory()
            ->createModel('Links', 'Administrator', ['ignore_request' => true]);

        // Set the Start and Limit
        $model->setState('list.start', 0);
        $model->setState('list.limit', $params->get('count', 5));

        // Set the state and http_status
        $model->setState('filter.state', $params->get('state', '0'));
        $model->setState('filter.http_status', $params->get('http_status', '301'));

        // Get the ordering column and order
        list($ordering, $direction) = explode(' ', $params->get('ordering', 'a.hits DESC'));
        $model->setState('list.ordering', $ordering);
        $model->setState('list.direction', $direction);

        return $model->getItems();
    }
}

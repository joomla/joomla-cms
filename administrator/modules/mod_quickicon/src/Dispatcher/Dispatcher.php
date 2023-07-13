<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Quickicon\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_quickicon
 *
 * @since  4.0.0
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $helper          = $this->app->bootModule('mod_quickicon', 'administrator')->getHelper('QuickIconHelper');
        $data['buttons'] = $helper->getButtons($data['params'], $this->getApplication());

        return $data;
    }
}

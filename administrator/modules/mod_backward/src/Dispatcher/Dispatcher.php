<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_backward
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Backward\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_backward
 *
 * @since  5.4.0
 */
class Dispatcher extends AbstractModuleDispatcher
{

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.4.0
     */
    protected function getLayoutData()
    {
        $data            = parent::getLayoutData();
        $data['compat']  = PluginHelper::isEnabled('behaviour', 'compat');
        $data['compat6'] = PluginHelper::isEnabled('behaviour', 'compat6');

        return $data;
    }
}

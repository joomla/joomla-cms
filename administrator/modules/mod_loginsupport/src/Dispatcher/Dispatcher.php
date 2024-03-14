<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_loginsupport
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Loginsupport\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_loginsupport
 *
 * @since  5.1.0
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        if ($data['params']->get('automatic_title')) {
            $this->module->title = Text::_('MOD_LOGINSUPPORT_TITLE');
        }

        return $data;
    }
}

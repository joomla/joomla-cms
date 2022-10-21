<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for a Joomla Module Dispatcher
 *
 * Executes the single entry file of a module.
 *
 * @since  4.0.0
 */
class ModuleDispatcher extends AbstractModuleDispatcher
{
    /**
     * Dispatches the dispatcher.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function dispatch()
    {
        $path = JPATH_BASE . '/modules/' . $this->module->module . '/' . $this->module->module . '.php';

        if (!is_file($path)) {
            return;
        }

        $this->loadLanguage();

        // Execute the layout without the module context
        $loader = static function ($path, array $displayData) {
            // If $displayData doesn't exist in extracted data, unset the variable.
            if (!\array_key_exists('displayData', $displayData)) {
                extract($displayData);
                unset($displayData);
            } else {
                extract($displayData);
            }

            include $path;
        };

        $loader($path, $this->getLayoutData());
    }
}

<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Renderer\Html;

use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Layout\LayoutHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML document renderer for a module position
 *
 * @since  3.5
 */
class ModulesRenderer extends DocumentRenderer
{
    /**
     * Renders multiple modules script and returns the results as a string
     *
     * @param   string  $position  The position of the modules to render
     * @param   array   $params    Associative array of values
     * @param   string  $content   Module content
     *
     * @return  string  The output of the script
     *
     * @since   3.5
     */
    public function render($position, $params = [], $content = null)
    {
        $renderer = $this->_doc->loadRenderer('module');
        $buffer   = '';

        $app          = Factory::getApplication();
        $user         = Factory::getUser();
        $frontediting = ($app->isClient('site') && $app->get('frontediting', 1) && !$user->guest);
        $menusEditing = ($app->get('frontediting', 1) == 2) && $user->authorise('core.edit', 'com_menus');

        foreach (ModuleHelper::getModules($position) as $mod) {
            $moduleHtml = $renderer->render($mod, $params, $content);

            if ($frontediting && trim($moduleHtml) != '' && $user->authorise('module.edit.frontend', 'com_modules.module.' . $mod->id)) {
                $displayData = ['moduleHtml' => &$moduleHtml, 'module' => $mod, 'position' => $position, 'menusediting' => $menusEditing];
                LayoutHelper::render('joomla.edit.frontediting_modules', $displayData);
            }

            $buffer .= $moduleHtml;
        }

        $app->triggerEvent('onAfterRenderModules', [&$buffer, &$params]);

        return $buffer;
    }
}

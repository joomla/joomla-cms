<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Layout;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Functions to execute rendering a FileLayout.
 *
 * @since  __DEPLOY_VERSION__
 */
trait LayoutRendererTrait
{
    /**
     * Implementing classes have to provide layout include paths. If empty, then the default ones are
     * used from the FileLayout class.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    abstract protected function getLayoutPaths();

    /**
     * Render the given layout with the data.
     *
     * @param   string  $layout       The layout
     * @param   array   $displayData  The layout data
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function render(string $layout, array $displayData = []): string
    {
        // Check if the layout has a default one
        if (strpos($layout, ':') !== false) {
            // Get the template and file name from the string
            $temp          = explode(':', $layout);
            $layout        = $temp[1];
        }

        return $this->getRenderer($layout)->render($displayData);
    }

    /**
     * Get the renderer.
     *
     * @param   string  $layout  Id to load
     *
     * @return  FileLayout
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getRenderer($layout = 'default'): FileLayout
    {
        $renderer = new FileLayout($layout);

        $renderer->setDebug($this->isDebugEnabled());

        $layoutPaths = $this->getLayoutPaths();

        if ($layoutPaths) {
            $renderer->setIncludePaths($layoutPaths);
        }

        return $renderer;
    }

    /**
     * Is debug enabled.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function isDebugEnabled()
    {
        return false;
    }
}

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
 * Defines the trait for a LayoutFactoryInterface Aware Class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait LayoutRendererTrait
{
    /**
     * Render the given layout with the data.
     *
     * @param   string  $layout      The layout
     * @param   array   $layoutData  The layout data
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function render(string $layout, array $layoutData): string
    {
        return $this->getRenderer($layout)->render($layoutData);
    }

    /**
     * Get the renderer.
     *
     * @param   string  $layoutId  Id to load
     *
     * @return  FileLayout
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getRenderer($layoutId = 'default'): LayoutInterface
    {
        $renderer = new FileLayout($layoutId);

        $renderer->setDebug($this->isDebugEnabled());

        $layoutPaths = $this->getLayoutPaths();

        if ($layoutPaths) {
            $renderer->setIncludePaths($layoutPaths);
        }

        return $renderer;
    }

    /**
     * Allow to override renderer include paths in child fields.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getLayoutPaths()
    {
        return [];
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

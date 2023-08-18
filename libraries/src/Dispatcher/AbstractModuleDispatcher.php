<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Dispatcher;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Layout\LayoutRendererTrait;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for a Joomla Module Dispatcher.
 *
 * @since  4.0.0
 */
abstract class AbstractModuleDispatcher extends Dispatcher
{
    use LayoutRendererTrait;

    /**
     * The module instance
     *
     * @var    \stdClass
     * @since  4.0.0
     */
    protected $module;

    /**
     * Constructor for Dispatcher
     *
     * @param   \stdClass                $module  The module
     * @param   CMSApplicationInterface  $app     The application instance
     * @param   Input                    $input   The input instance
     *
     * @since   4.0.0
     */
    public function __construct(\stdClass $module, CMSApplicationInterface $app, Input $input)
    {
        parent::__construct($app, $input);

        $this->module = $module;
    }

    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function dispatch()
    {
        $this->loadLanguage();

        $displayData = $this->getLayoutData();

        // Stop when display data is false
        if ($displayData === false) {
            return;
        }

        echo $this->render($displayData['params']->get('layout', 'default'), $this->getLayoutData());
    }

    /**
     * Returns the layout data. This function can be overridden by subclasses to add more
     * attributes for the layout.
     *
     * If false is returned, then it means that the dispatch process should be stopped.
     *
     * @return  array|false
     *
     * @since   4.0.0
     */
    protected function getLayoutData()
    {
        return [
            'module'   => $this->module,
            'app'      => $this->app,
            'input'    => $this->input,
            'params'   => new Registry($this->module->params),
            'template' => $this->app->getTemplate(),
        ];
    }

    /**
     * Allow to override renderer include paths in extending classes.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getLayoutPaths(): array
    {
        $app = $this->getApplication();

        $templateObj = $app instanceof CMSWebApplicationInterface ? $app->getTemplate(true) : (object) [ 'template' => '', 'parent' => ''];

        // Build the template and base path for the layout
        $layoutPaths = [];

        if ($templateObj->template) {
            $layoutPaths[] = JPATH_THEMES . '/' . $templateObj->template . '/html' . $this->module->module;
        }

        if ($templateObj->parent) {
            $layoutPaths[] = JPATH_THEMES . '/' . $templateObj->parent . '/html' . $this->module->module;
        }

        $layoutPaths[] =  JPATH_BASE . '/modules/' . $this->module->module . '/tmpl';

        return $layoutPaths;
    }

    /**
     * Load the language.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function loadLanguage()
    {
        $language = $this->app->getLanguage();

        $coreLanguageDirectory      = JPATH_BASE;
        $extensionLanguageDirectory = JPATH_BASE . '/modules/' . $this->module->module;

        $langPaths = $language->getPaths();

        // Only load the module's language file if it hasn't been already
        if (!$langPaths || (!isset($langPaths[$coreLanguageDirectory]) && !isset($langPaths[$extensionLanguageDirectory]))) {
            // 1.5 or Core then 1.6 3PD
            $language->load($this->module->module, $coreLanguageDirectory) ||
            $language->load($this->module->module, $extensionLanguageDirectory);
        }
    }
}

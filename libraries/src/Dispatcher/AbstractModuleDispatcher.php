<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Dispatcher;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Document\DocumentAwareTrait;
use Joomla\CMS\Language\LanguageAwareTrait;
use Joomla\CMS\Layout\FileLayout;
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
    use LanguageAwareTrait;
    use DocumentAwareTrait;

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

        $renderer = new FileLayout($displayData['params']->get('layout', 'default'));

        try {
            $renderer->setLanguage($this->getLanguage());
        } catch (\UnexpectedValueException $e) {
            $renderer->setLanguage($this->getApplication()->getLanguage());
        }

        try {
            $renderer->setDocument($this->getDocument());
        } catch (\UnexpectedValueException $e) {
            $renderer->setDocument($this->getApplication()->getDocument());
        }

        $layout        = $renderer->getLayoutId();
        $templateObj   = $this->getApplication()->getTemplate(true);
        $defaultLayout = $layout;
        $template      = $templateObj->template;

        if (strpos($layout, ':') !== false) {
            // Get the template and file name from the string
            $temp          = explode(':', $layout);
            $template      = $temp[0] === '_' ? $template : $temp[0];
            $layout        = $temp[1];
            $defaultLayout = $temp[1] ?: 'default';
        }

        $renderer->addIncludePaths([
            JPATH_THEMES . '/' . $template . '/html/' . $this->module->module,
            JPATH_THEMES . '/' . $templateObj->parent . '/html/' . $this->module->module,
        ]);

        $content = $renderer->render($displayData);

        // Render the default layout
        if (!$content) {
            $content = $renderer->setLayoutId($defaultLayout)->render($displayData);
        }

        // Render default
        if (!$content) {
            $content = $renderer->setLayoutId('default')->render($displayData);
        }

        return $content;
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

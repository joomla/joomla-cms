<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Controller;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Finder\BeforeIndexEvent;
use Joomla\CMS\Event\Finder\BuildIndexEvent;
use Joomla\CMS\Event\Finder\StartIndexEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\DebugAdapter;
use Joomla\Component\Finder\Administrator\Indexer\DebugIndexer;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Response\Response;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Indexer controller class for Finder.
 *
 * @since  2.5
 */
class IndexerController extends BaseController
{
    /**
     * Method to start the indexer.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function start()
    {
        // Check for a valid token. If invalid, send a 403 with the error message.
        if (!Session::checkToken('request')) {
            static::sendResponse(new \Exception(Text::_('JINVALID_TOKEN_NOTICE'), 403));

            return;
        }

        $params     = ComponentHelper::getParams('com_finder');
        $dispatcher = $this->getDispatcher();

        if ($params->get('enable_logging', '0')) {
            $options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
            $options['text_file'] = 'indexer.php';
            Log::addLogger($options);
        }

        // Log the start
        try {
            Log::add('Starting the indexer', Log::INFO);
        } catch (\RuntimeException $exception) {
            // Informational log only
        }

        // We don't want this form to be cached.
        $this->app->allowCache(false);

        // Put in a buffer to silence noise.
        ob_start();

        // Reset the indexer state.
        Indexer::resetState();

        // Import the finder plugins.
        PluginHelper::importPlugin('finder', null, true, $dispatcher);

        // Add the indexer language to \JS
        Text::script('COM_FINDER_AN_ERROR_HAS_OCCURRED');
        Text::script('COM_FINDER_NO_ERROR_RETURNED');

        // Start the indexer.
        try {
            // Trigger the onStartIndex event.
            $dispatcher->dispatch('onStartIndex', new StartIndexEvent('onStartIndex', []));

            // Get the indexer state.
            $state        = Indexer::getState();
            $state->start = 1;

            $output = ob_get_contents();

            // Finder plugins should not create output of any kind. If there is output, that very likely is the result of a PHP error.
            if (trim($output)) {
                throw new \Exception(Text::_('COM_FINDER_AN_ERROR_HAS_OCCURRED'));
            }

            // Send the response.
            static::sendResponse($state);
        } catch (\Exception $e) {
            // Catch an exception and return the response.
            static::sendResponse($e);
        }
    }

    /**
     * Method to run the next batch of content through the indexer.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function batch()
    {
        // Check for a valid token. If invalid, send a 403 with the error message.
        if (!Session::checkToken('request')) {
            static::sendResponse(new \Exception(Text::_('JINVALID_TOKEN_NOTICE'), 403));

            return;
        }

        $params     = ComponentHelper::getParams('com_finder');
        $dispatcher = $this->getDispatcher();

        if ($params->get('enable_logging', '0')) {
            $options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
            $options['text_file'] = 'indexer.php';
            Log::addLogger($options);
        }

        // Log the start
        try {
            Log::add('Starting the indexer batch process', Log::INFO);
        } catch (\RuntimeException $exception) {
            // Informational log only
        }

        // We don't want this form to be cached.
        $this->app->allowCache(false);

        // Put in a buffer to silence noise.
        ob_start();

        // Remove the script time limit.
        if (\function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        // Get the indexer state.
        $state = Indexer::getState();

        // Reset the batch offset.
        $state->batchOffset = 0;

        // Update the indexer state.
        Indexer::setState($state);

        // Import the finder plugins.
        PluginHelper::importPlugin('finder', null, true, $dispatcher);

        /*
         * We are going to swap out the raw document object with an HTML document
         * in order to work around some plugins that don't do proper environment
         * checks before trying to use HTML document functions.
         */
        $lang = $this->app->getLanguage();

        // Get the document properties.
        $attributes = [
            'charset'   => 'utf-8',
            'lineend'   => 'unix',
            'tab'       => '  ',
            'language'  => $lang->getTag(),
            'direction' => $lang->isRtl() ? 'rtl' : 'ltr',
        ];

        // Start the indexer.
        try {
            // Trigger the onBeforeIndex event.
            $dispatcher->dispatch('onBeforeIndex', new BeforeIndexEvent('onBeforeIndex', []));

            // Trigger the onBuildIndex event.
            $dispatcher->dispatch('onBuildIndex', new BuildIndexEvent('onBuildIndex', []));

            // Get the indexer state.
            $state           = Indexer::getState();
            $state->start    = 0;
            $state->complete = 0;

            // Log batch completion and memory high-water mark.
            try {
                Log::add('Batch completed, peak memory usage: ' . number_format(memory_get_peak_usage(true)) . ' bytes', Log::INFO);
            } catch (\RuntimeException $exception) {
                // Informational log only
            }

            $output = ob_get_contents();

            // Finder plugins should not create output of any kind. If there is output, that very likely is the result of a PHP error.
            if (trim($output)) {
                throw new \Exception(Text::_('COM_FINDER_INDEXER_ERROR_PLUGIN_FAILURE'));
            }

            // Send the response.
            static::sendResponse($state);
        } catch (\Exception $e) {
            // Catch an exception and return the response.
            // Send the response.
            static::sendResponse($e);
        }
    }

    /**
     * Method to optimize the index and perform any necessary cleanup.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function optimize()
    {
        // Check for a valid token. If invalid, send a 403 with the error message.
        if (!Session::checkToken('request')) {
            static::sendResponse(new \Exception(Text::_('JINVALID_TOKEN_NOTICE'), 403));

            return;
        }

        // We don't want this form to be cached.
        $this->app->allowCache(false);

        // Put in a buffer to silence noise.
        ob_start();

        // Import the finder plugins.
        PluginHelper::importPlugin('finder', null, true, $this->getDispatcher());

        try {
            // Optimize the index
            $indexer = new Indexer();
            $indexer->optimize();

            // Get the indexer state.
            $state           = Indexer::getState();
            $state->start    = 0;
            $state->complete = 1;

            $output = ob_get_contents();

            // Finder plugins should not create output of any kind. If there is output, that very likely is the result of a PHP error.
            if (trim($output)) {
                throw new \Exception(Text::_('COM_FINDER_AN_ERROR_HAS_OCCURRED'));
            }

            // Send the response.
            static::sendResponse($state);
        } catch (\Exception $e) {
            // Catch an exception and return the response.
            static::sendResponse($e);
        }
    }

    /**
     * Method to handle a send a \JSON response. The body parameter
     * can be an \Exception object for when an error has occurred or
     * a CMSObject for a good response.
     *
     * @param   \Joomla\CMS\Object\CMSObject|\Exception  $data  CMSObject on success, \Exception on error. [optional]
     *
     * @return  void
     *
     * @since   2.5
     */
    public static function sendResponse($data = null)
    {
        $app = Factory::getApplication();

        $params = ComponentHelper::getParams('com_finder');

        if ($params->get('enable_logging', '0')) {
            $options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
            $options['text_file'] = 'indexer.php';
            Log::addLogger($options);
        }

        // Send the assigned error code if we are catching an exception.
        if ($data instanceof \Exception) {
            try {
                Log::add($data->getMessage(), Log::ERROR);
            } catch (\RuntimeException $exception) {
                // Informational log only
            }

            $app->setHeader('status', $data->getCode());
        }

        // Create the response object.
        $response = new Response($data);

        if (\JDEBUG) {
            // Add the buffer and memory usage
            $response->buffer = ob_get_contents();
            $response->memory = memory_get_usage(true);
        }
        ob_clean();

        // Send the JSON response.
        echo json_encode($response);
    }

    /**
     * Method to call a specific indexing plugin and return debug info
     *
     * @return  void
     *
     * @since   5.0.0
     * @internal
     */
    public function debug()
    {
        // Check for a valid token. If invalid, send a 403 with the error message.
        if (!Session::checkToken('request')) {
            static::sendResponse(new \Exception(Text::_('JINVALID_TOKEN_NOTICE'), 403));

            return;
        }

        // We don't want this form to be cached.
        $this->app->allowCache(false);

        // Put in a buffer to silence noise.
        ob_start();

        // Remove the script time limit.
        @set_time_limit(0);

        // Get the indexer state.
        Indexer::resetState();
        $state = Indexer::getState();

        // Reset the batch offset.
        $state->batchOffset = 0;

        // Update the indexer state.
        Indexer::setState($state);

        // Start the indexer.
        try {
            // Import the finder plugins.
            class_alias(DebugAdapter::class, Adapter::class);
            $plugin = $this->app->bootPlugin($this->app->getInput()->get('plugin'), 'finder');
            $plugin->setIndexer(new DebugIndexer());
            $plugin->debug($this->app->getInput()->get('id'));

            $output = '';

            // Create list of attributes
            $output .= '<fieldset><legend>' . Text::_('COM_FINDER_INDEXER_FIELDSET_ATTRIBUTES') . '</legend>';
            $output .= '<dl class="row">';

            foreach (DebugIndexer::$item as $key => $value) {
                $output .= '<dt class="col-sm-2">' . $key . '</dt><dd class="col-sm-10">' . $value . '</dd>';
            }

            $output .= '</dl>';
            $output .= '</fieldset>';

            $output .= '<fieldset><legend>' . Text::_('COM_FINDER_INDEXER_FIELDSET_ELEMENTS') . '</legend>';
            $output .= '<dl class="row">';

            foreach (DebugIndexer::$item->getElements() as $key => $element) {
                $output .= '<dt class="col-sm-2">' . $key . '</dt><dd class="col-sm-10">' . $element . '</dd>';
            }

            $output .= '</dl>';
            $output .= '</fieldset>';

            $output .= '<fieldset><legend>' . Text::_('COM_FINDER_INDEXER_FIELDSET_INSTRUCTIONS') . '</legend>';
            $output .= '<dl class="row">';
            $contexts = [
                1 => 'Title context',
                2 => 'Text context',
                3 => 'Meta context',
                4 => 'Path context',
                5 => 'Misc context',
            ];

            foreach (DebugIndexer::$item->getInstructions() as $key => $element) {
                $output .= '<dt class="col-sm-2">' . $contexts[$key] . '</dt><dd class="col-sm-10">' . json_encode($element) . '</dd>';
            }

            $output .= '</dl>';
            $output .= '</fieldset>';

            $output .= '<fieldset><legend>' . Text::_('COM_FINDER_INDEXER_FIELDSET_TAXONOMIES') . '</legend>';
            $output .= '<dl class="row">';

            foreach (DebugIndexer::$item->getTaxonomy() as $key => $element) {
                $output .= '<dt class="col-sm-2">' . $key . '</dt><dd class="col-sm-10">' . json_encode($element) . '</dd>';
            }

            $output .= '</dl>';
            $output .= '</fieldset>';

            // Get the indexer state.
            $state           = Indexer::getState();
            $state->start    = 0;
            $state->complete = 0;
            $state->rendered = $output;

            echo json_encode($state);
        } catch (\Exception $e) {
            // Catch an exception and return the response.
            // Send the response.
            static::sendResponse($e);
        }
    }
}

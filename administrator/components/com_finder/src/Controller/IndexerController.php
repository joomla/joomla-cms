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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
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

        $params = ComponentHelper::getParams('com_finder');

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
        PluginHelper::importPlugin('finder');

        // Add the indexer language to \JS
        Text::script('COM_FINDER_AN_ERROR_HAS_OCCURRED');
        Text::script('COM_FINDER_NO_ERROR_RETURNED');

        // Start the indexer.
        try {
            // Trigger the onStartIndex event.
            $this->app->triggerEvent('onStartIndex');

            // Get the indexer state.
            $state        = Indexer::getState();
            $state->start = 1;

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

        $params = ComponentHelper::getParams('com_finder');

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
        @set_time_limit(0);

        // Get the indexer state.
        $state = Indexer::getState();

        // Reset the batch offset.
        $state->batchOffset = 0;

        // Update the indexer state.
        Indexer::setState($state);

        // Import the finder plugins.
        PluginHelper::importPlugin('finder');

        /*
         * We are going to swap out the raw document object with an HTML document
         * in order to work around some plugins that don't do proper environment
         * checks before trying to use HTML document functions.
         */
        $lang = Factory::getLanguage();

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
            $this->app->triggerEvent('onBeforeIndex');

            // Trigger the onBuildIndex event.
            $this->app->triggerEvent('onBuildIndex');

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
        PluginHelper::importPlugin('finder');

        try {
            // Optimize the index
            $indexer = new Indexer();
            $indexer->optimize();

            // Get the indexer state.
            $state           = Indexer::getState();
            $state->start    = 0;
            $state->complete = 1;

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

        // Send the JSON response.
        echo json_encode($response);
    }
}

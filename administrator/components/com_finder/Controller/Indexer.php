<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Finder\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Controller\Controller;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;

// Register dependent classes.
\JLoader::register('FinderIndexer', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/indexer.php');

/**
 * Indexer controller class for Finder.
 *
 * @since  2.5
 */
class Indexer extends Controller
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
		$params = ComponentHelper::getParams('com_finder');

		if ($params->get('enable_logging', '0'))
		{
			$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
			$options['text_file'] = 'indexer.php';
			Log::addLogger($options);
		}

		// Log the start
		try
		{
			Log::add('Starting the indexer', Log::INFO);
		}
		catch (\RuntimeException $exception)
		{
			// Informational log only
		}

		// We don't want this form to be cached.
		$this->app->allowCache(false);

		// Check for a valid token. If invalid, send a 403 with the error message.
		Session::checkToken('request') or $this->sendResponse(new \Exception(\JText::_('JINVALID_TOKEN'), 403));

		// Put in a buffer to silence noise.
		ob_start();

		// Reset the indexer state.
		\FinderIndexer::resetState();

		// Import the finder plugins.
		PluginHelper::importPlugin('finder');

		// Add the indexer language to \JS
		\JText::script('COM_FINDER_AN_ERROR_HAS_OCCURRED');
		\JText::script('COM_FINDER_NO_ERROR_RETURNED');

		// Start the indexer.
		try
		{
			// Trigger the onStartIndex event.
			$this->app->triggerEvent('onStartIndex');

			// Get the indexer state.
			$state = \FinderIndexer::getState();
			$state->start = 1;

			// Send the response.
			$this->sendResponse($state);
		}
		// Catch an exception and return the response.
		catch (\Exception $e)
		{
			$this->sendResponse($e);
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
		$params = ComponentHelper::getParams('com_finder');

		if ($params->get('enable_logging', '0'))
		{
			$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
			$options['text_file'] = 'indexer.php';
			Log::addLogger($options);
		}

		// Log the start
		try
		{
			Log::add('Starting the indexer batch process', Log::INFO);
		}
		catch (\RuntimeException $exception)
		{
			// Informational log only
		}

		// We don't want this form to be cached.
		$this->app->allowCache(false);

		// Check for a valid token. If invalid, send a 403 with the error message.
		Session::checkToken('request') or $this->sendResponse(new \Exception(\JText::_('JINVALID_TOKEN'), 403));

		// Put in a buffer to silence noise.
		ob_start();

		// Remove the script time limit.
		@set_time_limit(0);

		// Get the indexer state.
		$state = \FinderIndexer::getState();

		// Reset the batch offset.
		$state->batchOffset = 0;

		// Update the indexer state.
		\FinderIndexer::setState($state);

		// Import the finder plugins.
		PluginHelper::importPlugin('finder');

		/*
		 * We are going to swap out the raw document object with an HTML document
		 * in order to work around some plugins that don't do proper environment
		 * checks before trying to use HTML document functions.
		 */
		$raw = clone Factory::getDocument();
		$lang = Factory::getLanguage();

		// Get the document properties.
		$attributes = array (
			'charset'   => 'utf-8',
			'lineend'   => 'unix',
			'tab'       => '  ',
			'language'  => $lang->getTag(),
			'direction' => $lang->isRtl() ? 'rtl' : 'ltr'
		);

		// Get the HTML document.
		$html = \JDocument::getInstance('html', $attributes);
		$doc = Factory::getDocument();

		// Swap the documents.
		$doc = $html;

		// Get the admin application.
		$admin = clone Factory::getApplication();

		// Get the site app.
		$site = CMSApplication::getInstance('site');

		// Swap the app.
		$app = Factory::getApplication();
		$app = $site;

		// Start the indexer.
		try
		{
			// Trigger the onBeforeIndex event.
			Factory::getApplication()->triggerEvent('onBeforeIndex');

			// Trigger the onBuildIndex event.
			Factory::getApplication()->triggerEvent('onBuildIndex');

			// Get the indexer state.
			$state = \FinderIndexer::getState();
			$state->start = 0;
			$state->complete = 0;

			// Swap the documents back.
			$doc = $raw;

			// Swap the applications back.
			$app = $admin;

			// Log batch completion and memory high-water mark.
			try
			{
				Log::add('Batch completed, peak memory usage: ' . number_format(memory_get_peak_usage(true)) . ' bytes', Log::INFO);
			}
			catch (\RuntimeException $exception)
			{
				// Informational log only
			}

			// Send the response.
			$this->sendResponse($state);
		}
		// Catch an exception and return the response.
		catch (\Exception $e)
		{
			// Swap the documents back.
			$doc = $raw;

			// Send the response.
			$this->sendResponse($e);
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
		// We don't want this form to be cached.
		$this->app->allowCache(false);

		// Check for a valid token. If invalid, send a 403 with the error message.
		Session::checkToken('request') or $this->sendResponse(new \Exception(\JText::_('JINVALID_TOKEN'), 403));

		// Put in a buffer to silence noise.
		ob_start();

		// Import the finder plugins.
		PluginHelper::importPlugin('finder');

		try
		{
			// Optimize the index
			\FinderIndexer::getInstance()->optimize();

			// Get the indexer state.
			$state = \FinderIndexer::getState();
			$state->start = 0;
			$state->complete = 1;

			// Send the response.
			$this->sendResponse($state);
		}
		// Catch an exception and return the response.
		catch (\Exception $e)
		{
			$this->sendResponse($e);
		}
	}

	/**
	 * Method to handle a send a \JSON response. The body parameter
	 * can be an \Exception object for when an error has occurred or
	 * a \JObject for a good response.
	 *
	 * @param   mixed  $data  \JObject on success, \Exception on error. [optional]
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function sendResponse($data = null)
	{
		$app = Factory::getApplication();

		$params = ComponentHelper::getParams('com_finder');

		if ($params->get('enable_logging', '0'))
		{
			$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
			$options['text_file'] = 'indexer.php';
			Log::addLogger($options);
		}

		// Send the assigned error code if we are catching an exception.
		if ($data instanceof \Exception)
		{
			try
			{
				Log::add($data->getMessage(), Log::ERROR);
			}
			catch (\RuntimeException $exception)
			{
				// Informational log only
			}

			$app->setHeader('status', $data->getCode());
		}

		// Create the response object.
		$response = new FinderIndexerResponse($data);

		// Add the buffer.
		$response->buffer = \JDEBUG ? ob_get_contents() : ob_end_clean();

		// Send the JSON response.
		echo json_encode($response);
	}
}

/**
 * Finder Indexer JSON Response Class
 *
 * @since  2.5
 */
class FinderIndexerResponse
{
	/**
	 * Class Constructor
	 *
	 * @param   mixed  $state  The processing state for the indexer
	 *
	 * @since   2.5
	 */
	public function __construct($state)
	{
		$params = ComponentHelper::getParams('com_finder');

		if ($params->get('enable_logging', '0'))
		{
			$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
			$options['text_file'] = 'indexer.php';
			Log::addLogger($options);
		}

		// The old token is invalid so send a new one.
		$this->token = Factory::getSession()->getFormToken();

		// Check if we are dealing with an error.
		if ($state instanceof \Exception)
		{
			// Log the error
			try
			{
				Log::add($state->getMessage(), Log::ERROR);
			}
			catch (\RuntimeException $exception)
			{
				// Informational log only
			}

			// Prepare the error response.
			$this->error = true;
			$this->header = \JText::_('COM_FINDER_INDEXER_HEADER_ERROR');
			$this->message = $state->getMessage();
		}
		else
		{
			// Prepare the response data.
			$this->batchSize = (int) $state->batchSize;
			$this->batchOffset = (int) $state->batchOffset;
			$this->totalItems = (int) $state->totalItems;

			$this->startTime = $state->startTime;
			$this->endTime = Factory::getDate()->toSql();

			$this->start = !empty($state->start) ? (int) $state->start : 0;
			$this->complete = !empty($state->complete) ? (int) $state->complete : 0;

			// Set the appropriate messages.
			if ($this->totalItems <= 0 && $this->complete)
			{
				$this->header = \JText::_('COM_FINDER_INDEXER_HEADER_COMPLETE');
				$this->message = \JText::_('COM_FINDER_INDEXER_MESSAGE_COMPLETE');
			}
			elseif ($this->totalItems <= 0)
			{
				$this->header = \JText::_('COM_FINDER_INDEXER_HEADER_OPTIMIZE');
				$this->message = \JText::_('COM_FINDER_INDEXER_MESSAGE_OPTIMIZE');
			}
			else
			{
				$this->header = \JText::_('COM_FINDER_INDEXER_HEADER_RUNNING');
				$this->message = \JText::_('COM_FINDER_INDEXER_MESSAGE_RUNNING');
			}
		}
	}
}

// Register the error handler.
\JError::setErrorHandling(E_ALL, 'callback', array('FinderControllerIndexer', 'sendResponse'));

<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Register dependent classes.
JLoader::register('FinderIndexer', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/indexer.php');

/**
 * Indexer controller class for Finder.
 *
 * @since  2.5
 */
class FinderControllerIndexer extends JControllerLegacy
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
		$params = JComponentHelper::getParams('com_finder');

		if ($params->get('enable_logging', '0'))
		{
			$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
			$options['text_file'] = 'indexer.php';
			JLog::addLogger($options);
		}

		// Log the start
		try
		{
			JLog::add('Starting the indexer', JLog::INFO);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		// We don't want this form to be cached.
		$app = JFactory::getApplication();
		$app->setHeader('Expires', 'Mon, 1 Jan 2001 00:00:00 GMT', true);
		$app->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT', true);
		$app->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', false);
		$app->setHeader('Pragma', 'no-cache');

		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken('request') or static::sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Put in a buffer to silence noise.
		ob_start();

		// Reset the indexer state.
		FinderIndexer::resetState();

		// Import the finder plugins.
		JPluginHelper::importPlugin('finder');

		// Add the indexer language to JS
		JText::script('COM_FINDER_AN_ERROR_HAS_OCCURRED');
		JText::script('COM_FINDER_NO_ERROR_RETURNED');

		// Start the indexer.
		try
		{
			// Trigger the onStartIndex event.
			JEventDispatcher::getInstance()->trigger('onStartIndex');

			// Get the indexer state.
			$state = FinderIndexer::getState();
			$state->start = 1;

			// Send the response.
			static::sendResponse($state);
		}

		// Catch an exception and return the response.
		catch (Exception $e)
		{
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
		$params = JComponentHelper::getParams('com_finder');

		if ($params->get('enable_logging', '0'))
		{
			$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
			$options['text_file'] = 'indexer.php';
			JLog::addLogger($options);
		}

		// Log the start
		try
		{
			JLog::add('Starting the indexer batch process', JLog::INFO);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		// We don't want this form to be cached.
		$app = JFactory::getApplication();
		$app->setHeader('Expires', 'Mon, 1 Jan 2001 00:00:00 GMT', true);
		$app->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT', true);
		$app->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', false);
		$app->setHeader('Pragma', 'no-cache');

		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken('request') or static::sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Put in a buffer to silence noise.
		ob_start();

		// Remove the script time limit.
		@set_time_limit(0);

		// Get the indexer state.
		$state = FinderIndexer::getState();

		// Reset the batch offset.
		$state->batchOffset = 0;

		// Update the indexer state.
		FinderIndexer::setState($state);

		// Import the finder plugins.
		JPluginHelper::importPlugin('finder');

		/*
		 * We are going to swap out the raw document object with an HTML document
		 * in order to work around some plugins that don't do proper environment
		 * checks before trying to use HTML document functions.
		 */
		$raw = clone JFactory::getDocument();
		$lang = JFactory::getLanguage();

		// Get the document properties.
		$attributes = array (
			'charset'   => 'utf-8',
			'lineend'   => 'unix',
			'tab'       => '  ',
			'language'  => $lang->getTag(),
			'direction' => $lang->isRtl() ? 'rtl' : 'ltr'
		);

		// Get the HTML document.
		$html = JDocument::getInstance('html', $attributes);

		// Todo: Why is this document fetched and immediately overwritten?
		$doc = JFactory::getDocument();

		// Swap the documents.
		$doc = $html;

		// Get the admin application.
		$admin = clone JFactory::getApplication();

		// Get the site app.
		$site = JApplicationCms::getInstance('site');

		// Swap the app.
		$app = JFactory::getApplication();

		// Todo: Why is the app fetched and immediately overwritten?
		$app = $site;

		// Start the indexer.
		try
		{
			// Trigger the onBeforeIndex event.
			JEventDispatcher::getInstance()->trigger('onBeforeIndex');

			// Trigger the onBuildIndex event.
			JEventDispatcher::getInstance()->trigger('onBuildIndex');

			// Get the indexer state.
			$state = FinderIndexer::getState();
			$state->start = 0;
			$state->complete = 0;

			// Swap the documents back.
			$doc = $raw;

			// Swap the applications back.
			$app = $admin;

			// Log batch completion and memory high-water mark.
			try
			{
				JLog::add('Batch completed, peak memory usage: ' . number_format(memory_get_peak_usage(true)) . ' bytes', JLog::INFO);
			}
			catch (RuntimeException $exception)
			{
				// Informational log only
			}

			// Send the response.
			static::sendResponse($state);
		}

		// Catch an exception and return the response.
		catch (Exception $e)
		{
			// Swap the documents back.
			$doc = $raw;

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
		// We don't want this form to be cached.
		$app = JFactory::getApplication();
		$app->setHeader('Expires', 'Mon, 1 Jan 2001 00:00:00 GMT', true);
		$app->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT', true);
		$app->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', false);
		$app->setHeader('Pragma', 'no-cache');

		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken('request') or static::sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Put in a buffer to silence noise.
		ob_start();

		// Import the finder plugins.
		JPluginHelper::importPlugin('finder');

		try
		{
			// Optimize the index
			FinderIndexer::getInstance()->optimize();

			// Get the indexer state.
			$state = FinderIndexer::getState();
			$state->start = 0;
			$state->complete = 1;

			// Send the response.
			static::sendResponse($state);
		}

		// Catch an exception and return the response.
		catch (Exception $e)
		{
			static::sendResponse($e);
		}
	}

	/**
	 * Method to handle a send a JSON response. The body parameter
	 * can be an Exception object for when an error has occurred or
	 * a JObject for a good response.
	 *
	 * @param   mixed  $data  JObject on success, Exception on error. [optional]
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function sendResponse($data = null)
	{
		// This method always sends a JSON response
		$app = JFactory::getApplication();
		$app->mimeType = 'application/json';

		$params = JComponentHelper::getParams('com_finder');

		if ($params->get('enable_logging', '0'))
		{
			$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
			$options['text_file'] = 'indexer.php';
			JLog::addLogger($options);
		}

		// Send the assigned error code if we are catching an exception.
		if ($data instanceof Exception)
		{
			try
			{
				JLog::add($data->getMessage(), JLog::ERROR);
			}
			catch (RuntimeException $exception)
			{
				// Informational log only
			}

			$app->setHeader('status', $data->getCode());
		}

		// Create the response object.
		$response = new FinderIndexerResponse($data);

		// Add the buffer.
		$response->buffer = JDEBUG ? ob_get_contents() : ob_end_clean();

		// Send the JSON response.
		$app->setHeader('Content-Type', $app->mimeType . '; charset=' . $app->charSet);
		$app->sendHeaders();
		echo json_encode($response);

		// Close the application.
		$app->close();
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
		$params = JComponentHelper::getParams('com_finder');

		if ($params->get('enable_logging', '0'))
		{
			$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
			$options['text_file'] = 'indexer.php';
			JLog::addLogger($options);
		}

		// The old token is invalid so send a new one.
		$this->token = JFactory::getSession()->getFormToken();

		// Check if we are dealing with an error.
		if ($state instanceof Exception)
		{
			// Log the error
			try
			{
				JLog::add($state->getMessage(), JLog::ERROR);
			}
			catch (RuntimeException $exception)
			{
				// Informational log only
			}

			// Prepare the error response.
			$this->error = true;
			$this->header = JText::_('COM_FINDER_INDEXER_HEADER_ERROR');
			$this->message = $state->getMessage();
		}
		else
		{
			// Prepare the response data.
			$this->batchSize = (int) $state->batchSize;
			$this->batchOffset = (int) $state->batchOffset;
			$this->totalItems = (int) $state->totalItems;

			$this->startTime = $state->startTime;
			$this->endTime = JFactory::getDate()->toSql();

			$this->start = !empty($state->start) ? (int) $state->start : 0;
			$this->complete = !empty($state->complete) ? (int) $state->complete : 0;

			// Set the appropriate messages.
			if ($this->totalItems <= 0 && $this->complete)
			{
				$this->header = JText::_('COM_FINDER_INDEXER_HEADER_COMPLETE');
				$this->message = JText::_('COM_FINDER_INDEXER_MESSAGE_COMPLETE');
			}
			elseif ($this->totalItems <= 0)
			{
				$this->header = JText::_('COM_FINDER_INDEXER_HEADER_OPTIMIZE');
				$this->message = JText::_('COM_FINDER_INDEXER_MESSAGE_OPTIMIZE');
			}
			else
			{
				$this->header = JText::_('COM_FINDER_INDEXER_HEADER_RUNNING');
				$this->message = JText::_('COM_FINDER_INDEXER_MESSAGE_RUNNING');
			}
		}
	}
}

// Register the error handler.
JError::setErrorHandling(E_ALL, 'callback', array('FinderControllerIndexer', 'sendResponse'));

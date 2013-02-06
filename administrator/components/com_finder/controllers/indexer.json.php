<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Register dependent classes.
JLoader::register('FinderIndexer', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/indexer/indexer.php');

/**
 * Indexer controller class for Finder.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
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
		static $log;

		$params = JComponentHelper::getParams('com_finder');

		if ($params->get('enable_logging', '0'))
		{
			if ($log == null)
			{
				$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
				$options['text_file'] = 'indexer.php';
				$log = JLog::addLogger($options);
			}
		}

		// Log the start
		JLog::add('Starting the indexer', JLog::INFO);

		// We don't want this form to be cached.
		header('Pragma: no-cache');
		header('Cache-Control: no-cache');
		header('Expires: -1');

		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken('request') or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

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
			$this->sendResponse($state);
		}
		// Catch an exception and return the response.
		catch (Exception $e)
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
		static $log;

		$params = JComponentHelper::getParams('com_finder');

		if ($params->get('enable_logging', '0'))
		{
			if ($log == null)
			{
				$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
				$options['text_file'] = 'indexer.php';
				$log = JLog::addLogger($options);
			}
		}

		// Log the start
		JLog::add('Starting the indexer batch process', JLog::INFO);

		// We don't want this form to be cached.
		header('Pragma: no-cache');
		header('Cache-Control: no-cache');
		header('Expires: -1');

		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken('request') or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

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
		$raw = clone(JFactory::getDocument());
		$lang = JFactory::getLanguage();

		// Get the document properties.
		$attributes = array (
			'charset'	=> 'utf-8',
			'lineend'	=> 'unix',
			'tab'		=> '  ',
			'language'	=> $lang->getTag(),
			'direction'	=> $lang->isRTL() ? 'rtl' : 'ltr'
		);

		// Get the HTML document.
		$html = JDocument::getInstance('html', $attributes);
		$doc = JFactory::getDocument();

		// Swap the documents.
		$doc = $html;

		// Get the admin application.
		$admin = clone(JFactory::getApplication());

		// Get the site app.
		include_once JPATH_SITE . '/includes/application.php';
		$site = JApplication::getInstance('site');

		// Swap the app.
		$app = JFactory::getApplication();
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

			// Send the response.
			$this->sendResponse($state);
		}
		// Catch an exception and return the response.
		catch (Exception $e)
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
		header('Pragma: no-cache');
		header('Cache-Control: no-cache');
		header('Expires: -1');

		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken('request') or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

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
			$this->sendResponse($state);
		}
		// Catch an exception and return the response.
		catch (Exception $e)
		{
			$this->sendResponse($e);
		}
	}

	/**
	 * Method to handle a send a JSON response. The body parameter
	 * can be a Exception object for when an error has occurred or
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
		static $log;

		$params = JComponentHelper::getParams('com_finder');

		if ($params->get('enable_logging', '0'))
		{
			if ($log == null)
			{
				$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
				$options['text_file'] = 'indexer.php';
				$log = JLog::addLogger($options);
			}
		}

		// Send the assigned error code if we are catching an exception.
		if ($data instanceof Exception)
		{
			JLog::add($data->getMessage(), JLog::ERROR);
			JResponse::setHeader('status', $data->getCode());
			JResponse::sendHeaders();
		}

		// Create the response object.
		$response = new FinderIndexerResponse($data);

		// Add the buffer.
		$response->buffer = JDEBUG ? ob_get_contents() : ob_end_clean();

		// Send the JSON response.
		echo json_encode($response);

		// Close the application.
		JFactory::getApplication()->close();
	}
}

/**
 * Finder Indexer JSON Response Class
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
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
		static $log;

		$params = JComponentHelper::getParams('com_finder');

		if ($params->get('enable_logging', '0'))
		{
			if ($log == null)
			{
				$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
				$options['text_file'] = 'indexer.php';
				$log = JLog::addLogger($options);
			}
		}

		// The old token is invalid so send a new one.
		$this->token = JFactory::getSession()->getFormToken();

		// Check if we are dealing with an error.
		if ($state instanceof Exception)
		{
			// Log the error
			JLog::add($state->getMessage(), JLog::ERROR);

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
			$this->endTime = JFactory::getDate()->toSQL();

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

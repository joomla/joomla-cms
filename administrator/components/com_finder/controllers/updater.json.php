<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

// Register dependent classes.
JLoader::register('FinderIndexer', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/indexer/indexer.php');
JLoader::register('FinderIndexerQueue', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/indexer/queue.php');

/**
 * Updater controller class for Finder.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderControllerUpdater extends JController
{
	/**
	 * Method to process updates to Finder data
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function update()
	{
		// We don't want this form to be cached.
		header('Pragma: no-cache');
		header('Cache-Control: no-cache');
		header('Expires: -1');

		// Put in a buffer to silence noise.
		ob_start();

		// Remove the script time limit.
		@set_time_limit(0);

		// Get the indexer queue.
		$queue = FinderIndexerQueue::get();

		// If the queue is empty, return.
		if (count($queue) === 0)
		{
			$this->sendResponse(null);
		}

		try
		{
			// Get the indexer state.
			$state = FinderIndexer::getState();

			$state->setup = false;

			// Import the finder plugins.
			JPluginHelper::importPlugin('finder');

			// Check if the indexer needs to be initialized.
			if (empty($state->initialized))
			{
				// Reset the indexer state.
				FinderIndexer::resetState();

				// Trigger the onStartIndex event.
				JDispatcher::getInstance()->trigger('onStartUpdate');

				// Get the indexer state.
				$state = FinderIndexer::getState();

				// Set the initialized flag.
				$state->initialized = true;
				$state->setup = true;

				// Check if the indexer is finished.
				if ($state->totalItems <= 0)
				{
					$state->finished = true;
				}

				// Update the indexer state.
				FinderIndexer::setState($state);
			}
			// Check if the indexer needs to be run again.
			elseif (!empty($state->initialized) && empty($state->processed) && empty($state->finished))
			{
				// Reset the batch offset.
				$state->batchOffset = 0;

				// Update the indexer state.
				FinderIndexer::setState($state);

				// Trigger the onBeforeIndex event.
				JDispatcher::getInstance()->trigger('onBeforeIndex');

				// Trigger the onBuildIndex event.
				JDispatcher::getInstance()->trigger('onBuildUpdate');

				// Get the indexer state.
				$state = FinderIndexer::getState();

				// Check if the indexer is finished.
				if ($state->totalItems <= 0)
				{
					$state->processed = true;
				}

				// Update the indexer state.
				FinderIndexer::setState($state);
			}
			elseif (!empty($state->processed) && empty($state->finished))
			{
				// Set the finished flag.
				$state->finished = true;

				// Purge the queue.
				FinderIndexerQueue::purge();

				// Reset the indexer state.
				FinderIndexer::resetState();
			}

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
	 * @param   object  $data  JObject on success, Exception on error.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function sendResponse($data = null)
	{
		// Send the assigned error code if we are catching an exception.
		if ($data instanceof Exception)
		{
			JResponse::setHeader('status', $data->getCode());
			JResponse::sendHeaders();
		}

		// Add the buffer.
		//@TODO: Should this be $data?
		$response->buffer = JDEBUG ? ob_get_contents() : ob_end_clean();

		// Send the JSON response.
		echo json_encode(new FinderUpdaterResponse($data));

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
class FinderUpdaterResponse
{
	/**
	 * Class Constructor
	 *
	 * @param   mixed  $state  The processing state for the updater
	 *
	 * @since   2.5
	 */
	public function __construct($state)
	{
		// Check if we are dealing with an error.
		if ($state instanceof Exception)
		{
			// Prepare the error response.
			$this->error = true;
			$this->message = $state->getMessage();
		}
		elseif ($state === null)
		{
			$this->finished = true;
			$this->message = JText::_('COM_FINDER_UPDATER_MESSAGE_COMPLETE');
		}
		else
		{
			// Prepare the response data.
			$this->batchSize = (int) $state->batchSize;
			$this->batchOffset = (int) $state->batchOffset;
			$this->totalItems = (int) $state->totalItems;

			$this->startTime = $state->startTime;
			$this->endTime = JFactory::getDate()->toMySQL();

			$this->setup = (int) $state->setup;

			$this->initialized = !empty($state->initialized) ? (int) $state->initialized : 0;
			$this->processed = !empty($state->processed) ? (int) $state->processed : 0;
			$this->finished = !empty($state->finished) ? (int) $state->finished : 0;

			// Set the appropriate messages.
			if ($this->finished)
			{
				$this->message = JText::_('COM_FINDER_UPDATER_MESSAGE_COMPLETE');
			}
			else
			{
				$this->message = JText::sprintf('COM_FINDER_UPDATER_MESSAGE_PROCESS', $this->totalItems);
			}
		}
	}
}

// Register the error handler.
JError::setErrorHandling(E_ALL, 'callback', array('FinderControllerUpdater', 'sendResponse'));

<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jtestreport
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Joomla! test report default controller
 *
 * @since  __DEPLOY_VERSION__
 */
class JTestreportControllerDefault extends JControllerForm
{

	/**
	 * Url to send the statistics.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $serverUrl = 'https://developer.joomla.org/tests/submit';

	/**
	 * Send the stats to the stats server
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  RuntimeException  If there is an error sending the data.
	 */
	public function send()
	{
		$model = $this->getModel('default');

		// Prepare Data
		$data = $model->prepareData();

		$jData = json_encode($data);

		// Just echo it out till we have the end point developed
		// Set the default view name and format from the Request.
		$vName   = 'default';
		$vFormat = 'html';
		$lName   = 'send';

		$view = $this->getView($vName, $vFormat);

		// Push the model into the view (as default).
		$view->setModel($model, true);
		$view->setLayout($lName);

		$view->data = $data;

		$view->displayResult();

		return true;

		try
		{
			// Don't let the request take longer than 20 seconds to avoid page timeout issues
			$response = JHttpFactory::getHttp()->post($this->serverUrl, $jData, null, 20);
		}
		catch (UnexpectedValueException $e)
		{
			// There was an error sending stats. Should we do anything?
			throw new RuntimeException('Could not send test statistics to remote server: ' . $e->getMessage(), 500);
		}
		catch (RuntimeException $e)
		{
			// There was an error connecting to the server or in the post request
			throw new RuntimeException('Could not connect to statistics server: ' . $e->getMessage(), 500);
		}
		catch (Exception $e)
		{
			// An unexpected error in processing; don't let this failure kill the site
			throw new RuntimeException('Unexpected error connecting to statistics server: ' . $e->getMessage(), 500);
		}

		if ($response->code !== 200)
		{
			$responseData = json_decode($response->body);

			throw new RuntimeException('Could not send test statistics to remote server: ' . $responseData->message, $response->code);
		}

		return true;
	}
}

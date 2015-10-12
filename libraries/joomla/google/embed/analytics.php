<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Google Analytics embed class for the Joomla Platform.
 *
 * @since  12.3
 */
class JGoogleEmbedAnalytics extends JGoogleEmbed
{
	/**
	 * Method to get the tracking code
	 *
	 * @return  string  The Google Analytics tracking code
	 *
	 * @since   12.3
	 */
	public function getCode()
	{
		return $this->getOption('code');
	}

	/**
	 * Method to set the tracking code
	 *
	 * @param   string  $code  The Google Analytics tracking code
	 *
	 * @return  JGoogleEmbedAnalytics  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setCode($code)
	{
		$this->setOption('code', $code);

		return $this;
	}

	/**
	 * Checks if the javascript is set to be asynchronous
	 *
	 * @return  boolean  True if asynchronous
	 *
	 * @since   12.3
	 */
	public function isAsync()
	{
		return $this->getOption('async') === null ? true : $this->getOption('async');
	}

	/**
	 * Load javascript asynchronously
	 *
	 * @return  JGoogleEmbedAnalytics  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function useAsync()
	{
		$this->setOption('async', true);

		return $this;
	}

	/**
	 * Load javascript synchronously
	 *
	 * @return  JGoogleEmbedAnalytics  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function useSync()
	{
		$this->setOption('async', false);

		return $this;
	}

	/**
	 * Add an analytics call
	 *
	 * @param   string  $method  The name of the function
	 * @param   array   $params  The parameters for the call
	 *
	 * @return  array  The added call
	 *
	 * @since   12.3
	 */
	public function addCall($method, $params = array())
	{
		$call = array('name' => $method, 'params' => $params);

		$calls = $this->listCalls();
		$calls[] = $call;
		$this->setOption('calls', $calls);

		return $call;
	}

	/**
	 * List the analytics calls to be executed
	 *
	 * @return  array  A list of calls
	 *
	 * @since   12.3
	 */
	public function listCalls()
	{
		return $this->getOption('calls') ? $this->getOption('calls') : array();
	}

	/**
	 * Delete a call from the stack
	 *
	 * @param   int  $index  Index of call to delete (defaults to last added call)
	 *
	 * @return  array  The deleted call
	 *
	 * @since   12.3
	 */
	public function deleteCall($index = null)
	{
		$calls = $this->listCalls();

		if ($index === null)
		{
			$index = count($calls) - 1;
		}

		$call = $calls[$index];
		unset($calls[$index]);
		$calls = array_values($calls);
		$this->setOption('calls', $calls);

		return $call;
	}

	/**
	 * Create a javascript function from the call parameters
	 *
	 * @param   string  $method  The name of the function
	 * @param   array   $params  The parameters for the call
	 *
	 * @return  string  The created call
	 *
	 * @since   12.3
	 */
	public function createCall($method, $params = array())
	{
		$params = array_values($params);

		if ($this->isAsync())
		{
			$output = "_gaq.push(['{$method}',";
			$output .= substr(json_encode($params), 1, -1);
			$output .= ']);';
		}
		else
		{
			$output = "pageTracker.{$method}(";
			$output .= substr(json_encode($params), 1, -1);
			$output .= ');';
		}

		return $output;
	}

	/**
	 * Add a custom variable to the analytics
	 *
	 * @param   int     $slot   The slot to store the variable in (1-5)
	 * @param   string  $name   The variable name
	 * @param   string  $value  The variable value
	 * @param   int     $scope  The scope of the variable (1: visitor level, 2: session level, 3: page level)
	 *
	 * @return  array  The added call
	 *
	 * @since   12.3
	 */
	public function addCustomVar($slot, $name, $value, $scope = 3)
	{
		return $this->addCall('_setCustomVar', array($slot, $name, $value, $scope));
	}

	/**
	 * Get the code to create a custom analytics variable
	 *
	 * @param   int     $slot   The slot to store the variable in (1-5)
	 * @param   string  $name   The variable name
	 * @param   string  $value  The variable value
	 * @param   int     $scope  The scope of the variable (1: visitor level, 2: session level, 3: page level)
	 *
	 * @return  string  The created call
	 *
	 * @since   12.3
	 */
	public function createCustomVar($slot, $name, $value, $scope = 3)
	{
		return $this->createCall('_setCustomVar', array($slot, $name, $value, $scope));
	}

	/**
	 * Track an analytics event
	 *
	 * @param   string   $category     The general event category
	 * @param   string   $action       The event action
	 * @param   string   $label        The event description
	 * @param   string   $value        The value of the event
	 * @param   boolean  $noninteract  Don't allow this event to impact bounce statistics
	 *
	 * @return  array  The added call
	 *
	 * @since   12.3
	 */
	public function addEvent($category, $action, $label = null, $value = null, $noninteract = false)
	{
		return $this->addCall('_trackEvent', array($category, $action, $label, $value, $noninteract));
	}

	/**
	 * Get the code to track an analytics event
	 *
	 * @param   string   $category     The general event category
	 * @param   string   $action       The event action
	 * @param   string   $label        The event description
	 * @param   string   $value        The value of the event
	 * @param   boolean  $noninteract  Don't allow this event to impact bounce statistics
	 *
	 * @return  string  The created call
	 *
	 * @since   12.3
	 */
	public function createEvent($category, $action, $label = null, $value = null, $noninteract = false)
	{
		return $this->createCall('_trackEvent', array($category, $action, $label, $value, $noninteract));
	}

	/**
	 * Get code to load Google Analytics javascript
	 *
	 * @return  string  Javascript code
	 *
	 * @since   12.3
	 */
	public function getHeader()
	{
		if (!$this->isAsync())
		{
			// Synchronous code is included only in the body
			return '';
		}

		if (!$this->getOption('code'))
		{
			throw new UnexpectedValueException('A Google Analytics tracking code is required.');
		}

		$code = $this->getOption('code');

		$output = '<script type="text/javascript">';
		$output .= 'var _gaq = _gaq || [];';
		$output .= "_gaq.push(['_setAccount', '{$code}']);";

		foreach ($this->listCalls() as $call)
		{
			$output .= $this->createCall($call['name'], $call['params']);
		}

		$output .= '_gaq.push(["_trackPageview"]);';
		$output .= '</script>';

		return $output;
	}

	/**
	 * Google Analytics only needs to be included in the header
	 *
	 * @return  null
	 *
	 * @since   12.3
	 */
	public function getBody()
	{
		if (!$this->getOption('code'))
		{
			throw new UnexpectedValueException('A Google Analytics tracking code is required.');
		}

		$prefix = $this->isSecure() ? 'https://ssl' : 'http://www';
		$code = $this->getOption('code');

		if ($this->isAsync())
		{
			$output = '<script type="text/javascript">';
			$output .= '(function() {';
			$output .= 'var ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true;';
			$output .= "ga.src = '{$prefix}.google-analytics.com/ga.js';";
			$output .= 'var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ga, s);';
			$output .= '})();';
			$output .= '</script>';
		}
		else
		{
			$output = '<script type="text/javascript">';
			$output .= "document.write(unescape(\"%3Cscript src='{$prefix}.google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));";
			$output .= '</script>';
			$output .= '<script type="text/javascript">';
			$output .= 'try{';
			$output .= "var pageTracker = _gat._getTracker('{$code}');";

			foreach ($this->listCalls() as $call)
			{
				$output .= $this->createCall($call['name'], $call['params']);
			}

			$output .= 'pageTracker._trackPageview();';
			$output .= '} catch(err) {}</script>';
		}

		return $output;
	}
}

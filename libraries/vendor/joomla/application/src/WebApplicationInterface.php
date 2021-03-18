<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application;

use Joomla\Input\Input;
use Psr\Http\Message\ResponseInterface;

/**
 * Application sub-interface defining a web application class
 *
 * @since  __DEPLOY_VERSION__
 */
interface WebApplicationInterface extends ApplicationInterface
{
	/**
	 * Method to get the application input object.
	 *
	 * @return  Input
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getInput(): Input;

	/**
	 * Redirect to another URL.
	 *
	 * If the headers have not been sent the redirect will be accomplished using a "301 Moved Permanently" or "303 See Other" code in the header
	 * pointing to the new location. If the headers have already been sent this will be accomplished using a JavaScript statement.
	 *
	 * @param   string           $url     The URL to redirect to. Can only be http/https URL
	 * @param   integer|boolean  $status  The HTTP status code to be provided. 303 is assumed by default.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException
	 */
	public function redirect($url, $status = 303);

	/**
	 * Set/get cachable state for the response.
	 *
	 * If $allow is set, sets the cachable state of the response.  Always returns the current state.
	 *
	 * @param   boolean  $allow  True to allow browser caching.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function allowCache($allow = null);

	/**
	 * Method to set a response header.
	 *
	 * If the replace flag is set then all headers with the given name will be replaced by the new one.
	 * The headers are stored in an internal array to be sent when the site is sent to the browser.
	 *
	 * @param   string   $name     The name of the header to set.
	 * @param   string   $value    The value of the header to set.
	 * @param   boolean  $replace  True to replace any headers with the same name.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setHeader($name, $value, $replace = false);

	/**
	 * Method to get the array of response headers to be sent when the response is sent to the client.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getHeaders();

	/**
	 * Method to clear any set response headers.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clearHeaders();

	/**
	 * Send the response headers.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function sendHeaders();

	/**
	 * Set body content.  If body content already defined, this will replace it.
	 *
	 * @param   string  $content  The content to set as the response body.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setBody($content);

	/**
	 * Prepend content to the body content
	 *
	 * @param   string  $content  The content to prepend to the response body.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function prependBody($content);

	/**
	 * Append content to the body content
	 *
	 * @param   string  $content  The content to append to the response body.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function appendBody($content);

	/**
	 * Return the body content
	 *
	 * @return  mixed  The response body as a string.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getBody();

	/**
	 * Get the PSR-7 Response Object.
	 *
	 * @return  ResponseInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getResponse(): ResponseInterface;

	/**
	 * Check if the value is a valid HTTP status code
	 *
	 * @param   integer  $code  The potential status code
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isValidHttpStatus($code);

	/**
	 * Set the PSR-7 Response Object.
	 *
	 * @param   ResponseInterface  $response  The response object
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setResponse(ResponseInterface $response): void;

	/**
	 * Determine if we are using a secure (SSL) connection.
	 *
	 * @return  boolean  True if using SSL, false if not.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isSslConnection();
}

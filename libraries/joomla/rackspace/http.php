<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTTP client class for connecting to an Rackspace instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
class JRackspaceHttp extends JHttp
{
	/**
	 * Constructor.
	 *
	 * @param   JRegistry       $options    Client options object.
	 * @param   JHttpTransport  $transport  The HTTP transport object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JHttpTransport $transport = null)
	{
		// Call the JHttp constructor to setup the object.
		parent::__construct($options, $transport);

		// Set the default timeout to 120 seconds.
		$this->options->def('timeout', 120);
	}

	/**
	 * Method to send the DELETE command to the server.
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 * @param   integer  $timeout  Read timeout in seconds.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   11.3
	 */
	public function deleteWithBody($url, $data, array $headers = null, $timeout = null)
	{
		// Look for headers set in the options.
		$temp = (array) $this->options->get('headers');

		foreach ($temp as $key => $val)
		{
			if (!isset($headers[$key]))
			{
				$headers[$key] = $val;
			}
		}

		// Look for timeout set in the options.
		if ($timeout === null && $this->options->exists('timeout'))
		{
			$timeout = $this->options->get('timeout');
		}

		return $this->transport->request('DELETE', new JUri($url), $data, $headers, $timeout, $this->options->get('userAgent', null));
	}
}

<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Markdown class.
 *
 * @documentation https://developer.github.com/v3/markdown
 *
 * @since       3.3 (CMS)
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageMarkdown extends JGithubPackage
{
	/**
	 * Method to render a markdown document.
	 *
	 * @param   string  $text     The text object being parsed.
	 * @param   string  $mode     The parsing mode; valid options are 'markdown' or 'gfm'.
	 * @param   string  $context  An optional repository context, only used in 'gfm' mode.
	 *
	 * @since   3.3 (CMS)
	 * @throws  DomainException
	 * @throws  InvalidArgumentException
	 *
	 * @return  string  Formatted HTML
	 */
	public function render($text, $mode = 'gfm', $context = null)
	{
		// The valid modes
		$validModes = array('gfm', 'markdown');

		// Make sure the scope is valid
		if (!in_array($mode, $validModes))
		{
			throw new InvalidArgumentException(sprintf('The %s mode is not valid. Valid modes are "gfm" or "markdown".', $mode));
		}

		// Build the request path.
		$path = '/markdown';

		// Build the request data.
		$data = str_replace('\\/', '/', json_encode(
				array(
					'text'    => $text,
					'mode'    => $mode,
					'context' => $context,
				)
			)
		);

		// Send the request.
		$response = $this->client->post($this->fetchUrl($path), $data);

		// Validate the response code.
		if ($response->code != 200)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			$message = (isset($error->message)) ? $error->message : 'Error: ' . $response->code;
			throw new DomainException($message, $response->code);
		}

		return $response->body;
	}
}

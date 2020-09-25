<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API References class for the Joomla Platform.
 *
 * Management of email addresses via the API requires that you are authenticated
 * through basic auth or OAuth with the user scope.
 *
 * @documentation https://developer.github.com/v3/users/emails
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageUsersEmails extends JGithubPackage
{
	/**
	 * List email addresses for a user.
	 *
	 * Future response:
	 * In the final version of the API, this method will return an array of hashes
	 * with extended information for each email address indicating if the address
	 * has been verified and if it’s the user’s primary email address for GitHub.
	 *
	 * Until API v3 is finalized, use the application/vnd.github.v3 media type
	 * to get this response format.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function getList()
	{
		// Build the request path.
		$path = '/user/emails';

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Add email address(es).
	 *
	 * @param   string|array  $email  The email address(es).
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function add($email)
	{
		// Build the request path.
		$path = '/user/emails';

		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), json_encode($email)),
			201
		);
	}

	/**
	 * Delete email address(es).
	 *
	 * @param   string|array  $email  The email address(es).
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function delete($email)
	{
		// Build the request path.
		$path = '/user/emails';

		$this->client->setOption('body', json_encode($email));

		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}
}

<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Repositories Merging class for the Joomla Platform.
 *
 * @documentation https://developer.github.com/v3/repos/merging
 *
 * @since       11.3
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageRepositoriesMerging extends JGithubPackage
{
	/**
	 * Perform a merge.
	 *
	 * @param   string  $owner           The name of the owner of the GitHub repository.
	 * @param   string  $repo            The name of the GitHub repository.
	 * @param   string  $base            The name of the base branch that the head will be merged into.
	 * @param   string  $head            The head to merge. This can be a branch name or a commit SHA1.
	 * @param   string  $commit_message  Commit message to use for the merge commit.
	 *                                   If omitted, a default message will be used.
	 *
	 * @throws UnexpectedValueException
	 * @since   12.4
	 *
	 * @return  boolean
	 */
	public function perform($owner, $repo, $base, $head, $commit_message = '')
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/merges';

		$data = new stdClass;

		$data->base = $base;
		$data->head = $head;

		if ($commit_message)
		{
			$data->commit_message = $commit_message;
		}

		// Send the request.
		$response = $this->client->post($this->fetchUrl($path), json_encode($data));

		switch ($response->code)
		{
			case '201':
				// Success
				return json_decode($response->body);
				break;

			case '204':
				// No-op response (base already contains the head, nothing to merge)
				throw new UnexpectedValueException('Nothing to merge');
				break;

			case '404':
				// Missing base or Missing head response
				$error = json_decode($response->body);

				$message = (isset($error->message)) ? $error->message : 'Missing base or head: ' . $response->code;

				throw new UnexpectedValueException($message);
				break;

			case '409':
				// Merge conflict response
				$error = json_decode($response->body);

				$message = (isset($error->message)) ? $error->message : 'Merge conflict ' . $response->code;

				throw new UnexpectedValueException($message);
				break;

			default :
				throw new UnexpectedValueException('Unexpected response code: ' . $response->code);
				break;
		}
	}
}

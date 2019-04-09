<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Repositories Contents class for the Joomla Platform.
 *
 * These API methods let you retrieve the contents of files within a repository as Base64 encoded content.
 * See media types for requesting raw or other formats.
 *
 * @documentation https://developer.github.com/v3/repos/contents
 *
 * @since       1.7.3
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageRepositoriesContents extends JGithubPackage
{
	/**
	 * Get the README
	 *
	 * This method returns the preferred README for a repository.
	 *
	 * GET /repos/:owner/:repo/readme
	 *
	 * Parameters
	 *
	 * ref
	 * Optional string - The String name of the Commit/Branch/Tag. Defaults to master.
	 *
	 * Response
	 *
	 * Status: 200 OK
	 * X-RateLimit-Limit: 5000
	 * X-RateLimit-Remaining: 4999
	 *
	 * {
	 * "type": "file",
	 * "encoding": "base64",
	 * "_links": {
	 * "git": "https://api.github.com/repos/octokit/octokit.rb/git/blobs/3d21ec53a331a6f037a91c368710b99387d012c1",
	 * "self": "https://api.github.com/repos/octokit/octokit.rb/contents/README.md",
	 * "html": "https://github.com/octokit/octokit.rb/blob/master/README.md"
	 * },
	 * "size": 5362,
	 * "name": "README.md",
	 * "path": "README.md",
	 * "content": "encoded content ...",
	 * "sha": "3d21ec53a331a6f037a91c368710b99387d012c1"
	 * }
	 *
	 * @param   string  $owner  The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 * @param   string  $ref    The String name of the Commit/Branch/Tag. Defaults to master.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function getReadme($owner, $repo, $ref = '')
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/readme';

		if ($ref)
		{
			$path .= '?ref=' . $ref;
		}

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Get contents
	 *
	 * This method returns the contents of any file or directory in a repository.
	 *
	 * GET /repos/:owner/:repo/contents/:path
	 *
	 * Parameters
	 *
	 * path
	 * Optional string - The content path.
	 * ref
	 * Optional string - The String name of the Commit/Branch/Tag. Defaults to master.
	 *
	 * Response
	 *
	 * Status: 200 OK
	 * X-RateLimit-Limit: 5000
	 * X-RateLimit-Remaining: 4999
	 *
	 * {
	 * "type": "file",
	 * "encoding": "base64",
	 * "_links": {
	 * "git": "https://api.github.com/repos/octokit/octokit.rb/git/blobs/3d21ec53a331a6f037a91c368710b99387d012c1",
	 * "self": "https://api.github.com/repos/octokit/octokit.rb/contents/README.md",
	 * "html": "https://github.com/octokit/octokit.rb/blob/master/README.md"
	 * },
	 * "size": 5362,
	 * "name": "README.md",
	 * "path": "README.md",
	 * "content": "encoded content ...",
	 * "sha": "3d21ec53a331a6f037a91c368710b99387d012c1"
	 * }
	 *
	 * @param   string  $owner  The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 * @param   string  $path   The content path.
	 * @param   string  $ref    The String name of the Commit/Branch/Tag. Defaults to master.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function get($owner, $repo, $path, $ref = '')
	{
		// Build the request path.
		$rPath = '/repos/' . $owner . '/' . $repo . '/contents/' . $path;

		if ($ref)
		{
			$rPath .= '?ref=' . $ref;
		}

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($rPath))
		);
	}

	/**
	 * Get archive link
	 *
	 * This method will return a 302 to a URL to download a tarball or zipball archive for a repository.
	 * Please make sure your HTTP framework is configured to follow redirects or you will need to use the Location header to make a second GET request.
	 *
	 * Note: For private repositories, these links are temporary and expire quickly.
	 *
	 * GET /repos/:owner/:repo/:archive_format/:ref
	 *
	 * Parameters
	 *
	 * archive_format
	 * Either tarball or zipball
	 * ref
	 * Optional string - valid Git reference, defaults to master
	 *
	 * Response
	 *
	 * Status: 302 Found
	 * Location: http://github.com/me/myprivate/tarball/master?SSO=thistokenexpires
	 * X-RateLimit-Limit: 5000
	 * X-RateLimit-Remaining: 4999
	 *
	 * To follow redirects with curl, use the -L switch:
	 *
	 * curl -L https://api.github.com/repos/octokit/octokit.rb/tarball > octokit.tar.gz
	 *
	 * @param   string  $owner           The name of the owner of the GitHub repository.
	 * @param   string  $repo            The name of the GitHub repository.
	 * @param   string  $archive_format  Either tarball or zipball.
	 * @param   string  $ref             The String name of the Commit/Branch/Tag. Defaults to master.
	 *
	 * @throws UnexpectedValueException
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function getArchiveLink($owner, $repo, $archive_format = 'zipball', $ref = '')
	{
		if (false == in_array($archive_format, array('tarball', 'zipball')))
		{
			throw new UnexpectedValueException('Archive format must be either "tarball" or "zipball".');
		}

		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/' . $archive_format;

		if ($ref)
		{
			$path .= '?ref=' . $ref;
		}

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path)),
			302
		);
	}
}

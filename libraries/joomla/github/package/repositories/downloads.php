<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Repositories Downloads class for the Joomla Platform.
 *
 * The downloads API is for package downloads only.
 * If you want to get source tarballs you should use
 * https://developer.github.com/v3/repos/contents/#get-archive-link instead.
 *
 * @documentation https://developer.github.com/v3/repos/downloads
 *
 * @since       1.7.3
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageRepositoriesDownloads extends JGithubPackage
{
	/**
	 * List downloads for a repository.
	 *
	 * @param   string  $owner  The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function getList($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/downloads';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Get a single download.
	 *
	 * @param   string   $owner  The name of the owner of the GitHub repository.
	 * @param   string   $repo   The name of the GitHub repository.
	 * @param   integer  $id     The id of the download.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function get($owner, $repo, $id)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/downloads/' . $id;

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Create a new download (Part 1: Create the resource).
	 *
	 * Creating a new download is a two step process. You must first create a new download resource.
	 *
	 * @param   string  $owner         The name of the owner of the GitHub repository.
	 * @param   string  $repo          The name of the GitHub repository.
	 * @param   string  $name          The name.
	 * @param   string  $size          Size of file in bytes.
	 * @param   string  $description   The description.
	 * @param   string  $content_type  The content type.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function create($owner, $repo, $name, $size, $description = '', $content_type = '')
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/downloads';

		$data = array(
			'name' => $name,
			'size' => $size,
		);

		if ($description)
		{
			$data['description'] = $description;
		}

		if ($content_type)
		{
			$data['content_type'] = $content_type;
		}

		// Send the request.
		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), $data),
			201
		);
	}

	/**
	 * Create a new download (Part 2: Upload file to s3).
	 *
	 * Now that you have created the download resource, you can use the information
	 * in the response to upload your file to s3. This can be done with a POST to
	 * the s3_url you got in the create response. Here is a brief example using curl:
	 *
	 * curl \
	 *     -F "key=downloads/octocat/Hello-World/new_file.jpg" \
	 *     -F "acl=public-read" \
	 *     -F "success_action_status=201" \
	 *     -F "Filename=new_file.jpg" \
	 *     -F "AWSAccessKeyId=1ABCDEF..." \
	 *     -F "Policy=ewogIC..." \
	 *     -F "Signature=mwnF..." \
	 *     -F "Content-Type=image/jpeg" \
	 *     -F "file=@new_file.jpg" \
	 *           https://github.s3.amazonaws.com/
	 *
	 * NOTES
	 * The order in which you pass these fields matters! Follow the order shown above exactly.
	 * All parameters shown are required and if you excluded or modify them your upload will
	 * fail because the values are hashed and signed by the policy.
	 *
	 * More information about using the REST API to interact with s3 can be found here:
	 * http://docs.amazonwebservices.com/AmazonS3/latest/API/
	 *
	 * @param   string  $key                    Value of path field in the response.
	 * @param   string  $acl                    Value of acl field in the response.
	 * @param   string  $success_action_status  201, or whatever you want to get back.
	 * @param   string  $filename               Value of name field in the response.
	 * @param   string  $awsAccessKeyId         Value of accesskeyid field in the response.
	 * @param   string  $policy                 Value of policy field in the response.
	 * @param   string  $signature              Value of signature field in the response.
	 * @param   string  $content_type           Value of mime_type field in the response.
	 * @param   string  $file                   Local file. Example assumes the file existing in the directory
	 *                                          where you are running the curl command. Yes, the @ matters.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return boolean
	 */
	public function upload($key, $acl, $success_action_status, $filename, $awsAccessKeyId, $policy, $signature, $content_type, $file)
	{
		// Build the request path.
		$url = 'https://github.s3.amazonaws.com/';

		$data = array(
			'key'                   => $key,
			'acl'                   => $acl,
			'success_action_status' => (int) $success_action_status,
			'Filename'              => $filename,
			'AWSAccessKeyId'        => $awsAccessKeyId,
			'Policy'                => $policy,
			'Signature'             => $signature,
			'Content-Type'          => $content_type,
			'file'                  => $file,
		);

		// Send the request.
		$response = $this->client->post($url, $data);

		// @todo Process the response..

		return (201 == $response->code) ? true : false;
	}

	/**
	 * Delete a download.
	 *
	 * @param   string   $owner  The name of the owner of the GitHub repository.
	 * @param   string   $repo   The name of the GitHub repository.
	 * @param   integer  $id     The id of the download.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function delete($owner, $repo, $id)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/downloads/' . (int) $id;

		// Send the request.
		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}
}

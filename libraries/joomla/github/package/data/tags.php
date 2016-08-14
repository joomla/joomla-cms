<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Data Tags class for the Joomla Platform.
 *
 * This tags API only deals with tag objects - so only annotated tags, not lightweight tags.
 *
 * @documentation https://developer.github.com/v3/git/tags/
 *
 * @since       11.3
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageDataTags extends JGithubPackage
{
	/**
	 * Get a Tag.
	 *
	 * @param   string  $owner  The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 * @param   string  $sha    The SHA1 value to set the reference to.
	 *
	 * @since   3.3 (CMS)
	 *
	 * @return object
	 */
	public function get($owner, $repo, $sha)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/git/tags/' . $sha;

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Create a Tag Object
	 *
	 * Note that creating a tag object does not create the reference that makes a tag in Git.
	 * If you want to create an annotated tag in Git, you have to do this call to create the tag object,
	 * and then create the refs/tags/[tag] reference. If you want to create a lightweight tag,
	 * you simply have to create the reference - this call would be unnecessary.
	 *
	 * @param   string  $owner         The name of the owner of the GitHub repository.
	 * @param   string  $repo          The name of the GitHub repository.
	 * @param   string  $tag           The tag string.
	 * @param   string  $message       The tag message.
	 * @param   string  $object        The SHA of the git object this is tagging.
	 * @param   string  $type          The type of the object we’re tagging. Normally this is a commit
	 *                                 but it can also be a tree or a blob.
	 * @param   string  $tagger_name   The name of the author of the tag.
	 * @param   string  $tagger_email  The email of the author of the tag.
	 * @param   string  $tagger_date   Timestamp of when this object was tagged.
	 *
	 * @since   3.3 (CMS)
	 *
	 * @return object
	 */
	public function create($owner, $repo, $tag, $message, $object, $type, $tagger_name, $tagger_email, $tagger_date)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/git/tags';

		$data = array(
			'tag'          => $tag,
			'message'      => $message,
			'object'       => $object,
			'type'         => $type,
			'tagger_name'  => $tagger_name,
			'tagger_email' => $tagger_email,
			'tagger_date'  => $tagger_date,
		);

		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), json_encode($data)),
			201
		);
	}
}

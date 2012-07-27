<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Linkedin API Social Stream class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 * @since       12.3
 */
class JLinkedinStream extends JLinkedinObject
{
	/**
	 * Method to add a new share. Note: post must contain comment and/or (title and url).
	 *
	 * @param   JLinkedinOAuth  $oauth        The JLinkedinOAuth object.
	 * @param   string          $comment      Text of member's comment.
	 * @param   string          $title        Title of shared document.
	 * @param   string          $url          URL for shared content.
	 * @param   string          $imge         URL for image of shared content.
	 * @param   string          $description  Description of shared content.
	 * @param   string          $visibility   One of anyone: all members or connections-only: connections only.
	 * @param   boolean         $twitter      True to have LinkedIn pass the status message along to a member's tethered Twitter account.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function share($oauth, $visibility, $comment = null, $title = null, $url = null, $image = null, $description = null, $twitter = false)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/people/~/shares';

		// Check if twitter is true.
		if ($twitter)
		{
			$base .= '?twitter-post=true';
		}

		// Build xml.
		$xml = '<share>
				  <visibility>
					 <code>' . $visibility . '</code>
				  </visibility>';

		// Check if comment specified.
		if ($comment)
		{
			$xml .= '<comment>' . $comment . '</comment>';
		}

		// Check if title and url are specified.
		if ($title && $url)
		{
			$xml .= '<content>
					   <title>' . $title . '</title>
					   <submitted-url>' . $url . '</submitted-url>';

			// Check if image is specified.
			if ($image)
			{
				$xml .= '<submitted-image-url>' . $image . '</submitted-image-url>';
			}

			// Check if descrption id specified.
			if ($description)
			{
				$xml .= '<description>' . $description . '</description>
						</content>';
			}
		}
		elseif (!$comment)
		{
			throw new RuntimeException('Post must contain comment and/or (title and url).');
		}

		$xml .= '</share>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);
		return $response;
	}
}

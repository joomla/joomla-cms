<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Facebook API Group class for the Joomla Platform.
 *
 * @link        http://developers.facebook.com/docs/reference/api/group/
 * @since       13.1
 * @deprecated  4.0  Use the `joomla/facebook` package via Composer instead
 */
class JFacebookGroup extends JFacebookObject
{
	/**
	 * Method to read a group. Requires authentication and user_groups or friends_groups permission for non-public groups.
	 *
	 * @param   string  $group  The group id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getGroup($group)
	{
		return $this->get($group);
	}

	/**
	 * Method to get the group's wall. Requires authentication and user_groups or friends_groups permission for non-public groups.
	 *
	 * @param   string   $group   The group id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getFeed($group, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($group, 'feed', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to get the group's members. Requires authentication and user_groups or friends_groups permission for non-public groups.
	 *
	 * @param   string   $group   The group id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getMembers($group, $limit = 0, $offset = 0)
	{
		return $this->getConnection($group, 'members', '', $limit, $offset);
	}

	/**
	 * Method to get the group's docs. Requires authentication and user_groups or friends_groups permission for non-public groups.
	 *
	 * @param   string   $group   The group id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getDocs($group, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($group, 'docs', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to get the groups's picture. Requires authentication and user_groups or friends_groups permission.
	 *
	 * @param   string  $group  The group id.
	 * @param   string  $type   To request a different photo use square | small | normal | large.
	 *
	 * @return  string   The URL to the group's picture.
	 *
	 * @since   13.1
	 */
	public function getPicture($group, $type = null)
	{
		if ($type)
		{
			$type = '?type=' . $type;
		}

		return $this->getConnection($group, 'picture', $type);
	}

	/**
	 * Method to post a link on group's wall. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $group    The group id.
	 * @param   string  $link     Link URL.
	 * @param   string  $message  Link message.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createLink($group, $link, $message = null)
	{
		// Set POST request parameters.
		$data = array();
		$data['link'] = $link;

		if ($message)
		{
			$data['message'] = $message;
		}

		return $this->createConnection($group, 'feed', $data);
	}

	/**
	 * Method to delete a link. Requires authentication.
	 *
	 * @param   mixed  $link  The Link ID.
	 *
	 * @return  boolean   Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deleteLink($link)
	{
		return $this->deleteConnection($link);
	}

	/**
	 * Method to post on group's wall. Message or link parameter is required. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $group        The group id.
	 * @param   string  $message      Post message.
	 * @param   string  $link         Post URL.
	 * @param   string  $picture      Post thumbnail image (can only be used if link is specified)
	 * @param   string  $name         Post name (can only be used if link is specified).
	 * @param   string  $caption      Post caption (can only be used if link is specified).
	 * @param   string  $description  Post description (can only be used if link is specified).
	 * @param   array   $actions      Post actions array of objects containing name and link.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createPost($group, $message = null, $link = null, $picture = null, $name = null, $caption = null,
		$description = null, $actions = null)
	{
		// Set POST request parameters.
		if ($message)
		{
			$data['message'] = $message;
		}

		if ($link)
		{
			$data['link'] = $link;
		}

		if ($name)
		{
			$data['name'] = $name;
		}

		if ($caption)
		{
			$data['caption'] = $caption;
		}

		if ($description)
		{
			$data['description'] = $description;
		}

		if ($actions)
		{
			$data['actions'] = $actions;
		}

		if ($picture)
		{
			$data['picture'] = $picture;
		}

		return $this->createConnection($group, 'feed', $data);
	}

	/**
	 * Method to delete a post. Note: you can only delete the post if it was created by the current user. Requires authentication.
	 *
	 * @param   string  $post  The Post ID.
	 *
	 * @return  boolean   Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deletePost($post)
	{
		return $this->deleteConnection($post);
	}

	/**
	 * Method to post a status message on behalf of the user on the group's wall. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $group    The group id.
	 * @param   string  $message  Status message content.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createStatus($group, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return $this->createConnection($group, 'feed', $data);
	}

	/**
	 * Method to delete a status. Note: you can only delete the status if it was created by the current user. Requires authentication.
	 *
	 * @param   string  $status  The Status ID.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deleteStatus($status)
	{
		return $this->deleteConnection($status);
	}
}

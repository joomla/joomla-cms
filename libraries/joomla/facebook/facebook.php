<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Joomla Platform class for interacting with a Facebook API instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * @since       13.1
 */
class JFacebook
{
	/**
	 * @var    JRegistry  Options for the Facebook object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var    JFacebookOAuth  The OAuth client.
	 * @since  13.1
	 */
	protected $oauth;

	/**
	 * @var    JFacebookUser  Facebook API object for user.
	 * @since  13.1
	 */
	protected $user;

	/**
	* @var    JFacebookStatus  Facebook API object for status.
	* @since  13.1
	*/
	protected $status;

	/**
	* @var    JFacebookCheckin  Facebook API object for checkin.
	* @since  13.1
	*/
	protected $checkin;

	/**
	* @var    JFacebookEvent  Facebook API object for event.
	* @since  13.1
	*/
	protected $event;

	/**
	* @var    JFacebookGroup  Facebook API object for group.
	* @since  13.1
	*/
	protected $group;

	/**
	* @var    JFacebookLink  Facebook API object for link.
	* @since  13.1
	*/
	protected $link;

	/**
	* @var    JFacebookNote  Facebook API object for note.
	* @since  13.1
	*/
	protected $note;

	/**
	* @var    JFacebookPost  Facebook API object for post.
	* @since  13.1
	*/
	protected $post;

	/**
	* @var    JFacebookComment  Facebook API object for comment.
	* @since  13.1
	*/
	protected $comment;

	/**
	* @var    JFacebookPhoto  Facebook API object for photo.
	* @since  13.1
	*/
	protected $photo;

	/**
	* @var    JFacebookVideo  Facebook API object for video.
	* @since  13.1
	*/
	protected $video;

	/**
	* @var    JFacebookAlbum  Facebook API object for album.
	* @since  13.1
	*/
	protected $album;

	/**
	 * Constructor.
	 *
	 * @param   JFacebookOAuth  $oauth    OAuth client.
	 * @param   JRegistry       $options  Facebook options object.
	 * @param   JFacebookHttp   $client   The HTTP client object.
	 *
	 * @since   13.1
	 */
	public function __construct(JFacebookOAuth $oauth = null, JRegistry $options = null, JHttp $client = null)
	{
		$this->oauth = $oauth;
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 'https://graph.facebook.com/');
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JFacebookObject  Facebook API object (status, user, friends etc).
	 *
	 * @since   13.1
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'user':
				if ($this->user == null)
				{
					$this->user = new JFacebookUser($this->options, $this->client, $this->oauth);
				}
				return $this->user;

			case 'status':
				if ($this->status == null)
				{
					$this->status = new JFacebookStatus($this->options, $this->client, $this->oauth);
				}
				return $this->status;

			case 'checkin':
				if ($this->checkin == null)
				{
					$this->checkin = new JFacebookCheckin($this->options, $this->client, $this->oauth);
				}
				return $this->checkin;

			case 'event':
				if ($this->event == null)
				{
					$this->event = new JFacebookEvent($this->options, $this->client, $this->oauth);
				}
				return $this->event;

			case 'group':
				if ($this->group == null)
				{
					$this->group = new JFacebookGroup($this->options, $this->client, $this->oauth);
				}
				return $this->group;

			case 'link':
				if ($this->link == null)
				{
					$this->link = new JFacebookLink($this->options, $this->client, $this->oauth);
				}
				return $this->link;

			case 'note':
				if ($this->note == null)
				{
					$this->note = new JFacebookNote($this->options, $this->client, $this->oauth);
				}
				return $this->note;

			case 'post':
				if ($this->post == null)
				{
					$this->post = new JFacebookPost($this->options, $this->client, $this->oauth);
				}
				return $this->post;

			case 'comment':
				if ($this->comment == null)
				{
					$this->comment = new JFacebookComment($this->options, $this->client, $this->oauth);
				}
				return $this->comment;

			case 'photo':
				if ($this->photo == null)
				{
					$this->photo = new JFacebookPhoto($this->options, $this->client, $this->oauth);
				}
				return $this->photo;

			case 'video':
				if ($this->video == null)
				{
					$this->video = new JFacebookVideo($this->options, $this->client, $this->oauth);
				}
				return $this->video;

			case 'album':
				if ($this->album == null)
				{
					$this->album = new JFacebookAlbum($this->options, $this->client, $this->oauth);
				}
				return $this->album;
		}
	}

	/**
	 * Get an option from the JFacebook instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   13.1
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the JFacebook instance.
	 *
	* @param   string  $key    The name of the option to set.
	* @param   mixed   $value  The option value to set.
	*
	* @return  JFacebook  This object for method chaining.
	*
	* @since   13.1
	*/
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}

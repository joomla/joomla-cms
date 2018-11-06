<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Google+ data class for the Joomla Platform.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/google` package via Composer instead
 */
class JGoogleDataPlus extends JGoogleData
{
	/**
	 * @var    JGoogleDataPlusPeople  Google+ API object for people.
	 * @since  3.1.4
	 */
	protected $people;

	/**
	 * @var    JGoogleDataPlusActivities  Google+ API object for people.
	 * @since  3.1.4
	 */
	protected $activities;

	/**
	 * @var    JGoogleDataPlusComments  Google+ API object for people.
	 * @since  3.1.4
	 */
	protected $comments;

	/**
	 * Constructor.
	 *
	 * @param   Registry     $options  Google options object
	 * @param   JGoogleAuth  $auth     Google data http client object
	 *
	 * @since   3.1.4
	 */
	public function __construct(Registry $options = null, JGoogleAuth $auth = null)
	{
		// Setup the default API url if not already set.
		$options->def('api.url', 'https://www.googleapis.com/plus/v1/');

		parent::__construct($options, $auth);

		if (isset($this->auth) && !$this->auth->getOption('scope'))
		{
			$this->auth->setOption('scope', 'https://www.googleapis.com/auth/plus.me');
		}
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JGoogleDataPlus  Google+ API object (people, activities, comments).
	 *
	 * @since   3.1.4
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'people':
				if ($this->people == null)
				{
					$this->people = new JGoogleDataPlusPeople($this->options, $this->auth);
				}

				return $this->people;

			case 'activities':
				if ($this->activities == null)
				{
					$this->activities = new JGoogleDataPlusActivities($this->options, $this->auth);
				}

				return $this->activities;

			case 'comments':
				if ($this->comments == null)
				{
					$this->comments = new JGoogleDataPlusComments($this->options, $this->auth);
				}

				return $this->comments;
		}
	}
}

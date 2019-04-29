<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Twitter API Profile class for the Joomla Platform.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/twitter` package via Composer instead
 */
class JTwitterProfile extends JTwitterObject
{
	/**
	 * Method to et values that users are able to set under the "Account" tab of their settings page.
	 *
	 * @param   string   $name         Full name associated with the profile. Maximum of 20 characters.
	 * @param   string   $url          URL associated with the profile. Will be prepended with "http://" if not present. Maximum of 100 characters.
	 * @param   string   $location     The city or country describing where the user of the account is located. The contents are not normalized
	 * 								   or geocoded in any way. Maximum of 30 characters.
	 * @param   string   $description  A description of the user owning the account. Maximum of 160 characters.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a
	 * 								   variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function updateProfile($name = null, $url = null, $location = null, $description = null, $entities = null, $skip_status = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('account', 'update_profile');

		$data = array();

		// Check if name is specified.
		if ($name)
		{
			$data['name'] = $name;
		}

		// Check if url is specified.
		if ($url)
		{
			$data['url'] = $url;
		}

		// Check if location is specified.
		if ($location)
		{
			$data['location'] = $location;
		}

		// Check if description is specified.
		if ($description)
		{
			$data['description'] = $description;
		}

		// Check if entities is specified.
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is specified.
		if (!is_null($skip_status))
		{
			$data['skip_status'] = $skip_status;
		}

		// Set the API path
		$path = '/account/update_profile.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to update the authenticating user's profile background image. This method can also be used to enable or disable the profile
	 * background image.
	 *
	 * @param   string   $image        The background image for the profile.
	 * @param   boolean  $tile         Whether or not to tile the background image.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a
	 * 								   variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 * @param   boolean  $use          Determines whether to display the profile background image or not.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function updateProfileBackgroundImage($image = null, $tile = false, $entities = null, $skip_status = null, $use = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('account', 'update_profile_background_image');

		$data = array();

		// Check if image is specified.
		if ($image)
		{
			$data['image'] = "@{$image}";
		}

		// Check if url is true.
		if ($tile)
		{
			$data['tile'] = $tile;
		}

		// Check if entities is specified.
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is specified.
		if (!is_null($skip_status))
		{
			$data['skip_status'] = $skip_status;
		}

		// Check if use is true.
		if ($use)
		{
			$data['use'] = $use;
		}

		// Set the API path
		$path = '/account/update_profile_background_image.json';

		$header = array('Content-Type' => 'multipart/form-data', 'Expect' => '');

		// Send the request.
		return $this->sendRequest($path, 'POST', $data, $header);
	}

	/**
	 * Method to update the authenticating user's profile image.
	 *
	 * @param   string   $image        The background image for the profile.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a
	 * 								   variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function updateProfileImage($image = null, $entities = null, $skip_status = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('account', 'update_profile_image');

		$data = array();

		// Check if image is specified.
		if ($image)
		{
			$data['image'] = "@{$image}";
		}

		// Check if entities is specified.
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is specified.
		if (!is_null($skip_status))
		{
			$data['skip_status'] = $skip_status;
		}

		// Set the API path
		$path = '/account/update_profile_image.json';

		$header = array('Content-Type' => 'multipart/form-data', 'Expect' => '');

		// Send the request.
		return $this->sendRequest($path, 'POST', $data, $header);
	}

	/**
	 * Method to set one or more hex values that control the color scheme of the authenticating user's profile page on twitter.com.
	 *
	 * @param   string   $background      Profile background color.
	 * @param   string   $link            Profile link color.
	 * @param   string   $sidebar_border  Profile sidebar's border color.
	 * @param   string   $sidebar_fill    Profile sidebar's fill color.
	 * @param   string   $text            Profile text color.
	 * @param   boolean  $entities        When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a
	 * 									  variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status     When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function updateProfileColors($background = null, $link = null, $sidebar_border = null, $sidebar_fill = null, $text = null,
		$entities = null, $skip_status = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('account', 'update_profile_colors');

		$data = array();

		// Check if background is specified.
		if ($background)
		{
			$data['profile_background_color'] = $background;
		}

		// Check if link is specified.
		if ($link)
		{
			$data['profile_link_color'] = $link;
		}

		// Check if sidebar_border is specified.
		if ($sidebar_border)
		{
			$data['profile_sidebar_border_color'] = $sidebar_border;
		}

		// Check if sidebar_fill is specified.
		if ($sidebar_fill)
		{
			$data['profile_sidebar_fill_color'] = $sidebar_fill;
		}

		// Check if text is specified.
		if ($text)
		{
			$data['profile_text_color'] = $text;
		}

		// Check if entities is specified.
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is true.
		if (!is_null($skip_status))
		{
			$data['skip_status'] = $skip_status;
		}

		// Set the API path
		$path = '/account/update_profile_colors.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to get the settings (including current trend, geo and sleep time information) for the authenticating user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getSettings()
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('account', 'settings');

		// Set the API path
		$path = '/account/settings.json';

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to update the authenticating user's settings.
	 *
	 * @param   integer  $location     The Yahoo! Where On Earth ID to use as the user's default trend location.
	 * @param   boolean  $sleep_time   When set to true, t or 1, will enable sleep time for the user.
	 * @param   integer  $start_sleep  The hour that sleep time should begin if it is enabled.
	 * @param   integer  $end_sleep    The hour that sleep time should end if it is enabled.
	 * @param   string   $time_zone    The timezone dates and times should be displayed in for the user. The timezone must be one of the
	 * 								   Rails TimeZone names.
	 * @param   string   $lang         The language which Twitter should render in for this user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function updateSettings($location = null, $sleep_time = false, $start_sleep = null, $end_sleep = null,
		$time_zone = null, $lang = null)
	{
		$data = array();

		// Check if location is specified.
		if ($location)
		{
			$data['trend_location_woeid '] = $location;
		}

		// Check if sleep_time is true.
		if ($sleep_time)
		{
			$data['sleep_time_enabled'] = $sleep_time;
		}

		// Check if start_sleep is specified.
		if ($start_sleep)
		{
			$data['start_sleep_time'] = $start_sleep;
		}

		// Check if end_sleep is specified.
		if ($end_sleep)
		{
			$data['end_sleep_time'] = $end_sleep;
		}

		// Check if time_zone is specified.
		if ($time_zone)
		{
			$data['time_zone'] = $time_zone;
		}

		// Check if lang is specified.
		if ($lang)
		{
			$data['lang'] = $lang;
		}

		// Set the API path
		$path = '/account/settings.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}
}

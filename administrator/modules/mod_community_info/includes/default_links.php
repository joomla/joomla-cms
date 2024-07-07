<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_community_info
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Array containing default values for the community links
 * shown in the module
 *
 * @since  4.5.0
 */
$defaultLinksArray = [
  "name"        => "joomla.org",
  "type"        => "default",
  "level"       => "0",
  "jug"         => "https://community.joomla.org/user-groups",
  "forum"       => "https://forum.joomla.org",
  "jday"        => "https://community.joomla.org/events?filter[calendars][1]=97",
  "messanger"   => "https://joomlacommunity.cloud.mattermost.com",
  "vportal"     => "https://volunteers.joomla.org",
  "geolocation" => "51.5000,0.0000",
  "news_feed"   => "https://community.joomla.org/blogs?format=feed&type=rss",
  "events_feed" => "https://djumla.dev/joomla-community-api/events.php?url=https://community.joomla.org/events\?format=feed&type=ical",
  "newsletter"  => "https://community.joomla.org/general-newsletter",
];

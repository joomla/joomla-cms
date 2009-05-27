<?php
/**
 * SimplePie
 *
 * A PHP-Based RSS and Atom Feed Framework.
 * Takes the hard work out of managing a complete RSS/Atom solution.
 *
 * Copyright (c) 2004-2007, Ryan Parman and Geoffrey Sneddon
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * 	* Redistributions of source code must retain the above copyright notice, this list of
 * 	  conditions and the following disclaimer.
 *
 * 	* Redistributions in binary form must reproduce the above copyright notice, this list
 * 	  of conditions and the following disclaimer in the documentation and/or other materials
 * 	  provided with the distribution.
 *
 * 	* Neither the name of the SimplePie Team nor the names of its contributors may be used
 * 	  to endorse or promote products derived from this software without specific prior
 * 	  written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS
 * AND CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package SimplePie
 * @version "Razzleberry"
 * @copyright 2004-2007 Ryan Parman, Geoffrey Sneddon
 * @author Ryan Parman
 * @author Geoffrey Sneddon
 * @link http://simplepie.org/ SimplePie
 * @link http://simplepie.org/support/ Please submit all bug reports and feature requests to the SimplePie forums
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @todo phpDoc comments
 */

/**
 * SimplePie Name
 */
define('SIMPLEPIE_NAME', 'SimplePie');

/**
 * SimplePie Version
 */
define('SIMPLEPIE_VERSION', '1.0.1');

/**
 * SimplePie Build
 * @todo Hardcode for release (there's no need to have to call SimplePie_Misc::parse_date() only every load of simplepie.inc)
 */
define('SIMPLEPIE_BUILD', 20070719221955);

/**
 * SimplePie Website URL
 */
define('SIMPLEPIE_URL', 'http://simplepie.org/');

/**
 * SimplePie Useragent
 * @see SimplePie::set_useragent()
 */
define('SIMPLEPIE_USERAGENT', SIMPLEPIE_NAME . '/' . SIMPLEPIE_VERSION . ' (Feed Parser; ' . SIMPLEPIE_URL . '; Allow like Gecko) Build/' . SIMPLEPIE_BUILD);

/**
 * SimplePie Linkback
 */
define('SIMPLEPIE_LINKBACK', '<a href="' . SIMPLEPIE_URL . '" title="' . SIMPLEPIE_NAME . ' ' . SIMPLEPIE_VERSION . '">' . SIMPLEPIE_NAME . '</a>');

/**
 * No Autodiscovery
 * @see SimplePie::set_autodiscovery_level()
 */
define('SIMPLEPIE_LOCATOR_NONE', 0);

/**
 * Feed Link Element Autodiscovery
 * @see SimplePie::set_autodiscovery_level()
 */
define('SIMPLEPIE_LOCATOR_AUTODISCOVERY', 1);

/**
 * Local Feed Extension Autodiscovery
 * @see SimplePie::set_autodiscovery_level()
 */
define('SIMPLEPIE_LOCATOR_LOCAL_EXTENSION', 2);

/**
 * Local Feed Body Autodiscovery
 * @see SimplePie::set_autodiscovery_level()
 */
define('SIMPLEPIE_LOCATOR_LOCAL_BODY', 4);

/**
 * Remote Feed Extension Autodiscovery
 * @see SimplePie::set_autodiscovery_level()
 */
define('SIMPLEPIE_LOCATOR_REMOTE_EXTENSION', 8);

/**
 * Remote Feed Body Autodiscovery
 * @see SimplePie::set_autodiscovery_level()
 */
define('SIMPLEPIE_LOCATOR_REMOTE_BODY', 16);

/**
 * All Feed Autodiscovery
 * @see SimplePie::set_autodiscovery_level()
 */
define('SIMPLEPIE_LOCATOR_ALL', 31);

/**
 * No known feed type
 */
define('SIMPLEPIE_TYPE_NONE', 0);

/**
 * RSS 0.90
 */
define('SIMPLEPIE_TYPE_RSS_090', 1);

/**
 * RSS 0.91 (Netscape)
 */
define('SIMPLEPIE_TYPE_RSS_091_NETSCAPE', 2);

/**
 * RSS 0.91 (Userland)
 */
define('SIMPLEPIE_TYPE_RSS_091_USERLAND', 4);

/**
 * RSS 0.91 (both Netscape and Userland)
 */
define('SIMPLEPIE_TYPE_RSS_091', 6);

/**
 * RSS 0.92
 */
define('SIMPLEPIE_TYPE_RSS_092', 8);

/**
 * RSS 0.93
 */
define('SIMPLEPIE_TYPE_RSS_093', 16);

/**
 * RSS 0.94
 */
define('SIMPLEPIE_TYPE_RSS_094', 32);

/**
 * RSS 1.0
 */
define('SIMPLEPIE_TYPE_RSS_10', 64);

/**
 * RSS 2.0
 */
define('SIMPLEPIE_TYPE_RSS_20', 128);

/**
 * RDF-based RSS
 */
define('SIMPLEPIE_TYPE_RSS_RDF', 65);

/**
 * Non-RDF-based RSS (truly intended as syndication format)
 */
define('SIMPLEPIE_TYPE_RSS_SYNDICATION', 190);

/**
 * All RSS
 */
define('SIMPLEPIE_TYPE_RSS_ALL', 255);

/**
 * Atom 0.3
 */
define('SIMPLEPIE_TYPE_ATOM_03', 256);

/**
 * Atom 1.0
 */
define('SIMPLEPIE_TYPE_ATOM_10', 512);

/**
 * All Atom
 */
define('SIMPLEPIE_TYPE_ATOM_ALL', 768);

/**
 * All feed types
 */
define('SIMPLEPIE_TYPE_ALL', 1023);

/**
 * No construct
 */
define('SIMPLEPIE_CONSTRUCT_NONE', 0);

/**
 * Text construct
 */
define('SIMPLEPIE_CONSTRUCT_TEXT', 1);

/**
 * HTML construct
 */
define('SIMPLEPIE_CONSTRUCT_HTML', 2);

/**
 * XHTML construct
 */
define('SIMPLEPIE_CONSTRUCT_XHTML', 4);

/**
 * base64-encoded construct
 */
define('SIMPLEPIE_CONSTRUCT_BASE64', 8);

/**
 * IRI construct
 */
define('SIMPLEPIE_CONSTRUCT_IRI', 16);

/**
 * A construct that might be HTML
 */
define('SIMPLEPIE_CONSTRUCT_MAYBE_HTML', 32);

/**
 * All constructs
 */
define('SIMPLEPIE_CONSTRUCT_ALL', 63);

/**
 * PCRE for HTML attributes
 */
define('SIMPLEPIE_PCRE_HTML_ATTRIBUTE', '((?:\s+(?:(?:[^\s:]+:)?[^\s:]+)(?:\s*=\s*(?:"(?:[^"]*)"|\'(?:[^\']*)\'|(?:[a-z0-9\-._:]*)))?)*)\s*');

/**
 * PCRE for XML attributes
 */
define('SIMPLEPIE_PCRE_XML_ATTRIBUTE', '((?:\s+(?:(?:[^\s:]+:)?[^\s:]+)\s*=\s*(?:"(?:[^"]*)"|\'(?:[^\']*)\'))*)\s*');

/**
 * XML Namespace
 */
define('SIMPLEPIE_NAMESPACE_XML', 'http://www.w3.org/XML/1998/namespace');

/**
 * Atom 1.0 Namespace
 */
define('SIMPLEPIE_NAMESPACE_ATOM_10', 'http://www.w3.org/2005/Atom');

/**
 * Atom 0.3 Namespace
 */
define('SIMPLEPIE_NAMESPACE_ATOM_03', 'http://purl.org/atom/ns#');

/**
 * RDF Namespace
 */
define('SIMPLEPIE_NAMESPACE_RDF', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');

/**
 * RSS 0.90 Namespace
 */
define('SIMPLEPIE_NAMESPACE_RSS_090', 'http://my.netscape.com/rdf/simple/0.9/');

/**
 * RSS 1.0 Namespace
 */
define('SIMPLEPIE_NAMESPACE_RSS_10', 'http://purl.org/rss/1.0/');

/**
 * RSS 1.0 Content Module Namespace
 */
define('SIMPLEPIE_NAMESPACE_RSS_10_MODULES_CONTENT', 'http://purl.org/rss/1.0/modules/content/');

/**
 * DC 1.0 Namespace
 */
define('SIMPLEPIE_NAMESPACE_DC_10', 'http://purl.org/dc/elements/1.0/');

/**
 * DC 1.1 Namespace
 */
define('SIMPLEPIE_NAMESPACE_DC_11', 'http://purl.org/dc/elements/1.1/');

/**
 * W3C Basic Geo (WGS84 lat/long) Vocabulary Namespace
 */
define('SIMPLEPIE_NAMESPACE_W3C_BASIC_GEO', 'http://www.w3.org/2003/01/geo/wgs84_pos#');

/**
 * GeoRSS Namespace
 */
define('SIMPLEPIE_NAMESPACE_GEORSS', 'http://www.georss.org/georss');

/**
 * Media RSS Namespace
 */
define('SIMPLEPIE_NAMESPACE_MEDIARSS', 'http://search.yahoo.com/mrss/');

/**
 * iTunes RSS Namespace
 */
define('SIMPLEPIE_NAMESPACE_ITUNES', 'http://www.itunes.com/dtds/podcast-1.0.dtd');

/**
 * XHTML Namespace
 */
define('SIMPLEPIE_NAMESPACE_XHTML', 'http://www.w3.org/1999/xhtml');

/**
 * IANA Link Relations Registry
 */
define('SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY', 'http://www.iana.org/assignments/relation/');

/**
 * Whether we're running on PHP5
 */
define('SIMPLEPIE_PHP5', version_compare(PHP_VERSION, '5.0.0', '>='));

/**
 * SimplePie
 *
 * @package SimplePie
 * @version "Razzleberry"
 * @copyright 2004-2007 Ryan Parman, Geoffrey Sneddon
 * @author Ryan Parman
 * @author Geoffrey Sneddon
 * @todo Option for type of fetching (cache, not modified header, fetch, etc.)
 */
class SimplePie
{
	/**
	 * @var array Raw data
	 * @access private
	 */
	var $data = array();

	/**
	 * @var mixed Error string
	 * @access private
	 */
	var $error;

	/**
	 * @var object Instance of SimplePie_Sanitize (or other class)
	 * @see SimplePie::set_sanitize_class()
	 * @access private
	 */
	var $sanitize;

	/**
	 * @var string SimplePie Useragent
	 * @see SimplePie::set_useragent()
	 * @access private
	 */
	var $useragent = SIMPLEPIE_USERAGENT;

	/**
	 * @var string Feed URL
	 * @see SimplePie::set_feed_url()
	 * @access private
	 */
	var $feed_url;

	/**
	 * @var object Instance of SimplePie_File to use as a feed
	 * @see SimplePie::set_file()
	 * @access private
	 */
	var $file;

	/**
	 * @var string Raw feed data
	 * @see SimplePie::set_raw_data()
	 * @access private
	 */
	var $raw_data;

	/**
	 * @var int Timeout for fetching remote files
	 * @see SimplePie::set_timeout()
	 * @access private
	 */
	var $timeout = 10;

	/**
	 * @var bool Forces fsockopen() to be used for remote files instead
	 * of cURL, even if a new enough version is installed
	 * @see SimplePie::force_fsockopen()
	 * @access private
	 */
	var $force_fsockopen = false;

	/**
	 * @var bool Enable/Disable XML dump
	 * @see SimplePie::enable_xml_dump()
	 * @access private
	 */
	var $xml_dump = false;

	/**
	 * @var bool Enable/Disable Caching
	 * @see SimplePie::enable_cache()
	 * @access private
	 */
	var $cache = true;

	/**
	 * @var int Cache duration (in seconds)
	 * @see SimplePie::set_cache_duration()
	 * @access private
	 */
	var $cache_duration = 3600;

	/**
	 * @var int Auto-discovery cache duration (in seconds)
	 * @see SimplePie::set_autodiscovery_cache_duration()
	 * @access private
	 */
	var $autodiscovery_cache_duration = 604800; // 7 Days.

	/**
	 * @var string Cache location (relative to executing script)
	 * @see SimplePie::set_cache_location()
	 * @access private
	 */
	var $cache_location = './cache';

	/**
	 * @var string Function that creates the cache filename
	 * @see SimplePie::set_cache_name_function()
	 * @access private
	 */
	var $cache_name_function = 'md5';

	/**
	 * @var bool Reorder feed by date descending
	 * @see SimplePie::enable_order_by_date()
	 * @access private
	 */
	var $order_by_date = true;

	/**
	 * @var mixed Force input encoding to be set to the follow value
	 * (false, or anything type-cast to false, disables this feature)
	 * @see SimplePie::set_input_encoding()
	 * @access private
	 */
	var $input_encoding = false;

	/**
	 * @var int Feed Autodiscovery Level
	 * @see SimplePie::set_autodiscovery_level()
	 * @access private
	 */
	var $autodiscovery = SIMPLEPIE_LOCATOR_ALL;

	/**
	 * @var string Class used for caching feeds
	 * @see SimplePie::set_cache_class()
	 * @access private
	 */
	var $cache_class = 'SimplePie_Cache';

	/**
	 * @var string Class used for locating feeds
	 * @see SimplePie::set_locator_class()
	 * @access private
	 */
	var $locator_class = 'SimplePie_Locator';

	/**
	 * @var string Class used for parsing feeds
	 * @see SimplePie::set_parser_class()
	 * @access private
	 */
	var $parser_class = 'SimplePie_Parser';

	/**
	 * @var string Class used for fetching feeds
	 * @see SimplePie::set_file_class()
	 * @access private
	 */
	var $file_class = 'SimplePie_File';

	/**
	 * @var string Class used for items
	 * @see SimplePie::set_item_class()
	 * @access private
	 */
	var $item_class = 'SimplePie_Item';

	/**
	 * @var string Class used for authors
	 * @see SimplePie::set_author_class()
	 * @access private
	 */
	var $author_class = 'SimplePie_Author';

	/**
	 * @var string Class used for categories
	 * @see SimplePie::set_category_class()
	 * @access private
	 */
	var $category_class = 'SimplePie_Category';

	/**
	 * @var string Class used for enclosures
	 * @see SimplePie::set_enclosures_class()
	 * @access private
	 */
	var $enclosure_class = 'SimplePie_Enclosure';

	/**
	 * @var string Class used for Media RSS <media:text> captions
	 * @see SimplePie::set_caption_class()
	 * @access private
	 */
	var $caption_class = 'SimplePie_Caption';

	/**
	 * @var string Class used for Media RSS <media:copyright>
	 * @see SimplePie::set_copyright_class()
	 * @access private
	 */
	var $copyright_class = 'SimplePie_Copyright';

	/**
	 * @var string Class used for Media RSS <media:credit>
	 * @see SimplePie::set_credit_class()
	 * @access private
	 */
	var $credit_class = 'SimplePie_Credit';

	/**
	 * @var string Class used for Media RSS <media:rating>
	 * @see SimplePie::set_rating_class()
	 * @access private
	 */
	var $rating_class = 'SimplePie_Rating';

	/**
	 * @var string Class used for Media RSS <media:restriction>
	 * @see SimplePie::set_restriction_class()
	 * @access private
	 */
	var $restriction_class = 'SimplePie_Restriction';

	/**
	 * @var mixed Set javascript query string parameter (false, or
	 * anything type-cast to false, disables this feature)
	 * @see SimplePie::set_javascript()
	 * @access private
	 */
	var $javascript = 'js';

	/**
	 * @var int Maximum number of feeds to check with autodiscovery
	 * @see SimplePie::set_max_checked_feeds()
	 * @access private
	 */
	var $max_checked_feeds = 10;

	/**
	 * @var string Web-accessible path to the handler_favicon.php file.
	 * @see SimplePie::set_favicon_handler()
	 * @access private
	 */
	var $favicon_handler = '';

	/**
	 * @var string Web-accessible path to the handler_image.php file.
	 * @see SimplePie::set_image_handler()
	 * @access private
	 */
	var $image_handler = '';

	/**
	 * @var array Stores the URLs when multiple feeds are being initialized.
	 * @see SimplePie::set_feed_url()
	 * @access private
	 */
	var $multifeed_url = array();

	/**
	 * @var array Stores SimplePie objects when multiple feeds initialized.
	 * @access private
	 */
	var $multifeed_objects = array();

	/**
	 * @var array Stores the get_object_vars() array for use with multifeeds.
	 * @see SimplePie::set_feed_url()
	 * @access private
	 */
	var $config_settings = null;

	/**
	 * @var array Stores the default attributes to be stripped by strip_attributes().
	 * @see SimplePie::strip_attributes()
	 * @access private
	 */
	var $strip_attributes = array('bgsound', 'class', 'expr', 'id', 'style', 'onclick', 'onerror', 'onfinish', 'onmouseover', 'onmouseout', 'onfocus', 'onblur', 'lowsrc', 'dynsrc');

	/**
	 * @var array Stores the default tags to be stripped by strip_htmltags().
	 * @see SimplePie::strip_htmltags()
	 * @access private
	 */
	var $strip_htmltags = array('base', 'blink', 'body', 'doctype', 'embed', 'font', 'form', 'frame', 'frameset', 'html', 'iframe', 'input', 'marquee', 'meta', 'noscript', 'object', 'param', 'script', 'style');

	/**
	 * The SimplePie class contains feed level data and options
	 *
	 * There are two ways that you can create a new SimplePie object. The first
	 * is by passing a feed URL as a parameter to the SimplePie constructor
	 * (as well as optionally setting the cache location and cache expiry). This
	 * will initialise the whole feed with all of the default settings, and you
	 * can begin accessing methods and properties immediately.
	 *
	 * The second way is to create the SimplePie object with no parameters
	 * at all. This will enable you to set configuration options. After setting
	 * them, you must initialise the feed using $feed->init(). At that point the
	 * object's methods and properties will be available to you. This format is
	 * what is used throughout this documentation.
	 *
	 * @access public
	 * @since 1.0 Preview Release
	 * @param string $feed_url This is the URL you want to parse.
	 * @param string $cache_location This is where you want the cache to be stored.
	 * @param int $cache_duration This is the number of seconds that you want to store the cache file for.
	 */
	function SimplePie($feed_url = null, $cache_location = null, $cache_duration = null)
	{
		// Other objects, instances created here so we can set options on them
		$this->sanitize = &new SimplePie_Sanitize;

		// Set options if they're passed to the constructor
		if ($cache_location !== null)
		{
			$this->set_cache_location($cache_location);
		}

		if ($cache_duration !== null)
		{
			$this->set_cache_duration($cache_duration);
		}

		// Only init the script if we're passed a feed URL
		if ($feed_url !== null)
		{
			$this->set_feed_url($feed_url);
			$this->init();
		}
	}

	/**
	 * Used for converting object to a string
	 */
	function __toString()
	{
		return md5(serialize($this->data));
	}

	/**
	 * This is the URL of the feed you want to parse.
	 *
	 * This allows you to enter the URL of the feed you want to parse, or the
	 * website you want to try to use auto-discovery on. This takes priority
	 * over any set raw data.
	 *
	 * You can set multiple feeds to mash together by passing an array instead
	 * of a string for the $url. Remember that with each additional feed comes
	 * additional processing and resources.
	 *
	 * @access public
	 * @since 1.0 Preview Release
	 * @param mixed $url This is the URL (or array of URLs) that you want to parse.
	 * @see SimplePie::set_raw_data()
	 */
	function set_feed_url($url)
	{
		if (is_array($url))
		{
			$this->multifeed_url = array();
			foreach ($url as $value)
			{
				$this->multifeed_url[] = SimplePie_Misc::fix_protocol($value, 1);
			}
		}
		else
		{
			$this->feed_url = SimplePie_Misc::fix_protocol($url, 1);
		}
	}

	/**
	 * Provides an instance of SimplePie_File to use as a feed
	 *
	 * @access public
	 * @param object &$file Instance of SimplePie_File (or subclass)
	 * @return bool True on success, false on failure
	 */
	function set_file(&$file)
	{
		if (SimplePie_Misc::is_a($file, 'SimplePie_File'))
		{
			$this->feed_url = $file->url;
			$this->file = &$file;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to use a string of RSS/Atom data instead of a remote feed.
	 *
	 * If you have a feed available as a string in PHP, you can tell SimplePie
	 * to parse that data string instead of a remote feed. Any set feed URL
	 * takes precedence.
	 *
	 * @access public
	 * @since 1.0 Beta 3
	 * @param string $data RSS or Atom data as a string.
	 * @see SimplePie::set_feed_url()
	 */
	function set_raw_data($data)
	{
		$this->raw_data = trim($data);
	}

	/**
	 * Allows you to override the default timeout for fetching remote feeds.
	 *
	 * This allows you to change the maximum time the feed's server to respond
	 * and send the feed back.
	 *
	 * @access public
	 * @since 1.0 Beta 3
	 * @param int $timeout The maximum number of seconds to spend waiting to retrieve a feed.
	 */
	function set_timeout($timeout = 10)
	{
		$this->timeout = (int) $timeout;
	}

	/**
	 * Forces SimplePie to use fsockopen() instead of the preferred cURL
	 * functions.
	 *
	 * @access public
	 * @since 1.0 Beta 3
	 * @param bool $enable Force fsockopen() to be used
	 */
	function force_fsockopen($enable = false)
	{
		$this->force_fsockopen = (bool) $enable;
	}

	/**
	 * Outputs the raw XML content of the feed, after it has gone through
	 * SimplePie's filters.
	 *
	 * Used only for debugging, this function will output the XML content as
	 * text/xml. When SimplePie reads in a feed, it does a bit of cleaning up
	 * before trying to parse it. Many parts of the feed are re-written in
	 * memory, and in the end, you have a parsable feed. XML dump shows you the
	 * actual XML that SimplePie tries to parse, which may or may not be very
	 * different from the original feed.
	 *
	 * @access public
	 * @since 1.0 Preview Release
	 * @param bool $enable Enable XML dump
	 */
	function enable_xml_dump($enable = false)
	{
		$this->xml_dump = (bool) $enable;
	}

	/**
	 * Enables/disables caching in SimplePie.
	 *
	 * This option allows you to disable caching all-together in SimplePie.
	 * However, disabling the cache can lead to longer load times.
	 *
	 * @access public
	 * @since 1.0 Preview Release
	 * @param bool $enable Enable caching
	 */
	function enable_cache($enable = true)
	{
		$this->cache = (bool) $enable;
	}

	/**
	 * Set the length of time (in seconds) that the contents of a feed
	 * will be cached.
	 *
	 * @access public
	 * @param int $seconds The feed content cache duration.
	 */
	function set_cache_duration($seconds = 3600)
	{
		$this->cache_duration = (int) $seconds;
	}

	/**
	 * Set the length of time (in seconds) that the autodiscovered feed
	 * URL will be cached.
	 *
	 * @access public
	 * @param int $seconds The autodiscovered feed URL cache duration.
	 */
	function set_autodiscovery_cache_duration($seconds = 604800)
	{
		$this->autodiscovery_cache_duration = (int) $seconds;
	}

	/**
	 * Set the file system location where the cached files should be stored.
	 *
	 * @access public
	 * @param string $location The file system location.
	 */
	function set_cache_location($location = './cache')
	{
		$this->cache_location = (string) $location;
	}

	/**
	 * Determines whether feed items should be sorted into reverse chronological order.
	 *
	 * @access public
	 * @param bool $enable Sort as reverse chronological order.
	 */
	function enable_order_by_date($enable = true)
	{
		$this->order_by_date = (bool) $enable;
	}

	/**
	 * Allows you to override the character encoding reported by the feed.
	 *
	 * @access public
	 * @param string $encoding Character encoding.
	 */
	function set_input_encoding($encoding = false)
	{
		if ($encoding)
		{
			$this->input_encoding = (string) $encoding;
		}
		else
		{
			$this->input_encoding = false;
		}
	}

	/**
	 * Set how much feed autodiscovery to do
	 *
	 * @access public
	 * @see SIMPLEPIE_LOCATOR_NONE
	 * @see SIMPLEPIE_LOCATOR_AUTODISCOVERY
	 * @see SIMPLEPIE_LOCATOR_LOCAL_EXTENSION
	 * @see SIMPLEPIE_LOCATOR_LOCAL_BODY
	 * @see SIMPLEPIE_LOCATOR_REMOTE_EXTENSION
	 * @see SIMPLEPIE_LOCATOR_REMOTE_BODY
	 * @see SIMPLEPIE_LOCATOR_ALL
	 * @param int $level Feed Autodiscovery Level (level can be a
	 * combination of the above constants, see bitwise OR operator)
	 */
	function set_autodiscovery_level($level = SIMPLEPIE_LOCATOR_ALL)
	{
		$this->autodiscovery = (int) $level;
	}

	/**
	 * Allows you to change which class SimplePie uses for caching.
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_cache_class($class = 'SimplePie_Cache')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Cache'))
		{
			$this->cache_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for auto-discovery.
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_locator_class($class = 'SimplePie_Locator')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Locator'))
		{
			$this->locator_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for XML parsing.
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_parser_class($class = 'SimplePie_Parser')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Parser'))
		{
			$this->parser_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for remote file fetching.
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_file_class($class = 'SimplePie_File')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_File'))
		{
			$this->file_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for data sanitization.
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_sanitize_class($class = 'SimplePie_Sanitize')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Sanitize'))
		{
			$this->sanitize = &new $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for handling feed items.
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_item_class($class = 'SimplePie_Item')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Item'))
		{
			$this->item_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for handling author data.
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_author_class($class = 'SimplePie_Author')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Author'))
		{
			$this->author_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for handling category data.
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_category_class($class = 'SimplePie_Category')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Category'))
		{
			$this->category_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for feed enclosures.
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_enclosure_class($class = 'SimplePie_Enclosure')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Enclosure'))
		{
			$this->enclosure_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for <media:text> captions
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_caption_class($class = 'SimplePie_Caption')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Caption'))
		{
			$this->caption_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for <media:copyright>
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_copyright_class($class = 'SimplePie_Copyright')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Copyright'))
		{
			$this->copyright_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for <media:credit>
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_credit_class($class = 'SimplePie_Credit')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Credit'))
		{
			$this->credit_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for <media:rating>
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_rating_class($class = 'SimplePie_Rating')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Rating'))
		{
			$this->rating_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to change which class SimplePie uses for <media:restriction>
	 * Useful when you are overloading or extending SimplePie's default classes.
	 *
	 * @access public
	 * @param string $class Name of custom class.
	 * @link http://php.net/manual/en/keyword.extends.php PHP4 extends documentation
	 * @link http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.extends PHP5 extends documentation
	 */
	function set_restriction_class($class = 'SimplePie_Restriction')
	{
		if (SimplePie_Misc::is_subclass_of($class, 'SimplePie_Restriction'))
		{
			$this->restriction_class = $class;
			return true;
		}
		return false;
	}

	/**
	 * Allows you to override the default user agent string.
	 *
	 * @access public
	 * @param string $ua New user agent string.
	 */
	function set_useragent($ua = SIMPLEPIE_USERAGENT)
	{
		$this->useragent = (string) $ua;
	}

	/**
	 * Set callback function to create cache filename with
	 *
	 * @access public
	 * @param mixed $function Callback function
	 */
	function set_cache_name_function($function = 'md5')
	{
		if (is_callable($function))
		{
			$this->cache_name_function = $function;
		}
	}

	/**
	 * Set javascript query string parameter
	 *
	 * @access public
	 * @param mixed $get Javascript query string parameter
	 */
	function set_javascript($get = 'js')
	{
		if ($get)
		{
			$this->javascript = (string) $get;
		}
		else
		{
			$this->javascript = false;
		}
	}

	/**
	 * Set options to make SP as fast as possible.  Forgoes a
	 * substantial amount of data sanitization in favor of speed.
	 *
	 * @access public
	 * @param bool $set Whether to set them or not
	 */
	function set_stupidly_fast($set = false)
	{
		if ($set)
		{
			$this->enable_order_by_date(false);
			$this->remove_div(false);
			$this->strip_comments(false);
			$this->strip_htmltags(false);
			$this->strip_attributes(false);
			$this->set_image_handler(false);
		}
	}

	/**
	 * Set maximum number of feeds to check with autodiscovery
	 *
	 * @access public
	 * @param int $max Maximum number of feeds to check
	 */
	function set_max_checked_feeds($max = 10)
	{
		$this->max_checked_feeds = (int) $max;
	}

	function remove_div($enable = true)
	{
		$this->sanitize->remove_div($enable);
	}

	function strip_htmltags($tags = '', $encode = null)
	{
		if ($tags === '')
		{
			$tags = $this->strip_htmltags;
		}
		$this->sanitize->strip_htmltags($tags);
		if ($encode !== null)
		{
			$this->sanitize->encode_instead_of_strip($tags);
		}
	}

	function encode_instead_of_strip($enable = true)
	{
		$this->sanitize->encode_instead_of_strip($enable);
	}

	function strip_attributes($attribs = '')
	{
		if ($attribs === '')
		{
			$attribs = $this->strip_attributes;
		}
		$this->sanitize->strip_attributes($attribs);
	}

	function set_output_encoding($encoding = 'UTF-8')
	{
		$this->sanitize->set_output_encoding($encoding);
	}

	function strip_comments($strip = false)
	{
		$this->sanitize->strip_comments($strip);
	}

	/**
	 * Set element/attribute key/value pairs of HTML attributes
	 * containing URLs that need to be resolved relative to the feed
	 *
	 * @access public
	 * @since 1.0
	 * @param array $element_attribute Element/attribute key/value pairs
	 */
	function set_url_replacements($element_attribute = array('a' => 'href', 'area' => 'href', 'blockquote' => 'cite', 'del' => 'cite', 'form' => 'action', 'img' => array('longdesc', 'src'), 'input' => 'src', 'ins' => 'cite', 'q' => 'cite'))
	{
		$this->sanitize->set_url_replacements($element_attribute);
	}

	/**
	 * Set the handler to enable the display of cached favicons.
	 *
	 * @access public
	 * @param str $page Web-accessible path to the handler_favicon.php file.
	 * @param str $qs The query string that the value should be passed to.
	 */
	function set_favicon_handler($page = false, $qs = 'i')
	{
		if ($page != false)
		{
			$this->favicon_handler = $page . '?' . $qs . '=';
		}
		else
		{
			$this->favicon_handler = '';
		}
	}

	/**
	 * Set the handler to enable the display of cached images.
	 *
	 * @access public
	 * @param str $page Web-accessible path to the handler_image.php file.
	 * @param str $qs The query string that the value should be passed to.
	 */
	function set_image_handler($page = false, $qs = 'i')
	{
		if ($page != false)
		{
			$this->sanitize->set_image_handler($page . '?' . $qs . '=');
		}
		else
		{
			$this->image_handler = '';
		}
	}

	function init()
	{
		if ((function_exists('version_compare') && version_compare(PHP_VERSION, '4.1.0', '<')) || !extension_loaded('xml') || !extension_loaded('pcre'))
		{
			return false;
		}
		if (isset($_GET[$this->javascript]))
		{
			if (function_exists('ob_gzhandler'))
			{
				ob_start('ob_gzhandler');
			}
			header('Content-type: text/javascript; charset: UTF-8');
			header('Cache-Control: must-revalidate');
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT'); // 7 days
			?>
function embed_odeo(link) {
	document.writeln('<embed src="http://odeo.com/flash/audio_player_fullsize.swf" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="440" height="80" wmode="transparent" allowScriptAccess="any" flashvars="valid_sample_rate=true&external_url='+link+'"></embed>');
}

function embed_quicktime(type, bgcolor, width, height, link, placeholder, loop) {
	if (placeholder != '') {
		document.writeln('<embed type="'+type+'" style="cursor:hand; cursor:pointer;" href="'+link+'" src="'+placeholder+'" width="'+width+'" height="'+height+'" autoplay="false" target="myself" controller="false" loop="'+loop+'" scale="aspect" bgcolor="'+bgcolor+'" pluginspage="http://www.apple.com/quicktime/download/"></embed>');
	}
	else {
		document.writeln('<embed type="'+type+'" style="cursor:hand; cursor:pointer;" src="'+link+'" width="'+width+'" height="'+height+'" autoplay="false" target="myself" controller="true" loop="'+loop+'" scale="aspect" bgcolor="'+bgcolor+'" pluginspage="http://www.apple.com/quicktime/download/"></embed>');
	}
}

function embed_flash(bgcolor, width, height, link, loop, type) {
	document.writeln('<embed src="'+link+'" pluginspage="http://www.macromedia.com/go/getflashplayer" type="'+type+'" quality="high" width="'+width+'" height="'+height+'" bgcolor="'+bgcolor+'" loop="'+loop+'"></embed>');
}

function embed_flv(width, height, link, placeholder, loop, player) {
	document.writeln('<embed src="'+player+'" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="'+width+'" height="'+height+'" wmode="transparent" flashvars="file='+link+'&autostart=false&repeat='+loop+'&showdigits=true&showfsbutton=false"></embed>');
}

function embed_wmedia(width, height, link) {
	document.writeln('<embed type="application/x-mplayer2" src="'+link+'" autosize="1" width="'+width+'" height="'+height+'" showcontrols="1" showstatusbar="0" showdisplay="0" autostart="0"></embed>');
}
			<?php
			exit;
		}

		// Pass whatever was set with config options over to the sanitizer.
		$this->sanitize->pass_cache_data($this->cache, $this->cache_location, $this->cache_name_function, $this->cache_class);
		$this->sanitize->pass_file_data($this->file_class, $this->timeout, $this->useragent, $this->force_fsockopen);

		if ($this->feed_url !== null || $this->raw_data !== null)
		{
			$this->data = array();
			$this->multifeed_objects = array();
			$cache = false;

			if ($this->feed_url !== null)
			{
				$parsed_feed_url = SimplePie_Misc::parse_url($this->feed_url);
				// Decide whether to enable caching
				if ($this->cache && $parsed_feed_url['scheme'] !== '')
				{
					$cache = &new $this->cache_class($this->cache_location, call_user_func($this->cache_name_function, $this->feed_url), 'spc');
				}
				// If it's enabled and we don't want an XML dump, use the cache
				if ($cache && !$this->xml_dump)
				{
					// Load the Cache
					$this->data = $cache->load();
					if (!empty($this->data))
					{
						// If the cache is for an outdated build of SimplePie
						if (!isset($this->data['build']) || $this->data['build'] != SIMPLEPIE_BUILD)
						{
							$cache->unlink();
							$this->data = array();
						}
						// If we've hit a collision just rerun it with caching disabled
						elseif (isset($this->data['url']) && $this->data['url'] != $this->feed_url)
						{
							$cache = false;
							$this->data = array();
						}
						// If we've got a non feed_url stored (if the page isn't actually a feed, or is a redirect) use that URL.
						elseif (isset($this->data['feed_url']))
						{
							// If the autodiscovery cache is still valid use it.
							if ($cache->mtime() + $this->autodiscovery_cache_duration > time())
							{
								// Do not need to do feed autodiscovery yet.
								if ($this->data['feed_url'] == $this->data['url'])
								{
									$cache->unlink();
									$this->data = array();
								}
								else
								{
									$this->set_feed_url($this->data['feed_url']);
									return $this->init();
								}
							}
						}
						// Check if the cache has been updated
						elseif ($cache->mtime() + $this->cache_duration < time())
						{
							// If we have last-modified and/or etag set
							if (isset($this->data['headers']['last-modified']) || isset($this->data['headers']['etag']))
							{
								$headers = array();
								if (isset($this->data['headers']['last-modified']))
								{
									$headers['if-modified-since'] = $this->data['headers']['last-modified'];
								}
								if (isset($this->data['headers']['etag']))
								{
									$headers['if-none-match'] = $this->data['headers']['etag'];
								}
								$file = &new $this->file_class($this->feed_url, $this->timeout/10, 5, $headers, $this->useragent, $this->force_fsockopen);
								if ($file->success)
								{
									if ($file->status_code == 304)
									{
										$cache->touch();
										return true;
									}
									else
									{
										$headers = $file->headers;
									}
								}
								else
								{
									unset($file);
								}
							}
						}
						// If the cache is still valid, just return true
						else
						{
							return true;
						}
					}
					// If the cache is empty, delete it
					else
					{
						$cache->unlink();
						$this->data = array();
					}
				}
				// If we don't already have the file (it'll only exist if we've opened it to check if the cache has been modified), open it.
				if (!isset($file))
				{
					if (SimplePie_Misc::is_a($this->file, 'SimplePie_File') && $this->file->url == $this->feed_url)
					{
						$file = &$this->file;
					}
					else
					{
						$file = &new $this->file_class($this->feed_url, $this->timeout, 5, null, $this->useragent, $this->force_fsockopen);
					}
				}
				// If the file connection has an error, set SimplePie::error to that and quit
				if (!$file->success)
				{
					$this->error = $file->error;
					if (!empty($this->data))
					{
						return true;
					}
					else
					{
						return false;
					}
				}

				// Check if the supplied URL is a feed, if it isn't, look for it.
				$locate = &new $this->locator_class($file, $this->timeout, $this->useragent, $this->file_class, $this->max_checked_feeds);
				if (!$locate->is_feed($file))
				{
					// We need to unset this so that if SimplePie::set_file() has been called that object is untouched
					unset($file);
					if ($file = $locate->find($this->autodiscovery))
					{
						if ($cache)
						{
							if (!$cache->save(array('url' => $this->feed_url, 'feed_url' => $file->url, 'build' => SIMPLEPIE_BUILD)))
							{
								trigger_error("$cache->name is not writeable", E_USER_WARNING);
							}
							$cache = &new $this->cache_class($this->cache_location, call_user_func($this->cache_name_function, $file->url), 'spc');
						}
						$this->feed_url = $file->url;
					}
					else
					{
						$this->error = "A feed could not be found at $this->feed_url";
						SimplePie_Misc::error($this->error, E_USER_NOTICE, __FILE__, __LINE__);
						return false;
					}
				}
				$locate = null;

				$headers = $file->headers;
				$data = trim($file->body);
				unset($file);
			}
			else
			{
				$data = $this->raw_data;
			}

			// First check to see if input has been overridden.
			if ($this->input_encoding !== false)
			{
				$encoding = $this->input_encoding;
			}
			// Second try HTTP headers
			elseif (isset($headers['content-type']) && preg_match('/;[\x09\x20]*charset=([^;]*)/i', $headers['content-type'], $charset))
			{
				$encoding = $charset[1];
			}
			// Then prolog, if at the very start of the document
			elseif (preg_match("/^<\?xml[\x20\x9\xD\xA]+version([\x20\x9\xD\xA]+)?=([\x20\x9\xD\xA]+)?(\"1.0\"|'1.0'|\"1.1\"|'1.1')[\x20\x9\xD\xA]+encoding([\x20\x9\xD\xA]+)?=([\x20\x9\xD\xA]+)?(\"[A-Za-z][A-Za-z0-9._\-]*\"|'[A-Za-z][A-Za-z0-9._\-]*')([\x20\x9\xD\xA]+standalone([\x20\x9\xD\xA]+)?=([\x20\x9\xD\xA]+)?(\"(yes|no)\"|'(yes|no)'))?([\x20\x9\xD\xA]+)?\?>/", $data, $prolog))
			{
				$encoding = substr($prolog[6], 1, -1);
			}
			// UTF-32 Big Endian BOM
			elseif (strpos($data, "\x0\x0\xFE\xFF") === 0)
			{
				$encoding = 'UTF-32be';
			}
			// UTF-32 Little Endian BOM
			elseif (strpos($data, "\xFF\xFE\x0\x0") === 0)
			{
				$encoding = 'UTF-32';
			}
			// UTF-16 Big Endian BOM
			elseif (strpos($data, "\xFE\xFF") === 0)
			{
				$encoding = 'UTF-16be';
			}
			// UTF-16 Little Endian BOM
			elseif (strpos($data, "\xFF\xFE") === 0)
			{
				$encoding = 'UTF-16le';
			}
			// UTF-8 BOM
			elseif (strpos($data, "\xEF\xBB\xBF") === 0)
			{
				$encoding = 'UTF-8';
			}
			// Fallback to the default (US-ASCII for text/xml, ISO-8859-1 for text/* MIME types, UTF-8 otherwise)
			elseif (isset($headers['content-type']) && strtolower(SimplePie_Misc::parse_mime($headers['content-type'])) == 'text/xml')
			{
				$encoding = 'US-ASCII';
			}
			elseif (isset($headers['content-type']) && SimplePie_Misc::stripos(SimplePie_Misc::parse_mime($headers['content-type']), 'text/') === 0)
			{
				$encoding = 'ISO-8859-1';
			}
			else
			{
				$encoding = 'UTF-8';
			}

			// Change the encoding to UTF-8 (as we always use UTF-8 internally)
			if ($encoding != 'UTF-8')
			{
				$data = SimplePie_Misc::change_encoding($data, $encoding, 'UTF-8');
			}

			// Strip illegal characters
			//$data = SimplePie_Misc::utf8_bad_replace($data);

			$parser = &new $this->parser_class();
			$parser->pre_process($data, 'UTF-8');
			// If we want the XML, just output that and quit
			if ($this->xml_dump)
			{
				header('Content-type: text/xml; charset=UTF-8');
				echo $data;
				exit;
			}
			// If it's parsed fine
			elseif ($parser->parse($data))
			{
				unset($data);
				$this->data = $parser->get_data();
				if (isset($this->data['child']))
				{
					if (isset($headers))
					{
						$this->data['headers'] = $headers;
					}
					$this->data['build'] = SIMPLEPIE_BUILD;

					// Cache the file if caching is enabled
					if ($cache && !$cache->save($this->data))
					{
						trigger_error("$cache->name is not writeable", E_USER_WARNING);
					}
					return true;
				}
				else
				{
					$this->error = "A feed could not be found at $this->feed_url";
					SimplePie_Misc::error($this->error, E_USER_NOTICE, __FILE__, __LINE__);
					return false;
				}
			}
			// If we have an error, just set SimplePie::error to it and quit
			else
			{
				$this->error = sprintf('XML error: %s at line %d, column %d', $parser->get_error_string(), $parser->get_current_line(), $parser->get_current_column());
				SimplePie_Misc::error($this->error, E_USER_NOTICE, __FILE__, __LINE__);
				return false;
			}
		}
		elseif (!empty($this->multifeed_url))
		{
			$i = 0;
			$success = 0;
			$this->multifeed_objects = array();
			foreach ($this->multifeed_url as $url)
			{
				if (SIMPLEPIE_PHP5)
				{
					// This keyword needs to defy coding standards for PHP4 compatibility
					$this->multifeed_objects[$i] = clone($this);
				}
				else
				{
					$this->multifeed_objects[$i] = $this;
				}
				$this->multifeed_objects[$i]->set_feed_url($url);
				$success |= $this->multifeed_objects[$i]->init();
				$i++;
			}
			return (bool) $success;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Return the error message for the occured error
	 *
	 * @access public
	 * @return string Error message
	 */
	function error()
	{
		return $this->error;
	}

	function get_encoding()
	{
		return $this->sanitize->output_encoding;
	}

	function handle_content_type($mime = 'text/html')
	{
		if (!headers_sent())
		{
			$header = "Content-type: $mime;";
			if ($this->get_encoding())
			{
				$header .= ' charset=' . $this->get_encoding();
			}
			else
			{
				$header .= ' charset=UTF-8';
			}
			header($header);
		}
	}

	function get_type()
	{
		if (!isset($this->data['type']))
		{
			$this->data['type'] = SIMPLEPIE_TYPE_ALL;
			if (isset($this->data['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['feed']))
			{
				$this->data['type'] &= SIMPLEPIE_TYPE_ATOM_10;
			}
			elseif (isset($this->data['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['feed']))
			{
				$this->data['type'] &= SIMPLEPIE_TYPE_ATOM_03;
			}
			elseif (isset($this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF']))
			{
				if (isset($this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]['child'][SIMPLEPIE_NAMESPACE_RSS_10]['channel'])
				|| isset($this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]['child'][SIMPLEPIE_NAMESPACE_RSS_10]['image'])
				|| isset($this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]['child'][SIMPLEPIE_NAMESPACE_RSS_10]['item'])
				|| isset($this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]['child'][SIMPLEPIE_NAMESPACE_RSS_10]['textinput']))
				{
					$this->data['type'] &= SIMPLEPIE_TYPE_RSS_10;
				}
				if (isset($this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]['child'][SIMPLEPIE_NAMESPACE_RSS_090]['channel'])
				|| isset($this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]['child'][SIMPLEPIE_NAMESPACE_RSS_090]['image'])
				|| isset($this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]['child'][SIMPLEPIE_NAMESPACE_RSS_090]['item'])
				|| isset($this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]['child'][SIMPLEPIE_NAMESPACE_RSS_090]['textinput']))
				{
					$this->data['type'] &= SIMPLEPIE_TYPE_RSS_090;
				}
			}
			elseif (isset($this->data['child']['']['rss']))
			{
				$this->data['type'] &= SIMPLEPIE_TYPE_RSS_ALL;
				if (isset($this->data['child']['']['rss'][0]['attribs']['']['version']))
				{
					switch (trim($this->data['child']['']['rss'][0]['attribs']['']['version']))
					{
						case '0.91':
							$this->data['type'] &= SIMPLEPIE_TYPE_RSS_091;
							if (isset($this->data['child']['']['rss'][0]['child']['']['skiphours']['hour'][0]['data']))
							{
								switch (trim($this->data['child']['']['rss'][0]['child']['']['skiphours']['hour'][0]['data']))
								{
									case '0':
										$this->data['type'] &= SIMPLEPIE_TYPE_RSS_091_NETSCAPE;
										break;

									case '24':
										$this->data['type'] &= SIMPLEPIE_TYPE_RSS_091_USERLAND;
										break;
								}
							}
							break;

						case '0.92':
							$this->data['type'] &= SIMPLEPIE_TYPE_RSS_092;
							break;

						case '0.93':
							$this->data['type'] &= SIMPLEPIE_TYPE_RSS_093;
							break;

						case '0.94':
							$this->data['type'] &= SIMPLEPIE_TYPE_RSS_094;
							break;

						case '2.0':
							$this->data['type'] &= SIMPLEPIE_TYPE_RSS_20;
							break;
					}
				}
			}
			else
			{
				$this->data['type'] = SIMPLEPIE_TYPE_NONE;
			}
		}
		return $this->data['type'];
	}

	/**
	 * Returns the URL for the favicon of the feed's website.
	 *
	 * @access public
	 * @since 1.0
	 */
	function get_favicon()
	{
		if ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'icon'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($return[0]));
		}
		elseif (($url = $this->get_link()) !== null && preg_match('/^http(s)?:\/\//i', $url))
		{
			$favicon = SimplePie_Misc::absolutize_url('/favicon.ico', $url);

			if ($this->cache && $this->favicon_handler)
			{
				$cache = &new $this->cache_class($this->cache_location, call_user_func($this->cache_name_function, $favicon), 'spi');

				if ($cache->load())
				{
					return $this->sanitize($this->favicon_handler . rawurlencode($favicon), SIMPLEPIE_CONSTRUCT_IRI);
				}
				else
				{
					$file = &new $this->file_class($favicon, $this->timeout / 10, 5, array('X-FORWARDED-FOR' => $_SERVER['REMOTE_ADDR']), $this->useragent, $this->force_fsockopen);

					if ($file->success && ($file->status_code == 200 || ($file->status_code > 206 && $file->status_code < 300)) && strlen($file->body) > 0)
					{
						if ($cache->save(array('headers' => $file->headers, 'body' => $file->body)))
						{
							return $this->sanitize($this->favicon_handler . rawurlencode($favicon), SIMPLEPIE_CONSTRUCT_IRI);
						}
						else
						{
							trigger_error("$cache->name is not writeable", E_USER_WARNING);
							return $this->sanitize($favicon, SIMPLEPIE_CONSTRUCT_IRI);
						}
					}
				}
			}
			else
			{
				return $this->sanitize($favicon, SIMPLEPIE_CONSTRUCT_IRI);
			}
		}
		return false;
	}

	/**
	 * @todo If we have a perm redirect we should return the new URL
	 * @todo When we make the above change, let's support <itunes:new-feed-url> as well
	 * @todo Also, |atom:link|@rel=self
	 */
	function subscribe_url()
	{
		if ($this->feed_url !== null)
		{
			return $this->sanitize($this->feed_url, SIMPLEPIE_CONSTRUCT_IRI);
		}
		else
		{
			return null;
		}
	}

	function subscribe_feed()
	{
		if ($this->feed_url !== null)
		{
			return $this->sanitize(SimplePie_Misc::fix_protocol($this->feed_url, 2), SIMPLEPIE_CONSTRUCT_IRI);
		}
		else
		{
			return null;
		}
	}

	function subscribe_outlook()
	{
		if ($this->feed_url !== null)
		{
			return 'outlook' . $this->sanitize(SimplePie_Misc::fix_protocol($this->feed_url, 2), SIMPLEPIE_CONSTRUCT_IRI);
		}
		else
		{
			return null;
		}
	}

	function subscribe_podcast()
	{
		if ($this->feed_url !== null)
		{
			return $this->sanitize(SimplePie_Misc::fix_protocol($this->feed_url, 3), SIMPLEPIE_CONSTRUCT_IRI);
		}
		else
		{
			return null;
		}
	}

	function subscribe_itunes()
	{
		if ($this->feed_url !== null)
		{
			return $this->sanitize(SimplePie_Misc::fix_protocol($this->feed_url, 4), SIMPLEPIE_CONSTRUCT_IRI);
		}
		else
		{
			return null;
		}
	}

	/**
	 * Creates the subscribe_* methods' return data
	 *
	 * @access private
	 * @param string $feed_url String to prefix to the feed URL
	 * @param string $site_url String to prefix to the site URL (and
	 * suffix to the feed URL)
	 * @return mixed URL if feed exists, false otherwise
	 */
	function subscribe_service($feed_url, $site_url = null)
	{
		if ($this->subscribe_url())
		{
			$return = $this->sanitize($feed_url, SIMPLEPIE_CONSTRUCT_IRI) . rawurlencode($this->subscribe_url());
			if ($site_url !== null && $this->get_link() !== null)
			{
				$return .= $this->sanitize($site_url, SIMPLEPIE_CONSTRUCT_IRI) . rawurlencode($this->get_link());
			}
			return $return;
		}
		else
		{
			return null;
		}
	}

	function subscribe_aol()
	{
		return $this->subscribe_service('http://feeds.my.aol.com/add.jsp?url=');
	}

	function subscribe_bloglines()
	{
		return urldecode($this->subscribe_service('http://www.bloglines.com/sub/'));
	}

	function subscribe_eskobo()
	{
		return $this->subscribe_service('http://www.eskobo.com/?AddToMyPage=');
	}

	function subscribe_feedfeeds()
	{
		return $this->subscribe_service('http://www.feedfeeds.com/add?feed=');
	}

	function subscribe_feedster()
	{
		return $this->subscribe_service('http://www.feedster.com/myfeedster.php?action=addrss&confirm=no&rssurl=');
	}

	function subscribe_google()
	{
		return $this->subscribe_service('http://fusion.google.com/add?feedurl=');
	}

	function subscribe_gritwire()
	{
		return $this->subscribe_service('http://my.gritwire.com/feeds/addExternalFeed.aspx?FeedUrl=');
	}

	function subscribe_msn()
	{
		return $this->subscribe_service('http://my.msn.com/addtomymsn.armx?id=rss&ut=', '&ru=');
	}

	function subscribe_netvibes()
	{
		return $this->subscribe_service('http://www.netvibes.com/subscribe.php?url=');
	}

	function subscribe_newsburst()
	{
		return $this->subscribe_service('http://www.newsburst.com/Source/?add=');
	}

	function subscribe_newsgator()
	{
		return $this->subscribe_service('http://www.newsgator.com/ngs/subscriber/subext.aspx?url=');
	}

	function subscribe_odeo()
	{
		return $this->subscribe_service('http://www.odeo.com/listen/subscribe?feed=');
	}

	function subscribe_podnova()
	{
		return $this->subscribe_service('http://www.podnova.com/index_your_podcasts.srf?action=add&url=');
	}

	function subscribe_rojo()
	{
		return $this->subscribe_service('http://www.rojo.com/add-subscription?resource=');
	}

	function subscribe_yahoo()
	{
		return $this->subscribe_service('http://add.my.yahoo.com/rss?url=');
	}

	function get_feed_tags($namespace, $tag)
	{
		$type = $this->get_type();
		if ($type & SIMPLEPIE_TYPE_ATOM_10)
		{
			if (isset($this->data['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['feed'][0]['child'][$namespace][$tag]))
			{
				return $this->data['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['feed'][0]['child'][$namespace][$tag];
			}
		}
		if ($type & SIMPLEPIE_TYPE_ATOM_03)
		{
			if (isset($this->data['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['feed'][0]['child'][$namespace][$tag]))
			{
				return $this->data['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['feed'][0]['child'][$namespace][$tag];
			}
		}
		if ($type & SIMPLEPIE_TYPE_RSS_RDF)
		{
			if (isset($this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]['child'][$namespace][$tag]))
			{
				return $this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]['child'][$namespace][$tag];
			}
		}
		if ($type & SIMPLEPIE_TYPE_RSS_SYNDICATION)
		{
			if (isset($this->data['child']['']['rss'][0]['child'][$namespace][$tag]))
			{
				return $this->data['child']['']['rss'][0]['child'][$namespace][$tag];
			}
		}
		return null;
	}

	function get_channel_tags($namespace, $tag)
	{
		$type = $this->get_type();
		if ($type & SIMPLEPIE_TYPE_ATOM_ALL)
		{
			if ($return = $this->get_feed_tags($namespace, $tag))
			{
				return $return;
			}
		}
		if ($type & SIMPLEPIE_TYPE_RSS_10)
		{
			if ($channel = $this->get_feed_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'channel'))
			{
				if (isset($channel[0]['child'][$namespace][$tag]))
				{
					return $channel[0]['child'][$namespace][$tag];
				}
			}
		}
		if ($type & SIMPLEPIE_TYPE_RSS_090)
		{
			if ($channel = $this->get_feed_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'channel'))
			{
				if (isset($channel[0]['child'][$namespace][$tag]))
				{
					return $channel[0]['child'][$namespace][$tag];
				}
			}
		}
		if ($type & SIMPLEPIE_TYPE_RSS_SYNDICATION)
		{
			if ($channel = $this->get_feed_tags('', 'channel'))
			{
				if (isset($channel[0]['child'][$namespace][$tag]))
				{
					return $channel[0]['child'][$namespace][$tag];
				}
			}
		}
		return null;
	}

	function get_image_tags($namespace, $tag)
	{
		$type = $this->get_type();
		if ($type & SIMPLEPIE_TYPE_RSS_10)
		{
			if ($image = $this->get_feed_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'image'))
			{
				if (isset($image[0]['child'][$namespace][$tag]))
				{
					return $image[0]['child'][$namespace][$tag];
				}
			}
		}
		if ($type & SIMPLEPIE_TYPE_RSS_090)
		{
			if ($image = $this->get_feed_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'image'))
			{
				if (isset($image[0]['child'][$namespace][$tag]))
				{
					return $image[0]['child'][$namespace][$tag];
				}
			}
		}
		if ($type & SIMPLEPIE_TYPE_RSS_SYNDICATION)
		{
			if ($image = $this->get_channel_tags('', 'image'))
			{
				if (isset($image[0]['child'][$namespace][$tag]))
				{
					return $image[0]['child'][$namespace][$tag];
				}
			}
		}
		return null;
	}

	function get_base($element = array())
	{
		if (!($this->get_type() & SIMPLEPIE_TYPE_RSS_SYNDICATION) && !empty($element['xml_base_explicit']) && isset($element['xml_base']))
		{
			return $element['xml_base'];
		}
		elseif ($this->get_link() !== null)
		{
			return $this->get_link();
		}
		elseif (isset($this->data['headers']['content-location']))
		{
			return SimplePie_Misc::absolutize_url($this->data['headers']['content-location'], $this->subscribe_url());
		}
		else
		{
			return $this->subscribe_url();
		}
	}

	function sanitize($data, $type, $base = '')
	{
		return $this->sanitize->sanitize($data, $type, $base);
	}

	function get_title()
	{
		if ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'title'))
		{
			return $this->sanitize($return[0]['data'], SimplePie_Misc::atom_10_construct_type($return[0]['attribs']), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'title'))
		{
			return $this->sanitize($return[0]['data'], SimplePie_Misc::atom_03_construct_type($return[0]['attribs']), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags('', 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_DC_11, 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_DC_10, 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		else
		{
			return null;
		}
	}

	function get_link($key = 0, $rel = 'alternate')
	{
		$links = $this->get_links($rel);
		if (isset($links[$key]))
		{
			return $links[$key];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Added for parity between the parent-level and the item/entry-level.
	 */
	function get_permalink()
	{
		return $this->get_link(0);
	}

	function get_links($rel = 'alternate')
	{
		if (!isset($this->data['links']))
		{
			$this->data['links'] = array();
			if ($links = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'link'))
			{
				foreach ($links as $link)
				{
					if (isset($link['attribs']['']['href']))
					{
						$link_rel = (isset($link['attribs']['']['rel'])) ? $link['attribs']['']['rel'] : 'alternate';
						$this->data['links'][$link_rel][] = $this->sanitize($link['attribs']['']['href'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($link));
					}
				}
			}
			if ($links = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'link'))
			{
				foreach ($links as $link)
				{
					if (isset($link['attribs']['']['href']))
					{
						$link_rel = (isset($link['attribs']['']['rel'])) ? $link['attribs']['']['rel'] : 'alternate';
						$this->data['links'][$link_rel][] = $this->sanitize($link['attribs']['']['href'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($link));

					}
				}
			}
			if ($links = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'link'))
			{
				$this->data['links']['alternate'][] = $this->sanitize($links[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($links[0]));
			}
			if ($links = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'link'))
			{
				$this->data['links']['alternate'][] = $this->sanitize($links[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($links[0]));
			}
			if ($links = $this->get_channel_tags('', 'link'))
			{
				$this->data['links']['alternate'][] = $this->sanitize($links[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($links[0]));
			}

			$keys = array_keys($this->data['links']);
			foreach ($keys as $key)
			{
				if (SimplePie_Misc::is_isegment_nz_nc($key))
				{
					if (isset($this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key]))
					{
						$this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key] = array_merge($this->data['links'][$key], $this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key]);
						$this->data['links'][$key] = &$this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key];
					}
					else
					{
						$this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key] = &$this->data['links'][$key];
					}
				}
				elseif (substr($key, 0, 41) == SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY)
				{
					$this->data['links'][substr($key, 41)] = &$this->data['links'][$key];
				}
				$this->data['links'][$key] = array_unique($this->data['links'][$key]);
			}
		}

		if (isset($this->data['links'][$rel]))
		{
			return $this->data['links'][$rel];
		}
		else
		{
			return null;
		}
	}

	function get_description()
	{
		if ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'subtitle'))
		{
			return $this->sanitize($return[0]['data'], SimplePie_Misc::atom_10_construct_type($return[0]['attribs']), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'tagline'))
		{
			return $this->sanitize($return[0]['data'], SimplePie_Misc::atom_03_construct_type($return[0]['attribs']), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags('', 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_DC_11, 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_DC_10, 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'summary'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'subtitle'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_HTML, $this->get_base($return[0]));
		}
		else
		{
			return null;
		}
	}

	function get_copyright()
	{
		if ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'rights'))
		{
			return $this->sanitize($return[0]['data'], SimplePie_Misc::atom_10_construct_type($return[0]['attribs']), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags('', 'copyright'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_DC_11, 'rights'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_DC_10, 'rights'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		else
		{
			return null;
		}
	}

	function get_language()
	{
		if ($return = $this->get_channel_tags('', 'language'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_DC_11, 'language'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_DC_10, 'language'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif (isset($this->data['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['feed'][0]['xml_lang']))
		{
			return $this->sanitize($this->data['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['feed'][0]['xml_lang'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif (isset($this->data['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['feed'][0]['xml_lang']))
		{
			return $this->sanitize($this->data['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['feed'][0]['xml_lang'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif (isset($this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]['xml_lang']))
		{
			return $this->sanitize($this->data['child'][SIMPLEPIE_NAMESPACE_RDF]['RDF'][0]['xml_lang'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif (isset($this->data['headers']['content-language']))
		{
			return $this->sanitize($this->data['headers']['content-language'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		else
		{
			return null;
		}
	}

	function get_latitude()
	{
		if ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_W3C_BASIC_GEO, 'lat'))
		{
			return (float) $return[0]['data'];
		}
		elseif (($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_GEORSS, 'point')) && preg_match('/^((?:-)?[0-9]+(?:\.[0-9]+)) ((?:-)?[0-9]+(?:\.[0-9]+))$/', $return[0]['data'], $match))
		{
			return (float) $match[1];
		}
		else
		{
			return null;
		}
	}

	function get_longitude()
	{
		if ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_W3C_BASIC_GEO, 'long'))
		{
			return (float) $return[0]['data'];
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_W3C_BASIC_GEO, 'lon'))
		{
			return (float) $return[0]['data'];
		}
		elseif (($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_GEORSS, 'point')) && preg_match('/^((?:-)?[0-9]+(?:\.[0-9]+)) ((?:-)?[0-9]+(?:\.[0-9]+))$/', $return[0]['data'], $match))
		{
			return (float) $match[2];
		}
		else
		{
			return null;
		}
	}

	function get_image_title()
	{
		if ($return = $this->get_image_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_image_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_image_tags('', 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_image_tags(SIMPLEPIE_NAMESPACE_DC_11, 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_image_tags(SIMPLEPIE_NAMESPACE_DC_10, 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		else
		{
			return null;
		}
	}

	function get_image_url()
	{
		if ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'image'))
		{
			return $this->sanitize($return[0]['attribs']['']['href'], SIMPLEPIE_CONSTRUCT_IRI);
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'logo'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_channel_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'icon'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_image_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'url'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_image_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'url'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_image_tags('', 'url'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($return[0]));
		}
		else
		{
			return null;
		}
	}

	function get_image_link()
	{
		if ($return = $this->get_image_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'link'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_image_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'link'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_image_tags('', 'link'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($return[0]));
		}
		else
		{
			return null;
		}
	}

	function get_image_width()
	{
		if ($return = $this->get_image_tags('', 'width'))
		{
			return round($return[0]['data']);
		}
		elseif ($this->get_type() & SIMPLEPIE_TYPE_RSS_SYNDICATION && $this->get_image_tags('', 'url'))
		{
			return 88.0;
		}
		else
		{
			return null;
		}
	}

	function get_image_height()
	{
		if ($return = $this->get_image_tags('', 'height'))
		{
			return round($return[0]['data']);
		}
		elseif ($this->get_type() & SIMPLEPIE_TYPE_RSS_SYNDICATION && $this->get_image_tags('', 'url'))
		{
			return 31.0;
		}
		else
		{
			return null;
		}
	}

	function get_item_quantity($max = 0)
	{
		$qty = count($this->get_items());
		if ($max == 0)
		{
			return $qty;
		}
		else
		{
			return ($qty > $max) ? $max : $qty;
		}
	}

	function get_item($key = 0)
	{
		$items = $this->get_items();
		if (isset($items[$key]))
		{
			return $items[$key];
		}
		else
		{
			return null;
		}
	}

	function get_items($start = 0, $end = 0)
	{
		if (!empty($this->multifeed_objects))
		{
			return SimplePie::merge_items($this->multifeed_objects, $start, $end);
		}
		elseif (!isset($this->data['items']))
		{
			if ($items = $this->get_feed_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'entry'))
			{
				$keys = array_keys($items);
				foreach ($keys as $key)
				{
					$this->data['items'][] = &new $this->item_class($this, $items[$key]);
				}
			}
			if ($items = $this->get_feed_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'entry'))
			{
				$keys = array_keys($items);
				foreach ($keys as $key)
				{
					$this->data['items'][] = &new $this->item_class($this, $items[$key]);
				}
			}
			if ($items = $this->get_feed_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'item'))
			{
				$keys = array_keys($items);
				foreach ($keys as $key)
				{
					$this->data['items'][] = &new $this->item_class($this, $items[$key]);
				}
			}
			if ($items = $this->get_feed_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'item'))
			{
				$keys = array_keys($items);
				foreach ($keys as $key)
				{
					$this->data['items'][] = &new $this->item_class($this, $items[$key]);
				}
			}
			if ($items = $this->get_channel_tags('', 'item'))
			{
				$keys = array_keys($items);
				foreach ($keys as $key)
				{
					$this->data['items'][] = &new $this->item_class($this, $items[$key]);
				}
			}
		}

		if (!empty($this->data['items']))
		{
			// If we want to order it by date, check if all items have a date, and then sort it
			if ($this->order_by_date)
			{
				if (!isset($this->data['ordered_items']))
				{
					$do_sort = true;
					foreach ($this->data['items'] as $item)
					{
						if (!$item->get_date('U'))
						{
							$do_sort = false;
							break;
						}
					}
					$item = null;
					$this->data['ordered_items'] = $this->data['items'];
					if ($do_sort)
					{
						usort($this->data['ordered_items'], array(&$this, 'sort_items'));
					}
				}
				$items = $this->data['ordered_items'];
			}
			else
			{
				$items = $this->data['items'];
			}

			// Slice the data as desired
			if ($end == 0)
			{
				return array_slice($items, $start);
			}
			else
			{
				return array_slice($items, $start, $end);
			}
		}
		else
		{
			return array();
		}
	}

	function sort_items($a, $b)
	{
		return $a->get_date('U') <= $b->get_date('U');
	}

	function merge_items($urls, $start = 0, $end = 0)
	{
		if (is_array($urls) && sizeof($urls) > 0)
		{
			$items = array();
			foreach ($urls as $arg)
			{
				if (SimplePie_Misc::is_a($arg, 'SimplePie'))
				{
					$items = array_merge($items, $arg->get_items());
				}
				else
				{
					trigger_error('Arguments must be SimplePie objects', E_USER_WARNING);
				}
			}

			$do_sort = true;
			foreach ($items as $item)
			{
				if (!$item->get_date('U'))
				{
					$do_sort = false;
					break;
				}
			}
			$item = null;
			if ($do_sort)
			{
				usort($items, array('SimplePie', 'sort_items'));
			}

			if ($end == 0)
			{
				return array_slice($items, $start);
			}
			else
			{
				return array_slice($items, $start, $end);
			}
		}
		else
		{
			trigger_error('Cannot merge zero SimplePie objects', E_USER_WARNING);
			return array();
		}
	}
}

class SimplePie_Item
{
	var $feed;
	var $data = array();

	function SimplePie_Item($feed, $data)
	{
		$this->feed = $feed;
		$this->data = $data;
	}

	function __toString()
	{
		return md5(serialize($this->data));
	}

	function get_item_tags($namespace, $tag)
	{
		if (isset($this->data['child'][$namespace][$tag]))
		{
			return $this->data['child'][$namespace][$tag];
		}
		else
		{
			return null;
		}
	}

	function get_base($element = array())
	{
		return $this->feed->get_base($element);
	}

	function sanitize($data, $type, $base = '')
	{
		return $this->feed->sanitize($data, $type, $base);
	}

	function get_feed()
	{
		return $this->feed;
	}

	function get_id($hash = false)
	{
		if ($hash)
		{
			return $this->__toString();
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'id'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'id'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_item_tags('', 'guid'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'identifier'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'identifier'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif (($return = $this->get_permalink()) !== null)
		{
			return $return;
		}
		elseif (($return = $this->get_title()) !== null)
		{
			return $return;
		}
		else
		{
			return $this->__toString();
		}
	}

	function get_title()
	{
		if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'title'))
		{
			return $this->sanitize($return[0]['data'], SimplePie_Misc::atom_10_construct_type($return[0]['attribs']), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'title'))
		{
			return $this->sanitize($return[0]['data'], SimplePie_Misc::atom_03_construct_type($return[0]['attribs']), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags('', 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'title'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		else
		{
			return null;
		}
	}

	function get_description($description_only = false)
	{
		if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'summary'))
		{
			return $this->sanitize($return[0]['data'], SimplePie_Misc::atom_10_construct_type($return[0]['attribs']), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'summary'))
		{
			return $this->sanitize($return[0]['data'], SimplePie_Misc::atom_03_construct_type($return[0]['attribs']), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags('', 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'description'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'summary'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_HTML, $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'subtitle'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif (!$description_only)
		{
			return $this->get_content(true);
		}
		else
		{
			return null;
		}
	}

	function get_content($content_only = false)
	{
		if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'content'))
		{
			return $this->sanitize($return[0]['data'], SimplePie_Misc::atom_10_content_construct_type($return[0]['attribs']), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'content'))
		{
			return $this->sanitize($return[0]['data'], SimplePie_Misc::atom_03_construct_type($return[0]['attribs']), $this->get_base($return[0]));
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_10_MODULES_CONTENT, 'encoded'))
		{
			return $this->sanitize($return[0]['data'], SIMPLEPIE_CONSTRUCT_HTML, $this->get_base($return[0]));
		}
		elseif (!$content_only)
		{
			return $this->get_description(true);
		}
		else
		{
			return null;
		}
	}

	function get_category($key = 0)
	{
		$categories = $this->get_categories();
		if (isset($categories[$key]))
		{
			return $categories[$key];
		}
		else
		{
			return null;
		}
	}

	function get_categories()
	{
		$categories = array();

		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'category') as $category)
		{
			$term = null;
			$scheme = null;
			$label = null;
			if (isset($category['attribs']['']['term']))
			{
				$term = $this->sanitize($category['attribs']['']['term'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if (isset($category['attribs']['']['scheme']))
			{
				$scheme = $this->sanitize($category['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if (isset($category['attribs']['']['label']))
			{
				$label = $this->sanitize($category['attribs']['']['label'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			$categories[] = &new $this->feed->category_class($term, $scheme, $label);
		}
		foreach ((array) $this->get_item_tags('', 'category') as $category)
		{
			$categories[] = &new $this->feed->category_class($this->sanitize($category['data'], SIMPLEPIE_CONSTRUCT_TEXT), null, null);
		}
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'subject') as $category)
		{
			$categories[] = &new $this->feed->category_class($this->sanitize($category['data'], SIMPLEPIE_CONSTRUCT_TEXT), null, null);
		}
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'subject') as $category)
		{
			$categories[] = &new $this->feed->category_class($this->sanitize($category['data'], SIMPLEPIE_CONSTRUCT_TEXT), null, null);
		}

		if (!empty($categories))
		{
			return SimplePie_Misc::array_unique($categories);
		}
		else
		{
			return null;
		}
	}

	function get_author($key = 0)
	{
		$authors = $this->get_authors();
		if (isset($authors[$key]))
		{
			return $authors[$key];
		}
		else
		{
			return null;
		}
	}

	/**
	 * @todo Atom inheritance (item author, source author, feed author)
	 */
	function get_authors()
	{
		$authors = array();
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'author') as $author)
		{
			$name = null;
			$uri = null;
			$email = null;
			if (isset($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['name'][0]['data']))
			{
				$name = $this->sanitize($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['name'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if (isset($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['uri'][0]['data']))
			{
				$uri = $this->sanitize($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['uri'][0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['uri'][0]));
			}
			if (isset($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['email'][0]['data']))
			{
				$email = $this->sanitize($author['child'][SIMPLEPIE_NAMESPACE_ATOM_10]['email'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if ($name !== null || $email !== null || $uri !== null)
			{
				$authors[] = &new $this->feed->author_class($name, $uri, $email);
			}
		}
		if ($author = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'author'))
		{
			$name = null;
			$url = null;
			$email = null;
			if (isset($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['name'][0]['data']))
			{
				$name = $this->sanitize($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['name'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if (isset($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['url'][0]['data']))
			{
				$uri = $this->sanitize($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['url'][0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['url'][0]));
			}
			if (isset($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['email'][0]['data']))
			{
				$email = $this->sanitize($author[0]['child'][SIMPLEPIE_NAMESPACE_ATOM_03]['email'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
			}
			if ($name !== null || $email !== null || $uri !== null)
			{
				$authors[] = &new $this->feed->author_class($name, $url, $email);
			}
		}
		if ($author = $this->get_item_tags('', 'author'))
		{
			$authors[] = &new $this->feed->author_class(null, null, $this->sanitize($author[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT));
		}
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'creator') as $author)
		{
			$authors[] = &new $this->feed->author_class($this->sanitize($author['data'], SIMPLEPIE_CONSTRUCT_TEXT), null, null);
		}
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'creator') as $author)
		{
			$authors[] = &new $this->feed->author_class($this->sanitize($author['data'], SIMPLEPIE_CONSTRUCT_TEXT), null, null);
		}
		foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'author') as $author)
		{
			$authors[] = &new $this->feed->author_class($this->sanitize($author['data'], SIMPLEPIE_CONSTRUCT_TEXT), null, null);
		}

		if (!empty($authors))
		{
			return SimplePie_Misc::array_unique($authors);
		}
		else
		{
			return null;
		}
	}

	function get_date($date_format = 'j F Y, g:i a')
	{
		if (!isset($this->data['date']))
		{
			if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'published'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'updated'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'issued'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'created'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'modified'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags('', 'pubDate'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'date'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}
			elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_DC_10, 'date'))
			{
				$this->data['date']['raw'] = $return[0]['data'];
			}

			if (!empty($this->data['date']['raw']))
			{
				$this->data['date']['parsed'] = SimplePie_Misc::parse_date($this->data['date']['raw']);
			}
			else
			{
				$this->data['date'] = null;
			}
		}
		if ($this->data['date'])
		{
			$date_format = (string) $date_format;
			switch ($date_format)
			{
				case '':
					return $this->sanitize($this->data['date']['raw'], SIMPLEPIE_CONSTRUCT_TEXT);

				case 'U':
					return $this->data['date']['parsed'];

				default:
					return date($date_format, $this->data['date']['parsed']);
			}
		}
		else
		{
			return null;
		}
	}

	function get_local_date($date_format = '%c')
	{
		if (!$date_format)
		{
			return $this->sanitize($this->get_date(''), SIMPLEPIE_CONSTRUCT_TEXT);
		}
		elseif (($date = $this->get_date('U')) !== null)
		{
			return strftime($date_format, $date);
		}
		else
		{
			return null;
		}
	}

	function get_permalink()
	{
		$link = $this->get_link();
		$enclosure = $this->get_enclosure(0);
		if ($link !== null)
		{
			return $link;
		}
		elseif ($enclosure !== null)
		{
			return $enclosure->get_link();
		}
		else
		{
			return null;
		}
	}

	function get_link($key = 0, $rel = 'alternate')
	{
		$links = $this->get_links($rel);
		if ($links[$key] !== null)
		{
			return $links[$key];
		}
		else
		{
			return null;
		}
	}

	function get_links($rel = 'alternate')
	{
		if (!isset($this->data['links']))
		{
			$this->data['links'] = array();
			foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'link') as $link)
			{
				if (isset($link['attribs']['']['href']))
				{
					$link_rel = (isset($link['attribs']['']['rel'])) ? $link['attribs']['']['rel'] : 'alternate';
					$this->data['links'][$link_rel][] = $this->sanitize($link['attribs']['']['href'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($link));

				}
			}
			foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'link') as $link)
			{
				if (isset($link['attribs']['']['href']))
				{
					$link_rel = (isset($link['attribs']['']['rel'])) ? $link['attribs']['']['rel'] : 'alternate';
					$this->data['links'][$link_rel][] = $this->sanitize($link['attribs']['']['href'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($link));
				}
			}
			if ($links = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_10, 'link'))
			{
				$this->data['links']['alternate'][] = $this->sanitize($links[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($links[0]));
			}
			if ($links = $this->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_090, 'link'))
			{
				$this->data['links']['alternate'][] = $this->sanitize($links[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($links[0]));
			}
			if ($links = $this->get_item_tags('', 'link'))
			{
				$this->data['links']['alternate'][] = $this->sanitize($links[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($links[0]));
			}
			if ($links = $this->get_item_tags('', 'guid'))
			{
				if (!isset($links[0]['attribs']['']['isPermaLink']) || strtolower(trim($links[0]['attribs']['']['isPermaLink'])) == 'true')
				{
					$this->data['links']['alternate'][] = $this->sanitize($links[0]['data'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($links[0]));
				}
			}

			$keys = array_keys($this->data['links']);
			foreach ($keys as $key)
			{
				if (SimplePie_Misc::is_isegment_nz_nc($key))
				{
					if (isset($this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key]))
					{
						$this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key] = array_merge($this->data['links'][$key], $this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key]);
						$this->data['links'][$key] = &$this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key];
					}
					else
					{
						$this->data['links'][SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY . $key] = &$this->data['links'][$key];
					}
				}
				elseif (substr($key, 0, 41) == SIMPLEPIE_IANA_LINK_RELATIONS_REGISTRY)
				{
					$this->data['links'][substr($key, 41)] = &$this->data['links'][$key];
				}
				$this->data['links'][$key] = array_unique($this->data['links'][$key]);
			}
		}
		if (isset($this->data['links'][$rel]))
		{
			return $this->data['links'][$rel];
		}
		else
		{
			return null;
		}
	}

	/**
	 * @todo Add ability to prefer one type of content over another (in a media group).
	 */
	function get_enclosure($key = 0, $prefer = null)
	{
		$enclosures = $this->get_enclosures();
		if (isset($enclosures[$key]))
		{
			return $enclosures[$key];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Grabs all available enclosures (podcasts, etc.)
	 *
	 * Supports the <enclosure> RSS tag, as well as Media RSS and iTunes RSS.
	 *
	 * At this point, we're pretty much assuming that all enclosures for an item are the same content.  Anything else is too complicated to properly support.
	 *
	 * @todo Add support for end-user defined sorting of enclosures by type/handler (so we can prefer the faster-loading FLV over MP4).
	 * @todo Add support for itunes: tags.  These should be relatively simple compared to media:.
	 * @todo If an element exists at a level, but it's value is empty, we should fall back to the value from the parent (if it exists).
	 */
	function get_enclosures()
	{
		if (!isset($this->data['enclosures']))
		{
			$this->data['enclosures'] = array();

			// Elements
			$captions_parent = null;
			$categories_parent = null;
			$copyrights_parent = null;
			$credits_parent = null;
			$description_parent = null;
			$duration_parent = null;
			$hashes_parent = null;
			$keywords_parent = null;
			$player_parent = null;
			$ratings_parent = null;
			$restrictions_parent = null;
			$thumbnails_parent = null;
			$title_parent = null;

			// Let's do the channel and item-level ones first, and just re-use them if we need to.
			$parent = $this->get_feed();

			// CAPTIONS
			if ($captions = $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'text'))
			{
				foreach ($captions as $caption)
				{
					$caption_type = null;
					$caption_lang = null;
					$caption_startTime = null;
					$caption_endTime = null;
					$caption_text = null;
					if (isset($caption['attribs']['']['type']))
					{
						$caption_type = $this->sanitize($caption['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($caption['attribs']['']['lang']))
					{
						$caption_lang = $this->sanitize($caption['attribs']['']['lang'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($caption['attribs']['']['start']))
					{
						$caption_startTime = $this->sanitize($caption['attribs']['']['start'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($caption['attribs']['']['end']))
					{
						$caption_endTime = $this->sanitize($caption['attribs']['']['end'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($caption['data']))
					{
						$caption_text = $this->sanitize($caption['data'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					$captions_parent[] = &new $this->feed->caption_class($caption_type, $caption_lang, $caption_startTime, $caption_endTime, $caption_text);
				}
			}
			elseif ($captions = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'text'))
			{
				foreach ($captions as $caption)
				{
					$caption_type = null;
					$caption_lang = null;
					$caption_startTime = null;
					$caption_endTime = null;
					$caption_text = null;
					if (isset($caption['attribs']['']['type']))
					{
						$caption_type = $this->sanitize($caption['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($caption['attribs']['']['lang']))
					{
						$caption_lang = $this->sanitize($caption['attribs']['']['lang'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($caption['attribs']['']['start']))
					{
						$caption_startTime = $this->sanitize($caption['attribs']['']['start'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($caption['attribs']['']['end']))
					{
						$caption_endTime = $this->sanitize($caption['attribs']['']['end'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($caption['data']))
					{
						$caption_text = $this->sanitize($caption['data'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					$captions_parent[] = &new $this->feed->caption_class($caption_type, $caption_lang, $caption_startTime, $caption_endTime, $caption_text);
				}
			}
			if (is_array($captions_parent))
			{
				$captions_parent = array_values(SimplePie_Misc::array_unique($captions_parent));
			}

			// CATEGORIES
			foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'category') as $category)
			{
				$term = null;
				$scheme = null;
				$label = null;
				if (isset($category['data']))
				{
					$term = $this->sanitize($category['data'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
				if (isset($category['attribs']['']['scheme']))
				{
					$scheme = $this->sanitize($category['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
				else
				{
					$scheme = 'http://search.yahoo.com/mrss/category_schema';
				}
				if (isset($category['attribs']['']['label']))
				{
					$label = $this->sanitize($category['attribs']['']['label'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
				$categories_parent[] = &new $this->feed->category_class($term, $scheme, $label);
			}
			foreach ((array) $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'category') as $category)
			{
				$term = null;
				$scheme = null;
				$label = null;
				if (isset($category['data']))
				{
					$term = $this->sanitize($category['data'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
				if (isset($category['attribs']['']['scheme']))
				{
					$scheme = $this->sanitize($category['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
				else
				{
					$scheme = 'http://search.yahoo.com/mrss/category_schema';
				}
				if (isset($category['attribs']['']['label']))
				{
					$label = $this->sanitize($category['attribs']['']['label'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
				$categories_parent[] = &new $this->feed->category_class($term, $scheme, $label);
			}
			foreach ((array) $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'category') as $category)
			{
				$term = null;
				$scheme = 'http://www.itunes.com/dtds/podcast-1.0.dtd';
				$label = null;
				if (isset($category['attribs']['']['text']))
				{
					$label = $this->sanitize($category['attribs']['']['text'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
				$categories_parent[] = &new $this->feed->category_class($term, $scheme, $label);

				if (isset($category['child'][SIMPLEPIE_NAMESPACE_ITUNES]['category']))
				{
					foreach ((array) $category['child'][SIMPLEPIE_NAMESPACE_ITUNES]['category'] as $subcategory)
					{
						if (isset($subcategory['attribs']['']['text']))
						{
							$label = $this->sanitize($subcategory['attribs']['']['text'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						$categories_parent[] = &new $this->feed->category_class($term, $scheme, $label);
					}
				}
			}
			if (is_array($categories_parent))
			{
				$categories_parent = array_values(SimplePie_Misc::array_unique($categories_parent));
			}

			// COPYRIGHT
			if ($copyright = $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'copyright'))
			{
				$copyright_url = null;
				$copyright_label = null;
				if (isset($copyright[0]['attribs']['']['url']))
				{
					$copyright_url = $this->sanitize($copyright[0]['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
				if (isset($copyright[0]['data']))
				{
					$copyright_label = $this->sanitize($copyright[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
				$copyrights_parent = &new $this->feed->copyright_class($copyright_url, $copyright_label);
			}
			elseif ($copyright = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'copyright'))
			{
				$copyright_url = null;
				$copyright_label = null;
				if (isset($copyright[0]['attribs']['']['url']))
				{
					$copyright_url = $this->sanitize($copyright[0]['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
				if (isset($copyright[0]['data']))
				{
					$copyright_label = $this->sanitize($copyright[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
				$copyrights_parent = &new $this->feed->copyright_class($copyright_url, $copyright_label);
			}

			// CREDITS
			if ($credits = $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'credit'))
			{
				foreach ($credits as $credit)
				{
					$credit_role = null;
					$credit_scheme = null;
					$credit_name = null;
					if (isset($credit['attribs']['']['role']))
					{
						$credit_role = $this->sanitize($credit['attribs']['']['role'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($credit['attribs']['']['scheme']))
					{
						$credit_scheme = $this->sanitize($credit['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					else
					{
						$credit_scheme = 'urn:ebu';
					}
					if (isset($credit['data']))
					{
						$credit_name = $this->sanitize($credit['data'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					$credits_parent[] = &new $this->feed->credit_class($credit_role, $credit_scheme, $credit_name);
				}
			}
			elseif ($credits = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'credit'))
			{
				foreach ($credits as $credit)
				{
					$credit_role = null;
					$credit_scheme = null;
					$credit_name = null;
					if (isset($credit['attribs']['']['role']))
					{
						$credit_role = $this->sanitize($credit['attribs']['']['role'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($credit['attribs']['']['scheme']))
					{
						$credit_scheme = $this->sanitize($credit['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					else
					{
						$credit_scheme = 'urn:ebu';
					}
					if (isset($credit['data']))
					{
						$credit_name = $this->sanitize($credit['data'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					$credits_parent[] = &new $this->feed->credit_class($credit_role, $credit_scheme, $credit_name);
				}
			}
			if (is_array($credits_parent))
			{
				$credits_parent = array_values(SimplePie_Misc::array_unique($credits_parent));
			}

			// DESCRIPTION
			if ($description_parent = $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'description'))
			{
				if (isset($description_parent[0]['data']))
				{
					$description_parent = $this->sanitize($description_parent[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
			}
			elseif ($description_parent = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'description'))
			{
				if (isset($description_parent[0]['data']))
				{
					$description_parent = $this->sanitize($description_parent[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
			}

			// DURATION
			if ($duration_parent = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'duration'))
			{
				$seconds = null;
				$minutes = null;
				$hours = null;
				if (isset($duration_parent[0]['data']))
				{
					$temp = explode(':', $this->sanitize($duration_parent[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT));
					if (sizeof($temp) > 0)
					{
						(int) $seconds = array_pop($temp);
					}
					if (sizeof($temp) > 0)
					{
						(int) $minutes = array_pop($temp);
						$seconds += $minutes * 60;
					}
					if (sizeof($temp) > 0)
					{
						(int) $hours = array_pop($temp);
						$seconds += $hours * 3600;
					}
					unset($temp);
					$duration_parent = $seconds;
				}
			}

			// HASHES
			if ($hashes_iterator = $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'hash'))
			{
				foreach ($hashes_iterator as $hash)
				{
					$value = null;
					$algo = null;
					if (isset($hash['data']))
					{
						$value = $this->sanitize($hash['data'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($hash['attribs']['']['algo']))
					{
						$algo = $this->sanitize($hash['attribs']['']['algo'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					else
					{
						$algo = 'md5';
					}
					$hashes_parent[] = $algo.':'.$value;
				}
			}
			elseif ($hashes_iterator = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'hash'))
			{
				foreach ($hashes_iterator as $hash)
				{
					$value = null;
					$algo = null;
					if (isset($hash['data']))
					{
						$value = $this->sanitize($hash['data'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($hash['attribs']['']['algo']))
					{
						$algo = $this->sanitize($hash['attribs']['']['algo'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					else
					{
						$algo = 'md5';
					}
					$hashes_parent[] = $algo.':'.$value;
				}
			}
			if (is_array($hashes_parent))
			{
				$hashes_parent = array_values(SimplePie_Misc::array_unique($hashes_parent));
			}

			// KEYWORDS
			if ($keywords = $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'keywords'))
			{
				if (isset($keywords[0]['data']))
				{
					$temp = explode(',', $this->sanitize($keywords[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT));
					foreach ($temp as $word)
					{
						$keywords_parent[] = trim($word);
					}
				}
				unset($temp);
			}
			elseif ($keywords = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'keywords'))
			{
				if (isset($keywords[0]['data']))
				{
					$temp = explode(',', $this->sanitize($keywords[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT));
					foreach ($temp as $word)
					{
						$keywords_parent[] = trim($word);
					}
				}
				unset($temp);
			}
			elseif ($keywords = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'keywords'))
			{
				if (isset($keywords[0]['data']))
				{
					$temp = explode(',', $this->sanitize($keywords[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT));
					foreach ($temp as $word)
					{
						$keywords_parent[] = trim($word);
					}
				}
				unset($temp);
			}
			elseif ($keywords = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'keywords'))
			{
				if (isset($keywords[0]['data']))
				{
					$temp = explode(',', $this->sanitize($keywords[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT));
					foreach ($temp as $word)
					{
						$keywords_parent[] = trim($word);
					}
				}
				unset($temp);
			}
			if (is_array($keywords_parent))
			{
				$keywords_parent = array_values(SimplePie_Misc::array_unique($keywords_parent));
			}

			// PLAYER
			if ($player_parent = $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'player'))
			{
				if (isset($player_parent[0]['attribs']['']['url']))
				{
					$player_parent = $this->sanitize($player_parent[0]['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI);
				}
			}
			elseif ($player_parent = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'player'))
			{
				if (isset($player_parent[0]['attribs']['']['url']))
				{
					$player_parent = $this->sanitize($player_parent[0]['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI);
				}
			}

			// RATINGS
			if ($ratings = $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'rating'))
			{
				foreach ($ratings as $rating)
				{
					$rating_scheme = null;
					$rating_value = null;
					if (isset($rating['attribs']['']['scheme']))
					{
						$rating_scheme = $this->sanitize($rating['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					else
					{
						$rating_scheme = 'urn:simple';
					}
					if (isset($rating['data']))
					{
						$rating_value = $this->sanitize($rating['data'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					$ratings_parent[] = &new $this->feed->rating_class($rating_scheme, $rating_value);
				}
			}
			elseif ($ratings = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'explicit'))
			{
				foreach ($ratings as $rating)
				{
					$rating_scheme = 'urn:itunes';
					$rating_value = null;
					if (isset($rating['data']))
					{
						$rating_value = $this->sanitize($rating['data'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					$ratings_parent[] = &new $this->feed->rating_class($rating_scheme, $rating_value);
				}
			}
			elseif ($ratings = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'rating'))
			{
				foreach ($ratings as $rating)
				{
					$rating_scheme = null;
					$rating_value = null;
					if (isset($rating['attribs']['']['scheme']))
					{
						$rating_scheme = $this->sanitize($rating['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					else
					{
						$rating_scheme = 'urn:simple';
					}
					if (isset($rating['data']))
					{
						$rating_value = $this->sanitize($rating['data'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					$ratings_parent[] = &new $this->feed->rating_class($rating_scheme, $rating_value);
				}
			}
			elseif ($ratings = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'explicit'))
			{
				foreach ($ratings as $rating)
				{
					$rating_scheme = 'urn:itunes';
					$rating_value = null;
					if (isset($rating['data']))
					{
						$rating_value = $this->sanitize($rating['data'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					$ratings_parent[] = &new $this->feed->rating_class($rating_scheme, $rating_value);
				}
			}
			if (is_array($ratings_parent))
			{
				$ratings_parent = array_values(SimplePie_Misc::array_unique($ratings_parent));
			}

			// RESTRICTIONS
			if ($restrictions = $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'restriction'))
			{
				foreach ($restrictions as $restriction)
				{
					$restriction_relationship = null;
					$restriction_type = null;
					$restriction_value = null;
					if (isset($restriction['attribs']['']['relationship']))
					{
						$restriction_relationship = $this->sanitize($restriction['attribs']['']['relationship'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($restriction['attribs']['']['type']))
					{
						$restriction_type = $this->sanitize($restriction['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($restriction['data']))
					{
						$restriction_value = $this->sanitize($restriction['data'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					$restrictions_parent[] = &new $this->feed->restriction_class($restriction_relationship, $restriction_type, $restriction_value);
				}
			}
			elseif ($restrictions = $this->get_item_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'block'))
			{
				foreach ($restrictions as $restriction)
				{
					$restriction_relationship = 'allow';
					$restriction_type = null;
					$restriction_value = 'itunes';
					if (isset($restriction['data']) && strtolower($restriction['data']) == 'yes')
					{
						$restriction_relationship = 'deny';
					}
					$restrictions_parent[] = &new $this->feed->restriction_class($restriction_relationship, $restriction_type, $restriction_value);
				}
			}
			elseif ($restrictions = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'restriction'))
			{
				foreach ($restrictions as $restriction)
				{
					$restriction_relationship = null;
					$restriction_type = null;
					$restriction_value = null;
					if (isset($restriction['attribs']['']['relationship']))
					{
						$restriction_relationship = $this->sanitize($restriction['attribs']['']['relationship'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($restriction['attribs']['']['type']))
					{
						$restriction_type = $this->sanitize($restriction['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($restriction['data']))
					{
						$restriction_value = $this->sanitize($restriction['data'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					$restrictions_parent[] = &new $this->feed->restriction_class($restriction_relationship, $restriction_type, $restriction_value);
				}
			}
			elseif ($restrictions = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_ITUNES, 'block'))
			{
				foreach ($restrictions as $restriction)
				{
					$restriction_relationship = 'allow';
					$restriction_type = null;
					$restriction_value = 'itunes';
					if (isset($restriction['data']) && strtolower($restriction['data']) == 'yes')
					{
						$restriction_relationship = 'deny';
					}
					$restrictions_parent[] = &new $this->feed->restriction_class($restriction_relationship, $restriction_type, $restriction_value);
				}
			}
			if (is_array($restrictions_parent))
			{
				$restrictions_parent = array_values(SimplePie_Misc::array_unique($restrictions_parent));
			}

			// THUMBNAILS
			if ($thumbnails = $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'thumbnail'))
			{
				foreach ($thumbnails as $thumbnail)
				{
					if (isset($thumbnail['attribs']['']['url']))
					{
						$thumbnails_parent[] = $this->sanitize($thumbnail['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI);
					}
				}
			}
			elseif ($thumbnails = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'thumbnail'))
			{
				foreach ($thumbnails as $thumbnail)
				{
					if (isset($thumbnail['attribs']['']['url']))
					{
						$thumbnails_parent[] = $this->sanitize($thumbnail['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI);
					}
				}
			}

			// TITLES
			if ($title_parent = $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'title'))
			{
				if (isset($title_parent[0]['data']))
				{
					$title_parent = $this->sanitize($title_parent[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
			}
			elseif ($title_parent = $parent->get_channel_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'title'))
			{
				if (isset($title_parent[0]['data']))
				{
					$title_parent = $this->sanitize($title_parent[0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
				}
			}

			// Clear the memory
			unset($parent);

			// If we have media:group tags, loop through them.
			foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'group') as $group)
			{
				// If we have media:content tags, loop through them.
				foreach ((array) $group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['content'] as $content)
				{
					if (isset($content['attribs']['']['url']))
					{
						// Attributes
						$bitrate = null;
						$channels = null;
						$duration = null;
						$expression = null;
						$framerate = null;
						$height = null;
						$javascript = null;
						$lang = null;
						$length = null;
						$medium = null;
						$samplingrate = null;
						$type = null;
						$url = null;
						$width = null;

						// Elements
						$captions = null;
						$categories = null;
						$copyrights = null;
						$credits = null;
						$description = null;
						$hashes = null;
						$keywords = null;
						$player = null;
						$ratings = null;
						$restrictions = null;
						$thumbnails = null;
						$title = null;

						// Start checking the attributes of media:content
						if (isset($content['attribs']['']['bitrate']))
						{
							$bitrate = $this->sanitize($content['attribs']['']['bitrate'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['channels']))
						{
							$channels = $this->sanitize($content['attribs']['']['channels'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['duration']))
						{
							$duration = $this->sanitize($content['attribs']['']['duration'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						else
						{
							$duration = $duration_parent;
						}
						if (isset($content['attribs']['']['expression']))
						{
							$expression = $this->sanitize($content['attribs']['']['expression'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['framerate']))
						{
							$framerate = $this->sanitize($content['attribs']['']['framerate'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['height']))
						{
							$height = $this->sanitize($content['attribs']['']['height'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['lang']))
						{
							$lang = $this->sanitize($content['attribs']['']['lang'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['fileSize']))
						{
							$length = ceil($content['attribs']['']['fileSize']);
						}
						if (isset($content['attribs']['']['medium']))
						{
							$medium = $this->sanitize($content['attribs']['']['medium'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['samplingrate']))
						{
							$samplingrate = $this->sanitize($content['attribs']['']['samplingrate'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['type']))
						{
							$type = $this->sanitize($content['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['width']))
						{
							$width = $this->sanitize($content['attribs']['']['width'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						$url = $this->sanitize($content['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI);

						// Checking the other optional media: elements. Priority: media:content, media:group, item, channel

						// CAPTIONS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['text']))
						{
							foreach ($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['text'] as $caption)
							{
								$caption_type = null;
								$caption_lang = null;
								$caption_startTime = null;
								$caption_endTime = null;
								$caption_text = null;
								if (isset($caption['attribs']['']['type']))
								{
									$caption_type = $this->sanitize($caption['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($caption['attribs']['']['lang']))
								{
									$caption_lang = $this->sanitize($caption['attribs']['']['lang'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($caption['attribs']['']['start']))
								{
									$caption_startTime = $this->sanitize($caption['attribs']['']['start'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($caption['attribs']['']['end']))
								{
									$caption_endTime = $this->sanitize($caption['attribs']['']['end'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($caption['data']))
								{
									$caption_text = $this->sanitize($caption['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$captions[] = &new $this->feed->caption_class($caption_type, $caption_lang, $caption_startTime, $caption_endTime, $caption_text);
							}
							if (is_array($captions))
							{
								$captions = array_values(SimplePie_Misc::array_unique($captions));
							}
						}
						elseif (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['text']))
						{
							foreach ($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['text'] as $caption)
							{
								$caption_type = null;
								$caption_lang = null;
								$caption_startTime = null;
								$caption_endTime = null;
								$caption_text = null;
								if (isset($caption['attribs']['']['type']))
								{
									$caption_type = $this->sanitize($caption['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($caption['attribs']['']['lang']))
								{
									$caption_lang = $this->sanitize($caption['attribs']['']['lang'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($caption['attribs']['']['start']))
								{
									$caption_startTime = $this->sanitize($caption['attribs']['']['start'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($caption['attribs']['']['end']))
								{
									$caption_endTime = $this->sanitize($caption['attribs']['']['end'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($caption['data']))
								{
									$caption_text = $this->sanitize($caption['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$captions[] = &new $this->feed->caption_class($caption_type, $caption_lang, $caption_startTime, $caption_endTime, $caption_text);
							}
							if (is_array($captions))
							{
								$captions = array_values(SimplePie_Misc::array_unique($captions));
							}
						}
						else
						{
							$captions = $captions_parent;
						}

						// CATEGORIES
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['category']))
						{
							foreach ((array) $content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['category'] as $category)
							{
								$term = null;
								$scheme = null;
								$label = null;
								if (isset($category['data']))
								{
									$term = $this->sanitize($category['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($category['attribs']['']['scheme']))
								{
									$scheme = $this->sanitize($category['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								else
								{
									$scheme = 'http://search.yahoo.com/mrss/category_schema';
								}
								if (isset($category['attribs']['']['label']))
								{
									$label = $this->sanitize($category['attribs']['']['label'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$categories[] = &new $this->feed->category_class($term, $scheme, $label);
							}
						}
						if (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['category']))
						{
							foreach ((array) $group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['category'] as $category)
							{
								$term = null;
								$scheme = null;
								$label = null;
								if (isset($category['data']))
								{
									$term = $this->sanitize($category['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($category['attribs']['']['scheme']))
								{
									$scheme = $this->sanitize($category['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								else
								{
									$scheme = 'http://search.yahoo.com/mrss/category_schema';
								}
								if (isset($category['attribs']['']['label']))
								{
									$label = $this->sanitize($category['attribs']['']['label'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$categories[] = &new $this->feed->category_class($term, $scheme, $label);
							}
						}
						if (is_array($categories) && is_array($categories_parent))
						{
							$categories = array_values(SimplePie_Misc::array_unique(array_merge($categories, $categories_parent)));
						}
						elseif (is_array($categories))
						{
							$categories = array_values(SimplePie_Misc::array_unique($categories));
						}
						elseif (is_array($categories_parent))
						{
							$categories = array_values(SimplePie_Misc::array_unique($categories_parent));
						}

						// COPYRIGHTS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright']))
						{
							$copyright_url = null;
							$copyright_label = null;
							if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright'][0]['attribs']['']['url']))
							{
								$copyright_url = $this->sanitize($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright'][0]['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_TEXT);
							}
							if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright'][0]['data']))
							{
								$copyright_label = $this->sanitize($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
							}
							$copyrights = &new $this->feed->copyright_class($copyright_url, $copyright_label);
						}
						elseif (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright']))
						{
							$copyright_url = null;
							$copyright_label = null;
							if (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright'][0]['attribs']['']['url']))
							{
								$copyright_url = $this->sanitize($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright'][0]['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_TEXT);
							}
							if (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright'][0]['data']))
							{
								$copyright_label = $this->sanitize($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
							}
							$copyrights = &new $this->feed->copyright_class($copyright_url, $copyright_label);
						}
						else
						{
							$copyrights = $copyrights_parent;
						}

						// CREDITS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['credit']))
						{
							foreach ($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['credit'] as $credit)
							{
								$credit_role = null;
								$credit_scheme = null;
								$credit_name = null;
								if (isset($credit['attribs']['']['role']))
								{
									$credit_role = $this->sanitize($credit['attribs']['']['role'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($credit['attribs']['']['scheme']))
								{
									$credit_scheme = $this->sanitize($credit['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								else
								{
									$credit_scheme = 'urn:ebu';
								}
								if (isset($credit['data']))
								{
									$credit_name = $this->sanitize($credit['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$credits[] = &new $this->feed->credit_class($credit_role, $credit_scheme, $credit_name);
							}
							if (is_array($credits))
							{
								$credits = array_values(SimplePie_Misc::array_unique($credits));
							}
						}
						elseif (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['credit']))
						{
							foreach ($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['credit'] as $credit)
							{
								$credit_role = null;
								$credit_scheme = null;
								$credit_name = null;
								if (isset($credit['attribs']['']['role']))
								{
									$credit_role = $this->sanitize($credit['attribs']['']['role'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($credit['attribs']['']['scheme']))
								{
									$credit_scheme = $this->sanitize($credit['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								else
								{
									$credit_scheme = 'urn:ebu';
								}
								if (isset($credit['data']))
								{
									$credit_name = $this->sanitize($credit['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$credits[] = &new $this->feed->credit_class($credit_role, $credit_scheme, $credit_name);
							}
							if (is_array($credits))
							{
								$credits = array_values(SimplePie_Misc::array_unique($credits));
							}
						}
						else
						{
							$credits = $credits_parent;
						}

						// DESCRIPTION
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['description']))
						{
							$description = $this->sanitize($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['description'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						elseif (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['description']))
						{
							$description = $this->sanitize($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['description'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						else
						{
							$description = $description_parent;
						}

						// HASHES
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['hash']))
						{
							foreach ($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['hash'] as $hash)
							{
								$value = null;
								$algo = null;
								if (isset($hash['data']))
								{
									$value = $this->sanitize($hash['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($hash['attribs']['']['algo']))
								{
									$algo = $this->sanitize($hash['attribs']['']['algo'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								else
								{
									$algo = 'md5';
								}
								$hashes[] = $algo.':'.$value;
							}
							if (is_array($hashes))
							{
								$hashes = array_values(SimplePie_Misc::array_unique($hashes));
							}
						}
						elseif (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['hash']))
						{
							foreach ($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['hash'] as $hash)
							{
								$value = null;
								$algo = null;
								if (isset($hash['data']))
								{
									$value = $this->sanitize($hash['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($hash['attribs']['']['algo']))
								{
									$algo = $this->sanitize($hash['attribs']['']['algo'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								else
								{
									$algo = 'md5';
								}
								$hashes[] = $algo.':'.$value;
							}
							if (is_array($hashes))
							{
								$hashes = array_values(SimplePie_Misc::array_unique($hashes));
							}
						}
						else
						{
							$hashes = $hashes_parent;
						}

						// KEYWORDS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['keywords']))
						{
							if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['keywords'][0]['data']))
							{
								$temp = explode(',', $this->sanitize($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['keywords'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT));
								foreach ($temp as $word)
								{
									$keywords[] = trim($word);
								}
								unset($temp);
							}
							if (is_array($keywords))
							{
								$keywords = array_values(SimplePie_Misc::array_unique($keywords));
							}
						}
						elseif (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['keywords']))
						{
							if (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['keywords'][0]['data']))
							{
								$temp = explode(',', $this->sanitize($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['keywords'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT));
								foreach ($temp as $word)
								{
									$keywords[] = trim($word);
								}
								unset($temp);
							}
							if (is_array($keywords))
							{
								$keywords = array_values(SimplePie_Misc::array_unique($keywords));
							}
						}
						else
						{
							$keywords = $keywords_parent;
						}

						// PLAYER
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['player']))
						{
							$player = $this->sanitize($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['player'][0]['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI);
						}
						elseif (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['player']))
						{
							$player = $this->sanitize($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['player'][0]['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI);
						}
						else
						{
							$player = $player_parent;
						}

						// RATINGS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['rating']))
						{
							foreach ($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['rating'] as $rating)
							{
								$rating_scheme = null;
								$rating_value = null;
								if (isset($rating['attribs']['']['scheme']))
								{
									$rating_scheme = $this->sanitize($rating['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								else
								{
									$rating_scheme = 'urn:simple';
								}
								if (isset($rating['data']))
								{
									$rating_value = $this->sanitize($rating['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$ratings[] = &new $this->feed->rating_class($rating_scheme, $rating_value);
							}
							if (is_array($ratings))
							{
								$ratings = array_values(SimplePie_Misc::array_unique($ratings));
							}
						}
						elseif (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['rating']))
						{
							foreach ($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['rating'] as $rating)
							{
								$rating_scheme = null;
								$rating_value = null;
								if (isset($rating['attribs']['']['scheme']))
								{
									$rating_scheme = $this->sanitize($rating['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								else
								{
									$rating_scheme = 'urn:simple';
								}
								if (isset($rating['data']))
								{
									$rating_value = $this->sanitize($rating['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$ratings[] = &new $this->feed->rating_class($rating_scheme, $rating_value);
							}
							if (is_array($ratings))
							{
								$ratings = array_values(SimplePie_Misc::array_unique($ratings));
							}
						}
						else
						{
							$ratings = $ratings_parent;
						}

						// RESTRICTIONS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['restriction']))
						{
							foreach ($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['restriction'] as $restriction)
							{
								$restriction_relationship = null;
								$restriction_type = null;
								$restriction_value = null;
								if (isset($restriction['attribs']['']['relationship']))
								{
									$restriction_relationship = $this->sanitize($restriction['attribs']['']['relationship'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($restriction['attribs']['']['type']))
								{
									$restriction_type = $this->sanitize($restriction['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($restriction['data']))
								{
									$restriction_value = $this->sanitize($restriction['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$restrictions[] = &new $this->feed->restriction_class($restriction_relationship, $restriction_type, $restriction_value);
							}
							if (is_array($restrictions))
							{
								$restrictions = array_values(SimplePie_Misc::array_unique($restrictions));
							}
						}
						elseif (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['restriction']))
						{
							foreach ($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['restriction'] as $restriction)
							{
								$restriction_relationship = null;
								$restriction_type = null;
								$restriction_value = null;
								if (isset($restriction['attribs']['']['relationship']))
								{
									$restriction_relationship = $this->sanitize($restriction['attribs']['']['relationship'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($restriction['attribs']['']['type']))
								{
									$restriction_type = $this->sanitize($restriction['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($restriction['data']))
								{
									$restriction_value = $this->sanitize($restriction['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$restrictions[] = &new $this->feed->restriction_class($restriction_relationship, $restriction_type, $restriction_value);
							}
							if (is_array($restrictions))
							{
								$restrictions = array_values(SimplePie_Misc::array_unique($restrictions));
							}
						}
						else
						{
							$restrictions = $restrictions_parent;
						}

						// THUMBNAILS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['thumbnail']))
						{
							foreach ($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['thumbnail'] as $thumbnail)
							{
								$thumbnails[] = $this->sanitize($thumbnail['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI);
							}
							if (is_array($thumbnails))
							{
								$thumbnails = array_values(SimplePie_Misc::array_unique($thumbnails));
							}
						}
						elseif (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['thumbnail']))
						{
							foreach ($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['thumbnail'] as $thumbnail)
							{
								$thumbnails[] = $this->sanitize($thumbnail['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI);
							}
							if (is_array($thumbnails))
							{
								$thumbnails = array_values(SimplePie_Misc::array_unique($thumbnails));
							}
						}
						else
						{
							$thumbnails = $thumbnails_parent;
						}

						// TITLES
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['title']))
						{
							$title = $this->sanitize($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['title'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						elseif (isset($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['title']))
						{
							$title = $this->sanitize($group['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['title'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						else
						{
							$title = $title_parent;
						}

						$this->data['enclosures'][] = &new $this->feed->enclosure_class($url, $type, $length, $this->feed->javascript, $bitrate, $captions, $categories, $channels, $copyrights, $credits, $description, $duration, $expression, $framerate, $hashes, $height, $keywords, $lang, $medium, $player, $ratings, $restrictions, $samplingrate, $thumbnails, $title, $width);
					}
				}
			}

			// If we have standalone media:content tags, loop through them.
			if (isset($this->data['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['content']))
			{
				foreach ((array) $this->data['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['content'] as $content)
				{
					if (isset($content['attribs']['']['url']))
					{
						// Attributes
						$bitrate = null;
						$channels = null;
						$duration = null;
						$expression = null;
						$framerate = null;
						$height = null;
						$javascript = null;
						$lang = null;
						$length = null;
						$medium = null;
						$samplingrate = null;
						$type = null;
						$url = null;
						$width = null;

						// Elements
						$captions = null;
						$categories = null;
						$copyrights = null;
						$credits = null;
						$description = null;
						$hashes = null;
						$keywords = null;
						$player = null;
						$ratings = null;
						$restrictions = null;
						$thumbnails = null;
						$title = null;

						// Start checking the attributes of media:content
						if (isset($content['attribs']['']['bitrate']))
						{
							$bitrate = $this->sanitize($content['attribs']['']['bitrate'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['channels']))
						{
							$channels = $this->sanitize($content['attribs']['']['channels'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['duration']))
						{
							$duration = $this->sanitize($content['attribs']['']['duration'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						else
						{
							$duration = $duration_parent;
						}
						if (isset($content['attribs']['']['expression']))
						{
							$expression = $this->sanitize($content['attribs']['']['expression'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['framerate']))
						{
							$framerate = $this->sanitize($content['attribs']['']['framerate'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['height']))
						{
							$height = $this->sanitize($content['attribs']['']['height'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['lang']))
						{
							$lang = $this->sanitize($content['attribs']['']['lang'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['fileSize']))
						{
							$length = ceil($content['attribs']['']['fileSize']);
						}
						if (isset($content['attribs']['']['medium']))
						{
							$medium = $this->sanitize($content['attribs']['']['medium'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['samplingrate']))
						{
							$samplingrate = $this->sanitize($content['attribs']['']['samplingrate'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['type']))
						{
							$type = $this->sanitize($content['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						if (isset($content['attribs']['']['width']))
						{
							$width = $this->sanitize($content['attribs']['']['width'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						$url = $this->sanitize($content['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI);

						// Checking the other optional media: elements. Priority: media:content, media:group, item, channel

						// CAPTIONS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['text']))
						{
							foreach ($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['text'] as $caption)
							{
								$caption_type = null;
								$caption_lang = null;
								$caption_startTime = null;
								$caption_endTime = null;
								$caption_text = null;
								if (isset($caption['attribs']['']['type']))
								{
									$caption_type = $this->sanitize($caption['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($caption['attribs']['']['lang']))
								{
									$caption_lang = $this->sanitize($caption['attribs']['']['lang'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($caption['attribs']['']['start']))
								{
									$caption_startTime = $this->sanitize($caption['attribs']['']['start'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($caption['attribs']['']['end']))
								{
									$caption_endTime = $this->sanitize($caption['attribs']['']['end'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($caption['data']))
								{
									$caption_text = $this->sanitize($caption['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$captions[] = &new $this->feed->caption_class($caption_type, $caption_lang, $caption_startTime, $caption_endTime, $caption_text);
							}
							if (is_array($captions))
							{
								$captions = array_values(SimplePie_Misc::array_unique($captions));
							}
						}
						else
						{
							$captions = $captions_parent;
						}

						// CATEGORIES
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['category']))
						{
							foreach ((array) $content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['category'] as $category)
							{
								$term = null;
								$scheme = null;
								$label = null;
								if (isset($category['data']))
								{
									$term = $this->sanitize($category['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($category['attribs']['']['scheme']))
								{
									$scheme = $this->sanitize($category['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								else
								{
									$scheme = 'http://search.yahoo.com/mrss/category_schema';
								}
								if (isset($category['attribs']['']['label']))
								{
									$label = $this->sanitize($category['attribs']['']['label'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$categories[] = &new $this->feed->category_class($term, $scheme, $label);
							}
						}
						if (is_array($categories) && is_array($categories_parent))
						{
							$categories = array_values(SimplePie_Misc::array_unique(array_merge($categories, $categories_parent)));
						}
						elseif (is_array($categories))
						{
							$categories = array_values(SimplePie_Misc::array_unique($categories));
						}
						elseif (is_array($categories_parent))
						{
							$categories = array_values(SimplePie_Misc::array_unique($categories_parent));
						}
						else
						{
							$categories = null;
						}

						// COPYRIGHTS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright']))
						{
							$copyright_url = null;
							$copyright_label = null;
							if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright'][0]['attribs']['']['url']))
							{
								$copyright_url = $this->sanitize($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright'][0]['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_TEXT);
							}
							if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright'][0]['data']))
							{
								$copyright_label = $this->sanitize($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['copyright'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
							}
							$copyrights = &new $this->feed->copyright_class($copyright_url, $copyright_label);
						}
						else
						{
							$copyrights = $copyrights_parent;
						}

						// CREDITS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['credit']))
						{
							foreach ($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['credit'] as $credit)
							{
								$credit_role = null;
								$credit_scheme = null;
								$credit_name = null;
								if (isset($credit['attribs']['']['role']))
								{
									$credit_role = $this->sanitize($credit['attribs']['']['role'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($credit['attribs']['']['scheme']))
								{
									$credit_scheme = $this->sanitize($credit['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								else
								{
									$credit_scheme = 'urn:ebu';
								}
								if (isset($credit['data']))
								{
									$credit_name = $this->sanitize($credit['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$credits[] = &new $this->feed->credit_class($credit_role, $credit_scheme, $credit_name);
							}
							if (is_array($credits))
							{
								$credits = array_values(SimplePie_Misc::array_unique($credits));
							}
						}
						else
						{
							$credits = $credits_parent;
						}

						// DESCRIPTION
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['description']))
						{
							$description = $this->sanitize($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['description'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						else
						{
							$description = $description_parent;
						}

						// HASHES
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['hash']))
						{
							foreach ($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['hash'] as $hash)
							{
								$value = null;
								$algo = null;
								if (isset($hash['data']))
								{
									$value = $this->sanitize($hash['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($hash['attribs']['']['algo']))
								{
									$algo = $this->sanitize($hash['attribs']['']['algo'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								else
								{
									$algo = 'md5';
								}
								$hashes[] = $algo.':'.$value;
							}
							if (is_array($hashes))
							{
								$hashes = array_values(SimplePie_Misc::array_unique($hashes));
							}
						}
						else
						{
							$hashes = $hashes_parent;
						}

						// KEYWORDS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['keywords']))
						{
							if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['keywords'][0]['data']))
							{
								$temp = explode(',', $this->sanitize($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['keywords'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT));
								foreach ($temp as $word)
								{
									$keywords[] = trim($word);
								}
								unset($temp);
							}
							if (is_array($keywords))
							{
								$keywords = array_values(SimplePie_Misc::array_unique($keywords));
							}
						}
						else
						{
							$keywords = $keywords_parent;
						}

						// PLAYER
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['player']))
						{
							$player = $this->sanitize($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['player'][0]['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI);
						}
						else
						{
							$player = $player_parent;
						}

						// RATINGS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['rating']))
						{
							foreach ($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['rating'] as $rating)
							{
								$rating_scheme = null;
								$rating_value = null;
								if (isset($rating['attribs']['']['scheme']))
								{
									$rating_scheme = $this->sanitize($rating['attribs']['']['scheme'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								else
								{
									$rating_scheme = 'urn:simple';
								}
								if (isset($rating['data']))
								{
									$rating_value = $this->sanitize($rating['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$ratings[] = &new $this->feed->rating_class($rating_scheme, $rating_value);
							}
							if (is_array($ratings))
							{
								$ratings = array_values(SimplePie_Misc::array_unique($ratings));
							}
						}
						else
						{
							$ratings = $ratings_parent;
						}

						// RESTRICTIONS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['restriction']))
						{
							foreach ($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['restriction'] as $restriction)
							{
								$restriction_relationship = null;
								$restriction_type = null;
								$restriction_value = null;
								if (isset($restriction['attribs']['']['relationship']))
								{
									$restriction_relationship = $this->sanitize($restriction['attribs']['']['relationship'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($restriction['attribs']['']['type']))
								{
									$restriction_type = $this->sanitize($restriction['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								if (isset($restriction['data']))
								{
									$restriction_value = $this->sanitize($restriction['data'], SIMPLEPIE_CONSTRUCT_TEXT);
								}
								$restrictions[] = &new $this->feed->restriction_class($restriction_relationship, $restriction_type, $restriction_value);
							}
							if (is_array($restrictions))
							{
								$restrictions = array_values(SimplePie_Misc::array_unique($restrictions));
							}
						}
						else
						{
							$restrictions = $restrictions_parent;
						}

						// THUMBNAILS
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['thumbnail']))
						{
							foreach ($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['thumbnail'] as $thumbnail)
							{
								$thumbnails[] = $this->sanitize($thumbnail['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI);
							}
							if (is_array($thumbnails))
							{
								$thumbnails = array_values(SimplePie_Misc::array_unique($thumbnails));
							}
						}
						else
						{
							$thumbnails = $thumbnails_parent;
						}

						// TITLES
						if (isset($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['title']))
						{
							$title = $this->sanitize($content['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['title'][0]['data'], SIMPLEPIE_CONSTRUCT_TEXT);
						}
						else
						{
							$title = $title_parent;
						}

						$this->data['enclosures'][] = &new $this->feed->enclosure_class($url, $type, $length, $this->feed->javascript, $bitrate, $captions, $categories, $channels, $copyrights, $credits, $description, $duration, $expression, $framerate, $hashes, $height, $keywords, $lang, $medium, $player, $ratings, $restrictions, $samplingrate, $thumbnails, $title, $width);
					}
				}
			}

			foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'link') as $link)
			{
				if (isset($link['attribs']['']['href']) && !empty($link['attribs']['']['rel']) && $link['attribs']['']['rel'] == 'enclosure')
				{
					// Attributes
					$bitrate = null;
					$channels = null;
					$duration = null;
					$expression = null;
					$framerate = null;
					$height = null;
					$javascript = null;
					$lang = null;
					$length = null;
					$medium = null;
					$samplingrate = null;
					$type = null;
					$url = null;
					$width = null;

					$url = $this->sanitize($link['attribs']['']['href'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($link));
					if (isset($link['attribs']['']['type']))
					{
						$type = $this->sanitize($link['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($link['attribs']['']['length']))
					{
						$length = ceil($link['attribs']['']['length']);
					}

					// Since we don't have group or content for these, we'll just pass the '*_parent' variables directly to the constructor
					$this->data['enclosures'][] = &new $this->feed->enclosure_class($url, $type, $length, $this->feed->javascript, $bitrate, $captions_parent, $categories_parent, $channels, $copyrights_parent, $credits_parent, $description_parent, $duration_parent, $expression, $framerate, $hashes_parent, $height, $keywords_parent, $lang, $medium, $player_parent, $ratings_parent, $restrictions_parent, $samplingrate, $thumbnails_parent, $title_parent, $width);
				}
			}

			foreach ((array) $this->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_03, 'link') as $link)
			{
				if (isset($link['attribs']['']['href']) && !empty($link['attribs']['']['rel']) && $link['attribs']['']['rel'] == 'enclosure')
				{
					// Attributes
					$bitrate = null;
					$channels = null;
					$duration = null;
					$expression = null;
					$framerate = null;
					$height = null;
					$javascript = null;
					$lang = null;
					$length = null;
					$medium = null;
					$samplingrate = null;
					$type = null;
					$url = null;
					$width = null;

					$url = $this->sanitize($link['attribs']['']['href'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($link));
					if (isset($link['attribs']['']['type']))
					{
						$type = $this->sanitize($link['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($link['attribs']['']['length']))
					{
						$length = ceil($link['attribs']['']['length']);
					}

					// Since we don't have group or content for these, we'll just pass the '*_parent' variables directly to the constructor
					$this->data['enclosures'][] = &new $this->feed->enclosure_class($url, $type, $length, $this->feed->javascript, $bitrate, $captions_parent, $categories_parent, $channels, $copyrights_parent, $credits_parent, $description_parent, $duration_parent, $expression, $framerate, $hashes_parent, $height, $keywords_parent, $lang, $medium, $player_parent, $ratings_parent, $restrictions_parent, $samplingrate, $thumbnails_parent, $title_parent, $width);
				}
			}

			if ($enclosure = $this->get_item_tags('', 'enclosure'))
			{
				if (isset($enclosure[0]['attribs']['']['url']))
				{
					// Attributes
					$bitrate = null;
					$channels = null;
					$duration = null;
					$expression = null;
					$framerate = null;
					$height = null;
					$javascript = null;
					$lang = null;
					$length = null;
					$medium = null;
					$samplingrate = null;
					$type = null;
					$url = null;
					$width = null;

					$url = $this->sanitize($enclosure[0]['attribs']['']['url'], SIMPLEPIE_CONSTRUCT_IRI, $this->get_base($enclosure[0]));
					if (isset($enclosure[0]['attribs']['']['type']))
					{
						$type = $this->sanitize($enclosure[0]['attribs']['']['type'], SIMPLEPIE_CONSTRUCT_TEXT);
					}
					if (isset($enclosure[0]['attribs']['']['length']))
					{
						$length = ceil($enclosure[0]['attribs']['']['length']);
					}

					// Since we don't have group or content for these, we'll just pass the '*_parent' variables directly to the constructor
					$this->data['enclosures'][] = &new $this->feed->enclosure_class($url, $type, $length, $this->feed->javascript, $bitrate, $captions_parent, $categories_parent, $channels, $copyrights_parent, $credits_parent, $description_parent, $duration_parent, $expression, $framerate, $hashes_parent, $height, $keywords_parent, $lang, $medium, $player_parent, $ratings_parent, $restrictions_parent, $samplingrate, $thumbnails_parent, $title_parent, $width);
				}
			}
			$this->data['enclosures'] = array_values(SimplePie_Misc::array_unique($this->data['enclosures']));
		}
		if (!empty($this->data['enclosures']))
		{
			return $this->data['enclosures'];
		}
		else
		{
			return null;
		}
	}

	function get_latitude()
	{
		if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_W3C_BASIC_GEO, 'lat'))
		{
			return (float) $return[0]['data'];
		}
		elseif (($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_GEORSS, 'point')) && preg_match('/^((?:-)?[0-9]+(?:\.[0-9]+)) ((?:-)?[0-9]+(?:\.[0-9]+))$/', $return[0]['data'], $match))
		{
			return (float) $match[1];
		}
		else
		{
			return null;
		}
	}

	function get_longitude()
	{
		if ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_W3C_BASIC_GEO, 'long'))
		{
			return (float) $return[0]['data'];
		}
		elseif ($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_W3C_BASIC_GEO, 'lon'))
		{
			return (float) $return[0]['data'];
		}
		elseif (($return = $this->get_item_tags(SIMPLEPIE_NAMESPACE_GEORSS, 'point')) && preg_match('/^((?:-)?[0-9]+(?:\.[0-9]+)) ((?:-)?[0-9]+(?:\.[0-9]+))$/', $return[0]['data'], $match))
		{
			return (float) $match[2];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Creates the add_to_* methods' return data
	 *
	 * @access private
	 * @param string $item_url String to prefix to the item permalink
	 * @param string $title_url String to prefix to the item title
	 * (and suffix to the item permalink)
	 * @return mixed URL if feed exists, false otherwise
	 */
	function add_to_service($item_url, $title_url = null)
	{
		if ($this->get_permalink() !== null)
		{
			$return = $this->sanitize($item_url, SIMPLEPIE_CONSTRUCT_IRI) . rawurlencode($this->get_permalink());
			if ($title_url !== null && $this->get_title() !== null)
			{
				$return .= $this->sanitize($title_url, SIMPLEPIE_CONSTRUCT_IRI) . rawurlencode($this->get_title());
			}
			return $return;
		}
		else
		{
			return null;
		}
	}

	function add_to_blinklist()
	{
		return $this->add_to_service('http://www.blinklist.com/index.php?Action=Blink/addblink.php&Description=&Url=', '&Title=');
	}

	function add_to_blogmarks()
	{
		return $this->add_to_service('http://blogmarks.net/my/new.php?mini=1&simple=1&url=', '&title=');
	}

	function add_to_delicious()
	{
		return $this->add_to_service('http://del.icio.us/post/?v=3&url=', '&title=');
	}

	function add_to_digg()
	{
		return $this->add_to_service('http://digg.com/submit?phase=2&URL=');
	}

	function add_to_furl()
	{
		return $this->add_to_service('http://www.furl.net/storeIt.jsp?u=', '&t=');
	}

	function add_to_magnolia()
	{
		return $this->add_to_service('http://ma.gnolia.com/bookmarklet/add?url=', '&title=');
	}

	function add_to_myweb20()
	{
		return $this->add_to_service('http://myweb2.search.yahoo.com/myresults/bookmarklet?u=', '&t=');
	}

	function add_to_newsvine()
	{
		return $this->add_to_service('http://www.newsvine.com/_wine/save?u=', '&h=');
	}

	function add_to_reddit()
	{
		return $this->add_to_service('http://reddit.com/submit?url=', '&title=');
	}

	function add_to_segnalo()
	{
		return $this->add_to_service('http://segnalo.com/post.html.php?url=', '&title=');
	}

	function add_to_simpy()
	{
		return $this->add_to_service('http://www.simpy.com/simpy/LinkAdd.do?href=', '&title=');
	}

	function add_to_spurl()
	{
		return $this->add_to_service('http://www.spurl.net/spurl.php?v=3&url=', '&title=');
	}

	function add_to_wists()
	{
		return $this->add_to_service('http://wists.com/r.php?c=&r=', '&title=');
	}

	function search_technorati()
	{
		return $this->add_to_service('http://www.technorati.com/search/');
	}
}

class SimplePie_Author
{
	var $name;
	var $link;
	var $email;

	// Constructor, used to input the data
	function SimplePie_Author($name = null, $link = null, $email = null)
	{
		$this->name = $name;
		$this->link = $link;
		$this->email = $email;
	}

	function __toString()
	{
		// There is no $this->data here
		return md5(serialize($this));
	}

	function get_name()
	{
		if ($this->name !== null)
		{
			return $this->name;
		}
		else
		{
			return null;
		}
	}

	function get_link()
	{
		if ($this->link !== null)
		{
			return $this->link;
		}
		else
		{
			return null;
		}
	}

	function get_email()
	{
		if ($this->email !== null)
		{
			return $this->email;
		}
		else
		{
			return null;
		}
	}
}

class SimplePie_Category
{
	var $term;
	var $scheme;
	var $label;

	// Constructor, used to input the data
	function SimplePie_Category($term = null, $scheme = null, $label = null)
	{
		$this->term = $term;
		$this->scheme = $scheme;
		$this->label = $label;
	}

	function __toString()
	{
		// There is no $this->data here
		return md5(serialize($this));
	}

	function get_term()
	{
		if ($this->term !== null)
		{
			return $this->term;
		}
		else
		{
			return null;
		}
	}

	function get_scheme()
	{
		if ($this->scheme !== null)
		{
			return $this->scheme;
		}
		else
		{
			return null;
		}
	}

	function get_label()
	{
		if ($this->label !== null)
		{
			return $this->label;
		}
		else
		{
			return $this->get_term();
		}
	}
}

class SimplePie_Enclosure
{
	var $bitrate;
	var $captions;
	var $categories;
	var $channels;
	var $copyright;
	var $credits;
	var $description;
	var $duration;
	var $expression;
	var $framerate;
	var $handler;
	var $hashes;
	var $height;
	var $javascript;
	var $keywords;
	var $lang;
	var $length;
	var $link;
	var $medium;
	var $player;
	var $ratings;
	var $restrictions;
	var $samplingrate;
	var $thumbnails;
	var $title;
	var $type;
	var $width;

	// Constructor, used to input the data
	function SimplePie_Enclosure($link = null, $type = null, $length = null, $javascript = null, $bitrate = null, $captions = null, $categories = null, $channels = null, $copyright = null, $credits = null, $description = null, $duration = null, $expression = null, $framerate = null, $hashes = null, $height = null, $keywords = null, $lang = null, $medium = null, $player = null, $ratings = null, $restrictions = null, $samplingrate = null, $thumbnails = null, $title = null, $width = null)
	{
		$this->bitrate = $bitrate;
		$this->captions = $captions;
		$this->categories = $categories;
		$this->channels = $channels;
		$this->copyright = $copyright;
		$this->credits = $credits;
		$this->description = $description;
		$this->duration = $duration;
		$this->expression = $expression;
		$this->framerate = $framerate;
		$this->hashes = $hashes;
		$this->height = $height;
		$this->javascript = $javascript;
		$this->keywords = $keywords;
		$this->lang = $lang;
		$this->length = $length;
		$this->link = $link;
		$this->medium = $medium;
		$this->player = $player;
		$this->ratings = $ratings;
		$this->restrictions = $restrictions;
		$this->samplingrate = $samplingrate;
		$this->thumbnails = $thumbnails;
		$this->title = $title;
		$this->type = $type;
		$this->width = $width;
		if (class_exists('idna_convert'))
		{
			$idn = &new idna_convert;
			$parsed = SimplePie_Misc::parse_url($link);
			$this->link = SimplePie_Misc::compress_parse_url($parsed['scheme'], $idn->encode($parsed['authority']), $parsed['path'], $parsed['query'], $parsed['fragment']);
		}
		$this->handler = $this->get_handler(); // Needs to load last
	}

	function __toString()
	{
		// There is no $this->data here
		return md5(serialize($this));
	}

	function get_bitrate()
	{
		if ($this->bitrate !== null)
		{
			return $this->bitrate;
		}
		else
		{
			return null;
		}
	}

	function get_caption($key = 0)
	{
		$captions = $this->get_captions();
		if (isset($captions[$key]))
		{
			return $captions[$key];
		}
		else
		{
			return null;
		}
	}

	function get_captions()
	{
		if ($this->captions !== null)
		{
			return $this->captions;
		}
		else
		{
			return null;
		}
	}

	function get_category($key = 0)
	{
		$categories = $this->get_categories();
		if (isset($categories[$key]))
		{
			return $categories[$key];
		}
		else
		{
			return null;
		}
	}

	function get_categories()
	{
		if ($this->categories !== null)
		{
			return $this->categories;
		}
		else
		{
			return null;
		}
	}

	function get_channels()
	{
		if ($this->channels !== null)
		{
			return $this->channels;
		}
		else
		{
			return null;
		}
	}

	function get_copyright()
	{
		if ($this->copyright !== null)
		{
			return $this->copyright;
		}
		else
		{
			return null;
		}
	}

	function get_credit($key = 0)
	{
		$credits = $this->get_credits();
		if (isset($credits[$key]))
		{
			return $credits[$key];
		}
		else
		{
			return null;
		}
	}

	function get_credits()
	{
		if ($this->credits !== null)
		{
			return $this->credits;
		}
		else
		{
			return null;
		}
	}

	function get_description()
	{
		if ($this->description !== null)
		{
			return $this->description;
		}
		else
		{
			return null;
		}
	}

	function get_duration($convert = false)
	{
		if ($this->duration !== null)
		{
			if ($convert)
			{
				$time = SimplePie_Misc::time_hms($this->duration);
				return $time;
			}
			else
			{
				return $this->duration;
			}
		}
		else
		{
			return null;
		}
	}

	function get_expression()
	{
		if ($this->expression !== null)
		{
			return $this->expression;
		}
		else
		{
			return 'full';
		}
	}

	function get_extension()
	{
		if ($this->link !== null)
		{
			$url = SimplePie_Misc::parse_url($this->link);
			if ($url['path'] !== '')
			{
				return pathinfo($url['path'], PATHINFO_EXTENSION);
			}
		}
		return null;
	}

	function get_framerate()
	{
		if ($this->framerate !== null)
		{
			return $this->framerate;
		}
		else
		{
			return null;
		}
	}

	function get_handler()
	{
		return $this->get_real_type(true);
	}

	function get_hash($key = 0)
	{
		$hashes = $this->get_hashes();
		if (isset($hashes[$key]))
		{
			return $hashes[$key];
		}
		else
		{
			return null;
		}
	}

	function get_hashes()
	{
		if ($this->hashes !== null)
		{
			return $this->hashes;
		}
		else
		{
			return null;
		}
	}

	function get_height()
	{
		if ($this->height !== null)
		{
			return $this->height;
		}
		else
		{
			return null;
		}
	}

	function get_language()
	{
		if ($this->lang !== null)
		{
			return $this->lang;
		}
		else
		{
			return null;
		}
	}

	function get_keyword($key = 0)
	{
		$keywords = $this->get_keywords();
		if (isset($keywords[$key]))
		{
			return $keywords[$key];
		}
		else
		{
			return null;
		}
	}

	function get_keywords()
	{
		if ($this->keywords !== null)
		{
			return $this->keywords;
		}
		else
		{
			return null;
		}
	}

	function get_length()
	{
		if ($this->length !== null)
		{
			return $this->length;
		}
		else
		{
			return null;
		}
	}

	function get_link()
	{
		if ($this->link !== null)
		{
			return urldecode($this->link);
		}
		else
		{
			return null;
		}
	}

	function get_medium()
	{
		if ($this->medium !== null)
		{
			return $this->medium;
		}
		else
		{
			return null;
		}
	}

	function get_player()
	{
		if ($this->player !== null)
		{
			return $this->player;
		}
		else
		{
			return null;
		}
	}

	function get_rating($key = 0)
	{
		$ratings = $this->get_ratings();
		if (isset($ratings[$key]))
		{
			return $ratings[$key];
		}
		else
		{
			return null;
		}
	}

	function get_ratings()
	{
		if ($this->ratings !== null)
		{
			return $this->ratings;
		}
		else
		{
			return null;
		}
	}

	function get_restriction($key = 0)
	{
		$restrictions = $this->get_restrictions();
		if (isset($restrictions[$key]))
		{
			return $restrictions[$key];
		}
		else
		{
			return null;
		}
	}

	function get_restrictions()
	{
		if ($this->restrictions !== null)
		{
			return $this->restrictions;
		}
		else
		{
			return null;
		}
	}

	function get_sampling_rate()
	{
		if ($this->samplingrate !== null)
		{
			return $this->samplingrate;
		}
		else
		{
			return null;
		}
	}

	function get_size()
	{
		$length = $this->get_length();
		if ($length !== null)
		{
			return round($length/1048576, 2);
		}
		else
		{
			return null;
		}
	}

	function get_thumbnail($key = 0)
	{
		$thumbnails = $this->get_thumbnails();
		if (isset($thumbnails[$key]))
		{
			return $thumbnails[$key];
		}
		else
		{
			return null;
		}
	}

	function get_thumbnails()
	{
		if ($this->thumbnails !== null)
		{
			return $this->thumbnails;
		}
		else
		{
			return null;
		}
	}

	function get_title()
	{
		if ($this->title !== null)
		{
			return $this->title;
		}
		else
		{
			return null;
		}
	}

	function get_type()
	{
		if ($this->type !== null)
		{
			return $this->type;
		}
		else
		{
			return null;
		}
	}

	function get_width()
	{
		if ($this->width !== null)
		{
			return $this->width;
		}
		else
		{
			return null;
		}
	}

	function native_embed($options='')
	{
		return $this->embed($options, true);
	}

	/**
	 * @todo If the dimensions for media:content are defined, use them when width/height are set to 'auto'.
	 */
	function embed($options = '', $native = false)
	{
		// Set up defaults
		$audio = '';
		$video = '';
		$alt = '';
		$altclass = '';
		$loop = 'false';
		$width = 'auto';
		$height = 'auto';
		$bgcolor = '#ffffff';
		$mediaplayer = '';
		$widescreen = false;
		$handler = $this->get_handler();
		$type = $this->get_real_type();

		// Process options and reassign values as necessary
		if (is_array($options))
		{
			extract($options);
		}
		else
		{
			$options = explode(',', $options);
			foreach($options as $option)
			{
				$opt = explode(':', $option, 2);
				if (isset($opt[0], $opt[1]))
				{
					$opt[0] = trim($opt[0]);
					$opt[1] = trim($opt[1]);
					switch ($opt[0])
					{
						case 'audio':
							$audio = $opt[1];
							break;

						case 'video':
							$video = $opt[1];
							break;

						case 'alt':
							$alt = $opt[1];
							break;

						case 'altclass':
							$altclass = $opt[1];
							break;

						case 'loop':
							$loop = $opt[1];
							break;

						case 'width':
							$width = $opt[1];
							break;

						case 'height':
							$height = $opt[1];
							break;

						case 'bgcolor':
							$bgcolor = $opt[1];
							break;

						case 'mediaplayer':
							$mediaplayer = $opt[1];
							break;

						case 'widescreen':
							$widescreen = $opt[1];
							break;
					}
				}
			}
		}

		$mime = explode('/', $type, 2);
		$mime = $mime[0];

		// Process values for 'auto'
		if ($width == 'auto')
		{
			if ($mime == 'video')
			{
				if ($height == 'auto')
				{
					$width = 480;
				}
				elseif ($widescreen)
				{
					$width = round((intval($height)/9)*16);
				}
				else
				{
					$width = round((intval($height)/3)*4);
				}
			}
			else
			{
				$width = '100%';
			}
		}

		if ($height == 'auto')
		{
			if ($mime == 'audio')
			{
				$height = 0;
			}
			elseif ($mime == 'video')
			{
				if ($width == 'auto')
				{
					if ($widescreen)
					{
						$height = 270;
					}
					else
					{
						$height = 360;
					}
				}
				elseif ($widescreen)
				{
					$height = round((intval($width)/16)*9);
				}
				else
				{
					$height = round((intval($width)/4)*3);
				}
			}
			else
			{
				$height = 376;
			}
		}
		elseif ($mime == 'audio')
		{
			$height = 0;
		}

		// Set proper placeholder value
		if ($mime == 'audio')
		{
			$placeholder = $audio;
		}
		elseif ($mime == 'video')
		{
			$placeholder = $video;
		}

		$embed = '';

		// Make sure the JS library is included
		if (!$native)
		{
			static $javascript_outputted = null;
			if (!$javascript_outputted && $this->javascript)
			{
				$embed .= '<script type="text/javascript" src="?' . htmlspecialchars($this->javascript) . '"></script>';
				$javascript_outputted = true;
			}
		}

		// Odeo Feed MP3's
		if ($handler == 'odeo')
		{
			if ($native)
			{
				$embed .= '<embed src="http://odeo.com/flash/audio_player_fullsize.swf" pluginspage="http://adobe.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="440" height="80" wmode="transparent" allowScriptAccess="any" flashvars="valid_sample_rate=true&external_url=' . $this->get_link() . '"></embed>';
			}
			else
			{
				$embed .= '<script type="text/javascript">embed_odeo("' . $this->get_link() . '");</script>';
			}
		}

		// Flash
		elseif ($handler == 'flash')
		{
			if ($native)
			{
				$embed .= "<embed src=\"" . $this->get_link() . "\" pluginspage=\"http://adobe.com/go/getflashplayer\" type=\"$type\" quality=\"high\" width=\"$width\" height=\"$height\" bgcolor=\"$bgcolor\" loop=\"$loop\"></embed>";
			}
			else
			{
				$embed .= "<script type='text/javascript'>embed_flash('$bgcolor', '$width', '$height', '" . $this->get_link() . "', '$loop', '$type');</script>";
			}
		}

		// Flash Media Player file types.
		// Preferred handler for MP3 file types.
		elseif ($handler == 'fmedia' || ($handler == 'mp3' && $mediaplayer != ''))
		{
			$height += 20;
			if ($native)
			{
				$embed .= "<embed src=\"$mediaplayer\" pluginspage=\"http://adobe.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" quality=\"high\" width=\"$width\" height=\"$height\" wmode=\"transparent\" flashvars=\"file=" . rawurlencode($this->get_link().'?file_extension=.'.$this->get_extension()) . "&autostart=false&repeat=$loop&showdigits=true&showfsbutton=false\"></embed>";
			}
			else
			{
				$embed .= "<script type='text/javascript'>embed_flv('$width', '$height', '" . rawurlencode($this->get_link().'?file_extension=.'.$this->get_extension()) . "', '$placeholder', '$loop', '$mediaplayer');</script>";
			}
		}

		// QuickTime 7 file types.  Need to test with QuickTime 6.
		// Only handle MP3's if the Flash Media Player is not present.
		elseif ($handler == 'quicktime' || ($handler == 'mp3' && $mediaplayer == ''))
		{
			$height += 16;
			if ($native)
			{
				if ($placeholder != ""){
					$embed .= "<embed type=\"$type\" style=\"cursor:hand; cursor:pointer;\" href=\"" . $this->get_link() . "\" src=\"$placeholder\" width=\"$width\" height=\"$height\" autoplay=\"false\" target=\"myself\" controller=\"false\" loop=\"$loop\" scale=\"aspect\" bgcolor=\"$bgcolor\" pluginspage=\"http://apple.com/quicktime/download/\"></embed>";
				}
				else {
					$embed .= "<embed type=\"$type\" style=\"cursor:hand; cursor:pointer;\" src=\"" . $this->get_link() . "\" width=\"$width\" height=\"$height\" autoplay=\"false\" target=\"myself\" controller=\"true\" loop=\"$loop\" scale=\"aspect\" bgcolor=\"$bgcolor\" pluginspage=\"http://apple.com/quicktime/download/\"></embed>";
				}
			}
			else
			{
				$embed .= "<script type='text/javascript'>embed_quicktime('$type', '$bgcolor', '$width', '$height', '" . $this->get_link() . "', '$placeholder', '$loop');</script>";
			}
		}

		// Windows Media
		elseif ($handler == 'wmedia')
		{
			$height += 45;
			if ($native)
			{
				$embed .= "<embed type=\"application/x-mplayer2\" src=\"" . $this->get_link() . "\" autosize=\"1\" width=\"$width\" height=\"$height\" showcontrols=\"1\" showstatusbar=\"0\" showdisplay=\"0\" autostart=\"0\"></embed>";
			}
			else
			{
				$embed .= "<script type='text/javascript'>embed_wmedia('$width', '$height', '" . $this->get_link() . "');</script>";
			}
		}

		// Everything else
		else $embed .= '<a href="' . $this->get_link() . '" class="' . $altclass . '">' . $alt . '</a>';

		return $embed;
	}

	function get_real_type($find_handler = false)
	{
		// If it's Odeo, let's get it out of the way.
		if (substr(strtolower($this->get_link()), 0, 15) == 'http://odeo.com')
		{
			return 'odeo';
		}

		// Mime-types by handler.
		$types_flash = array('application/x-shockwave-flash', 'application/futuresplash'); // Flash
		$types_fmedia = array('video/flv', 'video/x-flv'); // Flash Media Player
		$types_quicktime = array('audio/3gpp', 'audio/3gpp2', 'audio/aac', 'audio/x-aac', 'audio/aiff', 'audio/x-aiff', 'audio/mid', 'audio/midi', 'audio/x-midi', 'audio/mp4', 'audio/m4a', 'audio/x-m4a', 'audio/wav', 'audio/x-wav', 'video/3gpp', 'video/3gpp2', 'video/m4v', 'video/x-m4v', 'video/mp4', 'video/mpeg', 'video/x-mpeg', 'video/quicktime', 'video/sd-video'); // QuickTime
		$types_wmedia = array('application/asx', 'application/x-mplayer2', 'audio/x-ms-wma', 'audio/x-ms-wax', 'video/x-ms-asf-plugin', 'video/x-ms-asf', 'video/x-ms-wm', 'video/x-ms-wmv', 'video/x-ms-wvx'); // Windows Media
		$types_mp3 = array('audio/mp3', 'audio/x-mp3', 'audio/mpeg', 'audio/x-mpeg'); // MP3

		if ($this->get_type() !== null)
		{
			$type = strtolower($this->type);
		}
		else
		{
			$type = null;
		}

		// If we encounter an unsupported mime-type, check the file extension and guess intelligently.
		if (!in_array($type, array_merge($types_flash, $types_fmedia, $types_quicktime, $types_wmedia, $types_mp3)))
		{
			switch (strtolower($this->get_extension()))
			{
				// Audio mime-types
				case 'aac':
				case 'adts':
					$type = 'audio/acc';
					break;

				case 'aif':
				case 'aifc':
				case 'aiff':
				case 'cdda':
					$type = 'audio/aiff';
					break;

				case 'bwf':
					$type = 'audio/wav';
					break;

				case 'kar':
				case 'mid':
				case 'midi':
				case 'smf':
					$type = 'audio/midi';
					break;

				case 'm4a':
					$type = 'audio/x-m4a';
					break;

				case 'mp3':
				case 'swa':
					$type = 'audio/mp3';
					break;

				case 'wav':
					$type = 'audio/wav';
					break;

				case 'wax':
					$type = 'audio/x-ms-wax';
					break;

				case 'wma':
					$type = 'audio/x-ms-wma';
					break;

				// Video mime-types
				case '3gp':
				case '3gpp':
					$type = 'video/3gpp';
					break;

				case '3g2':
				case '3gp2':
					$type = 'video/3gpp2';
					break;

				case 'asf':
					$type = 'video/x-ms-asf';
					break;

				case 'flv':
					$type = 'video/x-flv';
					break;

				case 'm1a':
				case 'm1s':
				case 'm1v':
				case 'm15':
				case 'm75':
				case 'mp2':
				case 'mpa':
				case 'mpeg':
				case 'mpg':
				case 'mpm':
				case 'mpv':
					$type = 'video/mpeg';
					break;

				case 'm4v':
					$type = 'video/x-m4v';
					break;

				case 'mov':
				case 'qt':
					$type = 'video/quicktime';
					break;

				case 'mp4':
				case 'mpg4':
					$type = 'video/mp4';
					break;

				case 'sdv':
					$type = 'video/sd-video';
					break;

				case 'wm':
					$type = 'video/x-ms-wm';
					break;

				case 'wmv':
					$type = 'video/x-ms-wmv';
					break;

				case 'wvx':
					$type = 'video/x-ms-wvx';
					break;

				// Flash mime-types
				case 'spl':
					$type = 'application/futuresplash';
					break;

				case 'swf':
					$type = 'application/x-shockwave-flash';
					break;
			}
		}

		if ($find_handler)
		{
			if (in_array($type, $types_flash))
			{
				return 'flash';
			}
			elseif (in_array($type, $types_fmedia))
			{
				return 'fmedia';
			}
			elseif (in_array($type, $types_quicktime))
			{
				return 'quicktime';
			}
			elseif (in_array($type, $types_wmedia))
			{
				return 'wmedia';
			}
			elseif (in_array($type, $types_mp3))
			{
				return 'mp3';
			}
			else
			{
				return null;
			}
		}
		else
		{
			return $type;
		}
	}
}

class SimplePie_Caption
{
	var $type;
	var $lang;
	var $startTime;
	var $endTime;
	var $text;

	// Constructor, used to input the data
	function SimplePie_Caption($type = null, $lang = null, $startTime = null, $endTime = null, $text = null)
	{
		$this->type = $type;
		$this->lang = $lang;
		$this->startTime = $startTime;
		$this->endTime = $endTime;
		$this->text = $text;
	}

	function __toString()
	{
		// There is no $this->data here
		return md5(serialize($this));
	}

	function get_endtime()
	{
		if ($this->endTime !== null)
		{
			return $this->endTime;
		}
		else
		{
			return null;
		}
	}

	function get_language()
	{
		if ($this->language !== null)
		{
			return $this->language;
		}
		else
		{
			return null;
		}
	}

	function get_starttime()
	{
		if ($this->startTime !== null)
		{
			return $this->startTime;
		}
		else
		{
			return null;
		}
	}

	function get_text()
	{
		if ($this->text !== null)
		{
			return $this->text;
		}
		else
		{
			return null;
		}
	}

	function get_type()
	{
		if ($this->type !== null)
		{
			return $this->type;
		}
		else
		{
			return null;
		}
	}
}

class SimplePie_Credit
{
	var $role;
	var $scheme;
	var $name;

	// Constructor, used to input the data
	function SimplePie_Credit($role = null, $scheme = null, $name = null)
	{
		$this->role = $role;
		$this->scheme = $scheme;
		$this->name = $name;
	}

	function __toString()
	{
		// There is no $this->data here
		return md5(serialize($this));
	}

	function get_role()
	{
		if ($this->role !== null)
		{
			return $this->role;
		}
		else
		{
			return null;
		}
	}

	function get_scheme()
	{
		if ($this->scheme !== null)
		{
			return $this->scheme;
		}
		else
		{
			return null;
		}
	}

	function get_name()
	{
		if ($this->name !== null)
		{
			return $this->name;
		}
		else
		{
			return null;
		}
	}
}

class SimplePie_Copyright
{
	var $url;
	var $label;

	// Constructor, used to input the data
	function SimplePie_Copyright($url = null, $label = null)
	{
		$this->url = $url;
		$this->label = $label;
	}

	function __toString()
	{
		// There is no $this->data here
		return md5(serialize($this));
	}

	function get_url()
	{
		if ($this->url !== null)
		{
			return $this->url;
		}
		else
		{
			return null;
		}
	}

	function get_attribution()
	{
		if ($this->label !== null)
		{
			return $this->label;
		}
		else
		{
			return null;
		}
	}
}

class SimplePie_Rating
{
	var $scheme;
	var $value;

	// Constructor, used to input the data
	function SimplePie_Rating($scheme = null, $value = null)
	{
		$this->scheme = $scheme;
		$this->value = $value;
	}

	function __toString()
	{
		// There is no $this->data here
		return md5(serialize($this));
	}

	function get_scheme()
	{
		if ($this->scheme !== null)
		{
			return $this->scheme;
		}
		else
		{
			return null;
		}
	}

	function get_value()
	{
		if ($this->value !== null)
		{
			return $this->value;
		}
		else
		{
			return null;
		}
	}
}

class SimplePie_Restriction
{
	var $relationship;
	var $type;
	var $value;

	// Constructor, used to input the data
	function SimplePie_Restriction($relationship = null, $type = null, $value = null)
	{
		$this->relationship = $relationship;
		$this->type = $type;
		$this->value = $value;
	}

	function __toString()
	{
		// There is no $this->data here
		return md5(serialize($this));
	}

	function get_relationship()
	{
		if ($this->relationship !== null)
		{
			return $this->relationship;
		}
		else
		{
			return null;
		}
	}

	function get_type()
	{
		if ($this->type !== null)
		{
			return $this->type;
		}
		else
		{
			return null;
		}
	}

	function get_value()
	{
		if ($this->value !== null)
		{
			return $this->value;
		}
		else
		{
			return null;
		}
	}
}

/**
 * @todo Move to properly supporting RFC2616 (HTTP/1.1)
 */
class SimplePie_File
{
	var $url;
	var $useragent;
	var $success = true;
	var $headers = array();
	var $body;
	var $status_code;
	var $redirects = 0;
	var $error;
	var $method;

	function SimplePie_File($url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false)
	{
		if (class_exists('idna_convert'))
		{
			$idn = &new idna_convert;
			$parsed = SimplePie_Misc::parse_url($url);
			$url = SimplePie_Misc::compress_parse_url($parsed['scheme'], $idn->encode($parsed['authority']), $parsed['path'], $parsed['query'], $parsed['fragment']);
		}
		$this->url = $url;
		$this->useragent = $useragent;
		if (preg_match('/^http(s)?:\/\//i', $url))
		{
			if ($useragent === null)
			{
				$useragent = ini_get('user_agent');
				$this->useragent = $useragent;
			}
			if (!is_array($headers))
			{
				$headers = array();
			}
			if (!$force_fsockopen && extension_loaded('curl'))
			{
				$this->method = 'curl';
				$fp = curl_init();
				$headers2 = array();
				foreach ($headers as $key => $value)
				{
					$headers2[] = "$key: $value";
				}
				if (version_compare(SimplePie_Misc::get_curl_version(), '7.10.5', '>='))
				{
					curl_setopt($fp, CURLOPT_ENCODING, '');
				}
				curl_setopt($fp, CURLOPT_URL, $url);
				curl_setopt($fp, CURLOPT_HEADER, 1);
				curl_setopt($fp, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($fp, CURLOPT_TIMEOUT, $timeout);
				curl_setopt($fp, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt($fp, CURLOPT_REFERER, $url);
				curl_setopt($fp, CURLOPT_USERAGENT, $useragent);
				curl_setopt($fp, CURLOPT_HTTPHEADER, $headers2);
				if (!ini_get('open_basedir') && !ini_get('safe_mode') && version_compare(SimplePie_Misc::get_curl_version(), '7.15.2', '>='))
				{
					curl_setopt($fp, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt($fp, CURLOPT_MAXREDIRS, $redirects);
				}

				$this->headers = curl_exec($fp);
				if (curl_errno($fp) == 23 || curl_errno($fp) == 61)
				{
					curl_setopt($fp, CURLOPT_ENCODING, 'none');
					$this->headers = curl_exec($fp);
				}
				if (curl_errno($fp))
				{
					$this->error = 'cURL error ' . curl_errno($fp) . ': ' . curl_error($fp);
					$this->success = false;
				}
				else
				{
					$info = curl_getinfo($fp);
					curl_close($fp);
					$this->headers = explode("\r\n\r\n", $this->headers, $info['redirect_count'] + 1);
					$this->headers = array_pop($this->headers);
					$parser = &new SimplePie_HTTP_Parser($this->headers);
					if ($parser->parse())
					{
						$this->headers = $parser->headers;
						$this->body = $parser->body;
						$this->status_code = $parser->status_code;
						if (($this->status_code == 300 || $this->status_code == 301 || $this->status_code == 302 || $this->status_code == 303 || $this->status_code == 307 || $this->status_code > 307 && $this->status_code < 400) && isset($this->headers['location']) && $this->redirects < $redirects)
						{
							$this->redirects++;
							if (isset($this->headers['content-location']))
							{
								$location = SimplePie_Misc::absolutize_url($this->headers['location'], SimplePie_Misc::absolutize_url($this->headers['content-location'], $url));
							}
							else
							{
								$location = SimplePie_Misc::absolutize_url($this->headers['location'], $url);
							}
							return $this->SimplePie_File($location, $timeout, $redirects, $headers, $useragent, $force_fsockopen);
						}
					}
				}
			}
			else
			{
				$this->method = 'fsockopen';
				$url_parts = parse_url($url);
				if (isset($url_parts['scheme']) && strtolower($url_parts['scheme']) == 'https')
				{
					$url_parts['host'] = "ssl://$url_parts[host]";
					$url_parts['port'] = 443;
				}
				if (!isset($url_parts['port']))
				{
					$url_parts['port'] = 80;
				}
				$fp = fsockopen($url_parts['host'], $url_parts['port'], $errno, $errstr, $timeout);
				if (!$fp)
				{
					$this->error = 'fsockopen error: ' . $errstr;
					$this->success = false;
				}
				else
				{
					if (function_exists('stream_set_timeout'))
					{
						stream_set_timeout($fp, $timeout);
					}
					else
					{
						socket_set_timeout($fp, $timeout);
					}
					if (isset($url_parts['path']))
					{
						if (isset($url_parts['query']))
						{
							$get = "$url_parts[path]?$url_parts[query]";
						}
						else
						{
							$get = $url_parts['path'];
						}
					}
					else
					{
						$get = '/';
					}
					$out = "GET $get HTTP/1.0\r\n";
					$out .= "Host: $url_parts[host]\r\n";
					$out .= "User-Agent: $useragent\r\n";
					if (function_exists('gzinflate'))
					{
						$out .= "Accept-Encoding: gzip,deflate\r\n";
					}

					if (isset($url_parts['user']) && isset($url_parts['pass']))
					{
						$out .= "Authorization: Basic " . base64_encode("$url_parts[user]:$url_parts[pass]") . "\r\n";
					}
					foreach ($headers as $key => $value)
					{
						$out .= "$key: $value\r\n";
					}
					$out .= "Connection: Close\r\n\r\n";
					fwrite($fp, $out);

					if (function_exists('stream_get_meta_data'))
					{
						$info = stream_get_meta_data($fp);
					}
					else
					{
						$info = socket_get_status($fp);
					}

					$this->headers = '';
					while (!$info['eof'] && !$info['timed_out'])
					{
						$this->headers .= fread($fp, 1160);
						if (function_exists('stream_get_meta_data'))
						{
							$info = stream_get_meta_data($fp);
						}
						else
						{
							$info = socket_get_status($fp);
						}
					}
					if (!$info['timed_out'])
					{
						$parser = &new SimplePie_HTTP_Parser($this->headers);
						if ($parser->parse())
						{
							$this->headers = $parser->headers;
							$this->body = $parser->body;
							$this->status_code = $parser->status_code;
							if (($this->status_code == 300 || $this->status_code == 301 || $this->status_code == 302 || $this->status_code == 303 || $this->status_code == 307 || $this->status_code > 307 && $this->status_code < 400) && isset($this->headers['location']) && $this->redirects < $redirects)
							{
								$this->redirects++;
								if (isset($this->headers['content-location']))
								{
									$location = SimplePie_Misc::absolutize_url($this->headers['location'], SimplePie_Misc::absolutize_url($this->headers['content-location'], $url));
								}
								else
								{
									$location = SimplePie_Misc::absolutize_url($this->headers['location'], $url);
								}
								return $this->SimplePie_File($location, $timeout, $redirects, $headers, $useragent, $force_fsockopen);
							}
							if (isset($this->headers['content-encoding']) && ($this->headers['content-encoding'] == 'gzip' || $this->headers['content-encoding'] == 'deflate'))
							{
								if (substr($this->body, 0, 8) == "\x1f\x8b\x08\x00\x00\x00\x00\x00")
								{
									$this->body = substr($this->body, 10);
								}
								$this->body = gzinflate($this->body);
							}
						}
					}
					else
					{
						$this->error = 'fsocket timed out';
						$this->success = false;
					}
					fclose($fp);
				}
			}
		}
		elseif (function_exists('file_get_contents'))
		{
			$this->method = 'file_get_contents';
			if (!$this->body = file_get_contents($url))
			{
				$this->error = 'file_get_contents could not read the file';
				$this->success = false;
			}
		}
		else
		{
			$this->method = 'fopen';
			if (($fp = fopen($url, 'rb')) === false)
			{
				$this->error = 'failed to open stream: No such file or directory';
				$this->success = false;
			}
			else
			{
				$this->body = '';
				while (!feof($fp))
				{
					$this->body .= fread($fp, 8192);
				}
				fclose($fp);
			}
		}
	}
}

/**
 * HTTP Response Parser
 *
 * @package SimplePie
 * @todo Support HTTP Requests
 */
class SimplePie_HTTP_Parser
{
	/**
	 * HTTP Version
	 *
	 * @access public
	 * @var string
	 */
	var $http_version = '';

	/**
	 * Status code
	 *
	 * @access public
	 * @var string
	 */
	var $status_code = '';

	/**
	 * Reason phrase
	 *
	 * @access public
	 * @var string
	 */
	var $reason = '';

	/**
	 * Key/value pairs of the headers
	 *
	 * @access public
	 * @var array
	 */
	var $headers = array();

	/**
	 * Body of the response
	 *
	 * @access public
	 * @var string
	 */
	var $body = '';

	/**
	 * Current state of the state machine
	 *
	 * @access private
	 * @var string
	 */
	var $state = 'start';

	/**
	 * Input data
	 *
	 * @access private
	 * @var string
	 */
	var $data = '';

	/**
	 * Input data length (to avoid calling strlen() everytime this is needed)
	 *
	 * @access private
	 * @var int
	 */
	var $data_length = 0;

	/**
	 * Current position of the pointer
	 *
	 * @access private
	 * @var int
	 */
	var $position = 0;

	/**
	 * Name of the hedaer currently being parsed
	 *
	 * @access private
	 * @var string
	 */
	var $name = '';

	/**
	 * Value of the hedaer currently being parsed
	 *
	 * @access private
	 * @var string
	 */
	var $value = '';

	/**
	 * Create an instance of the class with the input data
	 *
	 * @access public
	 * @param string $data Input data
	 */
	function SimplePie_HTTP_Parser($data)
	{
		$this->data = $data;
		$this->data_length = strlen($this->data);
	}

	/**
	 * Parse the input data
	 *
	 * @access public
	 * @return bool true on success, false on failure
	 */
	function parse()
	{
		while ($this->state && $this->state != 'emit' && $this->has_data())
		{
			$state = $this->state;
			$this->$state();
		}
		$this->data = '';
		if ($this->state == 'emit')
		{
			return true;
		}
		else
		{
			$this->http_version = '';
			$this->status_code = '';
			$this->headers = array();
			$this->body = '';
			return false;
		}
	}

	/**
	 * Check whether there is data beyond the pointer
	 *
	 * @access private
	 * @return bool true if there is further data, false if not
	 */
	function has_data()
	{
		return (bool) ($this->position < $this->data_length);
	}

	/**
	 * See if the next character is LWS
	 *
	 * @access private
	 * @return bool true if the next character is LWS, false if not
	 */
	function is_linear_whitespace()
	{
		return (bool) (strspn($this->data, "\x09\x20", $this->position, 1)
			|| (substr($this->data, $this->position, 2) == "\r\n" && strspn($this->data, "\x09\x20", $this->position + 2, 1))
			|| (strspn($this->data, "\r\n", $this->position, 1) && strspn($this->data, "\x09\x20", $this->position + 1, 1)));
	}

	/**
	 * The starting state of the state machine, see if the data is a response or request
	 *
	 * @access private
	 */
	function start()
	{
		$this->state = 'http_version_response';
	}

	/**
	 * Parse an HTTP-version string
	 *
	 * @access private
	 */
	function http_version()
	{
		if (preg_match('/^HTTP\/([0-9]+\.[0-9]+)/i', substr($this->data, $this->position, strcspn($this->data, "\r\n", $this->position)), $match))
		{
			$this->position += strlen($match[0]);
			$this->http_version = $match[1];
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Parse LWS, replacing consecutive characters with a single space
	 *
	 * @access private
	 */
	function linear_whitespace()
	{
		do
		{
			if (substr($this->data, $this->position, 2) == "\r\n")
			{
				$this->position += 2;
			}
			elseif (strspn($this->data, "\r\n", $this->position, 1))
			{
				$this->position++;
			}
			$this->position += strspn($this->data, "\x09\x20", $this->position);
		} while ($this->is_linear_whitespace());
		$this->value .= "\x20";
	}

	/**
	 * Parse an HTTP-version string within a response
	 *
	 * @access private
	 */
	function http_version_response()
	{
		if ($this->http_version() && $this->data[$this->position] == "\x20")
		{
			$this->state = 'status_code';
			$this->position++;
		}
		else
		{
			$this->state = false;
		}
	}

	/**
	 * Parse a status code
	 *
	 * @access private
	 */
	function status_code()
	{
		if (strspn($this->data, '1234567890', $this->position, 3) == 3)
		{
			$this->status_code = substr($this->data, $this->position, 3);
			$this->state = 'reason_phrase';
			$this->position += 3;
		}
		else
		{
			$this->state = false;
		}
	}

	/**
	 * Skip over the reason phrase (it has no normative value, and you can send absolutely anything here)
	 *
	 * @access private
	 */
	function reason_phrase()
	{
		$len = strcspn($this->data, "\r\n", $this->position);
		$this->reason = substr($this->data, $this->position, $len);
		$this->position += $len;
		if ($this->has_data())
		{
			if (substr($this->data, $this->position, 2) == "\r\n")
			{
				$this->position += 2;
			}
			elseif (strspn($this->data, "\r\n", $this->position, 1))
			{
				$this->position++;
			}
			$this->state = 'name';
		}
	}

	/**
	 * Parse a header name
	 *
	 * @access private
	 */
	function name()
	{
		$len = strcspn($this->data, ':', $this->position);
		$this->name = substr($this->data, $this->position, $len);
		$this->position += $len;

		if ($this->has_data() && $this->data[$this->position] == ':')
		{
			$this->state = 'value_next';
			$this->position++;
		}
		else
		{
			$this->state = false;
		}
	}

	/**
	 * See what state to move the state machine to while within non-quoted header values
	 *
	 * @access private
	 */
	function value_next()
	{
		if ($this->is_linear_whitespace())
		{
			$this->state = 'value_linear_whitespace';
		}
		elseif ($this->data[$this->position] == '"')
		{
			$this->state = 'value_quote_next';
			$this->position++;
		}
		elseif (substr($this->data, $this->position, 2) == "\r\n")
		{
			$this->state = 'end_crlf';
			$this->position += 2;
		}
		elseif (strspn($this->data, "\r\n", $this->position, 1))
		{
			$this->state = 'end_crlf';
			$this->position++;
		}
		else
		{
			$this->state = 'value_no_quote';
		}
	}

	/**
	 * Parse a header value while outside quotes
	 *
	 * @access private
	 */
	function value_no_quote()
	{
		$len = strcspn($this->data, "\x09\x20\r\n\"", $this->position);
		$this->value .= substr($this->data, $this->position, $len);
		$this->state = 'value_next';
		$this->position += $len;
	}

	/**
	 * Parse LWS outside quotes
	 *
	 * @access private
	 */
	function value_linear_whitespace()
	{
		$this->linear_whitespace();
		$this->state = 'value_next';
	}

	/**
	 * See what state to move the state machine to while within quoted header values
	 *
	 * @access private
	 */
	function value_quote_next()
	{
		if ($this->is_linear_whitespace())
		{
			$this->state = 'value_linear_whitespace_quote';
		}
		else
		{
			switch ($this->data[$this->position])
			{
				case '"':
					$this->state = 'value_next';
					$this->position++;
					break;

				case '\\':
					$this->state = 'value_quote_char';
					$this->position++;
					break;

				default:
					$this->state = 'value_quote';
					break;
			}
		}
	}

	/**
	 * Parse a header value while within quotes
	 *
	 * @access private
	 */
	function value_quote()
	{
		$len = strcspn($this->data, "\x09\x20\r\n\"\\", $this->position);
		$this->value .= substr($this->data, $this->position, $len);
		$this->position += $len;
		$this->state = 'value_quote_next';
	}

	/**
	 * Parse an escaped character within quotes
	 *
	 * @access private
	 */
	function value_quote_char()
	{
		$this->value .= $this->data[$this->position];
		$this->state = 'value_quote_next';
		$this->position++;
	}

	/**
	 * Parse LWS within quotes
	 *
	 * @access private
	 */
	function value_linear_whitespace_quote()
	{
		$this->linear_whitespace();
		$this->state = 'value_quote_next';
	}

	/**
	 * Parse a CRLF, and see whether we have a further header, or whether we are followed by the body
	 *
	 * @access private
	 */
	function end_crlf()
	{
		$this->name = strtolower($this->name);
		$this->value = trim($this->value, "\x20");
		if (isset($this->headers[$this->name]))
		{
			$this->headers[$this->name] .= ', ' . $this->value;
		}
		else
		{
			$this->headers[$this->name] = $this->value;
		}

		if (substr($this->data, $this->position, 2) == "\r\n")
		{
			$this->body = substr($this->data, $this->position + 2);
			$this->state = 'emit';
		}
		elseif (strspn($this->data, "\r\n", $this->position, 1))
		{
			$this->body = substr($this->data, $this->position + 1);
			$this->state = 'emit';
		}
		else
		{
			$this->name = '';
			$this->value = '';
			$this->state = 'name';
		}
	}
}

class SimplePie_Cache
{
	var $location;
	var $filename;
	var $extension;
	var $name;

	function SimplePie_Cache($location, $filename, $extension)
	{
		$this->location = $location;
		$this->filename = rawurlencode($filename);
		$this->extension = rawurlencode($extension);
		$this->name = "$location/$this->filename.$this->extension";
	}

	function save($data)
	{
		if (file_exists($this->name) && is_writeable($this->name) || file_exists($this->location) && is_writeable($this->location))
		{
			if (function_exists('file_put_contents'))
			{
				return (bool) file_put_contents($this->name, serialize($data));
			}
			else
			{
				$fp = fopen($this->name, 'wb');
				if ($fp)
				{
					fwrite($fp, serialize($data));
					fclose($fp);
					return true;
				}
			}
		}
		return false;
	}

	function load()
	{
		if (file_exists($this->name) && is_readable($this->name))
		{
			if (function_exists('file_get_contents'))
			{
				return unserialize(file_get_contents($this->name));
			}
			elseif (($fp = fopen($this->name, 'rb')) !== false)
			{
				$data = '';
				while (!feof($fp))
				{
					$data .= fread($fp, 8192);
				}
				fclose($fp);
				return unserialize($data);
			}
		}
		return false;
	}

	function mtime()
	{
		if (file_exists($this->name))
		{
			return filemtime($this->name);
		}
		return false;
	}

	function touch()
	{
		if (file_exists($this->name))
		{
			return touch($this->name);
		}
		return false;
	}

	function unlink()
	{
		if (file_exists($this->name))
		{
			return unlink($this->name);
		}
		return false;
	}
}

class SimplePie_Misc
{
	function time_hms($seconds)
	{
		$time = '';

		$hours = floor($seconds / 3600);
		$remainder = $seconds % 3600;
		if ($hours > 0)
		{
			$time .= $hours.':';
		}

		$minutes = floor($remainder / 60);
		$seconds = $remainder % 60;
		if ($minutes < 10 && $hours > 0)
		{
			$minutes = '0' . $minutes;
		}
		if ($seconds < 10)
		{
			$seconds = '0' . $seconds;
		}

		$time .= $minutes.':';
		$time .= $seconds;

		return $time;
	}

	function absolutize_url($relative, $base)
	{
		if ($relative !== '')
		{
			$relative = SimplePie_Misc::parse_url($relative);
			if ($relative['scheme'] !== '')
			{
				$target = $relative;
			}
			elseif ($base !== '')
			{
				$base = SimplePie_Misc::parse_url($base);
				$target = SimplePie_Misc::parse_url('');
				if ($relative['authority'] !== '')
				{
					$target = $relative;
					$target['scheme'] = $base['scheme'];
				}
				else
				{
					$target['scheme'] = $base['scheme'];
					$target['authority'] = $base['authority'];
					if ($relative['path'] !== '')
					{
						if (strpos($relative['path'], '/') === 0)
						{
							$target['path'] = $relative['path'];
						}
						elseif (($target['path'] = dirname("$base[path].")) == '/')
						{
							$target['path'] .= $relative['path'];
						}
						else
						{
							$target['path'] .= '/' . $relative['path'];
						}
						if ($relative['query'] !== '')
						{
							$target['query'] = $relative['query'];
						}
					}
					else
					{
						if ($base['path'] !== '')
						{
							$target['path'] = $base['path'];
						}
						else
						{
							$target['path'] = '/';
						}
						if ($relative['query'] !== '')
						{
							$target['query'] = $relative['query'];
						}
						elseif ($base['query'] !== '')
						{
							$target['query'] = $base['query'];
						}
					}
				}
				if ($relative['fragment'] !== '')
				{
					$target['fragment'] = $relative['fragment'];
				}
			}
			else
			{
				// No base URL, just return the relative URL
				$target = $relative;
			}
			$return = SimplePie_Misc::compress_parse_url($target['scheme'], $target['authority'], $target['path'], $target['query'], $target['fragment']);
		}
		else
		{
			$return = $base;
		}
		$return = SimplePie_Misc::normalize_url($return);
		return $return;
	}

	function remove_dot_segments($input)
	{
		$output = '';
		while (strpos($input, './') !== false || strpos($input, '/.') !== false || $input == '.' || $input == '..')
		{
			// A: If the input buffer begins with a prefix of "../" or "./", then remove that prefix from the input buffer; otherwise,
			if (strpos($input, '../') === 0)
			{
				$input = substr($input, 3);
			}
			elseif (strpos($input, './') === 0)
			{
				$input = substr($input, 2);
			}
			// B: if the input buffer begins with a prefix of "/./" or "/.", where "." is a complete path segment, then replace that prefix with "/" in the input buffer; otherwise,
			elseif (strpos($input, '/./') === 0)
			{
				$input = substr_replace($input, '/', 0, 3);
			}
			elseif ($input == '/.')
			{
				$input = '/';
			}
			// C: if the input buffer begins with a prefix of "/../" or "/..", where ".." is a complete path segment, then replace that prefix with "/" in the input buffer and remove the last segment and its preceding "/" (if any) from the output buffer; otherwise,
			elseif (strpos($input, '/../') === 0)
			{
				$input = substr_replace($input, '/', 0, 4);
				$output = substr_replace($output, '', strrpos($output, '/'));
			}
			elseif ($input == '/..')
			{
				$input = '/';
				$output = substr_replace($output, '', strrpos($output, '/'));
			}
			// D: if the input buffer consists only of "." or "..", then remove that from the input buffer; otherwise,
			elseif ($input == '.' || $input == '..')
			{
				$input = '';
			}
			// E: move the first path segment in the input buffer to the end of the output buffer, including the initial "/" character (if any) and any subsequent characters up to, but not including, the next "/" character or the end of the input buffer
			elseif (($pos = strpos($input, '/', 1)) !== false)
			{
				$output .= substr($input, 0, $pos);
				$input = substr_replace($input, '', 0, $pos);
			}
			else
			{
				$output .= $input;
				$input = '';
			}
		}
		return $output . $input;
	}

	function get_element($realname, $string)
	{
		$return = array();
		$name = preg_quote($realname, '/');
		if (preg_match_all("/<($name)" . SIMPLEPIE_PCRE_HTML_ATTRIBUTE . "(>(.*)<\/$name>|(\/)?>)/siU", $string, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE))
		{
			for ($i = 0, $total_matches = count($matches); $i < $total_matches; $i++)
			{
				$return[$i]['tag'] = $realname;
				$return[$i]['full'] = $matches[$i][0][0];
				$return[$i]['offset'] = $matches[$i][0][1];
				if (strlen($matches[$i][3][0]) <= 2)
				{
					$return[$i]['self_closing'] = true;
				}
				else
				{
					$return[$i]['self_closing'] = false;
					$return[$i]['content'] = $matches[$i][4][0];
				}
				$return[$i]['attribs'] = array();
				if (isset($matches[$i][2][0]) && preg_match_all('/((?:[^\s:]+:)?[^\s:]+)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([a-z0-9\-._:]*)))?\s/U', ' ' . $matches[$i][2][0] . ' ', $attribs, PREG_SET_ORDER))
				{
					for ($j = 0, $total_attribs = count($attribs); $j < $total_attribs; $j++)
					{
						if (count($attribs[$j]) == 2)
						{
							$attribs[$j][2] = $attribs[$j][1];
						}
						$return[$i]['attribs'][strtolower($attribs[$j][1])]['data'] = SimplePie_Misc::entities_decode(end($attribs[$j]), 'UTF-8');
					}
				}
			}
		}
		return $return;
	}

	function element_implode($element)
	{
		$full = "<$element[tag]";
		foreach ($element['attribs'] as $key => $value)
		{
			$key = strtolower($key);
			$full .= " $key=\"" . htmlspecialchars($value['data']) . '"';
		}
		if ($element['self_closing'])
		{
			$full .= ' />';
		}
		else
		{
			$full .= ">$element[content]</$element[tag]>";
		}
		return $full;
	}

	function error($message, $level, $file, $line)
	{
		switch ($level)
		{
			case E_USER_ERROR:
				$note = 'PHP Error';
				break;
			case E_USER_WARNING:
				$note = 'PHP Warning';
				break;
			case E_USER_NOTICE:
				$note = 'PHP Notice';
				break;
			default:
				$note = 'Unknown Error';
				break;
		}
		error_log("$note: $message in $file on line $line", 0);
		return $message;
	}

	/**
	 * If a file has been cached, retrieve and display it.
	 *
	 * This is most useful for caching images (get_favicon(), etc.),
	 * however it works for all cached files.  This WILL NOT display ANY
	 * file/image/page/whatever, but rather only display what has already
	 * been cached by SimplePie.
	 *
	 * @access public
	 * @see SimplePie::get_favicon()
	 * @param str $identifier_url URL that is used to identify the content.
	 * This may or may not be the actual URL of the live content.
	 * @param str $cache_location Location of SimplePie's cache.  Defaults
	 * to './cache'.
	 * @param str $cache_extension The file extension that the file was
	 * cached with.  Defaults to 'spc'.
	 * @param str $cache_class Name of the cache-handling class being used
	 * in SimplePie.  Defaults to 'SimplePie_Cache', and should be left
	 * as-is unless you've overloaded the class.
	 * @param str $cache_name_function Function that converts the filename
	 * for saving.  Defaults to 'md5'.
	 */
	function display_cached_file($identifier_url, $cache_location = './cache', $cache_extension = 'spc', $cache_class = 'SimplePie_Cache', $cache_name_function = 'md5')
	{
		$cache = &new $cache_class($cache_location, call_user_func($cache_name_function, $identifier_url), $cache_extension);

		if ($file = $cache->load())
		{
			header('Content-type:' . $file['headers']['content-type']);
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT'); // 7 days
			echo $file['body'];
			exit;
		}

		die('Cached file for ' . $identifier_url . ' cannot be found.');
	}

	function fix_protocol($url, $http = 1)
	{
		$url = SimplePie_Misc::normalize_url($url);
		$parsed = SimplePie_Misc::parse_url($url);
		if ($parsed['scheme'] !== '' && $parsed['scheme'] != 'http' && $parsed['scheme'] != 'https')
		{
			return SimplePie_Misc::fix_protocol(SimplePie_Misc::compress_parse_url('http', $parsed['authority'], $parsed['path'], $parsed['query'], $parsed['fragment']), $http);
		}

		if ($parsed['scheme'] === '' && $parsed['authority'] === '' && !file_exists($url))
		{
			return SimplePie_Misc::fix_protocol(SimplePie_Misc::compress_parse_url('http', $parsed['path'], '', $parsed['query'], $parsed['fragment']), $http);
		}

		if ($http == 2 && $parsed['scheme'] !== '')
		{
			return "feed:$url";
		}
		elseif ($http == 3 && strtolower($parsed['scheme']) == 'http')
		{
			return substr_replace($url, 'podcast', 0, 4);
		}
		elseif ($http == 4 && strtolower($parsed['scheme']) == 'http')
		{
			return substr_replace($url, 'itpc', 0, 4);
		}
		else
		{
			return $url;
		}
	}

	function parse_url($url)
	{
		static $cache = array();
		if (isset($cache[$url]))
		{
			return $cache[$url];
		}
		elseif (preg_match('/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/', $url, $match))
		{
			for ($i = count($match); $i <= 9; $i++)
			{
				$match[$i] = '';
			}
			return $cache[$url] = array('scheme' => $match[2], 'authority' => $match[4], 'path' => $match[5], 'query' => $match[7], 'fragment' => $match[9]);
		}
		else
		{
			return $cache[$url] = array('scheme' => '', 'authority' => '', 'path' => '', 'query' => '', 'fragment' => '');
		}
	}

	function compress_parse_url($scheme = '', $authority = '', $path = '', $query = '', $fragment = '')
	{
		$return = '';
		if ($scheme !== '')
		{
			$return .= "$scheme:";
		}
		if ($authority !== '')
		{
			$return .= "//$authority";
		}
		if ($path !== '')
		{
			$return .= $path;
		}
		if ($query !== '')
		{
			$return .= "?$query";
		}
		if ($fragment !== '')
		{
			$return .= "#$fragment";
		}
		return $return;
	}

	function normalize_url($url)
	{
		$url = preg_replace_callback('/%([0-9A-Fa-f]{2})/', array('SimplePie_Misc', 'percent_encoding_normalization'), $url);
		$url = SimplePie_Misc::parse_url($url);
		$url['scheme'] = strtolower($url['scheme']);
		if ($url['authority'] !== '')
		{
			$url['authority'] = strtolower($url['authority']);
			$url['path'] = SimplePie_Misc::remove_dot_segments($url['path']);
		}
		return SimplePie_Misc::compress_parse_url($url['scheme'], $url['authority'], $url['path'], $url['query'], $url['fragment']);
	}

	function percent_encoding_normalization($match)
	{
		$integer = hexdec($match[1]);
		if ($integer >= 0x41 && $integer <= 0x5A || $integer >= 0x61 && $integer <= 0x7A || $integer >= 0x30 && $integer <= 0x39 || $integer == 0x2D || $integer == 0x2E || $integer == 0x5F || $integer == 0x7E)
		{
			return chr($integer);
		}
		else
		{
			return strtoupper($match[0]);
		}
	}

	/**
	 * Remove bad UTF-8 bytes
	 *
	 * PCRE Pattern to locate bad bytes in a UTF-8 string comes from W3C
	 * FAQ: Multilingual Forms (modified to include full ASCII range)
	 *
	 * @author Geoffrey Sneddon
	 * @see http://www.w3.org/International/questions/qa-forms-utf-8
	 * @param string $str String to remove bad UTF-8 bytes from
	 * @return string UTF-8 string
	 */
	function utf8_bad_replace($str)
	{
		if (function_exists('iconv'))
		{
			return iconv('UTF-8', 'UTF-8//IGNORE', $str);
		}
		elseif (function_exists('mb_convert_encoding'))
		{
			return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
		}
		elseif (preg_match_all('/([\x00-\x7F]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})/', $str, $matches))
		{
			return implode("\xEF\xBF\xBD", $matches[0]);
		}
		elseif ($str !== '')
		{
			return "\xEF\xBF\xBD";
		}
		else
		{
			return '';
		}
	}

	function change_encoding($data, $input, $output)
	{
		$input = SimplePie_Misc::encoding($input);
		$output = SimplePie_Misc::encoding($output);

		if (function_exists('iconv') && ($return = @iconv($input, "$output//IGNORE", $data)))
		{
			return $return;
		}
		elseif (function_exists('iconv') && ($return = @iconv($input, $output, $data)))
		{
			return $return;
		}
		elseif (function_exists('mb_convert_encoding') && ($return = @mb_convert_encoding($data, $output, $input)))
		{
			return $return;
		}
		elseif ($input == 'ISO-8859-1' && $output == 'UTF-8')
		{
			return utf8_encode($data);
		}
		elseif ($input == 'UTF-8' && $output == 'ISO-8859-1')
		{
			return utf8_decode($data);
		}
		return $data;
	}

	function encoding($encoding)
	{
		// Character sets are case-insensitive (though we'll return them in the form given in their registration)
		switch (strtoupper($encoding))
		{
			case 'ANSI_X3.4-1968':
			case 'ISO-IR-6':
			case 'ANSI_X3.4-1986':
			case 'ISO_646.IRV:1991':
			case 'ASCII':
			case 'ISO646-US':
			case 'US-ASCII':
			case 'US':
			case 'IBM367':
			case 'CP367':
			case 'CSASCII':
				return 'US-ASCII';

			case 'ISO_8859-1:1987':
			case 'ISO-IR-100':
			case 'ISO_8859-1':
			case 'ISO-8859-1':
			case 'LATIN1':
			case 'L1':
			case 'IBM819':
			case 'CP819':
			case 'CSISOLATIN1':
				return 'ISO-8859-1';

			case 'ISO_8859-2:1987':
			case 'ISO-IR-101':
			case 'ISO_8859-2':
			case 'ISO-8859-2':
			case 'LATIN2':
			case 'L2':
			case 'CSISOLATIN2':
				return 'ISO-8859-2';

			case 'ISO_8859-3:1988':
			case 'ISO-IR-109':
			case 'ISO_8859-3':
			case 'ISO-8859-3':
			case 'LATIN3':
			case 'L3':
			case 'CSISOLATIN3':
				return 'ISO-8859-3';

			case 'ISO_8859-4:1988':
			case 'ISO-IR-110':
			case 'ISO_8859-4':
			case 'ISO-8859-4':
			case 'LATIN4':
			case 'L4':
			case 'CSISOLATIN4':
				return 'ISO-8859-4';

			case 'ISO_8859-5:1988':
			case 'ISO-IR-144':
			case 'ISO_8859-5':
			case 'ISO-8859-5':
			case 'CYRILLIC':
			case 'CSISOLATINCYRILLIC':
				return 'ISO-8859-5';

			case 'ISO_8859-6:1987':
			case 'ISO-IR-127':
			case 'ISO_8859-6':
			case 'ISO-8859-6':
			case 'ECMA-114':
			case 'ASMO-708':
			case 'ARABIC':
			case 'CSISOLATINARABIC':
				return 'ISO-8859-6';

			case 'ISO_8859-7:1987':
			case 'ISO-IR-126':
			case 'ISO_8859-7':
			case 'ISO-8859-7':
			case 'ELOT_928':
			case 'ECMA-118':
			case 'GREEK':
			case 'GREEK8':
			case 'CSISOLATINGREEK':
				return 'ISO-8859-7';

			case 'ISO_8859-8:1988':
			case 'ISO-IR-138':
			case 'ISO_8859-8':
			case 'ISO-8859-8':
			case 'HEBREW':
			case 'CSISOLATINHEBREW':
				return 'ISO-8859-8';

			case 'ISO_8859-9:1989':
			case 'ISO-IR-148':
			case 'ISO_8859-9':
			case 'ISO-8859-9':
			case 'LATIN5':
			case 'L5':
			case 'CSISOLATIN5':
				return 'ISO-8859-9';

			case 'ISO-8859-10':
			case 'ISO-IR-157':
			case 'L6':
			case 'ISO_8859-10:1992':
			case 'CSISOLATIN6':
			case 'LATIN6':
				return 'ISO-8859-10';

			case 'ISO_6937-2-ADD':
			case 'ISO-IR-142':
			case 'CSISOTEXTCOMM':
				return 'ISO_6937-2-add';

			case 'JIS_X0201':
			case 'X0201':
			case 'CSHALFWIDTHKATAKANA':
				return 'JIS_X0201';

			case 'JIS_ENCODING':
			case 'CSJISENCODING':
				return 'JIS_Encoding';

			case 'SHIFT_JIS':
			case 'MS_KANJI':
			case 'CSSHIFTJIS':
				return 'Shift_JIS';

			case 'EXTENDED_UNIX_CODE_PACKED_FORMAT_FOR_JAPANESE':
			case 'CSEUCPKDFMTJAPANESE':
			case 'EUC-JP':
				return 'EUC-JP';

			case 'EXTENDED_UNIX_CODE_FIXED_WIDTH_FOR_JAPANESE':
			case 'CSEUCFIXWIDJAPANESE':
				return 'Extended_UNIX_Code_Fixed_Width_for_Japanese';

			case 'BS_4730':
			case 'ISO-IR-4':
			case 'ISO646-GB':
			case 'GB':
			case 'UK':
			case 'CSISO4UNITEDKINGDOM':
				return 'BS_4730';

			case 'SEN_850200_C':
			case 'ISO-IR-11':
			case 'ISO646-SE2':
			case 'SE2':
			case 'CSISO11SWEDISHFORNAMES':
				return 'SEN_850200_C';

			case 'IT':
			case 'ISO-IR-15':
			case 'ISO646-IT':
			case 'CSISO15ITALIAN':
				return 'IT';

			case 'ES':
			case 'ISO-IR-17':
			case 'ISO646-ES':
			case 'CSISO17SPANISH':
				return 'ES';

			case 'DIN_66003':
			case 'ISO-IR-21':
			case 'DE':
			case 'ISO646-DE':
			case 'CSISO21GERMAN':
				return 'DIN_66003';

			case 'NS_4551-1':
			case 'ISO-IR-60':
			case 'ISO646-NO':
			case 'NO':
			case 'CSISO60DANISHNORWEGIAN':
			case 'CSISO60NORWEGIAN1':
				return 'NS_4551-1';

			case 'NF_Z_62-010':
			case 'ISO-IR-69':
			case 'ISO646-FR':
			case 'FR':
			case 'CSISO69FRENCH':
				return 'NF_Z_62-010';

			case 'ISO-10646-UTF-1':
			case 'CSISO10646UTF1':
				return 'ISO-10646-UTF-1';

			case 'ISO_646.BASIC:1983':
			case 'REF':
			case 'CSISO646BASIC1983':
				return 'ISO_646.basic:1983';

			case 'INVARIANT':
			case 'CSINVARIANT':
				return 'INVARIANT';

			case 'ISO_646.IRV:1983':
			case 'ISO-IR-2':
			case 'IRV':
			case 'CSISO2INTLREFVERSION':
				return 'ISO_646.irv:1983';

			case 'NATS-SEFI':
			case 'ISO-IR-8-1':
			case 'CSNATSSEFI':
				return 'NATS-SEFI';

			case 'NATS-SEFI-ADD':
			case 'ISO-IR-8-2':
			case 'CSNATSSEFIADD':
				return 'NATS-SEFI-ADD';

			case 'NATS-DANO':
			case 'ISO-IR-9-1':
			case 'CSNATSDANO':
				return 'NATS-DANO';

			case 'NATS-DANO-ADD':
			case 'ISO-IR-9-2':
			case 'CSNATSDANOADD':
				return 'NATS-DANO-ADD';

			case 'SEN_850200_B':
			case 'ISO-IR-10':
			case 'FI':
			case 'ISO646-FI':
			case 'ISO646-SE':
			case 'SE':
			case 'CSISO10SWEDISH':
				return 'SEN_850200_B';

			case 'KS_C_5601-1987':
			case 'ISO-IR-149':
			case 'KS_C_5601-1989':
			case 'KSC_5601':
			case 'KOREAN':
			case 'CSKSC56011987':
				return 'KS_C_5601-1987';

			case 'ISO-2022-KR':
			case 'CSISO2022KR':
				return 'ISO-2022-KR';

			case 'EUC-KR':
			case 'CSEUCKR':
				return 'EUC-KR';

			case 'ISO-2022-JP':
			case 'CSISO2022JP':
				return 'ISO-2022-JP';

			case 'ISO-2022-JP-2':
			case 'CSISO2022JP2':
				return 'ISO-2022-JP-2';

			case 'JIS_C6220-1969-JP':
			case 'JIS_C6220-1969':
			case 'ISO-IR-13':
			case 'KATAKANA':
			case 'X0201-7':
			case 'CSISO13JISC6220JP':
				return 'JIS_C6220-1969-jp';

			case 'JIS_C6220-1969-RO':
			case 'ISO-IR-14':
			case 'JP':
			case 'ISO646-JP':
			case 'CSISO14JISC6220RO':
				return 'JIS_C6220-1969-ro';

			case 'PT':
			case 'ISO-IR-16':
			case 'ISO646-PT':
			case 'CSISO16PORTUGUESE':
				return 'PT';

			case 'GREEK7-OLD':
			case 'ISO-IR-18':
			case 'CSISO18GREEK7OLD':
				return 'greek7-old';

			case 'LATIN-GREEK':
			case 'ISO-IR-19':
			case 'CSISO19LATINGREEK':
				return 'latin-greek';

			case 'NF_Z_62-010_(1973)':
			case 'ISO-IR-25':
			case 'ISO646-FR1':
			case 'CSISO25FRENCH':
				return 'NF_Z_62-010_(1973)';

			case 'LATIN-GREEK-1':
			case 'ISO-IR-27':
			case 'CSISO27LATINGREEK1':
				return 'Latin-greek-1';

			case 'ISO_5427':
			case 'ISO-IR-37':
			case 'CSISO5427CYRILLIC':
				return 'ISO_5427';

			case 'JIS_C6226-1978':
			case 'ISO-IR-42':
			case 'CSISO42JISC62261978':
				return 'JIS_C6226-1978';

			case 'BS_VIEWDATA':
			case 'ISO-IR-47':
			case 'CSISO47BSVIEWDATA':
				return 'BS_viewdata';

			case 'INIS':
			case 'ISO-IR-49':
			case 'CSISO49INIS':
				return 'INIS';

			case 'INIS-8':
			case 'ISO-IR-50':
			case 'CSISO50INIS8':
				return 'INIS-8';

			case 'INIS-CYRILLIC':
			case 'ISO-IR-51':
			case 'CSISO51INISCYRILLIC':
				return 'INIS-cyrillic';

			case 'ISO_5427:1981':
			case 'ISO-IR-54':
			case 'ISO5427CYRILLIC1981':
				return 'ISO_5427:1981';

			case 'ISO_5428:1980':
			case 'ISO-IR-55':
			case 'CSISO5428GREEK':
				return 'ISO_5428:1980';

			case 'GB_1988-80':
			case 'ISO-IR-57':
			case 'CN':
			case 'ISO646-CN':
			case 'CSISO57GB1988':
				return 'GB_1988-80';

			case 'GB_2312-80':
			case 'ISO-IR-58':
			case 'CHINESE':
			case 'CSISO58GB231280':
				return 'GB_2312-80';

			case 'NS_4551-2':
			case 'ISO646-NO2':
			case 'ISO-IR-61':
			case 'NO2':
			case 'CSISO61NORWEGIAN2':
				return 'NS_4551-2';

			case 'VIDEOTEX-SUPPL':
			case 'ISO-IR-70':
			case 'CSISO70VIDEOTEXSUPP1':
				return 'videotex-suppl';

			case 'PT2':
			case 'ISO-IR-84':
			case 'ISO646-PT2':
			case 'CSISO84PORTUGUESE2':
				return 'PT2';

			case 'ES2':
			case 'ISO-IR-85':
			case 'ISO646-ES2':
			case 'CSISO85SPANISH2':
				return 'ES2';

			case 'MSZ_7795.3':
			case 'ISO-IR-86':
			case 'ISO646-HU':
			case 'HU':
			case 'CSISO86HUNGARIAN':
				return 'MSZ_7795.3';

			case 'JIS_C6226-1983':
			case 'ISO-IR-87':
			case 'X0208':
			case 'JIS_X0208-1983':
			case 'CSISO87JISX0208':
				return 'JIS_C6226-1983';

			case 'GREEK7':
			case 'ISO-IR-88':
			case 'CSISO88GREEK7':
				return 'greek7';

			case 'ASMO_449':
			case 'ISO_9036':
			case 'ARABIC7':
			case 'ISO-IR-89':
			case 'CSISO89ASMO449':
				return 'ASMO_449';

			case 'ISO-IR-90':
			case 'CSISO90':
				return 'iso-ir-90';

			case 'JIS_C6229-1984-A':
			case 'ISO-IR-91':
			case 'JP-OCR-A':
			case 'CSISO91JISC62291984A':
				return 'JIS_C6229-1984-a';

			case 'JIS_C6229-1984-B':
			case 'ISO-IR-92':
			case 'ISO646-JP-OCR-B':
			case 'JP-OCR-B':
			case 'CSISO92JISC62991984B':
				return 'JIS_C6229-1984-b';

			case 'JIS_C6229-1984-B-ADD':
			case 'ISO-IR-93':
			case 'JP-OCR-B-ADD':
			case 'CSISO93JIS62291984BADD':
				return 'JIS_C6229-1984-b-add';

			case 'JIS_C6229-1984-HAND':
			case 'ISO-IR-94':
			case 'JP-OCR-HAND':
			case 'CSISO94JIS62291984HAND':
				return 'JIS_C6229-1984-hand';

			case 'JIS_C6229-1984-HAND-ADD':
			case 'ISO-IR-95':
			case 'JP-OCR-HAND-ADD':
			case 'CSISO95JIS62291984HANDADD':
				return 'JIS_C6229-1984-hand-add';

			case 'JIS_C6229-1984-KANA':
			case 'ISO-IR-96':
			case 'CSISO96JISC62291984KANA':
				return 'JIS_C6229-1984-kana';

			case 'ISO_2033-1983':
			case 'ISO-IR-98':
			case 'E13B':
			case 'CSISO2033':
				return 'ISO_2033-1983';

			case 'ANSI_X3.110-1983':
			case 'ISO-IR-99':
			case 'CSA_T500-1983':
			case 'NAPLPS':
			case 'CSISO99NAPLPS':
				return 'ANSI_X3.110-1983';

			case 'T.61-7BIT':
			case 'ISO-IR-102':
			case 'CSISO102T617BIT':
				return 'T.61-7bit';

			case 'T.61-8BIT':
			case 'T.61':
			case 'ISO-IR-103':
			case 'CSISO103T618BIT':
				return 'T.61-8bit';

			case 'ECMA-CYRILLIC':
			case 'ISO-IR-111':
			case 'KOI8-E':
			case 'CSISO111ECMACYRILLIC':
				return 'ECMA-cyrillic';

			case 'CSA_Z243.4-1985-1':
			case 'ISO-IR-121':
			case 'ISO646-CA':
			case 'CSA7-1':
			case 'CA':
			case 'CSISO121CANADIAN1':
				return 'CSA_Z243.4-1985-1';

			case 'CSA_Z243.4-1985-2':
			case 'ISO-IR-122':
			case 'ISO646-CA2':
			case 'CSA7-2':
			case 'CSISO122CANADIAN2':
				return 'CSA_Z243.4-1985-2';

			case 'CSA_Z243.4-1985-GR':
			case 'ISO-IR-123':
			case 'CSISO123CSAZ24341985GR':
				return 'CSA_Z243.4-1985-gr';

			case 'ISO_8859-6-E':
			case 'CSISO88596E':
			case 'ISO-8859-6-E':
				return 'ISO-8859-6-E';

			case 'ISO_8859-6-I':
			case 'CSISO88596I':
			case 'ISO-8859-6-I':
				return 'ISO-8859-6-I';

			case 'T.101-G2':
			case 'ISO-IR-128':
			case 'CSISO128T101G2':
				return 'T.101-G2';

			case 'ISO_8859-8-E':
			case 'CSISO88598E':
			case 'ISO-8859-8-E':
				return 'ISO-8859-8-E';

			case 'ISO_8859-8-I':
			case 'CSISO88598I':
			case 'ISO-8859-8-I':
				return 'ISO-8859-8-I';

			case 'CSN_369103':
			case 'ISO-IR-139':
			case 'CSISO139CSN369103':
				return 'CSN_369103';

			case 'JUS_I.B1.002':
			case 'ISO-IR-141':
			case 'ISO646-YU':
			case 'JS':
			case 'YU':
			case 'CSISO141JUSIB1002':
				return 'JUS_I.B1.002';

			case 'IEC_P27-1':
			case 'ISO-IR-143':
			case 'CSISO143IECP271':
				return 'IEC_P27-1';

			case 'JUS_I.B1.003-SERB':
			case 'ISO-IR-146':
			case 'SERBIAN':
			case 'CSISO146SERBIAN':
				return 'JUS_I.B1.003-serb';

			case 'JUS_I.B1.003-MAC':
			case 'MACEDONIAN':
			case 'ISO-IR-147':
			case 'CSISO147MACEDONIAN':
				return 'JUS_I.B1.003-mac';

			case 'GREEK-CCITT':
			case 'ISO-IR-150':
			case 'CSISO150':
			case 'CSISO150GREEKCCITT':
				return 'greek-ccitt';

			case 'NC_NC00-10:81':
			case 'CUBA':
			case 'ISO-IR-151':
			case 'ISO646-CU':
			case 'CSISO151CUBA':
				return 'NC_NC00-10:81';

			case 'ISO_6937-2-25':
			case 'ISO-IR-152':
			case 'CSISO6937ADD':
				return 'ISO_6937-2-25';

			case 'GOST_19768-74':
			case 'ST_SEV_358-88':
			case 'ISO-IR-153':
			case 'CSISO153GOST1976874':
				return 'GOST_19768-74';

			case 'ISO_8859-SUPP':
			case 'ISO-IR-154':
			case 'LATIN1-2-5':
			case 'CSISO8859SUPP':
				return 'ISO_8859-supp';

			case 'ISO_10367-BOX':
			case 'ISO-IR-155':
			case 'CSISO10367BOX':
				return 'ISO_10367-box';

			case 'LATIN-LAP':
			case 'LAP':
			case 'ISO-IR-158':
			case 'CSISO158LAP':
				return 'latin-lap';

			case 'JIS_X0212-1990':
			case 'X0212':
			case 'ISO-IR-159':
			case 'CSISO159JISX02121990':
				return 'JIS_X0212-1990';

			case 'DS_2089':
			case 'DS2089':
			case 'ISO646-DK':
			case 'DK':
			case 'CSISO646DANISH':
				return 'DS_2089';

			case 'US-DK':
			case 'CSUSDK':
				return 'us-dk';

			case 'DK-US':
			case 'CSDKUS':
				return 'dk-us';

			case 'KSC5636':
			case 'ISO646-KR':
			case 'CSKSC5636':
				return 'KSC5636';

			case 'UNICODE-1-1-UTF-7':
			case 'CSUNICODE11UTF7':
				return 'UNICODE-1-1-UTF-7';

			case 'ISO-2022-CN':
				return 'ISO-2022-CN';

			case 'ISO-2022-CN-EXT':
				return 'ISO-2022-CN-EXT';

			case 'UTF-8':
				return 'UTF-8';

			case 'ISO-8859-13':
				return 'ISO-8859-13';

			case 'ISO-8859-14':
			case 'ISO-IR-199':
			case 'ISO_8859-14:1998':
			case 'ISO_8859-14':
			case 'LATIN8':
			case 'ISO-CELTIC':
			case 'L8':
				return 'ISO-8859-14';

			case 'ISO-8859-15':
			case 'ISO_8859-15':
			case 'LATIN-9':
				return 'ISO-8859-15';

			case 'ISO-8859-16':
			case 'ISO-IR-226':
			case 'ISO_8859-16:2001':
			case 'ISO_8859-16':
			case 'LATIN10':
			case 'L10':
				return 'ISO-8859-16';

			case 'GBK':
			case 'CP936':
			case 'MS936':
			case 'WINDOWS-936':
				return 'GBK';

			case 'GB18030':
				return 'GB18030';

			case 'OSD_EBCDIC_DF04_15':
				return 'OSD_EBCDIC_DF04_15';

			case 'OSD_EBCDIC_DF03_IRV':
				return 'OSD_EBCDIC_DF03_IRV';

			case 'OSD_EBCDIC_DF04_1':
				return 'OSD_EBCDIC_DF04_1';

			case 'ISO-11548-1':
			case 'ISO_11548-1':
			case 'ISO_TR_11548-1':
			case 'CSISO115481':
				return 'ISO-11548-1';

			case 'KZ-1048':
			case 'STRK1048-2002':
			case 'RK1048':
			case 'CSKZ1048':
				return 'KZ-1048';

			case 'ISO-10646-UCS-2':
			case 'CSUNICODE':
				return 'ISO-10646-UCS-2';

			case 'ISO-10646-UCS-4':
			case 'CSUCS4':
				return 'ISO-10646-UCS-4';

			case 'ISO-10646-UCS-BASIC':
			case 'CSUNICODEASCII':
				return 'ISO-10646-UCS-Basic';

			case 'ISO-10646-UNICODE-LATIN1':
			case 'CSUNICODELATIN1':
			case 'ISO-10646':
				return 'ISO-10646-Unicode-Latin1';

			case 'ISO-10646-J-1':
				return 'ISO-10646-J-1';

			case 'ISO-UNICODE-IBM-1261':
			case 'CSUNICODEIBM1261':
				return 'ISO-Unicode-IBM-1261';

			case 'ISO-UNICODE-IBM-1268':
			case 'CSUNICODEIBM1268':
				return 'ISO-Unicode-IBM-1268';

			case 'ISO-UNICODE-IBM-1276':
			case 'CSUNICODEIBM1276':
				return 'ISO-Unicode-IBM-1276';

			case 'ISO-UNICODE-IBM-1264':
			case 'CSUNICODEIBM1264':
				return 'ISO-Unicode-IBM-1264';

			case 'ISO-UNICODE-IBM-1265':
			case 'CSUNICODEIBM1265':
				return 'ISO-Unicode-IBM-1265';

			case 'UNICODE-1-1':
			case 'CSUNICODE11':
				return 'UNICODE-1-1';

			case 'SCSU':
				return 'SCSU';

			case 'UTF-7':
				return 'UTF-7';

			case 'UTF-16BE':
				return 'UTF-16BE';

			case 'UTF-16LE':
				return 'UTF-16LE';

			case 'UTF-16':
				return 'UTF-16';

			case 'CESU-8':
			case 'CSCESU-8':
				return 'CESU-8';

			case 'UTF-32':
				return 'UTF-32';

			case 'UTF-32BE':
				return 'UTF-32BE';

			case 'UTF-32LE':
				return 'UTF-32LE';

			case 'BOCU-1':
			case 'CSBOCU-1':
				return 'BOCU-1';

			case 'ISO-8859-1-WINDOWS-3.0-LATIN-1':
			case 'CSWINDOWS30LATIN1':
				return 'ISO-8859-1-Windows-3.0-Latin-1';

			case 'ISO-8859-1-WINDOWS-3.1-LATIN-1':
			case 'CSWINDOWS31LATIN1':
				return 'ISO-8859-1-Windows-3.1-Latin-1';

			case 'ISO-8859-2-WINDOWS-LATIN-2':
			case 'CSWINDOWS31LATIN2':
				return 'ISO-8859-2-Windows-Latin-2';

			case 'ISO-8859-9-WINDOWS-LATIN-5':
			case 'CSWINDOWS31LATIN5':
				return 'ISO-8859-9-Windows-Latin-5';

			case 'HP-ROMAN8':
			case 'ROMAN8':
			case 'R8':
			case 'CSHPROMAN8':
				return 'hp-roman8';

			case 'ADOBE-STANDARD-ENCODING':
			case 'CSADOBESTANDARDENCODING':
				return 'Adobe-Standard-Encoding';

			case 'VENTURA-US':
			case 'CSVENTURAUS':
				return 'Ventura-US';

			case 'VENTURA-INTERNATIONAL':
			case 'CSVENTURAINTERNATIONAL':
				return 'Ventura-International';

			case 'DEC-MCS':
			case 'DEC':
			case 'CSDECMCS':
				return 'DEC-MCS';

			case 'IBM850':
			case 'CP850':
			case '850':
			case 'CSPC850MULTILINGUAL':
				return 'IBM850';

			case 'PC8-DANISH-NORWEGIAN':
			case 'CSPC8DANISHNORWEGIAN':
				return 'PC8-Danish-Norwegian';

			case 'IBM862':
			case 'CP862':
			case '862':
			case 'CSPC862LATINHEBREW':
				return 'IBM862';

			case 'PC8-TURKISH':
			case 'CSPC8TURKISH':
				return 'PC8-Turkish';

			case 'IBM-SYMBOLS':
			case 'CSIBMSYMBOLS':
				return 'IBM-Symbols';

			case 'IBM-THAI':
			case 'CSIBMTHAI':
				return 'IBM-Thai';

			case 'HP-LEGAL':
			case 'CSHPLEGAL':
				return 'HP-Legal';

			case 'HP-PI-FONT':
			case 'CSHPPIFONT':
				return 'HP-Pi-font';

			case 'HP-MATH8':
			case 'CSHPMATH8':
				return 'HP-Math8';

			case 'ADOBE-SYMBOL-ENCODING':
			case 'CSHPPSMATH':
				return 'Adobe-Symbol-Encoding';

			case 'HP-DESKTOP':
			case 'CSHPDESKTOP':
				return 'HP-DeskTop';

			case 'VENTURA-MATH':
			case 'CSVENTURAMATH':
				return 'Ventura-Math';

			case 'MICROSOFT-PUBLISHING':
			case 'CSMICROSOFTPUBLISHING':
				return 'Microsoft-Publishing';

			case 'WINDOWS-31J':
			case 'CSWINDOWS31J':
				return 'Windows-31J';

			case 'GB2312':
			case 'CSGB2312':
				return 'GB2312';

			case 'BIG5':
			case 'CSBIG5':
				return 'Big5';

			case 'MACINTOSH':
			case 'MAC':
			case 'CSMACINTOSH':
				return 'macintosh';

			case 'IBM037':
			case 'CP037':
			case 'EBCDIC-CP-US':
			case 'EBCDIC-CP-CA':
			case 'EBCDIC-CP-WT':
			case 'EBCDIC-CP-NL':
			case 'CSIBM037':
				return 'IBM037';

			case 'IBM038':
			case 'EBCDIC-INT':
			case 'CP038':
			case 'CSIBM038':
				return 'IBM038';

			case 'IBM273':
			case 'CP273':
			case 'CSIBM273':
				return 'IBM273';

			case 'IBM274':
			case 'EBCDIC-BE':
			case 'CP274':
			case 'CSIBM274':
				return 'IBM274';

			case 'IBM275':
			case 'EBCDIC-BR':
			case 'CP275':
			case 'CSIBM275':
				return 'IBM275';

			case 'IBM277':
			case 'EBCDIC-CP-DK':
			case 'EBCDIC-CP-NO':
			case 'CSIBM277':
				return 'IBM277';

			case 'IBM278':
			case 'CP278':
			case 'EBCDIC-CP-FI':
			case 'EBCDIC-CP-SE':
			case 'CSIBM278':
				return 'IBM278';

			case 'IBM280':
			case 'CP280':
			case 'EBCDIC-CP-IT':
			case 'CSIBM280':
				return 'IBM280';

			case 'IBM281':
			case 'EBCDIC-JP-E':
			case 'CP281':
			case 'CSIBM281':
				return 'IBM281';

			case 'IBM284':
			case 'CP284':
			case 'EBCDIC-CP-ES':
			case 'CSIBM284':
				return 'IBM284';

			case 'IBM285':
			case 'CP285':
			case 'EBCDIC-CP-GB':
			case 'CSIBM285':
				return 'IBM285';

			case 'IBM290':
			case 'CP290':
			case 'EBCDIC-JP-KANA':
			case 'CSIBM290':
				return 'IBM290';

			case 'IBM297':
			case 'CP297':
			case 'EBCDIC-CP-FR':
			case 'CSIBM297':
				return 'IBM297';

			case 'IBM420':
			case 'CP420':
			case 'EBCDIC-CP-AR1':
			case 'CSIBM420':
				return 'IBM420';

			case 'IBM423':
			case 'CP423':
			case 'EBCDIC-CP-GR':
			case 'CSIBM423':
				return 'IBM423';

			case 'IBM424':
			case 'CP424':
			case 'EBCDIC-CP-HE':
			case 'CSIBM424':
				return 'IBM424';

			case 'IBM437':
			case 'CP437':
			case '437':
			case 'CSPC8CODEPAGE437':
				return 'IBM437';

			case 'IBM500':
			case 'CP500':
			case 'EBCDIC-CP-BE':
			case 'EBCDIC-CP-CH':
			case 'CSIBM500':
				return 'IBM500';

			case 'IBM851':
			case 'CP851':
			case '851':
			case 'CSIBM851':
				return 'IBM851';

			case 'IBM852':
			case 'CP852':
			case '852':
			case 'CSPCP852':
				return 'IBM852';

			case 'IBM855':
			case 'CP855':
			case '855':
			case 'CSIBM855':
				return 'IBM855';

			case 'IBM857':
			case 'CP857':
			case '857':
			case 'CSIBM857':
				return 'IBM857';

			case 'IBM860':
			case 'CP860':
			case '860':
			case 'CSIBM860':
				return 'IBM860';

			case 'IBM861':
			case 'CP861':
			case '861':
			case 'CP-IS':
			case 'CSIBM861':
				return 'IBM861';

			case 'IBM863':
			case 'CP863':
			case '863':
			case 'CSIBM863':
				return 'IBM863';

			case 'IBM864':
			case 'CP864':
			case 'CSIBM864':
				return 'IBM864';

			case 'IBM865':
			case 'CP865':
			case '865':
			case 'CSIBM865':
				return 'IBM865';

			case 'IBM868':
			case 'CP868':
			case 'CP-AR':
			case 'CSIBM868':
				return 'IBM868';

			case 'IBM869':
			case 'CP869':
			case '869':
			case 'CP-GR':
			case 'CSIBM869':
				return 'IBM869';

			case 'IBM870':
			case 'CP870':
			case 'EBCDIC-CP-ROECE':
			case 'EBCDIC-CP-YU':
			case 'CSIBM870':
				return 'IBM870';

			case 'IBM871':
			case 'CP871':
			case 'EBCDIC-CP-IS':
			case 'CSIBM871':
				return 'IBM871';

			case 'IBM880':
			case 'CP880':
			case 'EBCDIC-CYRILLIC':
			case 'CSIBM880':
				return 'IBM880';

			case 'IBM891':
			case 'CP891':
			case 'CSIBM891':
				return 'IBM891';

			case 'IBM903':
			case 'CP903':
			case 'CSIBM903':
				return 'IBM903';

			case 'IBM904':
			case 'CP904':
			case '904':
			case 'CSIBBM904':
				return 'IBM904';

			case 'IBM905':
			case 'CP905':
			case 'EBCDIC-CP-TR':
			case 'CSIBM905':
				return 'IBM905';

			case 'IBM918':
			case 'CP918':
			case 'EBCDIC-CP-AR2':
			case 'CSIBM918':
				return 'IBM918';

			case 'IBM1026':
			case 'CP1026':
			case 'CSIBM1026':
				return 'IBM1026';

			case 'EBCDIC-AT-DE':
			case 'CSIBMEBCDICATDE':
				return 'EBCDIC-AT-DE';

			case 'EBCDIC-AT-DE-A':
			case 'CSEBCDICATDEA':
				return 'EBCDIC-AT-DE-A';

			case 'EBCDIC-CA-FR':
			case 'CSEBCDICCAFR':
				return 'EBCDIC-CA-FR';

			case 'EBCDIC-DK-NO':
			case 'CSEBCDICDKNO':
				return 'EBCDIC-DK-NO';

			case 'EBCDIC-DK-NO-A':
			case 'CSEBCDICDKNOA':
				return 'EBCDIC-DK-NO-A';

			case 'EBCDIC-FI-SE':
			case 'CSEBCDICFISE':
				return 'EBCDIC-FI-SE';

			case 'EBCDIC-FI-SE-A':
			case 'CSEBCDICFISEA':
				return 'EBCDIC-FI-SE-A';

			case 'EBCDIC-FR':
			case 'CSEBCDICFR':
				return 'EBCDIC-FR';

			case 'EBCDIC-IT':
			case 'CSEBCDICIT':
				return 'EBCDIC-IT';

			case 'EBCDIC-PT':
			case 'CSEBCDICPT':
				return 'EBCDIC-PT';

			case 'EBCDIC-ES':
			case 'CSEBCDICES':
				return 'EBCDIC-ES';

			case 'EBCDIC-ES-A':
			case 'CSEBCDICESA':
				return 'EBCDIC-ES-A';

			case 'EBCDIC-ES-S':
			case 'CSEBCDICESS':
				return 'EBCDIC-ES-S';

			case 'EBCDIC-UK':
			case 'CSEBCDICUK':
				return 'EBCDIC-UK';

			case 'EBCDIC-US':
			case 'CSEBCDICUS':
				return 'EBCDIC-US';

			case 'UNKNOWN-8BIT':
			case 'CSUNKNOWN8BIT':
				return 'UNKNOWN-8BIT';

			case 'MNEMONIC':
			case 'CSMNEMONIC':
				return 'MNEMONIC';

			case 'MNEM':
			case 'CSMNEM':
				return 'MNEM';

			case 'VISCII':
			case 'CSVISCII':
				return 'VISCII';

			case 'VIQR':
			case 'CSVIQR':
				return 'VIQR';

			case 'KOI8-R':
			case 'CSKOI8R':
				return 'KOI8-R';

			case 'HZ-GB-2312':
				return 'HZ-GB-2312';

			case 'IBM866':
			case 'CP866':
			case '866':
			case 'CSIBM866':
				return 'IBM866';

			case 'IBM775':
			case 'CP775':
			case 'CSPC775BALTIC':
				return 'IBM775';

			case 'KOI8-U':
				return 'KOI8-U';

			case 'IBM00858':
			case 'CCSID00858':
			case 'CP00858':
			case 'PC-MULTILINGUAL-850+EURO':
				return 'IBM00858';

			case 'IBM00924':
			case 'CCSID00924':
			case 'CP00924':
			case 'EBCDIC-LATIN9--EURO':
				return 'IBM00924';

			case 'IBM01140':
			case 'CCSID01140':
			case 'CP01140':
			case 'EBCDIC-US-37+EURO':
				return 'IBM01140';

			case 'IBM01141':
			case 'CCSID01141':
			case 'CP01141':
			case 'EBCDIC-DE-273+EURO':
				return 'IBM01141';

			case 'IBM01142':
			case 'CCSID01142':
			case 'CP01142':
			case 'EBCDIC-DK-277+EURO':
			case 'EBCDIC-NO-277+EURO':
				return 'IBM01142';

			case 'IBM01143':
			case 'CCSID01143':
			case 'CP01143':
			case 'EBCDIC-FI-278+EURO':
			case 'EBCDIC-SE-278+EURO':
				return 'IBM01143';

			case 'IBM01144':
			case 'CCSID01144':
			case 'CP01144':
			case 'EBCDIC-IT-280+EURO':
				return 'IBM01144';

			case 'IBM01145':
			case 'CCSID01145':
			case 'CP01145':
			case 'EBCDIC-ES-284+EURO':
				return 'IBM01145';

			case 'IBM01146':
			case 'CCSID01146':
			case 'CP01146':
			case 'EBCDIC-GB-285+EURO':
				return 'IBM01146';

			case 'IBM01147':
			case 'CCSID01147':
			case 'CP01147':
			case 'EBCDIC-FR-297+EURO':
				return 'IBM01147';

			case 'IBM01148':
			case 'CCSID01148':
			case 'CP01148':
			case 'EBCDIC-INTERNATIONAL-500+EURO':
				return 'IBM01148';

			case 'IBM01149':
			case 'CCSID01149':
			case 'CP01149':
			case 'EBCDIC-IS-871+EURO':
				return 'IBM01149';

			case 'BIG5-HKSCS':
				return 'Big5-HKSCS';

			case 'IBM1047':
			case 'IBM-1047':
				return 'IBM1047';

			case 'PTCP154':
			case 'CSPTCP154':
			case 'PT154':
			case 'CP154':
			case 'CYRILLIC-ASIAN':
				return 'PTCP154';

			case 'AMIGA-1251':
			case 'AMI1251':
			case 'AMIGA1251':
			case 'AMI-1251':
				return 'Amiga-1251';

			case 'KOI7-SWITCHED':
				return 'KOI7-switched';

			case 'BRF':
			case 'CSBRF':
				return 'BRF';

			case 'TSCII':
			case 'CSTSCII':
				return 'TSCII';

			case 'WINDOWS-1250':
				return 'windows-1250';

			case 'WINDOWS-1251':
				return 'windows-1251';

			case 'WINDOWS-1252':
				return 'windows-1252';

			case 'WINDOWS-1253':
				return 'windows-1253';

			case 'WINDOWS-1254':
				return 'windows-1254';

			case 'WINDOWS-1255':
				return 'windows-1255';

			case 'WINDOWS-1256':
				return 'windows-1256';

			case 'WINDOWS-1257':
				return 'windows-1257';

			case 'WINDOWS-1258':
				return 'windows-1258';

			default:
				return (string) $encoding;
		}
	}

	function get_curl_version()
	{
		if (is_array($curl = curl_version()))
		{
			$curl = $curl['version'];
		}
		elseif (substr($curl, 0, 5) == 'curl/')
		{
			$curl = substr($curl, 5, strcspn($curl, "\x09\x0A\x0B\x0C\x0D", 5));
		}
		elseif (substr($curl, 0, 8) == 'libcurl/')
		{
			$curl = substr($curl, 8, strcspn($curl, "\x09\x0A\x0B\x0C\x0D", 8));
		}
		else
		{
			$curl = 0;
		}
		return $curl;
	}

	function is_subclass_of($class1, $class2)
	{
		if (func_num_args() != 2)
		{
			trigger_error('Wrong parameter count for SimplePie_Misc::is_subclass_of()', E_USER_WARNING);
		}
		elseif (version_compare(PHP_VERSION, '5.0.3', '>=') || is_object($class1))
		{
			return is_subclass_of($class1, $class2);
		}
		elseif (is_string($class1) && is_string($class2))
		{
			if (class_exists($class1))
			{
				if (class_exists($class2))
				{
					$class2 = strtolower($class2);
					while ($class1 = strtolower(get_parent_class($class1)))
					{
						if ($class1 == $class2)
						{
							return true;
						}
					}
				}
			}
			else
			{
				trigger_error('Unknown class passed as parameter', E_USER_WARNNG);
			}
		}
		return false;
	}

	/**
	 * Strip HTML comments
	 *
	 * @access public
	 * @param string $data Data to strip comments from
	 * @return string Comment stripped string
	 */
	function strip_comments($data)
	{
		$output = '';
		while (($start = strpos($data, '<!--')) !== false)
		{
			$output .= substr($data, 0, $start);
			if (($end = strpos($data, '-->', $start)) !== false)
			{
				$data = substr_replace($data, '', 0, $end + 3);
			}
			else
			{
				$data = '';
			}
		}
		return $output . $data;
	}

	function parse_date($dt, $rfc822_tz = true)
	{
		static $cache = array();
		if (!isset($cache[$dt][$rfc822_tz]))
		{
			$dt = SimplePie_Misc::uncomment_rfc822($dt);
			/*
			Capturing subpatterns:
			1: RFC 822 date
			2: RFC 822 day
			3: RFC 822 month
			4: RFC 822 year
			5: ISO 8601 date
			6: ISO 8601 century
			7: ISO 8601 year
			8: ISO 8601 month
			9: ISO 8601 day
			10: ISO 8601 ordinal day
			11: ISO 8601 month
			12: ISO 8601 day
			13: ISO 8601 week
			14: ISO 8601 day of week
			15: Time
			16: Hour
			17: Hour Decimal
			18: Minute
			19: Minute Decimal
			20: Second
			21: Second Decimal
			22: Timezone
			23: Diff ±
			24: Hour
			25: Hour Decimal
			26: Minute
			27: Minute Decimal
			28: Alphabetic Timezone
			*/
			if (preg_match('/^(?:(?:(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun)[,\s]+)?(([0-9]{1,2})\s*(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s*([0-9]{4}|[0-9]{2}))|(([0-9]{2})(?:([0-9]{2})(?:(?:-|\s)*(?:([0-9]{2})([0-9]{2})|([0-9]{3})|([0-9]{2})(?:(?:-|\s)*([0-9]{2}))?|W([0-9]{2})(?:(?:-|\s)*([0-9]))?))?)?))((?:T|\s)+([0-9]{2})(?:(?:,|\.)([0-9]*)|(?:\:|\s)*([0-9]{2})(?:(?:,|\.)([0-9]*)|(?:\:|\s)*([0-9]{2})(?:(?:,|\.)([0-9]*))?)?)?(?:\s)*((?:(\+|-)([0-9]{2})(?:(?:,|\.)([0-9]*)|(?:\:|\s)*(?:([0-9]{2})(?:(?:,|\.)([0-9]*))?))?)|(UTC|GMT|EST|CST|MST|PST|EDT|CDT|MDT|PDT|UT|[A-IK-Z]))?)?$/i', $dt, $match))
			{
				// Fill all matches
				for ($i = count($match); $i <= 28; $i++)
				{
					$match[$i] = '';
				}

				// Set blank vars
				$year = 1970;
				$month = 1;
				$day = 1;
				$hour = 0;
				$minute = 0;
				$second = 0;
				$timezone = false;

				// RFC 822
				if ($match[1] !== '')
				{
					if (strlen($match[4]) == 2)
					{
						$year = ($match[4] < 70) ? "20$match[4]" : "19$match[4]";
					}
					else
					{
						$year = $match[4];
					}
					switch (strtolower($match[3]))
					{
						case 'jan':
							$month = 1;
							break;

						case 'feb':
							$month = 2;
							break;

						case 'mar':
							$month = 3;
							break;

						case 'apr':
							$month = 4;
							break;

						case 'may':
							$month = 5;
							break;

						case 'jun':
							$month = 6;
							break;

						case 'jul':
							$month = 7;
							break;

						case 'aug':
							$month = 8;
							break;

						case 'sep':
							$month = 9;
							break;

						case 'oct':
							$month = 10;
							break;

						case 'nov':
							$month = 11;
							break;

						case 'dec':
							$month = 12;
							break;
					}
					$day = $match[2];
				}
				// ISO 8601
				else
				{
					// Year
					if ($match[7] !== '')
					{
						$year = "$match[6]$match[7]";

						// Two Digit Month/Day
						if ($match[11] !== '')
						{
							$month = $match[11];
							if ($match[12] !== '')
							{
								$day = $match[12];
							}
						}

						// Four Digit Month/Day
						elseif ($match[8] !== '')
						{
							$month = $match[8];
							$day = $match[9];
						}

						// Ordinal Day
						elseif ($match[10] !== '')
						{
							$day = $match[10];
						}

						// Week Date
						elseif ($match[13] !== '')
						{
							// Week Day
							if ($match[14] !== '')
							{
								$day = $match[14];
							}

							$first_day_of_year = date('w', mktime(0, 0, 0, 1, 1, $year));
							if ($first_day_of_year == 0)
							{
								$first_day_of_year = 7;
							}

							$day = 7 * ($match[13] - 1) + $day - ($first_day_of_year - 1);
						}
					}
					else
					{
						$year = "$match[6]00";
					}
				}
				// Time
				if ($match[15] !== '')
				{
					$time = 0;
					$time += ($match[16] + ('.' . $match[17])) * 3600;
					$time += ($match[18] + ('.' . $match[19])) * 60;
					$time += $match[20] + ('.' . $match[21]);
					$hour = floor($time / 3600);
					$time -= $hour * 3600;
					$minute = floor($time / 60);
					$time -= $minute * 60;
					$second = round($time);

					// Timezone
					if ($match[22] !== '')
					{
						// Alphabetic Timezone
						if ($match[28] !== '')
						{
							// Military
							if (strlen($match[28]) == 1)
							{
								if ($match[28] == 'Z' || $match[28] == 'z' || !$rfc822_tz)
								{
									$timezone = 0;
								}
								else
								{
									$timezone = ord(strtoupper($match[28]));

									if ($timezone > 74)
									{
										$timezone--;
									}

									if ($timezone <= 76)
									{
										$timezone = -($timezone - 64);
									}
									else
									{
										$timezone -= 76;
									}

									$timezone *= 3600;
								}
							}
							// Code
							else
							{
								switch (strtoupper($match[28]))
								{
									case 'UT':
									case 'UTC':
									case 'GMT':
										$timezone = 0;
										break;

									case 'EST':
										$timezone = -18000;
										break;

									case 'CST':
										$timezone = -21600;
										break;

									case 'MST':
										$timezone = -25200;
										break;

									case 'PST':
										$timezone = -28800;
										break;

									case 'EDT':
										$timezone = -14400;
										break;

									case 'CDT':
										$timezone = -18000;
										break;

									case 'MDT':
										$timezone = -21600;
										break;

									case 'PDT':
										$timezone = -25200;
										break;
								}
							}
						}
						// Timezone difference from UTC
						else
						{
							$timezone = 0;
							$timezone += ($match[24] + ('.' . $match[25])) * 3600;
							$timezone += ($match[26] + ('.' . $match[27])) * 60;
							$timezone = (int) round($timezone);

							if ($match[23] == '-')
							{
								$timezone = -$timezone;
							}
						}
					}
				}
				if ($timezone === false)
				{
					$cache[$dt][$rfc822_tz] = mktime($hour, $minute, $second, $month, $day, $year);
				}
				else
				{
					$cache[$dt][$rfc822_tz] = gmmktime($hour, $minute, $second, $month, $day, $year) - $timezone;
				}
			}
			elseif (($time = strtotime($dt)) > 0)
			{
				$cache[$dt][$rfc822_tz] = $time;
			}
			else
			{
				$cache[$dt][$rfc822_tz] = false;
			}
		}
		return $cache[$dt][$rfc822_tz];
	}

	/**
	 * Decode HTML entities
	 *
	 * @static
	 * @access public
	 * @param string $data Input data
	 * @return string Output data
	 */
	function entities_decode($data)
	{
		$decoder = new SimplePie_Decode_HTML_Entities($data);
		return $decoder->parse();
	}

	/**
	 * Remove RFC822 comments
	 *
	 * @author Tomas V.V.Cox <cox@idecnet.com>
	 * @author Pierre-Alain Joye <pajoye@php.net>
	 * @author Amir Mohammad Saied <amir@php.net>
	 * @copyright 1997-2006 Pierre-Alain Joye,Tomas V.V.Cox,Amir Mohammad Saied
	 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
	 * @version CVS: $Id: simplepie.php 10381 2008-06-01 03:35:53Z pasamio $
	 * @link http://pear.php.net/package/Validate
	 * @access public
	 * @param string $data Data to strip comments from
	 * @return string Comment stripped string
	 */
	function uncomment_rfc822($data)
	{
		if ((version_compare(PHP_VERSION, '4.4.6', '>=') && version_compare(PHP_VERSION, '5', '<')) || version_compare(PHP_VERSION, '5.2.2', '>='))
		{
			return $data;
		}
		else
		{
			return preg_replace('/((?:(?:\\\\"|[^("])*(?:"(?:[^"\\\\\r]|\\\\.)*"\s*)?)*)((?<!\\\\)\((?:(?2)|.)*?(?<!\\\\)\))/', '$1', $data);
		}
	}

	function parse_mime($mime)
	{
		if (($pos = strpos($mime, ';')) === false)
		{
			return trim($mime);
		}
		else
		{
			return trim(substr($mime, 0, $pos));
		}
	}

	function htmlspecialchars_decode($string, $quote_style)
	{
		if (function_exists('htmlspecialchars_decode'))
		{
			return htmlspecialchars_decode($string, $quote_style);
		}
		else
		{
			return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
		}
	}

	function atom_03_construct_type($attribs)
	{
		if (isset($attribs['']['mode']) && strtolower(trim($attribs['']['mode']) == 'base64'))
		{
			$mode = SIMPLEPIE_CONSTRUCT_BASE64;
		}
		else
		{
			$mode = SIMPLEPIE_CONSTRUCT_NONE;
		}
		if (isset($attribs['']['type']))
		{
			switch (strtolower(trim($attribs['']['type'])))
			{
				case 'text':
				case 'text/plain':
					return SIMPLEPIE_CONSTRUCT_TEXT | $mode;

				case 'html':
				case 'text/html':
					return SIMPLEPIE_CONSTRUCT_HTML | $mode;

				case 'xhtml':
				case 'application/xhtml+xml':
					return SIMPLEPIE_CONSTRUCT_XHTML | $mode;

				default:
					return SIMPLEPIE_CONSTRUCT_NONE | $mode;
			}
		}
		else
		{
			return SIMPLEPIE_CONSTRUCT_TEXT | $mode;
		}
	}

	function atom_10_construct_type($attribs)
	{
		if (isset($attribs['']['type']))
		{
			switch (strtolower(trim($attribs['']['type'])))
			{
				case 'text':
					return SIMPLEPIE_CONSTRUCT_TEXT;

				case 'html':
					return SIMPLEPIE_CONSTRUCT_HTML;

				case 'xhtml':
					return SIMPLEPIE_CONSTRUCT_XHTML;

				default:
					return SIMPLEPIE_CONSTRUCT_NONE;
			}
		}
		return SIMPLEPIE_CONSTRUCT_TEXT;
	}

	function atom_10_content_construct_type($attribs)
	{
		if (isset($attribs['']['type']))
		{
			$type = strtolower(trim($attribs['']['type']));
			switch ($type)
			{
				case 'text':
					return SIMPLEPIE_CONSTRUCT_TEXT;

				case 'html':
					return SIMPLEPIE_CONSTRUCT_HTML;

				case 'xhtml':
					return SIMPLEPIE_CONSTRUCT_XHTML;
			}
			if (in_array(substr($type, -4), array('+xml', '/xml')) || substr($type, 0, 5) == 'text/')
			{
				return SIMPLEPIE_CONSTRUCT_NONE;
			}
			else
			{
				return SIMPLEPIE_CONSTRUCT_BASE64;
			}
		}
		else
		{
			return SIMPLEPIE_CONSTRUCT_TEXT;
		}
	}

	function is_isegment_nz_nc($string)
	{
		return (bool) preg_match('/^([A-Za-z0-9\-._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!$&\'()*+,;=@]|(%[0-9ABCDEF]{2}))+$/u', $string);
	}

	function space_seperated_tokens($string)
	{
		$space_characters = "\x20\x09\x0A\x0B\x0C\x0D";
		$string_length = strlen($string);

		$position = strspn($string, $space_characters);
		$tokens = array();

		while ($position < $string_length)
		{
			$len = strcspn($string, $space_characters, $position);
			$tokens[] = substr($string, $position, $len);
			$position += $len;
			$position += strspn($string, $space_characters, $position);
		}

		return $tokens;
	}

	function array_unique($array)
	{
		if (version_compare(PHP_VERSION, '5.2', '>='))
		{
			return array_unique($array);
		}
		else
		{
			$array = (array) $array;
			$new_array = array();
			$new_array_strings = array();
			foreach ($array as $key => $value)
			{
				if (is_object($value))
				{
					if (method_exists($value, '__toString'))
					{
						$cmp = $value->__toString();
					}
					else
					{
						trigger_error('Object of class ' . get_class($value) . ' could not be converted to string', E_USER_ERROR);
					}
				}
				elseif (is_array($value))
				{
					$cmp = (string) reset($value);
				}
				else
				{
					$cmp = (string) $value;
				}
				if (!in_array($cmp, $new_array_strings))
				{
					$new_array[$key] = $value;
					$new_array_strings[] = $cmp;
				}
			}
			return $new_array;
		}
	}

	/**
	 * Converts a unicode codepoint to a UTF-8 character
	 *
	 * @static
	 * @access public
	 * @param int $codepoint Unicode codepoint
	 * @return string UTF-8 character
	 */
	function codepoint_to_utf8($codepoint)
	{
		static $cache = array();
		$codepoint = (int) $codepoint;
		if (isset($cache[$codepoint]))
		{
			return $cache[$codepoint];
		}
		elseif ($codepoint < 0)
		{
			return $cache[$codepoint] = false;
		}
		else if ($codepoint <= 0x7f)
		{
			return $cache[$codepoint] = chr($codepoint);
		}
		else if ($codepoint <= 0x7ff)
		{
			return $cache[$codepoint] = chr(0xc0 | ($codepoint >> 6)) . chr(0x80 | ($codepoint & 0x3f));
		}
		else if ($codepoint <= 0xffff)
		{
			return $cache[$codepoint] = chr(0xe0 | ($codepoint >> 12)) . chr(0x80 | (($codepoint >> 6) & 0x3f)) . chr(0x80 | ($codepoint & 0x3f));
		}
		else if ($codepoint <= 0x10ffff)
		{
			return $cache[$codepoint] = chr(0xf0 | ($codepoint >> 18)) . chr(0x80 | (($codepoint >> 12) & 0x3f)) . chr(0x80 | (($codepoint >> 6) & 0x3f)) . chr(0x80 | ($codepoint & 0x3f));
		}
		else
		{
			// U+FFFD REPLACEMENT CHARACTER
			return $cache[$codepoint] = "\xEF\xBF\xBD";
		}
	}

	/**
	 * Re-implementation of PHP 4.2.0's is_a()
	 *
	 * @static
	 * @access public
	 * @param object $object The tested object
	 * @param string $class_name The class name
	 * @return bool Returns true if the object is of this class or has this class as one of its parents, false otherwise
	 */
	 function is_a($object, $class_name)
	 {
	 	if (function_exists('is_a'))
	 	{
	 		return is_a($object, $class_name);
	 	}
	 	elseif (!is_object($object))
	 	{
	 		return false;
	 	}
	 	elseif (get_class($object) == strtolower($class_name))
	 	{
	 		return true;
	 	}
	 	else
	 	{
	 		return is_subclass_of($object, $class_name);
	 	}
	 }

	/**
	 * Re-implementation of PHP 5's stripos()
	 *
	 * Returns the numeric position of the first occurrence of needle in the
	 * haystack string.
	 *
	 * @static
	 * @access string
	 * @param object $haystack
	 * @param string $needle Note that the needle may be a string of one or more
	 *     characters. If needle is not a string, it is converted to an integer
	 *     and applied as the ordinal value of a character.
	 * @param int $offset The optional offset parameter allows you to specify which
	 *     character in haystack to start searching. The position returned is still
	 *     relative to the beginning of haystack.
	 * @return bool If needle is not found, stripos() will return boolean false.
	 */
	 function stripos($haystack, $needle, $offset = 0)
	 {
	 	if (function_exists('stripos'))
	 	{
	 		return stripos($haystack, $needle, $offset);
	 	}
	 	else
	 	{
	 		if (is_string($needle))
	 		{
	 			$needle = strtolower($needle);
	 		}
	 		elseif (is_int($needle) || is_bool($needle) || is_double($needle))
	 		{
	 			$needle = strtolower(chr($needle));
	 		}
	 		else
	 		{
	 			trigger_error('needle is not a string or an integer', E_USER_WARNING);
	 			return false;
	 		}

	 		return strpos(strtolower($haystack), $needle, $offset);
	 	}
	 }
}

/**
 * Decode HTML Entities
 *
 * This implements HTML5 as of revision 967 (2007-06-28)
 *
 * @package SimplePie
 */
class SimplePie_Decode_HTML_Entities
{
	/**
	 * Data to be parsed
	 *
	 * @access private
	 * @var string
	 */
	var $data = '';

	/**
	 * Currently consumed bytes
	 *
	 * @access private
	 * @var string
	 */
	var $consumed = '';

	/**
	 * Position of the current byte being parsed
	 *
	 * @access private
	 * @var int
	 */
	var $position = 0;

	/**
	 * Create an instance of the class with the input data
	 *
	 * @access public
	 * @param string $data Input data
	 */
	function SimplePie_Decode_HTML_Entities($data)
	{
		$this->data = $data;
	}

	/**
	 * Parse the input data
	 *
	 * @access public
	 * @return string Output data
	 */
	function parse()
	{
		while (($this->position = strpos($this->data, '&', $this->position)) !== false)
		{
			$this->consume();
			$this->entity();
			$this->consumed = '';
		}
		return $this->data;
	}

	/**
	 * Consume the next byte
	 *
	 * @access private
	 * @return mixed The next byte, or false, if there is no more data
	 */
	function consume()
	{
		if (isset($this->data[$this->position]))
		{
			$this->consumed .= $this->data[$this->position];
			return $this->data[$this->position++];
		}
		else
		{
			$this->consumed = false;
			return false;
		}
	}

	/**
	 * Consume a range of characters
	 *
	 * @access private
	 * @param string $chars Characters to consume
	 * @return mixed A series of characters that match the range, or false
	 */
	function consume_range($chars)
	{
		if ($len = strspn($this->data, $chars, $this->position))
		{
			$data = substr($this->data, $this->position, $len);
			$this->consumed .= $data;
			$this->position += $len;
			return $data;
		}
		else
		{
			$this->consumed = false;
			return false;
		}
	}

	/**
	 * Unconsume one byte
	 *
	 * @access private
	 */
	function unconsume()
	{
		$this->consumed = substr($this->consumed, 0, -1);
		$this->position--;
	}

	/**
	 * Decode an entity
	 *
	 * @access private
	 */
	function entity()
	{
		switch ($this->consume())
		{
			case "\x09":
			case "\x0A":
			case "\x0B":
			case "\x0B":
			case "\x0C":
			case "\x20":
			case "\x3C":
			case "\x26":
			case false:
				break;

			case "\x23":
				switch ($this->consume())
				{
					case "\x78":
					case "\x58":
						$range = '0123456789ABCDEFabcdef';
						$hex = true;
						break;

					default:
						$range = '0123456789';
						$hex = false;
						$this->unconsume();
						break;
				}

				if ($codepoint = $this->consume_range($range))
				{
					static $windows_1252_specials = array(0x0D => "\x0A", 0x80 => "\xE2\x82\xAC", 0x81 => "\xEF\xBF\xBD", 0x82 => "\xE2\x80\x9A", 0x83 => "\xC6\x92", 0x84 => "\xE2\x80\x9E", 0x85 => "\xE2\x80\xA6", 0x86 => "\xE2\x80\xA0", 0x87 => "\xE2\x80\xA1", 0x88 => "\xCB\x86", 0x89 => "\xE2\x80\xB0", 0x8A => "\xC5\xA0", 0x8B => "\xE2\x80\xB9", 0x8C => "\xC5\x92", 0x8D => "\xEF\xBF\xBD", 0x8E => "\xC5\xBD", 0x8F => "\xEF\xBF\xBD", 0x90 => "\xEF\xBF\xBD", 0x91 => "\xE2\x80\x98", 0x92 => "\xE2\x80\x99", 0x93 => "\xE2\x80\x9C", 0x94 => "\xE2\x80\x9D", 0x95 => "\xE2\x80\xA2", 0x96 => "\xE2\x80\x93", 0x97 => "\xE2\x80\x94", 0x98 => "\xCB\x9C", 0x99 => "\xE2\x84\xA2", 0x9A => "\xC5\xA1", 0x9B => "\xE2\x80\xBA", 0x9C => "\xC5\x93", 0x9D => "\xEF\xBF\xBD", 0x9E => "\xC5\xBE", 0x9F => "\xC5\xB8");

					if ($hex)
					{
						$codepoint = hexdec($codepoint);
					}
					else
					{
						$codepoint = intval($codepoint);
					}

					if (isset($windows_1252_specials[$codepoint]))
					{
						$replacement = $windows_1252_specials[$codepoint];
					}
					else
					{
						$replacement = SimplePie_Misc::codepoint_to_utf8($codepoint);
					}

					if ($this->consume() != ';')
					{
						$this->unconsume();
					}

					$consumed_length = strlen($this->consumed);
					$this->data = substr_replace($this->data, $replacement, $this->position - $consumed_length, $consumed_length);
					$this->position += strlen($replacement) - $consumed_length;
				}
				break;

			default:
				static $entities = array('Aacute' => "\xC3\x81", 'aacute' => "\xC3\xA1", 'Aacute;' => "\xC3\x81", 'aacute;' => "\xC3\xA1", 'Acirc' => "\xC3\x82", 'acirc' => "\xC3\xA2", 'Acirc;' => "\xC3\x82", 'acirc;' => "\xC3\xA2", 'acute' => "\xC2\xB4", 'acute;' => "\xC2\xB4", 'AElig' => "\xC3\x86", 'aelig' => "\xC3\xA6", 'AElig;' => "\xC3\x86", 'aelig;' => "\xC3\xA6", 'Agrave' => "\xC3\x80", 'agrave' => "\xC3\xA0", 'Agrave;' => "\xC3\x80", 'agrave;' => "\xC3\xA0", 'alefsym;' => "\xE2\x84\xB5", 'Alpha;' => "\xCE\x91", 'alpha;' => "\xCE\xB1", 'AMP' => "\x26", 'amp' => "\x26", 'AMP;' => "\x26", 'amp;' => "\x26", 'and;' => "\xE2\x88\xA7", 'ang;' => "\xE2\x88\xA0", 'apos;' => "\x27", 'Aring' => "\xC3\x85", 'aring' => "\xC3\xA5", 'Aring;' => "\xC3\x85", 'aring;' => "\xC3\xA5", 'asymp;' => "\xE2\x89\x88", 'Atilde' => "\xC3\x83", 'atilde' => "\xC3\xA3", 'Atilde;' => "\xC3\x83", 'atilde;' => "\xC3\xA3", 'Auml' => "\xC3\x84", 'auml' => "\xC3\xA4", 'Auml;' => "\xC3\x84", 'auml;' => "\xC3\xA4", 'bdquo;' => "\xE2\x80\x9E", 'Beta;' => "\xCE\x92", 'beta;' => "\xCE\xB2", 'brvbar' => "\xC2\xA6", 'brvbar;' => "\xC2\xA6", 'bull;' => "\xE2\x80\xA2", 'cap;' => "\xE2\x88\xA9", 'Ccedil' => "\xC3\x87", 'ccedil' => "\xC3\xA7", 'Ccedil;' => "\xC3\x87", 'ccedil;' => "\xC3\xA7", 'cedil' => "\xC2\xB8", 'cedil;' => "\xC2\xB8", 'cent' => "\xC2\xA2", 'cent;' => "\xC2\xA2", 'Chi;' => "\xCE\xA7", 'chi;' => "\xCF\x87", 'circ;' => "\xCB\x86", 'clubs;' => "\xE2\x99\xA3", 'cong;' => "\xE2\x89\x85", 'COPY' => "\xC2\xA9", 'copy' => "\xC2\xA9", 'COPY;' => "\xC2\xA9", 'copy;' => "\xC2\xA9", 'crarr;' => "\xE2\x86\xB5", 'cup;' => "\xE2\x88\xAA", 'curren' => "\xC2\xA4", 'curren;' => "\xC2\xA4", 'Dagger;' => "\xE2\x80\xA1", 'dagger;' => "\xE2\x80\xA0", 'dArr;' => "\xE2\x87\x93", 'darr;' => "\xE2\x86\x93", 'deg' => "\xC2\xB0", 'deg;' => "\xC2\xB0", 'Delta;' => "\xCE\x94", 'delta;' => "\xCE\xB4", 'diams;' => "\xE2\x99\xA6", 'divide' => "\xC3\xB7", 'divide;' => "\xC3\xB7", 'Eacute' => "\xC3\x89", 'eacute' => "\xC3\xA9", 'Eacute;' => "\xC3\x89", 'eacute;' => "\xC3\xA9", 'Ecirc' => "\xC3\x8A", 'ecirc' => "\xC3\xAA", 'Ecirc;' => "\xC3\x8A", 'ecirc;' => "\xC3\xAA", 'Egrave' => "\xC3\x88", 'egrave' => "\xC3\xA8", 'Egrave;' => "\xC3\x88", 'egrave;' => "\xC3\xA8", 'empty;' => "\xE2\x88\x85", 'emsp;' => "\xE2\x80\x83", 'ensp;' => "\xE2\x80\x82", 'Epsilon;' => "\xCE\x95", 'epsilon;' => "\xCE\xB5", 'equiv;' => "\xE2\x89\xA1", 'Eta;' => "\xCE\x97", 'eta;' => "\xCE\xB7", 'ETH' => "\xC3\x90", 'eth' => "\xC3\xB0", 'ETH;' => "\xC3\x90", 'eth;' => "\xC3\xB0", 'Euml' => "\xC3\x8B", 'euml' => "\xC3\xAB", 'Euml;' => "\xC3\x8B", 'euml;' => "\xC3\xAB", 'euro;' => "\xE2\x82\xAC", 'exist;' => "\xE2\x88\x83", 'fnof;' => "\xC6\x92", 'forall;' => "\xE2\x88\x80", 'frac12' => "\xC2\xBD", 'frac12;' => "\xC2\xBD", 'frac14' => "\xC2\xBC", 'frac14;' => "\xC2\xBC", 'frac34' => "\xC2\xBE", 'frac34;' => "\xC2\xBE", 'frasl;' => "\xE2\x81\x84", 'Gamma;' => "\xCE\x93", 'gamma;' => "\xCE\xB3", 'ge;' => "\xE2\x89\xA5", 'GT' => "\x3E", 'gt' => "\x3E", 'GT;' => "\x3E", 'gt;' => "\x3E", 'hArr;' => "\xE2\x87\x94", 'harr;' => "\xE2\x86\x94", 'hearts;' => "\xE2\x99\xA5", 'hellip;' => "\xE2\x80\xA6", 'Iacute' => "\xC3\x8D", 'iacute' => "\xC3\xAD", 'Iacute;' => "\xC3\x8D", 'iacute;' => "\xC3\xAD", 'Icirc' => "\xC3\x8E", 'icirc' => "\xC3\xAE", 'Icirc;' => "\xC3\x8E", 'icirc;' => "\xC3\xAE", 'iexcl' => "\xC2\xA1", 'iexcl;' => "\xC2\xA1", 'Igrave' => "\xC3\x8C", 'igrave' => "\xC3\xAC", 'Igrave;' => "\xC3\x8C", 'igrave;' => "\xC3\xAC", 'image;' => "\xE2\x84\x91", 'infin;' => "\xE2\x88\x9E", 'int;' => "\xE2\x88\xAB", 'Iota;' => "\xCE\x99", 'iota;' => "\xCE\xB9", 'iquest' => "\xC2\xBF", 'iquest;' => "\xC2\xBF", 'isin;' => "\xE2\x88\x88", 'Iuml' => "\xC3\x8F", 'iuml' => "\xC3\xAF", 'Iuml;' => "\xC3\x8F", 'iuml;' => "\xC3\xAF", 'Kappa;' => "\xCE\x9A", 'kappa;' => "\xCE\xBA", 'Lambda;' => "\xCE\x9B", 'lambda;' => "\xCE\xBB", 'lang;' => "\xE3\x80\x88", 'laquo' => "\xC2\xAB", 'laquo;' => "\xC2\xAB", 'lArr;' => "\xE2\x87\x90", 'larr;' => "\xE2\x86\x90", 'lceil;' => "\xE2\x8C\x88", 'ldquo;' => "\xE2\x80\x9C", 'le;' => "\xE2\x89\xA4", 'lfloor;' => "\xE2\x8C\x8A", 'lowast;' => "\xE2\x88\x97", 'loz;' => "\xE2\x97\x8A", 'lrm;' => "\xE2\x80\x8E", 'lsaquo;' => "\xE2\x80\xB9", 'lsquo;' => "\xE2\x80\x98", 'LT' => "\x3C", 'lt' => "\x3C", 'LT;' => "\x3C", 'lt;' => "\x3C", 'macr' => "\xC2\xAF", 'macr;' => "\xC2\xAF", 'mdash;' => "\xE2\x80\x94", 'micro' => "\xC2\xB5", 'micro;' => "\xC2\xB5", 'middot' => "\xC2\xB7", 'middot;' => "\xC2\xB7", 'minus;' => "\xE2\x88\x92", 'Mu;' => "\xCE\x9C", 'mu;' => "\xCE\xBC", 'nabla;' => "\xE2\x88\x87", 'nbsp' => "\xC2\xA0", 'nbsp;' => "\xC2\xA0", 'ndash;' => "\xE2\x80\x93", 'ne;' => "\xE2\x89\xA0", 'ni;' => "\xE2\x88\x8B", 'not' => "\xC2\xAC", 'not;' => "\xC2\xAC", 'notin;' => "\xE2\x88\x89", 'nsub;' => "\xE2\x8A\x84", 'Ntilde' => "\xC3\x91", 'ntilde' => "\xC3\xB1", 'Ntilde;' => "\xC3\x91", 'ntilde;' => "\xC3\xB1", 'Nu;' => "\xCE\x9D", 'nu;' => "\xCE\xBD", 'Oacute' => "\xC3\x93", 'oacute' => "\xC3\xB3", 'Oacute;' => "\xC3\x93", 'oacute;' => "\xC3\xB3", 'Ocirc' => "\xC3\x94", 'ocirc' => "\xC3\xB4", 'Ocirc;' => "\xC3\x94", 'ocirc;' => "\xC3\xB4", 'OElig;' => "\xC5\x92", 'oelig;' => "\xC5\x93", 'Ograve' => "\xC3\x92", 'ograve' => "\xC3\xB2", 'Ograve;' => "\xC3\x92", 'ograve;' => "\xC3\xB2", 'oline;' => "\xE2\x80\xBE", 'Omega;' => "\xCE\xA9", 'omega;' => "\xCF\x89", 'Omicron;' => "\xCE\x9F", 'omicron;' => "\xCE\xBF", 'oplus;' => "\xE2\x8A\x95", 'or;' => "\xE2\x88\xA8", 'ordf' => "\xC2\xAA", 'ordf;' => "\xC2\xAA", 'ordm' => "\xC2\xBA", 'ordm;' => "\xC2\xBA", 'Oslash' => "\xC3\x98", 'oslash' => "\xC3\xB8", 'Oslash;' => "\xC3\x98", 'oslash;' => "\xC3\xB8", 'Otilde' => "\xC3\x95", 'otilde' => "\xC3\xB5", 'Otilde;' => "\xC3\x95", 'otilde;' => "\xC3\xB5", 'otimes;' => "\xE2\x8A\x97", 'Ouml' => "\xC3\x96", 'ouml' => "\xC3\xB6", 'Ouml;' => "\xC3\x96", 'ouml;' => "\xC3\xB6", 'para' => "\xC2\xB6", 'para;' => "\xC2\xB6", 'part;' => "\xE2\x88\x82", 'permil;' => "\xE2\x80\xB0", 'perp;' => "\xE2\x8A\xA5", 'Phi;' => "\xCE\xA6", 'phi;' => "\xCF\x86", 'Pi;' => "\xCE\xA0", 'pi;' => "\xCF\x80", 'piv;' => "\xCF\x96", 'plusmn' => "\xC2\xB1", 'plusmn;' => "\xC2\xB1", 'pound' => "\xC2\xA3", 'pound;' => "\xC2\xA3", 'Prime;' => "\xE2\x80\xB3", 'prime;' => "\xE2\x80\xB2", 'prod;' => "\xE2\x88\x8F", 'prop;' => "\xE2\x88\x9D", 'Psi;' => "\xCE\xA8", 'psi;' => "\xCF\x88", 'QUOT' => "\x22", 'quot' => "\x22", 'QUOT;' => "\x22", 'quot;' => "\x22", 'radic;' => "\xE2\x88\x9A", 'rang;' => "\xE3\x80\x89", 'raquo' => "\xC2\xBB", 'raquo;' => "\xC2\xBB", 'rArr;' => "\xE2\x87\x92", 'rarr;' => "\xE2\x86\x92", 'rceil;' => "\xE2\x8C\x89", 'rdquo;' => "\xE2\x80\x9D", 'real;' => "\xE2\x84\x9C", 'REG' => "\xC2\xAE", 'reg' => "\xC2\xAE", 'REG;' => "\xC2\xAE", 'reg;' => "\xC2\xAE", 'rfloor;' => "\xE2\x8C\x8B", 'Rho;' => "\xCE\xA1", 'rho;' => "\xCF\x81", 'rlm;' => "\xE2\x80\x8F", 'rsaquo;' => "\xE2\x80\xBA", 'rsquo;' => "\xE2\x80\x99", 'sbquo;' => "\xE2\x80\x9A", 'Scaron;' => "\xC5\xA0", 'scaron;' => "\xC5\xA1", 'sdot;' => "\xE2\x8B\x85", 'sect' => "\xC2\xA7", 'sect;' => "\xC2\xA7", 'shy' => "\xC2\xAD", 'shy;' => "\xC2\xAD", 'Sigma;' => "\xCE\xA3", 'sigma;' => "\xCF\x83", 'sigmaf;' => "\xCF\x82", 'sim;' => "\xE2\x88\xBC", 'spades;' => "\xE2\x99\xA0", 'sub;' => "\xE2\x8A\x82", 'sube;' => "\xE2\x8A\x86", 'sum;' => "\xE2\x88\x91", 'sup;' => "\xE2\x8A\x83", 'sup1' => "\xC2\xB9", 'sup1;' => "\xC2\xB9", 'sup2' => "\xC2\xB2", 'sup2;' => "\xC2\xB2", 'sup3' => "\xC2\xB3", 'sup3;' => "\xC2\xB3", 'supe;' => "\xE2\x8A\x87", 'szlig' => "\xC3\x9F", 'szlig;' => "\xC3\x9F", 'Tau;' => "\xCE\xA4", 'tau;' => "\xCF\x84", 'there4;' => "\xE2\x88\xB4", 'Theta;' => "\xCE\x98", 'theta;' => "\xCE\xB8", 'thetasym;' => "\xCF\x91", 'thinsp;' => "\xE2\x80\x89", 'THORN' => "\xC3\x9E", 'thorn' => "\xC3\xBE", 'THORN;' => "\xC3\x9E", 'thorn;' => "\xC3\xBE", 'tilde;' => "\xCB\x9C", 'times' => "\xC3\x97", 'times;' => "\xC3\x97", 'TRADE;' => "\xE2\x84\xA2", 'trade;' => "\xE2\x84\xA2", 'Uacute' => "\xC3\x9A", 'uacute' => "\xC3\xBA", 'Uacute;' => "\xC3\x9A", 'uacute;' => "\xC3\xBA", 'uArr;' => "\xE2\x87\x91", 'uarr;' => "\xE2\x86\x91", 'Ucirc' => "\xC3\x9B", 'ucirc' => "\xC3\xBB", 'Ucirc;' => "\xC3\x9B", 'ucirc;' => "\xC3\xBB", 'Ugrave' => "\xC3\x99", 'ugrave' => "\xC3\xB9", 'Ugrave;' => "\xC3\x99", 'ugrave;' => "\xC3\xB9", 'uml' => "\xC2\xA8", 'uml;' => "\xC2\xA8", 'upsih;' => "\xCF\x92", 'Upsilon;' => "\xCE\xA5", 'upsilon;' => "\xCF\x85", 'Uuml' => "\xC3\x9C", 'uuml' => "\xC3\xBC", 'Uuml;' => "\xC3\x9C", 'uuml;' => "\xC3\xBC", 'weierp;' => "\xE2\x84\x98", 'Xi;' => "\xCE\x9E", 'xi;' => "\xCE\xBE", 'Yacute' => "\xC3\x9D", 'yacute' => "\xC3\xBD", 'Yacute;' => "\xC3\x9D", 'yacute;' => "\xC3\xBD", 'yen' => "\xC2\xA5", 'yen;' => "\xC2\xA5", 'yuml' => "\xC3\xBF", 'Yuml;' => "\xC5\xB8", 'yuml;' => "\xC3\xBF", 'Zeta;' => "\xCE\x96", 'zeta;' => "\xCE\xB6", 'zwj;' => "\xE2\x80\x8D", 'zwnj;' => "\xE2\x80\x8C");

				for ($i = 0, $match = null; $i < 9 && $this->consume(); $i++)
				{
					$consumed = substr($this->consumed, 1);
					if (isset($entities[$consumed]))
					{
						$match = $consumed;
					}
				}

				if ($match !== null)
				{
 					$this->data = substr_replace($this->data, $entities[$match], $this->position - strlen($consumed) - 1, strlen($match) + 1);
					$this->position += strlen($entities[$match]) - strlen($consumed) - 1;
				}
				break;
		}
	}
}

class SimplePie_Locator
{
	var $useragent;
	var $timeout;
	var $file;
	var $local = array();
	var $elsewhere = array();
	var $file_class = 'SimplePie_File';
	var $cached_entities = array();
	var $http_base;
	var $base;
	var $base_location = 0;
	var $checked_feeds = 0;
	var $max_checked_feeds = 10;

	function SimplePie_Locator(&$file, $timeout = 10, $useragent = null, $file_class = 'SimplePie_File', $max_checked_feeds = 10)
	{
		$this->file = &$file;
		$this->file_class = $file_class;
		$this->useragent = $useragent;
		$this->timeout = $timeout;
		$this->max_checked_feeds = $max_checked_feeds;
	}

	function find($type = SIMPLEPIE_LOCATOR_ALL)
	{
		if ($this->is_feed($this->file))
		{
			return $this->file;
		}

		if ($type & ~SIMPLEPIE_LOCATOR_NONE)
		{
			$this->get_base();
		}

		if ($type & SIMPLEPIE_LOCATOR_AUTODISCOVERY && $working = $this->autodiscovery())
		{
			return $working;
		}

		if ($type & (SIMPLEPIE_LOCATOR_LOCAL_EXTENSION | SIMPLEPIE_LOCATOR_LOCAL_BODY | SIMPLEPIE_LOCATOR_REMOTE_EXTENSION | SIMPLEPIE_LOCATOR_REMOTE_BODY) && $this->get_links())
		{
			if ($type & SIMPLEPIE_LOCATOR_LOCAL_EXTENSION && $working = $this->extension($this->local))
			{
				return $working;
			}

			if ($type & SIMPLEPIE_LOCATOR_LOCAL_BODY && $working = $this->body($this->local))
			{
				return $working;
			}

			if ($type & SIMPLEPIE_LOCATOR_REMOTE_EXTENSION && $working = $this->extension($this->elsewhere))
			{
				return $working;
			}

			if ($type & SIMPLEPIE_LOCATOR_REMOTE_BODY && $working = $this->body($this->elsewhere))
			{
				return $working;
			}
		}
		return null;
	}

	function is_feed(&$file)
	{
		$body = SimplePie_Misc::strip_comments($file->body);
		if (preg_match('/<([^\s:]+:)?(rss|RDF|feed)' . SIMPLEPIE_PCRE_XML_ATTRIBUTE . '>/i', $body))
		{
			return true;
		}
		return false;
	}

	function get_base()
	{
		if (isset($this->file->headers['content-location']))
		{
			$this->http_base = SimplePie_Misc::absolutize_url(trim($this->file->headers['content-location']), $this->file->url);
		}
		else
		{
			$this->http_base = $this->file->url;
		}
		$this->base = $this->http_base;
		$elements = SimplePie_Misc::get_element('base', $this->file->body);
		foreach ($elements as $element)
		{
			if ($element['attribs']['href']['data'] !== '')
			{
				$this->base = SimplePie_Misc::absolutize_url(trim($element['attribs']['href']['data']), $this->http_base);
				$this->base_location = $element['offset'];
				break;
			}
		}
	}

	function autodiscovery()
	{
		$links = array_merge(SimplePie_Misc::get_element('link', $this->file->body), SimplePie_Misc::get_element('a', $this->file->body), SimplePie_Misc::get_element('area', $this->file->body));
		$done = array();
		foreach ($links as $link)
		{
			if ($this->checked_feeds == $this->max_checked_feeds)
			{
				break;
			}
			if (isset($link['attribs']['href']['data']) && isset($link['attribs']['rel']['data']))
			{
				$rel = array_unique(SimplePie_Misc::space_seperated_tokens(strtolower($link['attribs']['rel']['data'])));

				if ($this->base_location < $link['offset'])
				{
					$href = SimplePie_Misc::absolutize_url(trim($link['attribs']['href']['data']), $this->base);
				}
				else
				{
					$href = SimplePie_Misc::absolutize_url(trim($link['attribs']['href']['data']), $this->http_base);
				}

				if (!in_array($href, $done) && in_array('feed', $rel) || (in_array('alternate', $rel) && !empty($link['attribs']['type']['data']) && in_array(strtolower(SimplePie_Misc::parse_mime($link['attribs']['type']['data'])), array('application/rss+xml', 'application/atom+xml'))))
				{
					$this->checked_feeds++;
					$feed = &new $this->file_class($href, $this->timeout, 5, null, $this->useragent);
					if ($this->is_feed($feed))
					{
						return $feed;
					}
				}
				$done[] = $href;
			}
		}
		return null;
	}

	function get_links()
	{
		$links = SimplePie_Misc::get_element('a', $this->file->body);
		foreach ($links as $link)
		{
			if (isset($link['attribs']['href']['data']))
			{
				$href = trim($link['attribs']['href']['data']);
				$parsed = SimplePie_Misc::parse_url($href);
				if ($parsed['scheme'] === '' || preg_match('/^(http(s)|feed)?$/i', $parsed['scheme']))
				{
					if ($this->base_location < $link['offset'])
					{
						$href = SimplePie_Misc::absolutize_url(trim($link['attribs']['href']['data']), $this->base);
					}
					else
					{
						$href = SimplePie_Misc::absolutize_url(trim($link['attribs']['href']['data']), $this->http_base);
					}

					$current = SimplePie_Misc::parse_url($this->file->url);

					if ($parsed['authority'] === '' || $parsed['authority'] == $current['authority'])
					{
						$this->local[] = $href;
					}
					else
					{
						$this->elsewhere[] = $href;
					}
				}
			}
		}
		$this->local = array_unique($this->local);
		$this->elsewhere = array_unique($this->elsewhere);
		if (!empty($this->local) || !empty($this->elsewhere))
		{
			return true;
		}
		return null;
	}

	function extension(&$array)
	{
		foreach ($array as $key => $value)
		{
			if ($this->checked_feeds == $this->max_checked_feeds)
			{
				break;
			}
			if (in_array(strtolower(strrchr($value, '.')), array('.rss', '.rdf', '.atom', '.xml')))
			{
				$this->checked_feeds++;
				$feed = &new $this->file_class($value, $this->timeout, 5, null, $this->useragent);
				if ($this->is_feed($feed))
				{
					return $feed;
				}
				else
				{
					unset($array[$key]);
				}
			}
		}
		return null;
	}

	function body(&$array)
	{
		foreach ($array as $key => $value)
		{
			if ($this->checked_feeds == $this->max_checked_feeds)
			{
				break;
			}
			if (preg_match('/(rss|rdf|atom|xml)/i', $value))
			{
				$this->checked_feeds++;
				$feed = &new $this->file_class($value, $this->timeout, 5, null, $this->useragent);
				if ($this->is_feed($feed))
				{
					return $feed;
				}
				else
				{
					unset($array[$key]);
				}
			}
		}
		return null;
	}
}

class SimplePie_Parser
{
	var $xml;
	var $error_code;
	var $error_string;
	var $current_line;
	var $current_column;
	var $current_byte;
	var $separator = ' ';
	var $feed = false;
	var $namespace = array('');
	var $element = array('');
	var $xml_base = array('');
	var $xml_base_explicit = array(false);
	var $xml_lang = array('');
	var $data = array();
	var $datas = array(array());
	var $current_xhtml_construct = -1;
	var $encoding;

	function pre_process(&$data, $encoding)
	{
		// Use UTF-8 if we get passed US-ASCII, as every US-ASCII character is a UTF-8 character
		if (strtoupper($encoding) == 'US-ASCII')
		{
			$this->encoding = 'UTF-8';
		}
		else
		{
			$this->encoding = $encoding;
		}

		// Strip BOM:
		// UTF-32 Big Endian BOM
		if (strpos($data, "\x0\x0\xFE\xFF") === 0)
		{
			$data = substr($data, 4);
		}
		// UTF-32 Little Endian BOM
		elseif (strpos($data, "\xFF\xFE\x0\x0") === 0)
		{
			$data = substr($data, 4);
		}
		// UTF-16 Big Endian BOM
		elseif (strpos($data, "\xFE\xFF") === 0)
		{
			$data = substr($data, 2);
		}
		// UTF-16 Little Endian BOM
		elseif (strpos($data, "\xFF\xFE") === 0)
		{
			$data = substr($data, 2);
		}
		// UTF-8 BOM
		elseif (strpos($data, "\xEF\xBB\xBF") === 0)
		{
			$data = substr($data, 3);
		}

		// Make sure the XML prolog is sane and has the correct encoding
		$data = preg_replace("/^<\?xml[\x20\x9\xD\xA]+version([\x20\x9\xD\xA]+)?=([\x20\x9\xD\xA]+)?(\"1.0\"|'1.0'|\"1.1\"|'1.1')([\x20\x9\xD\xA]+encoding([\x20\x9\xD\xA]+)?=([\x20\x9\xD\xA]+)?(\"[A-Za-z][A-Za-z0-9._\-]*\"|'[A-Za-z][A-Za-z0-9._\-]*'))?([\x20\x9\xD\xA]+standalone([\x20\x9\xD\xA]+)?=([\x20\x9\xD\xA]+)?(\"(yes|no)\"|'(yes|no)'))?([\x20\x9\xD\xA]+)?\?>/", '', $data);
		$data = "<?xml version='1.0' encoding='$encoding'?>\n" . $data;
	}

	function parse(&$data)
	{
		$return = true;

		// Create the parser
		$this->xml = xml_parser_create_ns($this->encoding, $this->separator);
		xml_parser_set_option($this->xml, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option($this->xml, XML_OPTION_CASE_FOLDING, 0);
		xml_set_object($this->xml, $this);
		xml_set_character_data_handler($this->xml, 'cdata');
		xml_set_element_handler($this->xml, 'tag_open', 'tag_close');

		// Parse!
		if (!xml_parse($this->xml, $data, true))
		{
			$this->data = null;
			$this->error_code = xml_get_error_code($this->xml);
			$this->error_string = xml_error_string($this->error_code);
			$return = false;
		}
		$this->current_line = xml_get_current_line_number($this->xml);
		$this->current_column = xml_get_current_column_number($this->xml);
		$this->current_byte = xml_get_current_byte_index($this->xml);
		xml_parser_free($this->xml);
		return $return;
	}

	function get_error_code()
	{
		return $this->error_code;
	}

	function get_error_string()
	{
		return $this->error_string;
	}

	function get_current_line()
	{
		return $this->current_line;
	}

	function get_current_column()
	{
		return $this->current_column;
	}

	function get_current_byte()
	{
		return $this->current_byte;
	}

	function get_data()
	{
		return $this->data;
	}

	function tag_open($parser, $tag, $attributes)
	{
		if ($this->feed === 0)
		{
			return;
		}
		elseif ($this->feed == false)
		{
			if (in_array($tag, array(
				SIMPLEPIE_NAMESPACE_ATOM_10 . $this->separator . 'feed',
				SIMPLEPIE_NAMESPACE_ATOM_03 . $this->separator . 'feed',
				'rss',
				SIMPLEPIE_NAMESPACE_RDF . $this->separator . 'RDF'
			)))
			{
					$this->feed = 1;
			}
		}
		else
		{
			$this->feed++;
		}

		list($this->namespace[], $this->element[]) = $this->split_ns($tag);

		$attribs = array();
		foreach ($attributes as $name => $value)
		{
			list($attrib_namespace, $attribute) = $this->split_ns($name);
			$attribs[$attrib_namespace][$attribute] = $value;
		}

		if (isset($attribs[SIMPLEPIE_NAMESPACE_XML]['base']))
		{
			$this->xml_base[] = SimplePie_Misc::absolutize_url($attribs[SIMPLEPIE_NAMESPACE_XML]['base'], end($this->xml_base));
			$this->xml_base_explicit[] = true;
		}
		else
		{
			$this->xml_base[] = end($this->xml_base);
			$this->xml_base_explicit[] = end($this->xml_base_explicit);
		}

		if (isset($attribs[SIMPLEPIE_NAMESPACE_XML]['lang']))
		{
			$this->xml_lang[] = $attribs[SIMPLEPIE_NAMESPACE_XML]['lang'];
		}
		else
		{
			$this->xml_lang[] = end($this->xml_lang);
		}

		if ($this->current_xhtml_construct >= 0)
		{
			$this->current_xhtml_construct++;
			if (end($this->namespace) == SIMPLEPIE_NAMESPACE_XHTML)
			{
				$this->data['data'] .= '<' . end($this->element);
				if (isset($attribs['']))
				{
					foreach ($attribs[''] as $name => $value)
					{
						$this->data['data'] .= ' ' . $name . '="' . htmlspecialchars($value, ENT_COMPAT, $this->encoding) . '"';
					}
				}
				$this->data['data'] .= '>';
			}
		}
		else
		{
			$this->datas[] = &$this->data;
			$this->data = &$this->data['child'][end($this->namespace)][end($this->element)][];
			$this->data = array('data' => '', 'attribs' => $attribs, 'xml_base' => end($this->xml_base), 'xml_base_explicit' => end($this->xml_base_explicit), 'xml_lang' => end($this->xml_lang));
			if ((end($this->namespace) == SIMPLEPIE_NAMESPACE_ATOM_03 && in_array(end($this->element), array('title', 'tagline', 'copyright', 'info', 'summary', 'content')) && isset($attribs['']['mode']) && $attribs['']['mode'] == 'xml')
			|| (end($this->namespace) == SIMPLEPIE_NAMESPACE_ATOM_10 && in_array(end($this->element), array('rights', 'subtitle', 'summary', 'info', 'title', 'content')) && isset($attribs['']['type']) && $attribs['']['type'] == 'xhtml'))
			{
				$this->current_xhtml_construct = 0;
			}
		}
	}

	function cdata($parser, $cdata)
	{
		if ($this->current_xhtml_construct >= 0)
		{
			$this->data['data'] .= htmlspecialchars($cdata, ENT_QUOTES, $this->encoding);
		}
		elseif ($this->feed > 1)
		{
			$this->data['data'] .= $cdata;
		}
	}

	function tag_close($parser, $tag)
	{
		if (!$this->feed)
		{
			return;
		}

		if ($this->current_xhtml_construct >= 0)
		{
			$this->current_xhtml_construct--;
			if (end($this->namespace) == SIMPLEPIE_NAMESPACE_XHTML && !in_array(end($this->element), array('area', 'base', 'basefont', 'br', 'col', 'frame', 'hr', 'img', 'input', 'isindex', 'link', 'meta', 'param')))
			{
				$this->data['data'] .= '</' . end($this->element) . '>';
			}
		}
		if ($this->current_xhtml_construct == -1)
		{
			$this->data = &$this->datas[$this->feed];
			array_pop($this->datas);
		}

		array_pop($this->element);
		array_pop($this->namespace);
		array_pop($this->xml_base);
		array_pop($this->xml_base_explicit);
		array_pop($this->xml_lang);
		$this->feed--;
	}

	function split_ns($string)
	{
		static $cache = array();
		if (!isset($cache[$string]))
		{
			if ($pos = strpos($string, $this->separator))
			{
				static $separator_length;
				if (!$separator_length)
				{
					$separator_length = strlen($this->separator);
				}
				$cache[$string] = array(substr($string, 0, $pos), substr($string, $pos + $separator_length));
			}
			else
			{
				$cache[$string] = array('', $string);
			}
		}
		return $cache[$string];
	}
}

/**
 * @todo Move to using an actual HTML parser (this will allow tags to be properly stripped, and to switch between HTML and XHTML), this will also make it easier to shortern a string while preserving HTML tags
 */
class SimplePie_Sanitize
{
	// Private vars
	var $base;

	// Options
	var $remove_div = true;
	var $image_handler = '';
	var $strip_htmltags = array('base', 'blink', 'body', 'doctype', 'embed', 'font', 'form', 'frame', 'frameset', 'html', 'iframe', 'input', 'marquee', 'meta', 'noscript', 'object', 'param', 'script', 'style');
	var $encode_instead_of_strip = false;
	var $strip_attributes = array('bgsound', 'class', 'expr', 'id', 'style', 'onclick', 'onerror', 'onfinish', 'onmouseover', 'onmouseout', 'onfocus', 'onblur', 'lowsrc', 'dynsrc');
	var $strip_comments = false;
	var $output_encoding = 'UTF-8';
	var $enable_cache = true;
	var $cache_location = './cache';
	var $cache_name_function = 'md5';
	var $cache_class = 'SimplePie_Cache';
	var $file_class = 'SimplePie_File';
	var $timeout = 10;
	var $useragent = '';
	var $force_fsockopen = false;

	var $replace_url_attributes = array(
		'a' => 'href',
		'area' => 'href',
		'blockquote' => 'cite',
		'del' => 'cite',
		'form' => 'action',
		'img' => array('longdesc', 'src'),
		'input' => 'src',
		'ins' => 'cite',
		'q' => 'cite'
	);

	function remove_div($enable = true)
	{
		$this->remove_div = (bool) $enable;
	}

	function set_image_handler($page = false)
	{
		if ($page)
		{
			$this->image_handler = (string) $page;
		}
		else
		{
			$this->image_handler = false;
		}
	}

	function pass_cache_data($enable_cache = true, $cache_location = './cache', $cache_name_function = 'md5', $cache_class = 'SimplePie_Cache')
	{
		if (isset($enable_cache))
		{
			$this->enable_cache = (bool) $enable_cache;
		}

		if ($cache_location)
		{
			$this->cache_location = (string) $cache_location;
		}

		if ($cache_name_function)
		{
			$this->cache_name_function = (string) $cache_name_function;
		}

		if ($cache_class)
		{
			$this->cache_class = (string) $cache_class;
		}
	}

	function pass_file_data($file_class = 'SimplePie_File', $timeout = 10, $useragent = '', $force_fsockopen = false)
	{
		if ($file_class)
		{
			$this->file_class = (string) $file_class;
		}

		if ($timeout)
		{
			$this->timeout = (string) $timeout;
		}

		if ($useragent)
		{
			$this->useragent = (string) $useragent;
		}

		if ($force_fsockopen)
		{
			$this->force_fsockopen = (string) $force_fsockopen;
		}
	}

	function strip_htmltags($tags = array('base', 'blink', 'body', 'doctype', 'embed', 'font', 'form', 'frame', 'frameset', 'html', 'iframe', 'input', 'marquee', 'meta', 'noscript', 'object', 'param', 'script', 'style'))
	{
		if ($tags)
		{
			if (is_array($tags))
			{
				$this->strip_htmltags = $tags;
			}
			else
			{
				$this->strip_htmltags = explode(',', $tags);
			}
		}
		else
		{
			$this->strip_htmltags = false;
		}
	}

	function encode_instead_of_strip($encode = false)
	{
		$this->encode_instead_of_strip = (bool) $encode;
	}

	function strip_attributes($attribs = array('bgsound', 'class', 'expr', 'id', 'style', 'onclick', 'onerror', 'onfinish', 'onmouseover', 'onmouseout', 'onfocus', 'onblur', 'lowsrc', 'dynsrc'))
	{
		if ($attribs)
		{
			if (is_array($attribs))
			{
				$this->strip_attributes = $attribs;
			}
			else
			{
				$this->strip_attributes = explode(',', $attribs);
			}
		}
		else
		{
			$this->strip_attributes = false;
		}
	}

	function strip_comments($strip = false)
	{
		$this->strip_comments = (bool) $strip;
	}

	function set_output_encoding($encoding = 'UTF-8')
	{
		$this->output_encoding = (string) $encoding;
	}

	/**
	 * Set element/attribute key/value pairs of HTML attributes
	 * containing URLs that need to be resolved relative to the feed
	 *
	 * @access public
	 * @since 1.0
	 * @param array $element_attribute Element/attribute key/value pairs
	 */
	function set_url_replacements($element_attribute = array('a' => 'href', 'area' => 'href', 'blockquote' => 'cite', 'del' => 'cite', 'form' => 'action', 'img' => array('longdesc', 'src'), 'input' => 'src', 'ins' => 'cite', 'q' => 'cite'))
	{
		$this->replace_url_attributes = (array) $element_attribute;
	}

	function sanitize($data, $type, $base = '')
	{
		$data = trim($data);
		if ($data !== '' || $type & SIMPLEPIE_CONSTRUCT_IRI)
		{
			if ($type & SIMPLEPIE_CONSTRUCT_MAYBE_HTML)
			{
				if (preg_match('/(&(#(x[0-9a-fA-F]+|[0-9]+)|[a-zA-Z0-9]+)|<\/(\w+)' . SIMPLEPIE_PCRE_HTML_ATTRIBUTE . '>)/', $data))
				{
					$type |= SIMPLEPIE_CONSTRUCT_HTML;
				}
				else
				{
					$type |= SIMPLEPIE_CONSTRUCT_TEXT;
				}
			}

			if ($type & SIMPLEPIE_CONSTRUCT_BASE64)
			{
				$data = base64_decode($data);
			}

			if ($type & SIMPLEPIE_CONSTRUCT_XHTML)
			{
				if ($this->remove_div)
				{
					$data = preg_replace('/^<div' . SIMPLEPIE_PCRE_XML_ATTRIBUTE . '>/', '', $data);
					$data = preg_replace('/<\/div>$/', '', $data);
				}
				else
				{
					$data = preg_replace('/^<div' . SIMPLEPIE_PCRE_XML_ATTRIBUTE . '>/', '<div>', $data);
				}
			}

			if ($type & (SIMPLEPIE_CONSTRUCT_HTML | SIMPLEPIE_CONSTRUCT_XHTML))
			{
				// Strip comments
				if ($this->strip_comments)
				{
					$data = SimplePie_Misc::strip_comments($data);
				}

				// Strip out HTML tags and attributes that might cause various security problems.
				// Based on recommendations by Mark Pilgrim at:
				// http://diveintomark.org/archives/2003/06/12/how_to_consume_rss_safely
				if ($this->strip_htmltags)
				{
					foreach ($this->strip_htmltags as $tag)
					{
						$pcre = "/<($tag)" . SIMPLEPIE_PCRE_HTML_ATTRIBUTE . "(>(.*)<\/$tag" . SIMPLEPIE_PCRE_HTML_ATTRIBUTE . '>|(\/)?>)/siU';
						while (preg_match($pcre, $data))
						{
							$data = preg_replace_callback($pcre, array(&$this, 'do_strip_htmltags'), $data);
						}
					}
				}

				if ($this->strip_attributes)
				{
					foreach ($this->strip_attributes as $attrib)
					{
						$data = preg_replace('/ '. trim($attrib) .'=("|&quot;)(\w|\s|=|-|:|;|\/|\.|\?|&|,|#|!|\(|\)|\'|&apos;|<|>|\+|{|})*("|&quot;)/i', '', $data);
						$data = preg_replace('/ '. trim($attrib) .'=(\'|&apos;)(\w|\s|=|-|:|;|\/|\.|\?|&|,|#|!|\(|\)|"|&quot;|<|>|\+|{|})*(\'|&apos;)/i', '', $data);
						$data = preg_replace('/ '. trim($attrib) .'=(\w|\s|=|-|:|;|\/|\.|\?|&|,|#|!|\(|\)|\+|{|})*/i', '', $data);
					}
				}

				// Replace relative URLs
				$this->base = $base;
				foreach ($this->replace_url_attributes as $element => $attributes)
				{
					$data = $this->replace_urls($data, $element, $attributes);
				}

				// If image handling (caching, etc.) is enabled, cache and rewrite all the image tags.
				if (isset($this->image_handler) && ((string) $this->image_handler) !== '' && $this->enable_cache)
				{
					$images = SimplePie_Misc::get_element('img', $data);
					foreach ($images as $img)
					{
						if (isset($img['attribs']['src']['data']))
						{
							$image_url = $img['attribs']['src']['data'];
							$cache = &new $this->cache_class($this->cache_location, call_user_func($this->cache_name_function, $image_url), 'spi');

							if ($cache->load())
							{
								$img['attribs']['src']['data'] = $this->image_handler . rawurlencode($img['attribs']['src']['data']);
								$data = str_replace($img['full'], SimplePie_Misc::element_implode($img), $data);
							}
							else
							{
								$file = &new $this->file_class($image_url, $this->timeout, 5, array('X-FORWARDED-FOR' => $_SERVER['REMOTE_ADDR']), $this->useragent, $this->force_fsockopen);
								$headers = $file->headers;

								if ($file->success && ($file->status_code == 200 || ($file->status_code > 206 && $file->status_code < 300)))
								{
									if (!$cache->save(array('headers' => $file->headers, 'body' => $file->body)))
									{
										trigger_error("$cache->name is not writeable", E_USER_WARNING);
									}
									$img['attribs']['src']['data'] = $this->image_handler . rawurlencode($img['attribs']['src']['data']);
									$data = str_replace($img['full'], SimplePie_Misc::element_implode($img), $data);
								}
							}
						}
					}
				}

				// Having (possibly) taken stuff out, there may now be whitespace at the beginning/end of the data
				$data = trim($data);
			}

			if ($type & SIMPLEPIE_CONSTRUCT_IRI)
			{
				$data = SimplePie_Misc::absolutize_url($data, $base);
			}

			if ($type & (SIMPLEPIE_CONSTRUCT_TEXT | SIMPLEPIE_CONSTRUCT_IRI))
			{
				$data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
			}

			if ($this->output_encoding != 'UTF-8')
			{
				$data = SimplePie_Misc::change_encoding($data, 'UTF-8', $this->output_encoding);
			}
		}
		return $data;
	}

	function replace_urls($data, $tag, $attributes)
	{
		if (!is_array($this->strip_htmltags) || !in_array($tag, $this->strip_htmltags))
		{
			$elements = SimplePie_Misc::get_element($tag, $data);
			foreach ($elements as $element)
			{
				if (is_array($attributes))
				{
					foreach ($attributes as $attribute)
					{
						if (isset($element['attribs'][$attribute]['data']))
						{
							$element['attribs'][$attribute]['data'] = SimplePie_Misc::absolutize_url($element['attribs'][$attribute]['data'], $this->base);
							$data = str_replace($element['full'], SimplePie_Misc::element_implode($element), $data);
						}
					}
				}
				elseif (isset($element['attribs'][$attributes]['data']))
				{
					$element['attribs'][$attributes]['data'] = SimplePie_Misc::absolutize_url($element['attribs'][$attributes]['data'], $this->base);
					$data = str_replace($element['full'], SimplePie_Misc::element_implode($element), $data);
				}
			}
		}
		return $data;
	}

	function do_strip_htmltags($match)
	{
		if ($this->encode_instead_of_strip)
		{
			if (isset($match[4]) && !in_array(strtolower($match[1]), array('script', 'style')))
			{
				$match[1] = htmlspecialchars($match[1], ENT_COMPAT, 'UTF-8');
				$match[2] = htmlspecialchars($match[2], ENT_COMPAT, 'UTF-8');
				return "&lt;$match[1]$match[2]&gt;$match[3]&lt;/$match[1]&gt;";
			}
			else
			{
				return htmlspecialchars($match[0], ENT_COMPAT, 'UTF-8');
			}
		}
		elseif (isset($match[4]) && !in_array(strtolower($match[1]), array('script', 'style')))
		{
			return $match[4];
		}
		else
		{
			return '';
		}
	}
}

?>
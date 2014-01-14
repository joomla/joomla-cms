<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class to model a Web Client.
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.3
 */
class JWebClient
{
	const WINDOWS = 1;
	const WINDOWS_PHONE = 2;
	const WINDOWS_CE = 3;
	const IPHONE = 4;
	const IPAD = 5;
	const IPOD = 6;
	const MAC = 7;
	const BLACKBERRY = 8;
	const ANDROID = 9;
	const LINUX = 10;
	const TRIDENT = 11;
	const WEBKIT = 12;
	const GECKO = 13;
	const PRESTO = 14;
	const KHTML = 15;
	const AMAYA = 16;
	const IE = 17;
	const FIREFOX = 18;
	const CHROME = 19;
	const SAFARI = 20;
	const OPERA = 21;

	/**
	 * @var    integer  The detected platform on which the web client runs.
	 * @since  11.3
	 */
	protected $platform;

	/**
	 * @var    boolean  True if the web client is a mobile device.
	 * @since  11.3
	 */
	protected $mobile = false;

	/**
	 * @var    integer  The detected rendering engine used by the web client.
	 * @since  11.3
	 */
	protected $engine;

	/**
	 * @var    integer  The detected browser used by the web client.
	 * @since  11.3
	 */
	protected $browser;

	/**
	 * @var    string  The detected browser version used by the web client.
	 * @since  11.3
	 */
	protected $browserVersion;

	/**
	 * @var    array  The priority order detected accepted languages for the client.
	 * @since  11.3
	 */
	protected $languages = array();

	/**
	 * @var    array  The priority order detected accepted encodings for the client.
	 * @since  11.3
	 */
	protected $encodings = array();

	/**
	 * @var    string  The web client's user agent string.
	 * @since  11.3
	 */
	protected $userAgent;

	/**
	 * @var    string  The web client's accepted encoding string.
	 * @since  11.3
	 */
	protected $acceptEncoding;

	/**
	 * @var    string  The web client's accepted languages string.
	 * @since  11.3
	 */
	protected $acceptLanguage;

	/**
	 * @var    array  An array of flags determining whether or not a detection routine has been run.
	 * @since  11.3
	 */
	protected $detection = array();

	/**
	 * Class constructor.
	 *
	 * @param   mixed  $userAgent       The optional user-agent string to parse.
	 * @param   mixed  $acceptEncoding  The optional client accept encoding string to parse.
	 * @param   mixed  $acceptLanguage  The optional client accept language string to parse.
	 *
	 * @since   11.3
	 */
	public function __construct($userAgent = null, $acceptEncoding = null, $acceptLanguage = null)
	{
		// If no explicit user agent string was given attempt to use the implicit one from server environment.
		if (empty($userAgent) && isset($_SERVER['HTTP_USER_AGENT']))
		{
			$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		}
		else
		{
			$this->userAgent = $userAgent;
		}

		// If no explicit acceptable encoding string was given attempt to use the implicit one from server environment.
		if (empty($acceptEncoding) && isset($_SERVER['HTTP_ACCEPT_ENCODING']))
		{
			$this->acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
		}
		else
		{
			$this->acceptEncoding = $acceptEncoding;
		}

		// If no explicit acceptable languages string was given attempt to use the implicit one from server environment.
		if (empty($acceptLanguage) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$this->acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		}
		else
		{
			$this->acceptLanguage = $acceptLanguage;
		}
	}

	/**
	 * Magic method to get an object property's value by name.
	 *
	 * @param   string  $name  Name of the property for which to return a value.
	 *
	 * @return  mixed  The requested value if it exists.
	 *
	 * @since   11.3
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'mobile':
			case 'platform':
				if (empty($this->detection['platform']))
				{
					$this->detectPlatform($this->userAgent);
				}
				break;

			case 'engine':
				if (empty($this->detection['engine']))
				{
					$this->detectEngine($this->userAgent);
				}
				break;

			case 'browser':
			case 'browserVersion':
				if (empty($this->detection['browser']))
				{
					$this->detectBrowser($this->userAgent);
				}
				break;

			case 'languages':
				if (empty($this->detection['acceptLanguage']))
				{
					$this->detectLanguage($this->acceptLanguage);
				}
				break;

			case 'encodings':
				if (empty($this->detection['acceptEncoding']))
				{
					$this->detectEncoding($this->acceptEncoding);
				}
				break;
		}

		// Return the property if it exists.
		if (isset($this->$name))
		{
			return $this->$name;
		}
	}

	/**
	 * Detects the client browser and version in a user agent string.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function detectBrowser($userAgent)
	{
		// Attempt to detect the browser type.  Obviously we are only worried about major browsers.
		if ((stripos($userAgent, 'MSIE') !== false) && (stripos($userAgent, 'Opera') === false))
		{
			$this->browser = self::IE;
			$patternBrowser = 'MSIE';
		}
		elseif ((stripos($userAgent, 'Firefox') !== false) && (stripos($userAgent, 'like Firefox') === false))
		{
			$this->browser = self::FIREFOX;
			$patternBrowser = 'Firefox';
		}
		elseif (stripos($userAgent, 'Chrome') !== false)
		{
			$this->browser = self::CHROME;
			$patternBrowser = 'Chrome';
		}
		elseif (stripos($userAgent, 'Safari') !== false)
		{
			$this->browser = self::SAFARI;
			$patternBrowser = 'Safari';
		}
		elseif (stripos($userAgent, 'Opera') !== false)
		{
			$this->browser = self::OPERA;
			$patternBrowser = 'Opera';
		}

		// If we detected a known browser let's attempt to determine the version.
		if ($this->browser)
		{
			// Build the REGEX pattern to match the browser version string within the user agent string.
			$pattern = '#(?<browser>Version|' . $patternBrowser . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

			// Attempt to find version strings in the user agent string.
			$matches = array();
			if (preg_match_all($pattern, $userAgent, $matches))
			{
				// Do we have both a Version and browser match?
				if (count($matches['browser']) > 1)
				{
					// See whether Version or browser came first, and use the number accordingly.
					if (strripos($userAgent, 'Version') < strripos($userAgent, $patternBrowser))
					{
						$this->browserVersion = $matches['version'][0];
					}
					else
					{
						$this->browserVersion = $matches['version'][1];
					}
				}
				// We only have a Version or a browser so use what we have.
				else
				{
					$this->browserVersion = $matches['version'][0];
				}
			}
		}

		// Mark this detection routine as run.
		$this->detection['browser'] = true;
	}

	/**
	 * Method to detect the accepted response encoding by the client.
	 *
	 * @param   string  $acceptEncoding  The client accept encoding string to parse.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function detectEncoding($acceptEncoding)
	{
		// Parse the accepted encodings.
		$this->encodings = array_map('trim', (array) explode(',', $acceptEncoding));

		// Mark this detection routine as run.
		$this->detection['acceptEncoding'] = true;
	}

	/**
	 * Detects the client rendering engine in a user agent string.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function detectEngine($userAgent)
	{
		// Attempt to detect the client engine -- starting with the most popular ... for now.
		if (stripos($userAgent, 'MSIE') !== false || stripos($userAgent, 'Trident') !== false)
		{
			$this->engine = self::TRIDENT;
		}
		// Evidently blackberry uses WebKit and doesn't necessarily report it.  Bad RIM.
		elseif (stripos($userAgent, 'AppleWebKit') !== false || stripos($userAgent, 'blackberry') !== false)
		{
			$this->engine = self::WEBKIT;
		}
		// We have to check for like Gecko because some other browsers spoof Gecko.
		elseif (stripos($userAgent, 'Gecko') !== false && stripos($userAgent, 'like Gecko') === false)
		{
			$this->engine = self::GECKO;
		}
		// Sometimes Opera browsers don't say Presto.
		elseif (stripos($userAgent, 'Opera') !== false || stripos($userAgent, 'Presto') !== false)
		{
			$this->engine = self::PRESTO;
		}
		// *sigh*
		elseif (stripos($userAgent, 'KHTML') !== false)
		{
			$this->engine = self::KHTML;
		}
		// Lesser known engine but it finishes off the major list from Wikipedia :-)
		elseif (stripos($userAgent, 'Amaya') !== false)
		{
			$this->engine = self::AMAYA;
		}

		// Mark this detection routine as run.
		$this->detection['engine'] = true;
	}

	/**
	 * Method to detect the accepted languages by the client.
	 *
	 * @param   mixed  $acceptLanguage  The client accept language string to parse.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function detectLanguage($acceptLanguage)
	{
		// Parse the accepted encodings.
		$this->languages = array_map('trim', (array) explode(',', $acceptLanguage));

		// Mark this detection routine as run.
		$this->detection['acceptLanguage'] = true;
	}

	/**
	 * Detects the client platform in a user agent string.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function detectPlatform($userAgent)
	{
		// Attempt to detect the client platform.
		if (stripos($userAgent, 'Windows') !== false)
		{
			$this->platform = self::WINDOWS;

			// Let's look at the specific mobile options in the windows space.
			if (stripos($userAgent, 'Windows Phone') !== false)
			{
				$this->mobile = true;
				$this->platform = self::WINDOWS_PHONE;
			}
			elseif (stripos($userAgent, 'Windows CE') !== false)
			{
				$this->mobile = true;
				$this->platform = self::WINDOWS_CE;
			}
		}
		// Interestingly 'iPhone' is present in all iOS devices so far including iPad and iPods.
		elseif (stripos($userAgent, 'iPhone') !== false)
		{
			$this->mobile = true;
			$this->platform = self::IPHONE;

			// Let's look at the specific mobile options in the windows space.
			if (stripos($userAgent, 'iPad') !== false)
			{
				$this->platform = self::IPAD;
			}
			elseif (stripos($userAgent, 'iPod') !== false)
			{
				$this->platform = self::IPOD;
			}
		}
		// This has to come after the iPhone check because mac strings are also present in iOS devices.
		elseif (preg_match('/macintosh|mac os x/i', $userAgent))
		{
			$this->platform = self::MAC;
		}
		elseif (stripos($userAgent, 'Blackberry') !== false)
		{
			$this->mobile = true;
			$this->platform = self::BLACKBERRY;
		}
		elseif (stripos($userAgent, 'Android') !== false)
		{
			$this->mobile = true;
			$this->platform = self::ANDROID;
		}
		elseif (stripos($userAgent, 'Linux') !== false)
		{
			$this->platform = self::LINUX;
		}

		// Mark this detection routine as run.
		$this->detection['platform'] = true;
	}
}

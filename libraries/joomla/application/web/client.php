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
 * @property-read  integer  $platform        The detected platform on which the web client runs.
 * @property-read  boolean  $mobile          True if the web client is a mobile device.
 * @property-read  integer  $engine          The detected rendering engine used by the web client.
 * @property-read  integer  $browser         The detected browser used by the web client.
 * @property-read  string   $browserVersion  The detected browser version used by the web client.
 * @property-read  array    $languages       The priority order detected accepted languages for the client.
 * @property-read  array    $encodings       The priority order detected accepted encodings for the client.
 * @property-read  string   $userAgent       The web client's user agent string.
 * @property-read  string   $acceptEncoding  The web client's accepted encoding string.
 * @property-read  string   $acceptLanguage  The web client's accepted languages string.
 * @property-read  array    $detection       An array of flags determining whether or not a detection routine has been run.
 * @property-read  boolean  $robot           True if the web client is a robot
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       12.1
 */
class JApplicationWebClient
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
	const ANDROIDTABLET = 22;

	/**
	 * @var    integer  The detected platform on which the web client runs.
	 * @since  12.1
	 */
	protected $platform;

	/**
	 * @var    boolean  True if the web client is a mobile device.
	 * @since  12.1
	 */
	protected $mobile = false;

	/**
	 * @var    integer  The detected rendering engine used by the web client.
	 * @since  12.1
	 */
	protected $engine;

	/**
	 * @var    integer  The detected browser used by the web client.
	 * @since  12.1
	 */
	protected $browser;

	/**
	 * @var    string  The detected browser version used by the web client.
	 * @since  12.1
	 */
	protected $browserVersion;

	/**
	 * @var    array  The priority order detected accepted languages for the client.
	 * @since  12.1
	 */
	protected $languages = array();

	/**
	 * @var    array  The priority order detected accepted encodings for the client.
	 * @since  12.1
	 */
	protected $encodings = array();

	/**
	 * @var    string  The web client's user agent string.
	 * @since  12.1
	 */
	protected $userAgent;

	/**
	 * @var    string  The web client's accepted encoding string.
	 * @since  12.1
	 */
	protected $acceptEncoding;

	/**
	 * @var    string  The web client's accepted languages string.
	 * @since  12.1
	 */
	protected $acceptLanguage;

	/**
	 * @var    boolean  True if the web client is a robot.
	 * @since  12.3
	 */
	protected $robot = false;

	/**
	 * @var    array  An array of flags determining whether or not a detection routine has been run.
	 * @since  12.1
	 */
	protected $detection = array();

	/**
	 * Class constructor.
	 *
	 * @param   string  $userAgent       The optional user-agent string to parse.
	 * @param   string  $acceptEncoding  The optional client accept encoding string to parse.
	 * @param   string  $acceptLanguage  The optional client accept language string to parse.
	 *
	 * @since   12.1
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
	 * @since   12.1
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

			case 'robot':
				if (empty($this->detection['robot']))
				{
					$this->detectRobot($this->userAgent);
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
	 * @since   12.1
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
				if (count($matches['browser']) == 2)
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
				elseif (count($matches['browser']) > 2)
				{
					$key = array_search('Version', $matches['browser']);

					if ($key)
					{
						$this->browserVersion = $matches['version'][$key];
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
	 * @since   12.1
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
	 * @since   12.1
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
	 * @since   12.1
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
	 * @since   12.1
	 */
	protected function detectPlatform($userAgent)
	{
		// Attempt to detect the client platform.
		if (stripos($userAgent, 'Windows') !== false)
		{
			$this->platform = self::WINDOWS;

			// Let's look at the specific mobile options in the Windows space.
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

			// Let's look at the specific mobile options in the iOS space.
			if (stripos($userAgent, 'iPad') !== false)
			{
				$this->platform = self::IPAD;
			}
			elseif (stripos($userAgent, 'iPod') !== false)
			{
				$this->platform = self::IPOD;
			}
		}
			// In case where iPhone is not mentioed in iPad user agent string
			elseif (stripos($userAgent, 'iPad') !== false)
			{
				$this->mobile = true;
				$this->platform = self::IPAD;
			}
			// In case where iPhone is not mentioed in iPod user agent string
			elseif (stripos($userAgent, 'iPod') !== false)
			{
				$this->mobile = true;
				$this->platform = self::IPOD;
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
			/**
			 * Attempt to distinguish between Android phones and tablets
			 * There is no totally foolproof method but certain rules almost always hold
			 *   Android 3.x is only used for tablets
			 *   Some devices and browsers encourage users to change their UA string to include Tablet.
			 *   Google encourages manufacturers to exclude the string Mobile from tablet device UA strings.
			 *   In some modes Kindle Android devices include the string Mobile but they include the string Silk.
			 */
			if (stripos($userAgent, 'Android 3') !== false || stripos($userAgent, 'Tablet') !== false
				|| stripos($userAgent, 'Mobile') === false || stripos($userAgent, 'Silk') !== false )
			{
				$this->platform = self::ANDROIDTABLET;
			}
		}
		elseif (stripos($userAgent, 'Linux') !== false)
		{
			$this->platform = self::LINUX;
		}

		// Mark this detection routine as run.
		$this->detection['platform'] = true;
	}

	/**
	 * Determines if the browser is a robot or not.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function detectRobot($userAgent)
	{
		if (preg_match('/http|bot|robot|spider|crawler|curl|^$/i', $userAgent))
		{
			$this->robot = true;
		}
		else
		{
			$this->robot = false;
		}

		$this->detection['robot'] = true;
	}
}

<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Environment
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Browser class, provides capability information about the current web client.
 *
 * Browser identification is performed by examining the HTTP_USER_AGENT
 * environment variable provided by the web server.
 *
 * This class has many influences from the lib/Browser.php code in
 * version 3 of Horde by Chuck Hagenbuch and Jon Parise.
 *
 * @package     Joomla.Platform
 * @subpackage  Environment
 * @since       11.1
 * @deprecated  This API may be changed in the near future and should not be considered stable
 */
class JBrowser
{

	/**
	 * @var    integer  Major version number
	 * @since  11.1
	 */
	protected $_majorVersion = 0;

	/**
	 * @var    integer  Minor version number
	 * @since  11.1
	 */
	protected $_minorVersion = 0;

	/**
	 * @var    string  Browser name.
	 * @since  11.1
	 */
	protected $_browser = '';

	/**
	 * @var    string  Full user agent string.
	 * @since  11.1
	 */
	protected $_agent = '';

	/**
	 * @var    string  Lower-case user agent string
	 * @since  11.1
	 */
	protected $_lowerAgent = '';

	/**
	 * @var    string  HTTP_ACCEPT string.
	 * @since  11.1
	 */
	protected $_accept = '';

	/**
	 * @var    array  Parsed HTTP_ACCEPT string
	 * @since  11.1
	 */
	protected $_accept_parsed = array();

	/**
	 * @var    string  Platform the browser is running on
	 * @since  11.1
	 */
	protected $_platform = '';

	/**
	 * @var    array  Known robots.
	 * @since  11.1
	 */
	protected $_robots = array(
		/* The most common ones. */
		'Googlebot',
		'msnbot',
		'Slurp',
		'Yahoo',
		/* The rest alphabetically. */
		'Arachnoidea',
		'ArchitextSpider',
		'Ask Jeeves',
		'B-l-i-t-z-Bot',
		'Baiduspider',
		'BecomeBot',
		'cfetch',
		'ConveraCrawler',
		'ExtractorPro',
		'FAST-WebCrawler',
		'FDSE robot',
		'fido',
		'geckobot',
		'Gigabot',
		'Girafabot',
		'grub-client',
		'Gulliver',
		'HTTrack',
		'ia_archiver',
		'InfoSeek',
		'kinjabot',
		'KIT-Fireball',
		'larbin',
		'LEIA',
		'lmspider',
		'Lycos_Spider',
		'Mediapartners-Google',
		'MuscatFerret',
		'NaverBot',
		'OmniExplorer_Bot',
		'polybot',
		'Pompos',
		'Scooter',
		'Teoma',
		'TheSuBot',
		'TurnitinBot',
		'Ultraseek',
		'ViolaBot',
		'webbandit',
		'www.almaden.ibm.com/cs/crawler',
		'ZyBorg');

	/**
	 * @var    boolean  Is this a mobile browser?
	 * @since  11.1
	 */
	protected $_mobile = false;

	/**
	 * List of viewable image MIME subtypes.
	 * This list of viewable images works for IE and Netscape/Mozilla.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_images = array('jpeg', 'gif', 'png', 'pjpeg', 'x-png', 'bmp');

	/**
	 * @var    array  JBrowser instances container.
	 * @since  11.3
	 */
	protected static $instances = array();

	/**
	 * Create a browser instance (constructor).
	 *
	 * @param   string  $userAgent  The browser string to parse.
	 * @param   string  $accept     The HTTP_ACCEPT settings to use.
	 *
	 * @since   11.1
	 */
	public function __construct($userAgent = null, $accept = null)
	{
		$this->match($userAgent, $accept);
	}

	/**
	 * Returns the global Browser object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $userAgent  The browser string to parse.
	 * @param   string  $accept     The HTTP_ACCEPT settings to use.
	 *
	 * @return JBrowser  The Browser object.
	 *
	 * @since  11.1
	 */
	static public function getInstance($userAgent = null, $accept = null)
	{
		$signature = serialize(array($userAgent, $accept));

		if (empty(self::$instances[$signature]))
		{
			self::$instances[$signature] = new JBrowser($userAgent, $accept);
		}

		return self::$instances[$signature];
	}

	/**
	 * Parses the user agent string and inititializes the object with
	 * all the known features and quirks for the given browser.
	 *
	 * @param   string  $userAgent  The browser string to parse.
	 * @param   string  $accept     The HTTP_ACCEPT settings to use.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function match($userAgent = null, $accept = null)
	{
		// Set our agent string.
		if (is_null($userAgent))
		{
			if (isset($_SERVER['HTTP_USER_AGENT']))
			{
				$this->_agent = trim($_SERVER['HTTP_USER_AGENT']);
			}
		}
		else
		{
			$this->_agent = $userAgent;
		}
		$this->_lowerAgent = strtolower($this->_agent);

		// Set our accept string.
		if (is_null($accept))
		{
			if (isset($_SERVER['HTTP_ACCEPT']))
			{
				$this->_accept = strtolower(trim($_SERVER['HTTP_ACCEPT']));
			}
		}
		else
		{
			$this->_accept = strtolower($accept);
		}

		if (!empty($this->_agent))
		{
			$this->_setPlatform();

			if (strpos($this->_lowerAgent, 'mobileexplorer') !== false
				|| strpos($this->_lowerAgent, 'openwave') !== false
				|| strpos($this->_lowerAgent, 'opera mini') !== false
				|| strpos($this->_lowerAgent, 'opera mobi') !== false
				|| strpos($this->_lowerAgent, 'operamini') !== false)
			{
				$this->_mobile = true;
			}
			elseif (preg_match('|Opera[/ ]([0-9.]+)|', $this->_agent, $version))
			{
				$this->setBrowser('opera');
				list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);

				/* Due to changes in Opera UA, we need to check Version/xx.yy,
				 * but only if version is > 9.80. See: http://dev.opera.com/articles/view/opera-ua-string-changes/ */
				if ($this->_majorVersion == 9 && $this->_minorVersion >= 80)
				{
					$this->identifyBrowserVersion();
				}
			}
			elseif (preg_match('|Chrome[/ ]([0-9.]+)|', $this->_agent, $version))
			{
				$this->setBrowser('chrome');
				list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
			}
			elseif (preg_match('|CrMo[/ ]([0-9.]+)|', $this->_agent, $version))
			{
				$this->setBrowser('chrome');
				list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
			}
			elseif (strpos($this->_lowerAgent, 'elaine/') !== false
				|| strpos($this->_lowerAgent, 'palmsource') !== false
				|| strpos($this->_lowerAgent, 'digital paths') !== false)
			{
				$this->setBrowser('palm');
				$this->_mobile = true;
			}
			elseif ((preg_match('|MSIE ([0-9.]+)|', $this->_agent, $version)) || (preg_match('|Internet Explorer/([0-9.]+)|', $this->_agent, $version)))
			{
				$this->setBrowser('msie');

				if (strpos($version[1], '.') !== false)
				{
					list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
				}
				else
				{
					$this->_majorVersion = $version[1];
					$this->_minorVersion = 0;
				}

				/* Some Handhelds have their screen resolution in the
				 * user agent string, which we can use to look for
				 * mobile agents.
				 */
				if (preg_match('/; (120x160|240x280|240x320|320x320)\)/', $this->_agent))
				{
					$this->_mobile = true;
				}
			}
			elseif (preg_match('|amaya/([0-9.]+)|', $this->_agent, $version))
			{
				$this->setBrowser('amaya');
				$this->_majorVersion = $version[1];
				if (isset($version[2]))
				{
					$this->_minorVersion = $version[2];
				}
			}
			elseif (preg_match('|ANTFresco/([0-9]+)|', $this->_agent, $version))
			{
				$this->setBrowser('fresco');
			}
			elseif (strpos($this->_lowerAgent, 'avantgo') !== false)
			{
				$this->setBrowser('avantgo');
				$this->_mobile = true;
			}
			elseif (preg_match('|Konqueror/([0-9]+)|', $this->_agent, $version) || preg_match('|Safari/([0-9]+)\.?([0-9]+)?|', $this->_agent, $version))
			{
				// Konqueror and Apple's Safari both use the KHTML
				// rendering engine.
				$this->setBrowser('konqueror');
				$this->_majorVersion = $version[1];
				if (isset($version[2]))
				{
					$this->_minorVersion = $version[2];
				}

				if (strpos($this->_agent, 'Safari') !== false && $this->_majorVersion >= 60)
				{
					// Safari.
					$this->setBrowser('safari');
					$this->identifyBrowserVersion();
				}
			}
			elseif (preg_match('|Mozilla/([0-9.]+)|', $this->_agent, $version))
			{
				$this->setBrowser('mozilla');

				list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
			}
			elseif (preg_match('|Lynx/([0-9]+)|', $this->_agent, $version))
			{
				$this->setBrowser('lynx');
			}
			elseif (preg_match('|Links \(([0-9]+)|', $this->_agent, $version))
			{
				$this->setBrowser('links');
			}
			elseif (preg_match('|HotJava/([0-9]+)|', $this->_agent, $version))
			{
				$this->setBrowser('hotjava');
			}
			elseif (strpos($this->_agent, 'UP/') !== false || strpos($this->_agent, 'UP.B') !== false || strpos($this->_agent, 'UP.L') !== false)
			{
				$this->setBrowser('up');
				$this->_mobile = true;
			}
			elseif (strpos($this->_agent, 'Xiino/') !== false)
			{
				$this->setBrowser('xiino');
				$this->_mobile = true;
			}
			elseif (strpos($this->_agent, 'Palmscape/') !== false)
			{
				$this->setBrowser('palmscape');
				$this->_mobile = true;
			}
			elseif (strpos($this->_agent, 'Nokia') !== false)
			{
				$this->setBrowser('nokia');
				$this->_mobile = true;
			}
			elseif (strpos($this->_agent, 'Ericsson') !== false)
			{
				$this->setBrowser('ericsson');
				$this->_mobile = true;
			}
			elseif (strpos($this->_lowerAgent, 'wap') !== false)
			{
				$this->setBrowser('wap');
				$this->_mobile = true;
			}
			elseif (strpos($this->_lowerAgent, 'docomo') !== false || strpos($this->_lowerAgent, 'portalmmm') !== false)
			{
				$this->setBrowser('imode');
				$this->_mobile = true;
			}
			elseif (strpos($this->_agent, 'BlackBerry') !== false)
			{
				$this->setBrowser('blackberry');
				$this->_mobile = true;
			}
			elseif (strpos($this->_agent, 'MOT-') !== false)
			{
				$this->setBrowser('motorola');
				$this->_mobile = true;
			}
			elseif (strpos($this->_lowerAgent, 'j-') !== false)
			{
				$this->setBrowser('mml');
				$this->_mobile = true;
			}
		}
	}

	/**
	 * Match the platform of the browser.
	 *
	 * This is a pretty simplistic implementation, but it's intended
	 * to let us tell what line breaks to send, so it's good enough
	 * for its purpose.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function _setPlatform()
	{
		if (strpos($this->_lowerAgent, 'wind') !== false)
		{
			$this->_platform = 'win';
		}
		elseif (strpos($this->_lowerAgent, 'mac') !== false)
		{
			$this->_platform = 'mac';
		}
		else
		{
			$this->_platform = 'unix';
		}
	}

	/**
	 * Return the currently matched platform.
	 *
	 * @return  string  The user's platform.
	 *
	 * @since   11.1
	 */
	public function getPlatform()
	{
		return $this->_platform;
	}

	/**
	 * Set browser version, not by engine version
	 * Fallback to use when no other method identify the engine version
	 *
	 * @return void
	 */
	protected function identifyBrowserVersion()
	{
		if (preg_match('|Version[/ ]([0-9.]+)|', $this->_agent, $version))
		{
			list ($this->_majorVersion, $this->_minorVersion) = explode('.', $version[1]);
			return;
		}
		// Can't identify browser version
		$this->_majorVersion = 0;
		$this->_minorVersion = 0;
		JLog::add("Can't identify browser version. Agent: " . $this->_agent, JLog::NOTICE);
	}

	/**
	 * Sets the current browser.
	 *
	 * @param   string  $browser  The browser to set as current.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function setBrowser($browser)
	{
		$this->_browser = $browser;
	}

	/**
	 * Retrieve the current browser.
	 *
	 * @return  string  The current browser.
	 *
	 * @since   11.1
	 */
	public function getBrowser()
	{
		return $this->_browser;
	}

	/**
	 * Retrieve the current browser's major version.
	 *
	 * @return  integer  The current browser's major version
	 *
	 * @since   11.1.
	 */
	public function getMajor()
	{
		return $this->_majorVersion;
	}

	/**
	 * Retrieve the current browser's minor version.
	 *
	 * @return  integer  The current browser's minor version.
	 *
	 * @since   11.1
	 */
	public function getMinor()
	{
		return $this->_minorVersion;
	}

	/**
	 * Retrieve the current browser's version.
	 *
	 * @return  string  The current browser's version.
	 *
	 * @since   11.1
	 */
	public function getVersion()
	{
		return $this->_majorVersion . '.' . $this->_minorVersion;
	}

	/**
	 * Return the full browser agent string.
	 *
	 * @return  string  The browser agent string
	 *
	 * @since   11.1
	 */
	public function getAgentString()
	{
		return $this->_agent;
	}

	/**
	 * Returns the server protocol in use on the current server.
	 *
	 * @return  string  The HTTP server protocol version.
	 *
	 * @since   11.1
	 */
	public function getHTTPProtocol()
	{
		if (isset($_SERVER['SERVER_PROTOCOL']))
		{
			if (($pos = strrpos($_SERVER['SERVER_PROTOCOL'], '/')))
			{
				return substr($_SERVER['SERVER_PROTOCOL'], $pos + 1);
			}
		}
		return null;
	}

	/**
	 * Determines if a browser can display a given MIME type.
	 *
	 * Note that  image/jpeg and image/pjpeg *appear* to be the same
	 * entity, but Mozilla doesn't seem to want to accept the latter.
	 * For our purposes, we will treat them the same.
	 *
	 * @param   string  $mimetype  The MIME type to check.
	 *
	 * @return  boolean  True if the browser can display the MIME type.
	 *
	 * @since   11.1
	 */
	public function isViewable($mimetype)
	{
		$mimetype = strtolower($mimetype);
		list ($type, $subtype) = explode('/', $mimetype);

		if (!empty($this->_accept))
		{
			$wildcard_match = false;

			if (strpos($this->_accept, $mimetype) !== false)
			{
				return true;
			}

			if (strpos($this->_accept, '*/*') !== false)
			{
				$wildcard_match = true;
				if ($type != 'image')
				{
					return true;
				}
			}

			// Deal with Mozilla pjpeg/jpeg issue
			if ($this->isBrowser('mozilla') && ($mimetype == 'image/pjpeg') && (strpos($this->_accept, 'image/jpeg') !== false))
			{
				return true;
			}

			if (!$wildcard_match)
			{
				return false;
			}
		}

		if (!$this->hasFeature('images') || ($type != 'image'))
		{
			return false;
		}

		return (in_array($subtype, $this->_images));
	}

	/**
	 * Determine if the given browser is the same as the current.
	 *
	 * @param   string  $browser  The browser to check.
	 *
	 * @return  boolean  Is the given browser the same as the current?
	 *
	 * @since   11.1
	 */
	public function isBrowser($browser)
	{
		return ($this->_browser === $browser);
	}

	/**
	 * Determines if the browser is a robot or not.
	 *
	 * @return  boolean  True if browser is a known robot.
	 *
	 * @since   11.1
	 */
	public function isRobot()
	{
		foreach ($this->_robots as $robot)
		{
			if (strpos($this->_agent, $robot) !== false)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Determines if the browser is mobile version or not.
	 *
	 * @return boolean  True if browser is a known mobile version.
	 *
	 * @since   11.1
	 */
	public function isMobile()
	{
		return $this->_mobile;
	}

	/**
	 * Determine if we are using a secure (SSL) connection.
	 *
	 * @return  boolean  True if using SSL, false if not.
	 *
	 * @since   11.1
	 */
	public function isSSLConnection()
	{
		return ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) || getenv('SSL_PROTOCOL_VERSION'));
	}
}

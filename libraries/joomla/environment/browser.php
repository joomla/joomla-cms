<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Environment
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
 */
class JBrowser
{
	/**
	 * @var    integer  Major version number
	 * @since  12.1
	 */
	protected $majorVersion = 0;

	/**
	 * @var    integer  Minor version number
	 * @since  12.1
	 */
	protected $minorVersion = 0;

	/**
	 * @var    string  Browser name.
	 * @since  12.1
	 */
	protected $browser = '';

	/**
	 * @var    string  Full user agent string.
	 * @since  12.1
	 */
	protected $agent = '';

	/**
	 * @var    string  Lower-case user agent string
	 * @since  12.1
	 */
	protected $lowerAgent = '';

	/**
	 * @var    string  HTTP_ACCEPT string.
	 * @since  12.1
	 */
	protected $accept = '';

	/**
	 * @var    array  Parsed HTTP_ACCEPT string
	 * @since  12.1
	 */
	protected $acceptParsed = array();

	/**
	 * @var    string  Platform the browser is running on
	 * @since  12.1
	 */
	protected $platform = '';

	/**
	 * @var    array  Known robots.
	 * @since  12.1
	 */
	protected $robots = array(
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
	 * @since  12.1
	 */
	protected $mobile = false;

	/**
	 * List of viewable image MIME subtypes.
	 * This list of viewable images works for IE and Netscape/Mozilla.
	 *
	 * @var    array
	 * @since  12.1
	 */
	protected $images = array('jpeg', 'gif', 'png', 'pjpeg', 'x-png', 'bmp');

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
				$this->agent = trim($_SERVER['HTTP_USER_AGENT']);
			}
		}
		else
		{
			$this->agent = $userAgent;
		}
		$this->lowerAgent = strtolower($this->agent);

		// Set our accept string.
		if (is_null($accept))
		{
			if (isset($_SERVER['HTTP_ACCEPT']))
			{
				$this->accept = strtolower(trim($_SERVER['HTTP_ACCEPT']));
			}
		}
		else
		{
			$this->accept = strtolower($accept);
		}

		if (!empty($this->agent))
		{
			$this->_setPlatform();

			if (strpos($this->lowerAgent, 'mobileexplorer') !== false
				|| strpos($this->lowerAgent, 'openwave') !== false
				|| strpos($this->lowerAgent, 'opera mini') !== false
				|| strpos($this->lowerAgent, 'opera mobi') !== false
				|| strpos($this->lowerAgent, 'operamini') !== false)
			{
				$this->mobile = true;
			}
			elseif (preg_match('|Opera[/ ]([0-9.]+)|', $this->agent, $version))
			{
				$this->setBrowser('opera');
				list ($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);

				/* Due to changes in Opera UA, we need to check Version/xx.yy,
				 * but only if version is > 9.80. See: http://dev.opera.com/articles/view/opera-ua-string-changes/ */
				if ($this->majorVersion == 9 && $this->minorVersion >= 80)
				{
					$this->identifyBrowserVersion();
				}
			}
			elseif (preg_match('|Chrome[/ ]([0-9.]+)|', $this->agent, $version))
			{
				$this->setBrowser('chrome');
				list ($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
			}
			elseif (preg_match('|CrMo[/ ]([0-9.]+)|', $this->agent, $version))
			{
				$this->setBrowser('chrome');
				list ($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
			}
			elseif (preg_match('|CriOS[/ ]([0-9.]+)|', $this->agent, $version))
			{
				$this->setBrowser('chrome');
				list ($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
				$this->mobile = true;
			}
			elseif (strpos($this->lowerAgent, 'elaine/') !== false
				|| strpos($this->lowerAgent, 'palmsource') !== false
				|| strpos($this->lowerAgent, 'digital paths') !== false)
			{
				$this->setBrowser('palm');
				$this->mobile = true;
			}
			elseif ((preg_match('|MSIE ([0-9.]+)|', $this->agent, $version)) || (preg_match('|Internet Explorer/([0-9.]+)|', $this->agent, $version)))
			{
				$this->setBrowser('msie');

				if (strpos($version[1], '.') !== false)
				{
					list ($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
				}
				else
				{
					$this->majorVersion = $version[1];
					$this->minorVersion = 0;
				}

				/* Some Handhelds have their screen resolution in the
				 * user agent string, which we can use to look for
				 * mobile agents.
				 */
				if (preg_match('/; (120x160|240x280|240x320|320x320)\)/', $this->agent))
				{
					$this->mobile = true;
				}
			}
			elseif (preg_match('|amaya/([0-9.]+)|', $this->agent, $version))
			{
				$this->setBrowser('amaya');
				$this->majorVersion = $version[1];
				if (isset($version[2]))
				{
					$this->minorVersion = $version[2];
				}
			}
			elseif (preg_match('|ANTFresco/([0-9]+)|', $this->agent, $version))
			{
				$this->setBrowser('fresco');
			}
			elseif (strpos($this->lowerAgent, 'avantgo') !== false)
			{
				$this->setBrowser('avantgo');
				$this->mobile = true;
			}
			elseif (preg_match('|Konqueror/([0-9]+)|', $this->agent, $version) || preg_match('|Safari/([0-9]+)\.?([0-9]+)?|', $this->agent, $version))
			{
				// Konqueror and Apple's Safari both use the KHTML
				// rendering engine.
				$this->setBrowser('konqueror');
				$this->majorVersion = $version[1];
				if (isset($version[2]))
				{
					$this->minorVersion = $version[2];
				}

				if (strpos($this->agent, 'Safari') !== false && $this->majorVersion >= 60)
				{
					// Safari.
					$this->setBrowser('safari');
					$this->identifyBrowserVersion();
				}
			}
			elseif (preg_match('|Mozilla/([0-9.]+)|', $this->agent, $version))
			{
				$this->setBrowser('mozilla');

				list ($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
			}
			elseif (preg_match('|Lynx/([0-9]+)|', $this->agent, $version))
			{
				$this->setBrowser('lynx');
			}
			elseif (preg_match('|Links \(([0-9]+)|', $this->agent, $version))
			{
				$this->setBrowser('links');
			}
			elseif (preg_match('|HotJava/([0-9]+)|', $this->agent, $version))
			{
				$this->setBrowser('hotjava');
			}
			elseif (strpos($this->agent, 'UP/') !== false || strpos($this->agent, 'UP.B') !== false || strpos($this->agent, 'UP.L') !== false)
			{
				$this->setBrowser('up');
				$this->mobile = true;
			}
			elseif (strpos($this->agent, 'Xiino/') !== false)
			{
				$this->setBrowser('xiino');
				$this->mobile = true;
			}
			elseif (strpos($this->agent, 'Palmscape/') !== false)
			{
				$this->setBrowser('palmscape');
				$this->mobile = true;
			}
			elseif (strpos($this->agent, 'Nokia') !== false)
			{
				$this->setBrowser('nokia');
				$this->mobile = true;
			}
			elseif (strpos($this->agent, 'Ericsson') !== false)
			{
				$this->setBrowser('ericsson');
				$this->mobile = true;
			}
			elseif (strpos($this->lowerAgent, 'wap') !== false)
			{
				$this->setBrowser('wap');
				$this->mobile = true;
			}
			elseif (strpos($this->lowerAgent, 'docomo') !== false || strpos($this->lowerAgent, 'portalmmm') !== false)
			{
				$this->setBrowser('imode');
				$this->mobile = true;
			}
			elseif (strpos($this->agent, 'BlackBerry') !== false)
			{
				$this->setBrowser('blackberry');
				$this->mobile = true;
			}
			elseif (strpos($this->agent, 'MOT-') !== false)
			{
				$this->setBrowser('motorola');
				$this->mobile = true;
			}
			elseif (strpos($this->lowerAgent, 'j-') !== false)
			{
				$this->setBrowser('mml');
				$this->mobile = true;
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
		if (strpos($this->lowerAgent, 'wind') !== false)
		{
			$this->platform = 'win';
		}
		elseif (strpos($this->lowerAgent, 'mac') !== false)
		{
			$this->platform = 'mac';
		}
		else
		{
			$this->platform = 'unix';
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
		return $this->platform;
	}

	/**
	 * Set browser version, not by engine version
	 * Fallback to use when no other method identify the engine version
	 *
	 * @return void
	 */
	protected function identifyBrowserVersion()
	{
		if (preg_match('|Version[/ ]([0-9.]+)|', $this->agent, $version))
		{
			list ($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
			return;
		}
		// Can't identify browser version
		$this->majorVersion = 0;
		$this->minorVersion = 0;
		JLog::add("Can't identify browser version. Agent: " . $this->agent, JLog::NOTICE);
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
		$this->browser = $browser;
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
		return $this->browser;
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
		return $this->majorVersion;
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
		return $this->minorVersion;
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
		return $this->majorVersion . '.' . $this->minorVersion;
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
		return $this->agent;
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

		if (!empty($this->accept))
		{
			$wildcard_match = false;

			if (strpos($this->accept, $mimetype) !== false)
			{
				return true;
			}

			if (strpos($this->accept, '*/*') !== false)
			{
				$wildcard_match = true;
				if ($type != 'image')
				{
					return true;
				}
			}

			// Deal with Mozilla pjpeg/jpeg issue
			if ($this->isBrowser('mozilla') && ($mimetype == 'image/pjpeg') && (strpos($this->accept, 'image/jpeg') !== false))
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

		return (in_array($subtype, $this->images));
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
		return ($this->browser === $browser);
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
		foreach ($this->robots as $robot)
		{
			if (strpos($this->agent, $robot) !== false)
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
		return $this->mobile;
	}

	/**
	 * Determine if we are using a secure (SSL) connection.
	 *
	 * @return  boolean  True if using SSL, false if not.
	 *
	 * @since   11.1
	 * @deprecated  13.3 (Platform) & 4.0 (CMS) - Use the isSSLConnection method on the application object.
	 */
	public function isSSLConnection()
	{
		JLog::add('JBrowser::isSSLConnection() is deprecated. Use the isSSLConnection method on the application object instead.',
			JLog::WARNING, 'deprecated');

		return ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) || getenv('SSL_PROTOCOL_VERSION'));
	}
}

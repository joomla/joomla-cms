<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Environment;

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
 * @since  1.7.0
 */
class Browser
{
	/**
	 * @var    integer  Major version number
	 * @since  3.0.0
	 */
	protected $majorVersion = 0;

	/**
	 * @var    integer  Minor version number
	 * @since  3.0.0
	 */
	protected $minorVersion = 0;

	/**
	 * @var    string  Browser name.
	 * @since  3.0.0
	 */
	protected $browser = '';

	/**
	 * @var    string  Full user agent string.
	 * @since  3.0.0
	 */
	protected $agent = '';

	/**
	 * @var    string  Lower-case user agent string
	 * @since  3.0.0
	 */
	protected $lowerAgent = '';

	/**
	 * @var    string  HTTP_ACCEPT string.
	 * @since  3.0.0
	 */
	protected $accept = '';

	/**
	 * @var    array  Parsed HTTP_ACCEPT string
	 * @since  3.0.0
	 */
	protected $acceptParsed = array();

	/**
	 * @var    string  Platform the browser is running on
	 * @since  3.0.0
	 */
	protected $platform = '';

	/**
	 * @var    array  Known robots.
	 * @since  3.0.0
	 */
	protected $robots = array(
		'Googlebot\/',
		'Googlebot-Mobile',
		'Googlebot-Image',
		'Googlebot-News',
		'Googlebot-Video',
		'AdsBot-Google([^-]|$)',
		'AdsBot-Google-Mobile',
		'Feedfetcher-Google',
		'Mediapartners-Google',
		'Mediapartners \(Googlebot\)',
		'APIs-Google',
		'bingbot',
		'Slurp',
		'[wW]get',
		'curl',
		'LinkedInBot',
		'Python-urllib',
		'python-requests',
		'libwww',
		'httpunit',
		'nutch',
		'Go-http-client',
		'phpcrawl',
		'msnbot',
		'jyxobot',
		'FAST-WebCrawler',
		'FAST Enterprise Crawler',
		'BIGLOTRON',
		'Teoma',
		'convera',
		'seekbot',
		'Gigabot',
		'Gigablast',
		'exabot',
		'ia_archiver',
		'GingerCrawler',
		'webmon ',
		'HTTrack',
		'grub.org',
		'UsineNouvelleCrawler',
		'antibot',
		'netresearchserver',
		'speedy',
		'fluffy',
		'bibnum.bnf',
		'findlink',
		'msrbot',
		'panscient',
		'yacybot',
		'AISearchBot',
		'ips-agent',
		'tagoobot',
		'MJ12bot',
		'woriobot',
		'yanga',
		'buzzbot',
		'mlbot',
		'YandexBot',
		'yandex.com\/bots',
		'purebot',
		'Linguee Bot',
		'CyberPatrol',
		'voilabot',
		'Baiduspider',
		'citeseerxbot',
		'spbot',
		'twengabot',
		'postrank',
		'turnitinbot',
		'scribdbot',
		'page2rss',
		'sitebot',
		'linkdex',
		'Adidxbot',
		'blekkobot',
		'ezooms',
		'dotbot',
		'Mail.RU_Bot',
		'discobot',
		'heritrix',
		'findthatfile',
		'europarchive.org',
		'NerdByNature.Bot',
		'sistrix crawler',
		'Ahrefs(Bot|SiteAudit)',
		'fuelbot',
		'CrunchBot',
		'centurybot9',
		'IndeedBot',
		'mappydata',
		'woobot',
		'ZoominfoBot',
		'PrivacyAwareBot',
		'Multiviewbot',
		'SWIMGBot',
		'Grobbot',
		'eright',
		'Apercite',
		'semanticbot',
		'Aboundex',
		'domaincrawler',
		'wbsearchbot',
		'summify',
		'CCBot',
		'edisterbot',
		'seznambot',
		'ec2linkfinder',
		'gslfbot',
		'aiHitBot',
		'intelium_bot',
		'facebookexternalhit',
		'Yeti',
		'RetrevoPageAnalyzer',
		'lb-spider',
		'Sogou',
		'lssbot',
		'careerbot',
		'wotbox',
		'wocbot',
		'ichiro',
		'DuckDuckBot',
		'lssrocketcrawler',
		'drupact',
		'webcompanycrawler',
		'acoonbot',
		'openindexspider',
		'gnam gnam spider',
		'web-archive-net.com.bot',
		'backlinkcrawler',
		'coccoc',
		'integromedb',
		'content crawler spider',
		'toplistbot',
		'it2media-domain-crawler',
		'ip-web-crawler.com',
		'siteexplorer.info',
		'elisabot',
		'proximic',
		'changedetection',
		'arabot',
		'WeSEE:Search',
		'niki-bot',
		'CrystalSemanticsBot',
		'rogerbot',
		'360Spider',
		'psbot',
		'InterfaxScanBot',
		'CC Metadata Scaper',
		'g00g1e.net',
		'GrapeshotCrawler',
		'urlappendbot',
		'brainobot',
		'fr-crawler',
		'binlar',
		'SimpleCrawler',
		'Twitterbot',
		'cXensebot',
		'smtbot',
		'bnf.fr_bot',
		'A6-Indexer',
		'ADmantX',
		'Facebot',
		'OrangeBot\/',
		'memorybot',
		'AdvBot',
		'MegaIndex',
		'SemanticScholarBot',
		'ltx71',
		'nerdybot',
		'xovibot',
		'BUbiNG',
		'Qwantify',
		'archive.org_bot',
		'Applebot',
		'TweetmemeBot',
		'crawler4j',
		'findxbot',
		'S[eE][mM]rushBot',
		'yoozBot',
		'lipperhey',
		'Y!J',
		'Domain Re-Animator Bot',
		'AddThis',
		'Screaming Frog SEO Spider',
		'MetaURI',
		'Scrapy',
		'Livelap[bB]ot',
		'OpenHoseBot',
		'CapsuleChecker',
		'collection@infegy.com',
		'IstellaBot',
		'DeuSu\/',
		'betaBot',
		'Cliqzbot\/',
		'MojeekBot\/',
		'netEstate NE Crawler',
		'SafeSearch microdata crawler',
		'Gluten Free Crawler\/',
		'Sonic',
		'Sysomos',
		'Trove',
		'deadlinkchecker',
		'Slack-ImgProxy',
		'Embedly',
		'RankActiveLinkBot',
		'iskanie',
		'SafeDNSBot',
		'SkypeUriPreview',
		'Veoozbot',
		'Slackbot',
		'redditbot',
		'datagnionbot',
		'Google-Adwords-Instant',
		'adbeat_bot',
		'WhatsApp',
		'contxbot',
		'pinterest',
		'electricmonk',
		'GarlikCrawler',
		'BingPreview\/',
		'vebidoobot',
		'FemtosearchBot',
		'Yahoo Link Preview',
		'MetaJobBot',
		'DomainStatsBot',
		'mindUpBot',
		'Daum\/',
		'Jugendschutzprogramm-Crawler',
		'Xenu Link Sleuth',
		'Pcore-HTTP',
		'moatbot',
		'KosmioBot',
		'pingdom',
		'PhantomJS',
		'Gowikibot',
		'PiplBot',
		'Discordbot',
		'TelegramBot',
		'Jetslide',
		'newsharecounts',
		'James BOT',
		'Barkrowler',
		'TinEye',
		'SocialRankIOBot',
		'trendictionbot',
		'Ocarinabot',
		'epicbot',
		'Primalbot',
		'DuckDuckGo-Favicons-Bot',
		'GnowitNewsbot',
		'Leikibot',
		'LinkArchiver',
		'YaK\/',
		'PaperLiBot',
		'Digg Deeper',
		'dcrawl',
		'Snacktory',
		'AndersPinkBot',
		'Fyrebot',
		'EveryoneSocialBot',
		'Mediatoolkitbot',
		'Luminator-robots',
		'ExtLinksBot',
		'SurveyBot',
		'NING\/',
		'okhttp',
		'Nuzzel',
		'omgili',
		'PocketParser',
		'YisouSpider',
		'um-LN',
		'ToutiaoSpider',
		'MuckRack',
		'Jamie\'s Spider',
		'AHC\/',
		'NetcraftSurveyAgent',
		'Laserlikebot',
		'Apache-HttpClient',
		'AppEngine-Google',
		'Jetty',
		'Upflow',
		'Thinklab',
		'Traackr.com',
		'Twurly',
		'Mastodon',
		'http_get',
		'DnyzBot',
		'botify',
		'007ac9 Crawler',
		'BehloolBot',
		'BrandVerity',
		'check_http',
		'BDCbot',
		'ZumBot',
		'EZID',
		'ICC-Crawler',
		'ArchiveBot',
		'^LCC ',
		'filterdb.iss.net\/crawler',
		'BLP_bbot',
		'BomboraBot',
		'Buck\/',
		'Companybook-Crawler',
		'Genieo',
		'magpie-crawler',
		'MeltwaterNews',
		'Moreover',
		'newspaper\/',
		'ScoutJet',
		'(^| )sentry\/',
		'StorygizeBot',
		'UptimeRobot',
		'OutclicksBot',
		'seoscanners',
		'Hatena',
		'Google Web Preview',
		'MauiBot',
		'AlphaBot',
		'SBL-BOT',
		'IAS crawler',
		'adscanner',
		'Netvibes',
		'acapbot',
		'Baidu-YunGuanCe',
		'bitlybot',
		'blogmuraBot',
		'Bot.AraTurka.com',
		'bot-pge.chlooe.com',
		'BoxcarBot',
		'BTWebClient',
		'ContextAd Bot',
		'Digincore bot',
		'Disqus',
		'Feedly',
		'Fetch\/',
		'Fever',
		'Flamingo_SearchEngine',
		'FlipboardProxy',
		'g2reader-bot',
		'imrbot',
		'K7MLWCBot',
		'Kemvibot',
		'Landau-Media-Spider',
		'linkapediabot',
		'vkShare',
		'Siteimprove.com',
		'BLEXBot\/',
		'DareBoost',
		'ZuperlistBot\/',
		'Miniflux\/',
		'Feedspotbot\/',
		'Diffbot\/',
		'SEOkicks',
		'tracemyfile',
		'Nimbostratus-Bot',
		'zgrab',
		'PR-CY.RU',
		'AdsTxtCrawler',
		'Datafeedwatch',
		'Zabbix',
		'TangibleeBot',
		'google-xrawler',
		'axios',
		'Amazon CloudFront',
		'Pulsepoint',
	);

	/**
	 * @var    boolean  Is this a mobile browser?
	 * @since  3.0.0
	 */
	protected $mobile = false;

	/**
	 * List of viewable image MIME subtypes.
	 * This list of viewable images works for IE and Netscape/Mozilla.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected $images = array('jpeg', 'gif', 'png', 'pjpeg', 'x-png', 'bmp');

	/**
	 * @var    array  Browser instances container.
	 * @since  1.7.3
	 */
	protected static $instances = array();

	/**
	 * Create a browser instance (constructor).
	 *
	 * @param   string  $userAgent  The browser string to parse.
	 * @param   string  $accept     The HTTP_ACCEPT settings to use.
	 *
	 * @since   1.7.0
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
	 * @return  Browser  The Browser object.
	 *
	 * @since   1.7.0
	 */
	public static function getInstance($userAgent = null, $accept = null)
	{
		$signature = serialize(array($userAgent, $accept));

		if (empty(self::$instances[$signature]))
		{
			self::$instances[$signature] = new Browser($userAgent, $accept);
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
	 * @since   1.7.0
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

			/*
			 * Determine if mobile. Note: Some Handhelds have their screen resolution in the
			 * user agent string, which we can use to look for mobile agents.
			 */
			if (strpos($this->agent, 'MOT-') !== false
				|| strpos($this->lowerAgent, 'j-') !== false
				|| preg_match('/(mobileexplorer|openwave|opera mini|opera mobi|operamini|avantgo|wap|elaine)/i', $this->agent)
				|| preg_match('/(iPhone|iPod|iPad|Android|Mobile|Phone|BlackBerry|Xiino|Palmscape|palmsource)/i', $this->agent)
				|| preg_match('/(Nokia|Ericsson|docomo|digital paths|portalmmm|CriOS[\/ ]([0-9.]+))/i', $this->agent)
				|| preg_match('/(UP|UP.B|UP.L)/', $this->agent)
				|| preg_match('/; (120x160|240x280|240x320|320x320)\)/', $this->agent))
			{
				$this->mobile = true;
			}

			/*
			 * We have to check for Edge as the first browser, because Edge has something like:
			 * Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393
			 */
			if (preg_match('|Edge\/([0-9.]+)|', $this->agent, $version))
			{
				$this->setBrowser('edge');

				if (strpos($version[1], '.') !== false)
				{
					list($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
				}
				else
				{
					$this->majorVersion = $version[1];
					$this->minorVersion = 0;
				}
			}
			/*
			 * We have to check for Edge as the first browser, because Edge has something like:
			 * Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3738.0 Safari/537.36 Edg/75.0.107.0
			 */
			elseif (preg_match('|Edg\/([0-9.]+)|', $this->agent, $version))
			{
				$this->setBrowser('edg');

				list($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
			}
			elseif (preg_match('|Opera[\/ ]([0-9.]+)|', $this->agent, $version))
			{
				$this->setBrowser('opera');

				list($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);

				/*
				 * Due to changes in Opera UA, we need to check Version/xx.yy,
				 * but only if version is > 9.80. See: http://dev.opera.com/articles/view/opera-ua-string-changes/
				 */
				if ($this->majorVersion == 9 && $this->minorVersion >= 80)
				{
					$this->identifyBrowserVersion();
				}
			}

			// Opera 15+
			elseif (preg_match('/OPR[\/ ]([0-9.]+)/', $this->agent, $version))
			{
				$this->setBrowser('opera');

				list($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
			}
			elseif (preg_match('/Chrome[\/ ]([0-9.]+)/i', $this->agent, $version)
				|| preg_match('/CrMo[\/ ]([0-9.]+)/i', $this->agent, $version)
				|| preg_match('/CriOS[\/ ]([0-9.]+)/i', $this->agent, $version))
			{
				$this->setBrowser('chrome');

				list($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
			}
			elseif (strpos($this->lowerAgent, 'elaine/') !== false
				|| strpos($this->lowerAgent, 'palmsource') !== false
				|| strpos($this->lowerAgent, 'digital paths') !== false)
			{
				$this->setBrowser('palm');
			}
			elseif (preg_match('/MSIE ([0-9.]+)/i', $this->agent, $version)
				|| preg_match('/IE ([0-9.]+)/i', $this->agent, $version)
				|| preg_match('/Internet Explorer[\/ ]([0-9.]+)/i', $this->agent, $version)
				|| preg_match('/Trident\/.*rv:([0-9.]+)/i', $this->agent, $version))
			{
				$this->setBrowser('msie');

				// Special case for IE 11+
				if (strpos($version[0], 'Trident') !== false && strpos($version[0], 'rv:') !== false)
				{
					preg_match('|rv:([0-9.]+)|', $this->agent, $version);
				}

				if (strpos($version[1], '.') !== false)
				{
					list($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
				}
				else
				{
					$this->majorVersion = $version[1];
					$this->minorVersion = 0;
				}
			}
			elseif (preg_match('|amaya\/([0-9.]+)|', $this->agent, $version))
			{
				$this->setBrowser('amaya');
				$this->majorVersion = $version[1];

				if (isset($version[2]))
				{
					$this->minorVersion = $version[2];
				}
			}
			elseif (preg_match('|ANTFresco\/([0-9]+)|', $this->agent, $version))
			{
				$this->setBrowser('fresco');
			}
			elseif (strpos($this->lowerAgent, 'avantgo') !== false)
			{
				$this->setBrowser('avantgo');
			}
			elseif (preg_match('|[Kk]onqueror\/([0-9]+)|', $this->agent, $version) || preg_match('|Safari/([0-9]+)\.?([0-9]+)?|', $this->agent, $version))
			{
				// Konqueror and Apple's Safari both use the KHTML rendering engine.
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
			elseif (preg_match('|Firefox\/([0-9.]+)|', $this->agent, $version))
			{
				$this->setBrowser('firefox');

				list($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
			}
			elseif (preg_match('|Lynx\/([0-9]+)|', $this->agent, $version))
			{
				$this->setBrowser('lynx');
			}
			elseif (preg_match('|Links \(([0-9]+)|', $this->agent, $version))
			{
				$this->setBrowser('links');
			}
			elseif (preg_match('|HotJava\/([0-9]+)|', $this->agent, $version))
			{
				$this->setBrowser('hotjava');
			}
			elseif (strpos($this->agent, 'UP/') !== false || strpos($this->agent, 'UP.B') !== false || strpos($this->agent, 'UP.L') !== false)
			{
				$this->setBrowser('up');
			}
			elseif (strpos($this->agent, 'Xiino/') !== false)
			{
				$this->setBrowser('xiino');
			}
			elseif (strpos($this->agent, 'Palmscape/') !== false)
			{
				$this->setBrowser('palmscape');
			}
			elseif (strpos($this->agent, 'Nokia') !== false)
			{
				$this->setBrowser('nokia');
			}
			elseif (strpos($this->agent, 'Ericsson') !== false)
			{
				$this->setBrowser('ericsson');
			}
			elseif (strpos($this->lowerAgent, 'wap') !== false)
			{
				$this->setBrowser('wap');
			}
			elseif (strpos($this->lowerAgent, 'docomo') !== false || strpos($this->lowerAgent, 'portalmmm') !== false)
			{
				$this->setBrowser('imode');
			}
			elseif (strpos($this->agent, 'BlackBerry') !== false)
			{
				$this->setBrowser('blackberry');
			}
			elseif (strpos($this->agent, 'MOT-') !== false)
			{
				$this->setBrowser('motorola');
			}
			elseif (strpos($this->lowerAgent, 'j-') !== false)
			{
				$this->setBrowser('mml');
			}
			elseif (preg_match('|Mozilla\/([0-9.]+)|', $this->agent, $version))
			{
				$this->setBrowser('mozilla');

				list($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);
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
	 * @since   1.7.0
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
	 * @since   1.7.0
	 */
	public function getPlatform()
	{
		return $this->platform;
	}

	/**
	 * Set browser version, not by engine version
	 * Fallback to use when no other method identify the engine version
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function identifyBrowserVersion()
	{
		if (preg_match('|Version[/ ]([0-9.]+)|', $this->agent, $version))
		{
			list($this->majorVersion, $this->minorVersion) = explode('.', $version[1]);

			return;
		}

		// Can't identify browser version
		$this->majorVersion = 0;
		$this->minorVersion = 0;
	}

	/**
	 * Sets the current browser.
	 *
	 * @param   string  $browser  The browser to set as current.
	 *
	 * @return  void
	 *
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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

		return;
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
	 * @since   1.7.0
	 */
	public function isViewable($mimetype)
	{
		$mimetype = strtolower($mimetype);
		list($type, $subtype) = explode('/', $mimetype);

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

		if ($type != 'image')
		{
			return false;
		}

		return in_array($subtype, $this->images);
	}

	/**
	 * Determine if the given browser is the same as the current.
	 *
	 * @param   string  $browser  The browser to check.
	 *
	 * @return  boolean  Is the given browser the same as the current?
	 *
	 * @since   1.7.0
	 */
	public function isBrowser($browser)
	{
		return $this->browser === $browser;
	}

	/**
	 * Determines if the browser is a robot or not.
	 *
	 * @return  boolean  True if browser is a known robot.
	 *
	 * @since   1.7.0
	 */
	public function isRobot()
	{
		foreach ($this->robots as $robot)
		{
			if (preg_match('/' . $robot . '/', $this->agent))
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
	 * @since   1.7.0
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
	 * @since   1.7.0
	 * @deprecated  4.0 - Use the isSSLConnection method on the application object.
	 */
	public function isSSLConnection()
	{
		\JLog::add(
			'Browser::isSSLConnection() is deprecated. Use the isSSLConnection method on the application object instead.',
			\JLog::WARNING,
			'deprecated'
		);

		return (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) || getenv('SSL_PROTOCOL_VERSION');
	}
}

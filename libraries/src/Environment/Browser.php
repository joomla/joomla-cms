<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Environment;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
    protected $acceptParsed = [];

    /**
     * @var    string  Platform the browser is running on
     * @since  3.0.0
     */
    protected $platform = '';

    /**
     * @var    array  Known robots.
     * @since  3.0.0
     */
    protected $robots = [
            '/bot',
            '007ac9 crawler',
            '360spider',
            'AliyunSecBot',
            'Amazonbot',
            'Barkrowler',
            'Bytespider',
            'ClaudeBot',
            'DuckDuckBot',
            'Google-Read-Aloud',
            'OAI-SearchBot',
            'PetalBot',
            'SemrushBot',
            'a6-indexer',
            'aboundex',
            'acapbot',
            'acoonbot',
            'adbeat_bot',
            'addthis',
            'adidxbot',
            'admantx',
            'adsbot',
            'adsbot-google',
            'adsbot-google-mobile',
            'adscanner',
            'adstxtcrawler',
            'advbot',
            'ahc/',
            'ahrefsbot',
            'ahrefssiteaudit',
            'aihitbot',
            'aisearchbot',
            'alphabot',
            'amazon cloudfront',
            'anderspinkbot',
            'antibot',
            'apache-httpclient',
            'apercite',
            'apis-google',
            'appengine-google',
            'applebot',
            'arabot',
            'archive.org_bot',
            'archivebot',
            'axios',
            'backlinkcrawler',
            'baidu-yunguance',
            'baiduspider',
            'barkrowler',
            'bdcbot',
            'behloolbot',
            'betabot',
            'bibnum.bnf',
            'biglotron',
            'bingbot',
            'bingpreview/',
            'binlar',
            'bitlybot',
            'blekkobot',
            'blexbot/',
            'blogmurabot',
            'blp_bbot',
            'bnf.fr_bot',
            'bomborabot',
            'bot-pge.chlooe.com',
            'bot.araturka.com',
            'botify',
            'boxcarbot',
            'brainobot',
            'brandverity',
            'btwebclient',
            'bubing',
            'buck/',
            'buzzbot',
            'bytesspider',
            'capsulechecker',
            'careerbot',
            'cc metadata scaper',
            'ccbot',
            'centurybot9',
            'changedetection',
            'check_http',
            'citeseerxbot',
            'cliqzbot/',
            'coccoc',
            'collection@infegy.com',
            'companybook-crawler',
            'content crawler spider',
            'contextad bot',
            'contxbot',
            'convera',
            'crawler4j',
            'crunchbot',
            'crystalsemanticsbot',
            'curl',
            'cxensebot',
            'cyberpatrol',
            'dareboost',
            'datafeedwatch',
            'datagnionbot',
            'daum/',
            'dcrawl',
            'deadlinkchecker',
            'deusu/',
            'diffbot/',
            'digg deeper',
            'digincore bot',
            'discobot',
            'discordbot',
            'disqus',
            'dnyzbot',
            'domain re-animator bot',
            'domaincrawler',
            'domainstatsbot',
            'dotbot',
            'drupact',
            'duckduckbot',
            'duckduckgo-favicons-bot',
            'ec2linkfinder',
            'edisterbot',
            'electricmonk',
            'elisabot',
            'embedly',
            'epicbot',
            'eright',
            'europarchive.org',
            'everyonesocialbot',
            'exabot',
            'extlinksbot',
            'ezid',
            'ezooms',
            'facebookexternalhit',
            'facebot',
            'fast enterprise crawler',
            'fast-webcrawler',
            'feedfetcher-google',
            'feedly',
            'feedspotbot/',
            'femtosearchbot',
            'fetch/',
            'fever',
            'filterdb.iss.net/crawler',
            'findlink',
            'findthatfile',
            'findxbot',
            'flamingo_searchengine',
            'flipboardproxy',
            'fluffy',
            'fr-crawler',
            'fuelbot',
            'fyrebot',
            'g00g1e.net',
            'g2reader-bot',
            'garlikcrawler',
            'genieo',
            'gigablast',
            'gigabot',
            'gingercrawler',
            'gluten free crawler/',
            'gnam gnam spider',
            'gnowitnewsbot',
            'go-http-client',
            'google web preview',
            'google-adwords-instant',
            'google-extended',
            'google-inspection',
            'google-xrawler',
            'googlebot-image',
            'googlebot-mobile',
            'googlebot-news',
            'googlebot-video',
            'googlebot/',
            'googleother',
            'gowikibot',
            'gptbot',
            'grapeshotcrawler',
            'grobbot',
            'grub.org',
            'gslfbot',
            'hatena',
            'heritrix',
            'http_get',
            'httpunit',
            'httrack',
            'ia_archiver',
            'ias crawler',
            'icc-crawler',
            'ichiro',
            'imrbot',
            'indeedbot',
            'integromedb',
            'intelium_bot',
            'interfaxscanbot',
            'ip-web-crawler.com',
            'ips-agent',
            'iskanie',
            'istellabot',
            'it2media-domain-crawler',
            'james bot',
            'jamie\'s spider',
            'jetslide',
            'jetty',
            'jugendschutzprogramm-crawler',
            'jyxobot',
            'k7mlwcbot',
            'kemvibot',
            'kosmiobot',
            'landau-media-spider',
            'laserlikebot',
            'lb-spider',
            'leikibot',
            'libwww',
            'linguee bot',
            'linkapediabot',
            'linkarchiver',
            'linkdex',
            'linkedinbot',
            'lipperhey',
            'livelapbot',
            'lssbot',
            'lssrocketcrawler',
            'ltx71',
            'luminator-robots',
            'magpie-crawler',
            'mail.ru_bot',
            'mappydata',
            'mastodon',
            'mauibot',
            'mediapartners (googlebot)',
            'mediapartners-google',
            'mediatoolkitbot',
            'megaindex',
            'meltwaternews',
            'memorybot',
            'meta-externalagent',
            'metajobbot',
            'metauri',
            'mindupbot',
            'miniflux/',
            'mj12bot',
            'mlbot',
            'moatbot',
            'mojeekbot/',
            'moreover',
            'msnbot',
            'msrbot',
            'muckrack',
            'multiviewbot',
            'nerdbynature.bot',
            'nerdybot',
            'netcraftsurveyagent',
            'netestate ne crawler',
            'netresearchserver',
            'netvibes',
            'newsharecounts',
            'newspaper/',
            'niki-bot',
            'nimbostratus-bot',
            'ning/',
            'nutch',
            'nuzzel',
            'ocarinabot',
            'okhttp',
            'omgili',
            'openai.com/bot',
            'openhosebot',
            'openindexspider',
            'orangebot/',
            'outclicksbot',
            'page2rss',
            'panscient',
            'paperlibot',
            'pcore-http',
            'perplexitybot',
            'petalbot',
            'phantomjs',
            'phpcrawl',
            'pingdom',
            'pinterest',
            'piplbot',
            'pocketparser',
            'postrank',
            'pr-cy.ru',
            'primalbot',
            'privacyawarebot',
            'proximic',
            'psbot',
            'pulsepoint',
            'purebot',
            'python-requests',
            'python-urllib',
            'qwantify',
            'rankactivelinkbot',
            'redditbot',
            'retrevopageanalyzer',
            'rogerbot',
            'safednsbot',
            'safesearch microdata crawler',
            'sbl-bot',
            'scoutjet',
            'scrapy',
            'screaming frog seo spider',
            'scribdbot',
            'searchbot',
            'seekbot',
            'semanticbot',
            'semanticscholarbot',
            'semrushbot',
            'sentry/',
            'seokicks',
            'seoscanners',
            'seznambot',
            'simplecrawler',
            'sistrix crawler',
            'sitebot',
            'siteexplorer.info',
            'siteimprove.com',
            'skypeuripreview',
            'slack-imgproxy',
            'slackbot',
            'slurp',
            'smtbot',
            'snacktory',
            'socialrankiobot',
            'sogou',
            'sonic',
            'spbot',
            'speedy',
            'storygizebot',
            'summify',
            'surveybot',
            'swimgbot',
            'sysomos',
            'tagoobot',
            'tangibleebot',
            'telegrambot',
            'teoma',
            'thinklab',
            'tineye',
            'toplistbot',
            'toutiaospider',
            'traackr.com',
            'tracemyfile',
            'trendictionbot',
            'trove',
            'turnitinbot',
            'tweetmemebot',
            'twengabot',
            'twitterbot',
            'twurly',
            'um-ln',
            'upflow',
            'uptimerobot',
            'urlappendbot',
            'usinenouvellecrawler',
            'vebidoobot',
            'veoozbot',
            'vkshare',
            'voilabot',
            'wbsearchbot',
            'web-archive-net.com.bot',
            'webcompanycrawler',
            'webmon ',
            'wesee:search',
            'wget',
            'whatsapp',
            'wocbot',
            'woobot',
            'woriobot',
            'wotbox',
            'xenu link sleuth',
            'xovibot',
            'yacybot',
            'yahoo link preview',
            'yak/',
            'yandex.com/bots',
            'yandexbot',
            'yanga',
            'yeti',
            'yisouspider',
            'yoozbot',
            'zabbix',
            'zgrab',
            'zoominfobot',
            'zumbot',
            'zuperlistbot/',
    ];

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
    protected $images = ['jpeg', 'gif', 'png', 'pjpeg', 'x-png', 'bmp'];

    /**
     * @var    array  Browser instances container.
     * @since  1.7.3
     */
    protected static $instances = [];

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
        $signature = serialize([$userAgent, $accept]);

        if (empty(self::$instances[$signature])) {
            self::$instances[$signature] = new static($userAgent, $accept);
        }

        return self::$instances[$signature];
    }

    /**
     * Parses the user agent string and initializes the object with
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
        if (\is_null($userAgent)) {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $this->agent = trim($_SERVER['HTTP_USER_AGENT']);
            }
        } else {
            $this->agent = $userAgent;
        }

        $this->lowerAgent = strtolower($this->agent);

        // Set our accept string.
        if (\is_null($accept)) {
            if (isset($_SERVER['HTTP_ACCEPT'])) {
                $this->accept = strtolower(trim($_SERVER['HTTP_ACCEPT']));
            }
        } else {
            $this->accept = strtolower($accept);
        }

        if (!empty($this->agent)) {
            $this->_setPlatform();

            /*
             * Determine if mobile. Note: Some Handhelds have their screen resolution in the
             * user agent string, which we can use to look for mobile agents.
             */
            if (
                str_contains($this->agent, 'MOT-')
                || str_contains($this->lowerAgent, 'j-')
                || preg_match('/(mobileexplorer|openwave|opera mini|opera mobi|operamini|avantgo|wap|elaine)/i', $this->agent)
                || preg_match('/(iPhone|iPod|iPad|Android|Mobile|Phone|BlackBerry|Xiino|Palmscape|palmsource)/i', $this->agent)
                || preg_match('/(Nokia|Ericsson|docomo|digital paths|portalmmm|CriOS[\/ ]([0-9.]+))/i', $this->agent)
                || preg_match('/(UP|UP.B|UP.L)/', $this->agent)
                || preg_match('/; (120x160|240x280|240x320|320x320)\)/', $this->agent)
            ) {
                $this->mobile = true;
            }

            /*
             * We have to check for Edge as the first browser, because Edge has something like:
             * Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393
             */
            if (preg_match('|Edge\/([0-9.]+)|', $this->agent, $version)) {
                $this->setBrowser('edge');

                if (str_contains($version[1], '.')) {
                    [$this->majorVersion, $this->minorVersion] = explode('.', $version[1]);
                } else {
                    $this->majorVersion = $version[1];
                    $this->minorVersion = 0;
                }
            } elseif (preg_match('|Edg\/([0-9.]+)|', $this->agent, $version)) {
                /**
                 * We have to check for Edge as the first browser, because Edge has something like:
                 * Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3738.0 Safari/537.36 Edg/75.0.107.0
                 */
                $this->setBrowser('edg');

                [$this->majorVersion, $this->minorVersion] = explode('.', $version[1]);
            } elseif (preg_match('|Opera[\/ ]([0-9.]+)|', $this->agent, $version)) {
                $this->setBrowser('opera');

                [$this->majorVersion, $this->minorVersion] = explode('.', $version[1]);

                /*
                 * Due to changes in Opera UA, we need to check Version/xx.yy,
                 * but only if version is > 9.80. See: http://dev.opera.com/articles/view/opera-ua-string-changes/
                 */
                if ($this->majorVersion == 9 && $this->minorVersion >= 80) {
                    $this->identifyBrowserVersion();
                }
            } elseif (preg_match('/OPR[\/ ]([0-9.]+)/', $this->agent, $version)) {
                // Opera 15+
                $this->setBrowser('opera');

                [$this->majorVersion, $this->minorVersion] = explode('.', $version[1]);
            } elseif (
                preg_match('/Chrome[\/ ]([0-9.]+)/i', $this->agent, $version)
                || preg_match('/CrMo[\/ ]([0-9.]+)/i', $this->agent, $version)
                || preg_match('/CriOS[\/ ]([0-9.]+)/i', $this->agent, $version)
            ) {
                $this->setBrowser('chrome');

                [$this->majorVersion, $this->minorVersion] = explode('.', $version[1]);
            } elseif (
                str_contains($this->lowerAgent, 'elaine/')
                || str_contains($this->lowerAgent, 'palmsource')
                || str_contains($this->lowerAgent, 'digital paths')
            ) {
                $this->setBrowser('palm');
            } elseif (
                preg_match('/MSIE ([0-9.]+)/i', $this->agent, $version)
                || preg_match('/IE ([0-9.]+)/i', $this->agent, $version)
                || preg_match('/Internet Explorer[\/ ]([0-9.]+)/i', $this->agent, $version)
                || preg_match('/Trident\/.*rv:([0-9.]+)/i', $this->agent, $version)
            ) {
                $this->setBrowser('msie');

                // Special case for IE 11+
                if (str_contains($version[0], 'Trident') && str_contains($version[0], 'rv:')) {
                    preg_match('|rv:([0-9.]+)|', $this->agent, $version);
                }

                if (str_contains($version[1], '.')) {
                    [$this->majorVersion, $this->minorVersion] = explode('.', $version[1]);
                } else {
                    $this->majorVersion = $version[1];
                    $this->minorVersion = 0;
                }
            } elseif (preg_match('|amaya\/([0-9.]+)|', $this->agent, $version)) {
                $this->setBrowser('amaya');
                $this->majorVersion = $version[1];

                if (isset($version[2])) {
                    $this->minorVersion = $version[2];
                }
            } elseif (preg_match('|ANTFresco\/([0-9]+)|', $this->agent, $version)) {
                $this->setBrowser('fresco');
            } elseif (str_contains($this->lowerAgent, 'avantgo')) {
                $this->setBrowser('avantgo');
            } elseif (preg_match('|[Kk]onqueror\/([0-9]+)|', $this->agent, $version) || preg_match('|Safari/([0-9]+)\.?([0-9]+)?|', $this->agent, $version)) {
                // Konqueror and Apple's Safari both use the KHTML rendering engine.
                $this->setBrowser('konqueror');
                $this->majorVersion = $version[1];

                if (isset($version[2])) {
                    $this->minorVersion = $version[2];
                }

                if (str_contains($this->agent, 'Safari') && $this->majorVersion >= 60) {
                    // Safari.
                    $this->setBrowser('safari');
                    $this->identifyBrowserVersion();
                }
            } elseif (preg_match('|Firefox\/([0-9.]+)|', $this->agent, $version)) {
                $this->setBrowser('firefox');

                [$this->majorVersion, $this->minorVersion] = explode('.', $version[1]);
            } elseif (preg_match('|Lynx\/([0-9]+)|', $this->agent, $version)) {
                $this->setBrowser('lynx');
            } elseif (preg_match('|Links \(([0-9]+)|', $this->agent, $version)) {
                $this->setBrowser('links');
            } elseif (preg_match('|HotJava\/([0-9]+)|', $this->agent, $version)) {
                $this->setBrowser('hotjava');
            } elseif (str_contains($this->agent, 'UP/') || str_contains($this->agent, 'UP.B') || str_contains($this->agent, 'UP.L')) {
                $this->setBrowser('up');
            } elseif (str_contains($this->agent, 'Xiino/')) {
                $this->setBrowser('xiino');
            } elseif (str_contains($this->agent, 'Palmscape/')) {
                $this->setBrowser('palmscape');
            } elseif (str_contains($this->agent, 'Nokia')) {
                $this->setBrowser('nokia');
            } elseif (str_contains($this->agent, 'Ericsson')) {
                $this->setBrowser('ericsson');
            } elseif (str_contains($this->lowerAgent, 'wap')) {
                $this->setBrowser('wap');
            } elseif (str_contains($this->lowerAgent, 'docomo') || str_contains($this->lowerAgent, 'portalmmm')) {
                $this->setBrowser('imode');
            } elseif (str_contains($this->agent, 'BlackBerry')) {
                $this->setBrowser('blackberry');
            } elseif (str_contains($this->agent, 'MOT-')) {
                $this->setBrowser('motorola');
            } elseif (str_contains($this->lowerAgent, 'j-')) {
                $this->setBrowser('mml');
            } elseif (preg_match('|Mozilla\/([0-9.]+)|', $this->agent, $version)) {
                $this->setBrowser('mozilla');

                [$this->majorVersion, $this->minorVersion] = explode('.', $version[1]);
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
        if (str_contains($this->lowerAgent, 'wind')) {
            $this->platform = 'win';
        } elseif (str_contains($this->lowerAgent, 'mac')) {
            $this->platform = 'mac';
        } else {
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
        if (preg_match('|Version[/ ]([0-9.]+)|', $this->agent, $version)) {
            [$this->majorVersion, $this->minorVersion] = explode('.', $version[1]);

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
        if (isset($_SERVER['SERVER_PROTOCOL']) && ($pos = strrpos($_SERVER['SERVER_PROTOCOL'], '/'))) {
            return substr($_SERVER['SERVER_PROTOCOL'], $pos + 1);
        }

        return '';
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
        $mimetype         = strtolower($mimetype);
        [$type, $subtype] = explode('/', $mimetype);

        if (!empty($this->accept)) {
            $wildcard_match = false;

            if (str_contains($this->accept, $mimetype)) {
                return true;
            }

            if (str_contains($this->accept, '*/*')) {
                $wildcard_match = true;

                if ($type !== 'image') {
                    return true;
                }
            }

            // Deal with Mozilla pjpeg/jpeg issue
            if ($this->isBrowser('mozilla') && ($mimetype === 'image/pjpeg') && (str_contains($this->accept, 'image/jpeg'))) {
                return true;
            }

            if (!$wildcard_match) {
                return false;
            }
        }

        if ($type !== 'image') {
            return false;
        }

        return \in_array($subtype, $this->images);
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
        foreach ($this->robots as $robot) {
            if (str_contains($this->lowerAgent, $robot)) {
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
}

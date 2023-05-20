<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\FactoryInterface;
use Joomla\CMS\Filesystem\Stream;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\Registry\Registry;
use PHPMailer\PHPMailer\Exception as phpmailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla Platform Factory class.
 *
 * @since  1.7.0
 */
abstract class Factory
{
    /**
     * Global application object
     *
     * @var    CMSApplicationInterface
     * @since  1.7.0
     */
    public static $application = null;

    /**
     * Global cache object
     *
     * @var    Cache
     * @since  1.7.0
     */
    public static $cache = null;

    /**
     * Global configuration object
     *
     * @var         \JConfig
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the configuration object within the application
     *              Example:
     *              Factory::getApplication()->getConfig();
     */
    public static $config = null;

    /**
     * Global container object
     *
     * @var    Container
     * @since  4.0.0
     */
    public static $container = null;

    /**
     * Container for Date instances
     *
     * @var    array
     * @since  1.7.3
     */
    public static $dates = [];

    /**
     * Global session object
     *
     * @var         Session
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the session service in the DI container or get from the application object
     *              Example:
     *              Factory::getApplication()->getSession();
     */
    public static $session = null;

    /**
     * Global language object
     *
     * @var         Language
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the language service in the DI container or get from the application object
     *              Example:
     *              Factory::getApplication()->getLanguage();
     */
    public static $language = null;

    /**
     * Global document object
     *
     * @var         Document
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *               Use the document service in the DI container or get from the application object
     *               Example:
     *               Factory::getApplication()->getDocument();
     */
    public static $document = null;

    /**
     * Global database object
     *
     * @var         DatabaseDriver
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the database service in the DI container
     *              Example:
     *              Factory::getContainer()->get(DatabaseInterface::class);
     */
    public static $database = null;

    /**
     * Global mailer object
     *
     * @var    Mail
     * @since  1.7.0
     */
    public static $mailer = null;

    /**
     * Get the global application object. When the global application doesn't exist, an exception is thrown.
     *
     * @return  CMSApplicationInterface object
     *
     * @since   1.7.0
     * @throws  \Exception
     */
    public static function getApplication()
    {
        if (!self::$application) {
            throw new \Exception('Failed to start application', 500);
        }

        return self::$application;
    }

    /**
     * Get a configuration object
     *
     * Returns the global {@link \JConfig} object, only creating it if it doesn't already exist.
     *
     * @param   string  $file       The path to the configuration file
     * @param   string  $type       The type of the configuration file
     * @param   string  $namespace  The namespace of the configuration file
     *
     * @return  Registry
     *
     * @see         Registry
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the configuration object within the application
     *              Example:
     *              Factory::getApplication->getConfig();
     */
    public static function getConfig($file = null, $type = 'PHP', $namespace = '')
    {
        @trigger_error(
            sprintf(
                '%s() is deprecated. The configuration object should be read from the application.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        /**
         * If there is an application object, fetch the configuration from there.
         * Check it's not null because LanguagesModel can make it null and if it's null
         * we would want to re-init it from configuration.php.
         */
        if (self::$application && self::$application->getConfig() !== null) {
            return self::$application->getConfig();
        }

        if (!self::$config) {
            if ($file === null) {
                $file = JPATH_CONFIGURATION . '/configuration.php';
            }

            self::$config = self::createConfig($file, $type, $namespace);
        }

        return self::$config;
    }

    /**
     * Get a container object
     *
     * Returns the global service container object, only creating it if it doesn't already exist.
     *
     * This method is only suggested for use in code whose responsibility is to create new services
     * and needs to be able to resolve the dependencies, and should therefore only be used when the
     * container is not accessible by other means.  Valid uses of this method include:
     *
     * - A static `getInstance()` method calling a factory service from the container,
     *   see `Joomla\CMS\Toolbar\Toolbar::getInstance()` as an example
     * - An application front controller loading and executing the Joomla application class,
     *   see the `cli/joomla.php` file as an example
     * - Retrieving optional constructor dependencies when not injected into a class during a transitional
     *   period to retain backward compatibility, in this case a deprecation notice should also be emitted to
     *   notify developers of changes needed in their code
     *
     * This method is not suggested for use as a one-for-one replacement of static calls, such as
     * replacing calls to `Factory::getDbo()` with calls to `Factory::getContainer()->get('db')`, code
     * should be refactored to support dependency injection instead of making this change.
     *
     * @return  Container
     *
     * @since   4.0.0
     */
    public static function getContainer(): Container
    {
        if (!self::$container) {
            self::$container = self::createContainer();
        }

        return self::$container;
    }

    /**
     * Get a session object.
     *
     * Returns the global {@link Session} object, only creating it if it doesn't already exist.
     *
     * @param   array  $options  An array containing session options
     *
     * @return  Session object
     *
     * @see         Session
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the session service in the DI container or get from the application object
     *              Example:
     *              Factory::getApplication()->getSession();
     */
    public static function getSession(array $options = [])
    {
        @trigger_error(
            sprintf(
                '%1$s() is deprecated. Load the session from the dependency injection container or via %2$s::getApplication()->getSession().',
                __METHOD__,
                __CLASS__
            ),
            E_USER_DEPRECATED
        );

        return self::getApplication()->getSession();
    }

    /**
     * Get a language object.
     *
     * Returns the global {@link Language} object, only creating it if it doesn't already exist.
     *
     * @return  Language object
     *
     * @see         Language
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the language service in the DI container or get from the application object
     *              Example:
     *              Factory::getApplication()->getLanguage();
     */
    public static function getLanguage()
    {
        @trigger_error(
            sprintf(
                '%1$s() is deprecated. Load the language from the dependency injection container or via %2$s::getApplication()->getLanguage().',
                __METHOD__,
                __CLASS__
            ),
            E_USER_DEPRECATED
        );

        if (!self::$language) {
            self::$language = self::createLanguage();
        }

        return self::$language;
    }

    /**
     * Get a document object.
     *
     * Returns the global {@link \Joomla\CMS\Document\Document} object, only creating it if it doesn't already exist.
     *
     * @return  Document object
     *
     * @see         Document
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the document service in the DI container or get from the application object
     *              Example:
     *              Factory::getApplication()->getDocument();
     */
    public static function getDocument()
    {
        @trigger_error(
            sprintf(
                '%1$s() is deprecated. Load the document from the dependency injection container or via %2$s::getApplication()->getDocument().',
                __METHOD__,
                __CLASS__
            ),
            E_USER_DEPRECATED
        );

        if (!self::$document) {
            self::$document = self::createDocument();
        }

        return self::$document;
    }

    /**
     * Get a user object.
     *
     * Returns the global {@link User} object, only creating it if it doesn't already exist.
     *
     * @param   integer  $id  The user to load - Can be an integer or string - If string, it is converted to ID automatically.
     *
     * @return  User object
     *
     * @see         User
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Load the user service from the dependency injection container or get from the application object
     *              Example:
     *              Factory::getApplication()->getIdentity();
     */
    public static function getUser($id = null)
    {
        @trigger_error(
            sprintf(
                '%1$s() is deprecated. Load the user from the dependency injection container or via %2$s::getApplication()->getIdentity().',
                __METHOD__,
                __CLASS__
            ),
            E_USER_DEPRECATED
        );

        $instance = self::getApplication()->getSession()->get('user');

        if (\is_null($id)) {
            if (!($instance instanceof User)) {
                $instance = User::getInstance();
            }
        } elseif (!($instance instanceof User) || \is_string($id) || $instance->id !== $id) {
            // Check if we have a string as the id or if the numeric id is the current instance
            $instance = User::getInstance($id);
        }

        return $instance;
    }

    /**
     * Get a cache object
     *
     * Returns the global {@link CacheController} object
     *
     * @param   string  $group    The cache group name
     * @param   string  $handler  The handler to use
     * @param   string  $storage  The storage method
     *
     * @return  \Joomla\CMS\Cache\CacheController object
     *
     * @see         Cache
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the cache controller factory instead
     *              Example:
     *              Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController($handler, $options);
     */
    public static function getCache($group = '', $handler = 'callback', $storage = null)
    {
        @trigger_error(
            sprintf(
                '%s() is deprecated. The cache controller should be fetched from the factory.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        $hash = md5($group . $handler . $storage);

        if (isset(self::$cache[$hash])) {
            return self::$cache[$hash];
        }

        $handler = ($handler === 'function') ? 'callback' : $handler;

        $options = ['defaultgroup' => $group];

        if (isset($storage)) {
            $options['storage'] = $storage;
        }

        $cache = self::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController($handler, $options);

        self::$cache[$hash] = $cache;

        return self::$cache[$hash];
    }

    /**
     * Get a database object.
     *
     * Returns the global {@link DatabaseDriver} object, only creating it if it doesn't already exist.
     *
     * @return  DatabaseDriver
     *
     * @see         DatabaseDriver
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the database service in the DI container
     *              Example:
     *              Factory::getContainer()->get(DatabaseInterface::class);
     */
    public static function getDbo()
    {
        @trigger_error(
            sprintf(
                '%1$s() is deprecated. Load the database from the dependency injection container.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        if (!self::$database) {
            if (self::getContainer()->has('DatabaseDriver')) {
                self::$database = self::getContainer()->get('DatabaseDriver');
            } else {
                self::$database = self::createDbo();
            }
        }

        return self::$database;
    }

    /**
     * Get a mailer object.
     *
     * Returns the global {@link Mail} object, only creating it if it doesn't already exist.
     *
     * @return  Mail object
     *
     * @see     Mail
     * @since   1.7.0
     */
    public static function getMailer()
    {
        if (!self::$mailer) {
            self::$mailer = self::createMailer();
        }

        $copy = clone self::$mailer;

        return $copy;
    }

    /**
     * Return the {@link Date} object
     *
     * @param   mixed  $time      The initial time for the Date object
     * @param   mixed  $tzOffset  The timezone offset.
     *
     * @return  Date object
     *
     * @see     Date
     * @since   1.7.0
     */
    public static function getDate($time = 'now', $tzOffset = null)
    {
        static $classname;
        static $mainLocale;

        $language = self::getLanguage();
        $locale   = $language->getTag();

        if (!isset($classname) || $locale != $mainLocale) {
            // Store the locale for future reference
            $mainLocale = $locale;

            if ($mainLocale !== false) {
                $classname = str_replace('-', '_', $mainLocale) . 'Date';

                if (!class_exists($classname)) {
                    // The class does not exist, default to Date
                    $classname = 'Joomla\\CMS\\Date\\Date';
                }
            } else {
                // No tag, so default to Date
                $classname = 'Joomla\\CMS\\Date\\Date';
            }
        }

        $key = $time . '-' . ($tzOffset instanceof \DateTimeZone ? $tzOffset->getName() : (string) $tzOffset);

        if (!isset(self::$dates[$classname][$key])) {
            self::$dates[$classname][$key] = new $classname($time, $tzOffset);
        }

        $date = clone self::$dates[$classname][$key];

        return $date;
    }

    /**
     * Create a configuration object
     *
     * @param   string  $file       The path to the configuration file.
     * @param   string  $type       The type of the configuration file.
     * @param   string  $namespace  The namespace of the configuration file.
     *
     * @return  Registry
     *
     * @see         Registry
     * @since       1.7.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Use the configuration object within the application.
     *              Example: Factory::getApplication->getConfig();
     */
    protected static function createConfig($file, $type = 'PHP', $namespace = '')
    {
        @trigger_error(
            sprintf(
                '%s() is deprecated. The configuration object should be read from the application.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        if (is_file($file)) {
            include_once $file;
        }

        // Create the registry with a default namespace of config
        $registry = new Registry();

        // Sanitize the namespace.
        $namespace = ucfirst((string) preg_replace('/[^A-Z_]/i', '', $namespace));

        // Build the config name.
        $name = 'JConfig' . $namespace;

        // Handle the PHP configuration type.
        if ($type === 'PHP' && class_exists($name)) {
            // Create the JConfig object
            $config = new $name();

            // Load the configuration values into the registry
            $registry->loadObject($config);
        }

        return $registry;
    }

    /**
     * Create a container object
     *
     * @return  Container
     *
     * @since   4.0.0
     */
    protected static function createContainer(): Container
    {
        $container = (new Container())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Application())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Authentication())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\CacheController())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Config())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Console())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Database())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Dispatcher())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Document())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Form())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Logger())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Language())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Menu())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Pathway())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\HTMLRegistry())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Session())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Toolbar())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\WebAssetRegistry())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\Router())
            ->registerServiceProvider(new \Joomla\CMS\Service\Provider\User());

        return $container;
    }

    /**
     * Create a database object
     *
     * @return  DatabaseDriver
     *
     * @see         DatabaseDriver
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the database service in the DI container
     *              Example:
     *              Factory::getContainer()->get(DatabaseInterface::class);
     */
    protected static function createDbo()
    {
        @trigger_error(
            sprintf(
                '%1$s() is deprecated, register a service provider to create a %2$s instance instead.',
                __METHOD__,
                DatabaseInterface::class
            ),
            E_USER_DEPRECATED
        );

        $conf = self::getConfig();

        $host     = $conf->get('host');
        $user     = $conf->get('user');
        $password = $conf->get('password');
        $database = $conf->get('db');
        $prefix   = $conf->get('dbprefix');
        $driver   = $conf->get('dbtype');

        $options = ['driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix];

        if ((int) $conf->get('dbencryption') !== 0) {
            $options['ssl'] = [
                'enable'             => true,
                'verify_server_cert' => (bool) $conf->get('dbsslverifyservercert'),
            ];

            foreach (['cipher', 'ca', 'key', 'cert'] as $value) {
                $confVal = trim($conf->get('dbssl' . $value, ''));

                if ($confVal !== '') {
                    $options['ssl'][$value] = $confVal;
                }
            }
        }

        try {
            $db = DatabaseDriver::getInstance($options);
        } catch (\RuntimeException $e) {
            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
            }

            jexit('Database Error: ' . $e->getMessage());
        }

        return $db;
    }

    /**
     * Create a mailer object
     *
     * @return  Mail object
     *
     * @see     Mail
     * @since   1.7.0
     */
    protected static function createMailer()
    {
        $conf = self::getConfig();

        $smtpauth   = ($conf->get('smtpauth') == 0) ? null : 1;
        $smtpuser   = $conf->get('smtpuser');
        $smtppass   = $conf->get('smtppass');
        $smtphost   = $conf->get('smtphost');
        $smtpsecure = $conf->get('smtpsecure');
        $smtpport   = $conf->get('smtpport');
        $mailfrom   = $conf->get('mailfrom');
        $fromname   = $conf->get('fromname');
        $mailer     = $conf->get('mailer');

        // Create a Mail object
        $mail = Mail::getInstance();

        // Clean the email address
        $mailfrom = MailHelper::cleanLine($mailfrom);

        // Set default sender without Reply-to if the mailfrom is a valid address
        if (MailHelper::isEmailAddress($mailfrom)) {
            // Wrap in try/catch to catch phpmailerExceptions if it is throwing them
            try {
                // Check for a false return value if exception throwing is disabled
                if ($mail->setFrom($mailfrom, MailHelper::cleanLine($fromname), false) === false) {
                    Log::add(__METHOD__ . '() could not set the sender data.', Log::WARNING, 'mail');
                }
            } catch (phpmailerException $e) {
                Log::add(__METHOD__ . '() could not set the sender data.', Log::WARNING, 'mail');
            }
        }

        // Default mailer is to use PHP's mail function
        switch ($mailer) {
            case 'smtp':
                $mail->useSmtp($smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport);
                break;

            case 'sendmail':
                $mail->isSendmail();
                break;

            default:
                $mail->isMail();
                break;
        }

        return $mail;
    }

    /**
     * Create a language object
     *
     * @return  Language object
     *
     * @see         Language
     * @since       1.7.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Load the language service from the dependency injection container or via $app->getLanguage()
     *              Example: Factory::getContainer()->get(LanguageFactoryInterface::class)->createLanguage($locale, $debug)
     */
    protected static function createLanguage()
    {
        @trigger_error(
            sprintf(
                '%1$s() is deprecated. Load the language from the dependency injection container or via %2$s::getApplication()->getLanguage().',
                __METHOD__,
                __CLASS__
            ),
            E_USER_DEPRECATED
        );

        $conf   = self::getConfig();
        $locale = $conf->get('language');
        $debug  = $conf->get('debug_lang');
        $lang   = self::getContainer()->get(LanguageFactoryInterface::class)->createLanguage($locale, $debug);

        return $lang;
    }

    /**
     * Create a document object
     *
     * @return  Document object
     *
     * @see         Document
     * @since       1.7.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Load the document service from the dependency injection container or via $app->getDocument()
     *              Example: Factory::getContainer()->get(FactoryInterface::class)->createDocument($type, $attributes);
     */
    protected static function createDocument()
    {
        @trigger_error(
            sprintf(
                '%1$s() is deprecated. Load the document from the dependency injection container or via %2$s::getApplication()->getDocument().',
                __METHOD__,
                __CLASS__
            ),
            E_USER_DEPRECATED
        );

        $lang = self::getLanguage();

        $input = self::getApplication()->input;
        $type  = $input->get('format', 'html', 'cmd');

        $version = new Version();

        $attributes = [
            'charset'      => 'utf-8',
            'lineend'      => 'unix',
            'tab'          => "\t",
            'language'     => $lang->getTag(),
            'direction'    => $lang->isRtl() ? 'rtl' : 'ltr',
            'mediaversion' => $version->getMediaVersion(),
        ];

        return self::getContainer()->get(FactoryInterface::class)->createDocument($type, $attributes);
    }

    /**
     * Creates a new stream object with appropriate prefix
     *
     * @param   boolean  $usePrefix        Prefix the connections for writing
     * @param   boolean  $useNetwork       Use network if available for writing; use false to disable (e.g. FTP, SCP)
     * @param   string   $userAgentSuffix  String to append to user agent
     * @param   boolean  $maskUserAgent    User agent masking (prefix Mozilla)
     *
     * @return  Stream
     *
     * @see     Stream
     * @since   1.7.0
     */
    public static function getStream($usePrefix = true, $useNetwork = true, $userAgentSuffix = 'Joomla', $maskUserAgent = false)
    {
        // Setup the context; Joomla! UA and overwrite
        $context = [];
        $version = new Version();

        // Set the UA for HTTP and overwrite for FTP
        $context['http']['user_agent'] = $version->getUserAgent($userAgentSuffix, $maskUserAgent);
        $context['ftp']['overwrite']   = true;

        if ($usePrefix) {
            $FTPOptions = ClientHelper::getCredentials('ftp');
            $SCPOptions = ClientHelper::getCredentials('scp');

            if ($FTPOptions['enabled'] == 1 && $useNetwork) {
                $prefix = 'ftp://' . $FTPOptions['user'] . ':' . $FTPOptions['pass'] . '@' . $FTPOptions['host'];
                $prefix .= $FTPOptions['port'] ? ':' . $FTPOptions['port'] : '';
                $prefix .= $FTPOptions['root'];
            } elseif ($SCPOptions['enabled'] == 1 && $useNetwork) {
                $prefix = 'ssh2.sftp://' . $SCPOptions['user'] . ':' . $SCPOptions['pass'] . '@' . $SCPOptions['host'];
                $prefix .= $SCPOptions['port'] ? ':' . $SCPOptions['port'] : '';
                $prefix .= $SCPOptions['root'];
            } else {
                $prefix = JPATH_ROOT . '/';
            }

            $retval = new Stream($prefix, JPATH_ROOT, $context);
        } else {
            $retval = new Stream('', '', $context);
        }

        return $retval;
    }
}

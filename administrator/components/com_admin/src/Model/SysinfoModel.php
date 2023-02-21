<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Version;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model for the display of system information.
 *
 * @since  1.6
 */
class SysinfoModel extends BaseDatabaseModel
{
    /**
     * Some PHP settings
     *
     * @var    array
     * @since  1.6
     */
    protected $php_settings = [];

    /**
     * Config values
     *
     * @var    array
     * @since  1.6
     */
    protected $config = [];

    /**
     * Some system values
     *
     * @var    array
     * @since  1.6
     */
    protected $info = [];

    /**
     * PHP info
     *
     * @var    string
     * @since  1.6
     */
    protected $php_info = null;

    /**
     * Array containing the phpinfo() data.
     *
     * @var    array
     *
     * @since  3.5
     */
    protected $phpInfoArray;

    /**
     * Private/critical data that we don't want to share
     *
     * @var    array
     *
     * @since  3.5
     */
    protected $privateSettings = [
        'phpInfoArray' => [
            'CONTEXT_DOCUMENT_ROOT',
            'Cookie',
            'DOCUMENT_ROOT',
            'extension_dir',
            'error_log',
            'Host',
            'HTTP_COOKIE',
            'HTTP_HOST',
            'HTTP_ORIGIN',
            'HTTP_REFERER',
            'HTTP Request',
            'include_path',
            'mysql.default_socket',
            'MYSQL_SOCKET',
            'MYSQL_INCLUDE',
            'MYSQL_LIBS',
            'mysqli.default_socket',
            'MYSQLI_SOCKET',
            'PATH',
            'Path to sendmail',
            'pdo_mysql.default_socket',
            'Referer',
            'REMOTE_ADDR',
            'SCRIPT_FILENAME',
            'sendmail_path',
            'SERVER_ADDR',
            'SERVER_ADMIN',
            'Server Administrator',
            'SERVER_NAME',
            'Server Root',
            'session.name',
            'session.save_path',
            'upload_tmp_dir',
            'User/Group',
            'open_basedir',
        ],
        'other' => [
            'db',
            'dbprefix',
            'fromname',
            'live_site',
            'log_path',
            'mailfrom',
            'memcached_server_host',
            'open_basedir',
            'Origin',
            'proxy_host',
            'proxy_user',
            'proxy_pass',
            'redis_server_host',
            'redis_server_auth',
            'secret',
            'sendmail',
            'session.save_path',
            'session_memcached_server_host',
            'session_redis_server_host',
            'session_redis_server_auth',
            'sitename',
            'smtphost',
            'tmp_path',
            'open_basedir',
        ],
    ];

    /**
     * System values that can be "safely" shared
     *
     * @var    array
     *
     * @since  3.5
     */
    protected $safeData;

    /**
     * Information about writable state of directories
     *
     * @var    array
     * @since  1.6
     */
    protected $directories = [];

    /**
     * The current editor.
     *
     * @var    string
     * @since  1.6
     */
    protected $editor = null;

    /**
     * Remove sections of data marked as private in the privateSettings
     *
     * @param   array   $dataArray  Array with data that may contain private information
     * @param   string  $dataType   Type of data to search for a specific section in the privateSettings array
     *
     * @return  array
     *
     * @since   3.5
     */
    protected function cleanPrivateData(array $dataArray, string $dataType = 'other'): array
    {
        $dataType = isset($this->privateSettings[$dataType]) ? $dataType : 'other';

        $privateSettings = $this->privateSettings[$dataType];

        if (!$privateSettings) {
            return $dataArray;
        }

        foreach ($dataArray as $section => $values) {
            if (\is_array($values)) {
                $dataArray[$section] = $this->cleanPrivateData($values, $dataType);
            }

            if (\in_array($section, $privateSettings, true)) {
                $dataArray[$section] = $this->cleanSectionPrivateData($values);
            }
        }

        return $dataArray;
    }

    /**
     * Obfuscate section values
     *
     * @param   mixed  $sectionValues  Section data
     *
     * @return  string|array
     *
     * @since   3.5
     */
    protected function cleanSectionPrivateData($sectionValues)
    {
        if (!\is_array($sectionValues)) {
            if (strstr($sectionValues, JPATH_ROOT)) {
                $sectionValues = 'xxxxxx';
            }

            return \strlen($sectionValues) ? 'xxxxxx' : '';
        }

        foreach ($sectionValues as $setting => $value) {
            $sectionValues[$setting] = \strlen($value) ? 'xxxxxx' : '';
        }

        return $sectionValues;
    }

    /**
     * Method to get the PHP settings
     *
     * @return  array  Some PHP settings
     *
     * @since   1.6
     */
    public function &getPhpSettings(): array
    {
        if (!empty($this->php_settings)) {
            return $this->php_settings;
        }

        $this->php_settings = [
            'memory_limit'        => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size'       => ini_get('post_max_size'),
            'display_errors'      => ini_get('display_errors') == '1',
            'short_open_tag'      => ini_get('short_open_tag') == '1',
            'file_uploads'        => ini_get('file_uploads') == '1',
            'output_buffering'    => (int) ini_get('output_buffering') !== 0,
            'open_basedir'        => ini_get('open_basedir'),
            'session.save_path'   => ini_get('session.save_path'),
            'session.auto_start'  => ini_get('session.auto_start'),
            'disable_functions'   => ini_get('disable_functions'),
            'xml'                 => \extension_loaded('xml'),
            'zlib'                => \extension_loaded('zlib'),
            'zip'                 => \function_exists('zip_open') && \function_exists('zip_read'),
            'mbstring'            => \extension_loaded('mbstring'),
            'fileinfo'            => \extension_loaded('fileinfo'),
            'gd'                  => \extension_loaded('gd'),
            'iconv'               => \function_exists('iconv'),
            'intl'                => \function_exists('transliterator_transliterate'),
            'max_input_vars'      => ini_get('max_input_vars'),
        ];

        return $this->php_settings;
    }

    /**
     * Method to get the config
     *
     * @return  array  config values
     *
     * @since   1.6
     */
    public function &getConfig(): array
    {
        if (!empty($this->config)) {
            return $this->config;
        }

        $registry     = new Registry(new \JConfig());
        $this->config = $registry->toArray();
        $hidden       = [
            'host', 'user', 'password', 'ftp_user', 'ftp_pass',
            'smtpuser', 'smtppass', 'redis_server_auth', 'session_redis_server_auth',
            'proxy_user', 'proxy_pass', 'secret',
        ];

        foreach ($hidden as $key) {
            $this->config[$key] = 'xxxxxx';
        }

        return $this->config;
    }

    /**
     * Method to get the system information
     *
     * @return  array  System information values
     *
     * @since   1.6
     */
    public function &getInfo(): array
    {
        if (!empty($this->info)) {
            return $this->info;
        }

        $db = $this->getDatabase();

        $this->info = [
            'php'                    => php_uname(),
            'dbserver'               => $db->getServerType(),
            'dbversion'              => $db->getVersion(),
            'dbcollation'            => $db->getCollation(),
            'dbconnectioncollation'  => $db->getConnectionCollation(),
            'dbconnectionencryption' => $db->getConnectionEncryption(),
            'dbconnencryptsupported' => $db->isConnectionEncryptionSupported(),
            'phpversion'             => PHP_VERSION,
            'server'                 => $_SERVER['SERVER_SOFTWARE'] ?? getenv('SERVER_SOFTWARE'),
            'sapi_name'              => PHP_SAPI,
            'version'                => (new Version())->getLongVersion(),
            'useragent'              => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ];

        return $this->info;
    }

    /**
     * Check if the phpinfo function is enabled
     *
     * @return  boolean True if enabled
     *
     * @since   3.4.1
     */
    public function phpinfoEnabled(): bool
    {
        return !\in_array('phpinfo', explode(',', ini_get('disable_functions')));
    }

    /**
     * Method to get filter data from the model
     *
     * @param   string  $dataType  Type of data to get safely
     * @param   bool    $public    If true no sensitive information will be removed
     *
     * @return  array
     *
     * @since   3.5
     */
    public function getSafeData(string $dataType, bool $public = true): array
    {
        if (isset($this->safeData[$dataType])) {
            return $this->safeData[$dataType];
        }

        $methodName = 'get' . ucfirst($dataType);

        if (!method_exists($this, $methodName)) {
            return [];
        }

        $data = $this->$methodName($public);

        $this->safeData[$dataType] = $this->cleanPrivateData($data, $dataType);

        return $this->safeData[$dataType];
    }

    /**
     * Method to get the PHP info
     *
     * @return  string  PHP info
     *
     * @since   1.6
     */
    public function &getPHPInfo(): string
    {
        if (!$this->phpinfoEnabled()) {
            $this->php_info = Text::_('COM_ADMIN_PHPINFO_DISABLED');

            return $this->php_info;
        }

        if (!\is_null($this->php_info)) {
            return $this->php_info;
        }

        ob_start();
        date_default_timezone_set('UTC');
        phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES);
        $phpInfo = ob_get_contents();
        ob_end_clean();
        preg_match_all('#<body[^>]*>(.*)</body>#siU', $phpInfo, $output);
        $output         = preg_replace('#<table[^>]*>#', '<table class="table">', $output[1][0]);
        $output         = preg_replace('#(\w),(\w)#', '\1, \2', $output);
        $output         = preg_replace('#<hr />#', '', $output);
        $output         = str_replace('<div class="text-center">', '', $output);
        $output         = preg_replace('#<tr class="h">(.*)</tr>#', '<thead><tr class="h">$1</tr></thead><tbody>', $output);
        $output         = str_replace('</table>', '</tbody></table>', $output);
        $output         = str_replace('</div>', '', $output);
        $this->php_info = $output;

        return $this->php_info;
    }

    /**
     * Get phpinfo() output as array
     *
     * @return  array
     *
     * @since   3.5
     */
    public function getPhpInfoArray(): array
    {
        // Already cached
        if (null !== $this->phpInfoArray) {
            return $this->phpInfoArray;
        }

        $phpInfo = $this->getPHPInfo();

        $this->phpInfoArray = $this->parsePhpInfo($phpInfo);

        return $this->phpInfoArray;
    }

    /**
     * Method to get a list of installed extensions
     *
     * @return array installed extensions
     *
     * @since  3.5
     */
    public function getExtensions(): array
    {
        $installed = [];
        $db        = $this->getDatabase();
        $query     = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__extensions'));
        $db->setQuery($query);

        try {
            $extensions = $db->loadObjectList();
        } catch (\Exception $e) {
            try {
                Log::add(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), Log::WARNING, 'jerror');
            } catch (\RuntimeException $exception) {
                Factory::getApplication()->enqueueMessage(
                    Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                    'warning'
                );
            }

            return $installed;
        }

        if (empty($extensions)) {
            return $installed;
        }

        foreach ($extensions as $extension) {
            if (\strlen($extension->name) == 0) {
                continue;
            }

            $installed[$extension->name] = [
                'name'         => $extension->name,
                'type'         => $extension->type,
                'state'        => $extension->enabled ? Text::_('JENABLED') : Text::_('JDISABLED'),
                'author'       => 'unknown',
                'version'      => 'unknown',
                'creationDate' => 'unknown',
                'authorUrl'    => 'unknown',
            ];

            $manifest = new Registry($extension->manifest_cache);

            $extraData = [
                'author'       => $manifest->get('author', ''),
                'version'      => $manifest->get('version', ''),
                'creationDate' => $manifest->get('creationDate', ''),
                'authorUrl'    => $manifest->get('authorUrl', ''),
            ];

            $installed[$extension->name] = array_merge($installed[$extension->name], $extraData);
        }

        return $installed;
    }

    /**
     * Method to get the directory states
     *
     * @param   bool $public If true no information is going to be removed
     *
     * @return  array States of directories
     *
     * @throws \Exception
     * @since   1.6
     */
    public function getDirectory(bool $public = false): array
    {
        if (!empty($this->directories)) {
            return $this->directories;
        }

        $this->directories = [];

        $registry = Factory::getApplication()->getConfig();
        $cparams  = ComponentHelper::getParams('com_media');

        $this->addDirectory('administrator/components', JPATH_ADMINISTRATOR . '/components');
        $this->addDirectory('administrator/components/com_joomlaupdate', JPATH_ADMINISTRATOR . '/components/com_joomlaupdate');
        $this->addDirectory('administrator/language', JPATH_ADMINISTRATOR . '/language');

        // List all admin languages
        $admin_langs = new \DirectoryIterator(JPATH_ADMINISTRATOR . '/language');

        foreach ($admin_langs as $folder) {
            if ($folder->isDot() || !$folder->isDir()) {
                continue;
            }

            $this->addDirectory(
                'administrator/language/' . $folder->getFilename(),
                JPATH_ADMINISTRATOR . '/language/' . $folder->getFilename()
            );
        }

        // List all manifests folders
        $manifests = new \DirectoryIterator(JPATH_ADMINISTRATOR . '/manifests');

        foreach ($manifests as $folder) {
            if ($folder->isDot() || !$folder->isDir()) {
                continue;
            }

            $this->addDirectory(
                'administrator/manifests/' . $folder->getFilename(),
                JPATH_ADMINISTRATOR . '/manifests/' . $folder->getFilename()
            );
        }

        $this->addDirectory('administrator/modules', JPATH_ADMINISTRATOR . '/modules');
        $this->addDirectory('administrator/templates', JPATH_THEMES);

        $this->addDirectory('components', JPATH_SITE . '/components');

        $this->addDirectory($cparams->get('image_path'), JPATH_SITE . '/' . $cparams->get('image_path'));

        // List all images folders
        $image_folders = new \DirectoryIterator(JPATH_SITE . '/' . $cparams->get('image_path'));

        foreach ($image_folders as $folder) {
            if ($folder->isDot() || !$folder->isDir()) {
                continue;
            }

            $this->addDirectory(
                'images/' . $folder->getFilename(),
                JPATH_SITE . '/' . $cparams->get('image_path') . '/' . $folder->getFilename()
            );
        }

        $this->addDirectory('language', JPATH_SITE . '/language');

        // List all site languages
        $site_langs = new \DirectoryIterator(JPATH_SITE . '/language');

        foreach ($site_langs as $folder) {
            if ($folder->isDot() || !$folder->isDir()) {
                continue;
            }

            $this->addDirectory('language/' . $folder->getFilename(), JPATH_SITE . '/language/' . $folder->getFilename());
        }

        $this->addDirectory('libraries', JPATH_LIBRARIES);

        $this->addDirectory('media', JPATH_SITE . '/media');
        $this->addDirectory('modules', JPATH_SITE . '/modules');
        $this->addDirectory('plugins', JPATH_PLUGINS);

        $plugin_groups = new \DirectoryIterator(JPATH_SITE . '/plugins');

        foreach ($plugin_groups as $folder) {
            if ($folder->isDot() || !$folder->isDir()) {
                continue;
            }

            $this->addDirectory('plugins/' . $folder->getFilename(), JPATH_PLUGINS . '/' . $folder->getFilename());
        }

        $this->addDirectory('templates', JPATH_SITE . '/templates');
        $this->addDirectory('configuration.php', JPATH_CONFIGURATION . '/configuration.php');

        // Is there a cache path in configuration.php?
        if ($cache_path = trim($registry->get('cache_path', ''))) {
            // Frontend and backend use same directory for caching.
            $this->addDirectory($cache_path, $cache_path, 'COM_ADMIN_CACHE_DIRECTORY');
        } else {
            $this->addDirectory('administrator/cache', JPATH_CACHE, 'COM_ADMIN_CACHE_DIRECTORY');
        }

        $this->addDirectory('media/cache', JPATH_ROOT . '/media/cache', 'COM_ADMIN_MEDIA_CACHE_DIRECTORY');

        if ($public) {
            $this->addDirectory(
                'log',
                $registry->get('log_path', JPATH_ADMINISTRATOR . '/logs'),
                'COM_ADMIN_LOG_DIRECTORY'
            );
            $this->addDirectory(
                'tmp',
                $registry->get('tmp_path', JPATH_ROOT . '/tmp'),
                'COM_ADMIN_TEMP_DIRECTORY'
            );
        } else {
            $this->addDirectory(
                $registry->get('log_path', JPATH_ADMINISTRATOR . '/logs'),
                $registry->get('log_path', JPATH_ADMINISTRATOR . '/logs'),
                'COM_ADMIN_LOG_DIRECTORY'
            );
            $this->addDirectory(
                $registry->get('tmp_path', JPATH_ROOT . '/tmp'),
                $registry->get('tmp_path', JPATH_ROOT . '/tmp'),
                'COM_ADMIN_TEMP_DIRECTORY'
            );
        }

        return $this->directories;
    }

    /**
     * Method to add a directory
     *
     * @param   string  $name     Directory Name
     * @param   string  $path     Directory path
     * @param   string  $message  Message
     *
     * @return  void
     *
     * @since   1.6
     */
    private function addDirectory(string $name, string $path, string $message = ''): void
    {
        $this->directories[$name] = ['writable' => is_writable($path), 'message' => $message,];
    }

    /**
     * Method to get the editor
     *
     * @return  string  The default editor
     *
     * @note    Has to be removed (it is present in the config...)
     * @since   1.6
     */
    public function &getEditor(): string
    {
        if (!is_null($this->editor)) {
            return $this->editor;
        }

        $this->editor = Factory::getApplication()->get('editor');

        return $this->editor;
    }

    /**
     * Parse phpinfo output into an array
     * Source https://gist.github.com/sbmzhcn/6255314
     *
     * @param   string  $html  Output of phpinfo()
     *
     * @return  array
     *
     * @since   3.5
     */
    protected function parsePhpInfo(string $html): array
    {
        $html  = strip_tags($html, '<h2><th><td>');
        $html  = preg_replace('/<th[^>]*>([^<]+)<\/th>/', '<info>\1</info>', $html);
        $html  = preg_replace('/<td[^>]*>([^<]+)<\/td>/', '<info>\1</info>', $html);
        $t     = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $r     = [];
        $count = \count($t);
        $p1    = '<info>([^<]+)<\/info>';
        $p2    = '/' . $p1 . '\s*' . $p1 . '\s*' . $p1 . '/';
        $p3    = '/' . $p1 . '\s*' . $p1 . '/';

        for ($i = 1; $i < $count; $i++) {
            if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/', $t[$i], $matches)) {
                $name = trim($matches[1]);
                $vals = explode("\n", $t[$i + 1]);

                foreach ($vals as $val) {
                    // 3cols
                    if (preg_match($p2, $val, $matches)) {
                        $r[$name][trim($matches[1])] = [trim($matches[2]), trim($matches[3]),];
                    } elseif (preg_match($p3, $val, $matches)) {
                        // 2cols
                        $r[$name][trim($matches[1])] = trim($matches[2]);
                    }
                }
            }
        }

        return $r;
    }
}

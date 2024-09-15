<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extension Helper class.
 *
 * @since       3.7.4
 */
class ExtensionHelper
{
    /**
     * The loaded extensions.
     *
     * @var    array
     * @since  4.0.0
     */
    public static $extensions = [ModuleInterface::class => [], ComponentInterface::class => [], PluginInterface::class => []];

    /**
     * The loaded extensions.
     *
     * @var    array
     * @since  4.0.0
     */
    private static $loadedExtensions = [];

    /**
     * Array of core extensions
     * Each element is an array with elements "type", "element", "folder" and
     * "client_id".
     *
     * @var    array
     * @since  3.7.4
     */
    protected static $coreExtensions = [
        // Format: `type`, `element`, `folder`, `client_id`

        // Core component extensions
        ['component', 'com_actionlogs', '', 1],
        ['component', 'com_admin', '', 1],
        ['component', 'com_ajax', '', 1],
        ['component', 'com_associations', '', 1],
        ['component', 'com_banners', '', 1],
        ['component', 'com_cache', '', 1],
        ['component', 'com_categories', '', 1],
        ['component', 'com_checkin', '', 1],
        ['component', 'com_config', '', 1],
        ['component', 'com_contact', '', 1],
        ['component', 'com_content', '', 1],
        ['component', 'com_contenthistory', '', 1],
        ['component', 'com_cpanel', '', 1],
        ['component', 'com_fields', '', 1],
        ['component', 'com_finder', '', 1],
        ['component', 'com_guidedtours', '', 1],
        ['component', 'com_installer', '', 1],
        ['component', 'com_joomlaupdate', '', 1],
        ['component', 'com_languages', '', 1],
        ['component', 'com_login', '', 1],
        ['component', 'com_mails', '', 1],
        ['component', 'com_media', '', 1],
        ['component', 'com_menus', '', 1],
        ['component', 'com_messages', '', 1],
        ['component', 'com_modules', '', 1],
        ['component', 'com_newsfeeds', '', 1],
        ['component', 'com_plugins', '', 1],
        ['component', 'com_postinstall', '', 1],
        ['component', 'com_privacy', '', 1],
        ['component', 'com_redirect', '', 1],
        ['component', 'com_scheduler', '', 1],
        ['component', 'com_tags', '', 1],
        ['component', 'com_templates', '', 1],
        ['component', 'com_users', '', 1],
        ['component', 'com_workflow', '', 1],
        ['component', 'com_wrapper', '', 1],

        // Core file extensions
        ['file', 'joomla', '', 0],

        // Core language extensions - administrator
        ['language', 'en-GB', '', 1],

        // Core language extensions - site
        ['language', 'en-GB', '', 0],

        // Core language extensions - API
        ['language', 'en-GB', '', 3],

        // Core library extensions
        ['library', 'joomla', '', 0],
        ['library', 'phpass', '', 0],

        // Core module extensions - administrator
        ['module', 'mod_custom', '', 1],
        ['module', 'mod_feed', '', 1],
        ['module', 'mod_frontend', '', 1],
        ['module', 'mod_guidedtours', '', 1],
        ['module', 'mod_latest', '', 1],
        ['module', 'mod_latestactions', '', 1],
        ['module', 'mod_logged', '', 1],
        ['module', 'mod_login', '', 1],
        ['module', 'mod_loginsupport', '', 1],
        ['module', 'mod_menu', '', 1],
        ['module', 'mod_messages', '', 1],
        ['module', 'mod_multilangstatus', '', 1],
        ['module', 'mod_popular', '', 1],
        ['module', 'mod_post_installation_messages', '', 1],
        ['module', 'mod_privacy_dashboard', '', 1],
        ['module', 'mod_privacy_status', '', 1],
        ['module', 'mod_quickicon', '', 1],
        ['module', 'mod_sampledata', '', 1],
        ['module', 'mod_stats_admin', '', 1],
        ['module', 'mod_submenu', '', 1],
        ['module', 'mod_title', '', 1],
        ['module', 'mod_toolbar', '', 1],
        ['module', 'mod_user', '', 1],
        ['module', 'mod_version', '', 1],

        // Core module extensions - site
        ['module', 'mod_articles', '', 0],
        ['module', 'mod_articles_archive', '', 0],
        ['module', 'mod_articles_categories', '', 0],
        ['module', 'mod_articles_category', '', 0],
        ['module', 'mod_articles_latest', '', 0],
        ['module', 'mod_articles_news', '', 0],
        ['module', 'mod_articles_popular', '', 0],
        ['module', 'mod_banners', '', 0],
        ['module', 'mod_breadcrumbs', '', 0],
        ['module', 'mod_custom', '', 0],
        ['module', 'mod_feed', '', 0],
        ['module', 'mod_finder', '', 0],
        ['module', 'mod_footer', '', 0],
        ['module', 'mod_languages', '', 0],
        ['module', 'mod_login', '', 0],
        ['module', 'mod_menu', '', 0],
        ['module', 'mod_random_image', '', 0],
        ['module', 'mod_related_items', '', 0],
        ['module', 'mod_stats', '', 0],
        ['module', 'mod_syndicate', '', 0],
        ['module', 'mod_tags_popular', '', 0],
        ['module', 'mod_tags_similar', '', 0],
        ['module', 'mod_users_latest', '', 0],
        ['module', 'mod_whosonline', '', 0],
        ['module', 'mod_wrapper', '', 0],

        // Core package extensions
        ['package', 'pkg_en-GB', '', 0],

        // Core plugin extensions - actionlog
        ['plugin', 'joomla', 'actionlog', 0],

        // Core plugin extensions - API Authentication
        ['plugin', 'basic', 'api-authentication', 0],
        ['plugin', 'token', 'api-authentication', 0],

        // Core plugin extensions - authentication
        ['plugin', 'cookie', 'authentication', 0],
        ['plugin', 'joomla', 'authentication', 0],
        ['plugin', 'ldap', 'authentication', 0],

        // Core plugin extensions - behaviour
        ['plugin', 'compat', 'behaviour', 0],
        ['plugin', 'taggable', 'behaviour', 0],
        ['plugin', 'versionable', 'behaviour', 0],

        // Core plugin extensions - content
        ['plugin', 'confirmconsent', 'content', 0],
        ['plugin', 'contact', 'content', 0],
        ['plugin', 'emailcloak', 'content', 0],
        ['plugin', 'fields', 'content', 0],
        ['plugin', 'finder', 'content', 0],
        ['plugin', 'joomla', 'content', 0],
        ['plugin', 'loadmodule', 'content', 0],
        ['plugin', 'pagebreak', 'content', 0],
        ['plugin', 'pagenavigation', 'content', 0],
        ['plugin', 'vote', 'content', 0],

        // Core plugin extensions - editors
        ['plugin', 'codemirror', 'editors', 0],
        ['plugin', 'none', 'editors', 0],
        ['plugin', 'tinymce', 'editors', 0],

        // Core plugin extensions - editors xtd
        ['plugin', 'article', 'editors-xtd', 0],
        ['plugin', 'contact', 'editors-xtd', 0],
        ['plugin', 'fields', 'editors-xtd', 0],
        ['plugin', 'image', 'editors-xtd', 0],
        ['plugin', 'menu', 'editors-xtd', 0],
        ['plugin', 'module', 'editors-xtd', 0],
        ['plugin', 'pagebreak', 'editors-xtd', 0],
        ['plugin', 'readmore', 'editors-xtd', 0],

        // Core plugin extensions - extension
        ['plugin', 'joomla', 'extension', 0],
        ['plugin', 'namespacemap', 'extension', 0],
        ['plugin', 'finder', 'extension', 0],

        // Core plugin extensions - fields
        ['plugin', 'calendar', 'fields', 0],
        ['plugin', 'checkboxes', 'fields', 0],
        ['plugin', 'color', 'fields', 0],
        ['plugin', 'editor', 'fields', 0],
        ['plugin', 'imagelist', 'fields', 0],
        ['plugin', 'integer', 'fields', 0],
        ['plugin', 'list', 'fields', 0],
        ['plugin', 'media', 'fields', 0],
        ['plugin', 'radio', 'fields', 0],
        ['plugin', 'sql', 'fields', 0],
        ['plugin', 'subform', 'fields', 0],
        ['plugin', 'text', 'fields', 0],
        ['plugin', 'textarea', 'fields', 0],
        ['plugin', 'url', 'fields', 0],
        ['plugin', 'user', 'fields', 0],
        ['plugin', 'usergrouplist', 'fields', 0],

        // Core plugin extensions - filesystem
        ['plugin', 'local', 'filesystem', 0],

        // Core plugin extensions - finder
        ['plugin', 'categories', 'finder', 0],
        ['plugin', 'contacts', 'finder', 0],
        ['plugin', 'content', 'finder', 0],
        ['plugin', 'newsfeeds', 'finder', 0],
        ['plugin', 'tags', 'finder', 0],

        // Core plugin extensions - installer
        ['plugin', 'folderinstaller', 'installer', 0],
        ['plugin', 'override', 'installer', 0],
        ['plugin', 'packageinstaller', 'installer', 0],
        ['plugin', 'urlinstaller', 'installer', 0],
        ['plugin', 'webinstaller', 'installer', 0],

        // Core plugin extensions - media-action
        ['plugin', 'crop', 'media-action', 0],
        ['plugin', 'resize', 'media-action', 0],
        ['plugin', 'rotate', 'media-action', 0],

        // Core plugin extensions - Multi-factor Authentication
        ['plugin', 'email', 'multifactorauth', 0],
        ['plugin', 'fixed', 'multifactorauth', 0],
        ['plugin', 'totp', 'multifactorauth', 0],
        ['plugin', 'webauthn', 'multifactorauth', 0],
        ['plugin', 'yubikey', 'multifactorauth', 0],

        // Core plugin extensions - privacy
        ['plugin', 'actionlogs', 'privacy', 0],
        ['plugin', 'consents', 'privacy', 0],
        ['plugin', 'contact', 'privacy', 0],
        ['plugin', 'content', 'privacy', 0],
        ['plugin', 'message', 'privacy', 0],
        ['plugin', 'user', 'privacy', 0],

        // Core plugin extensions - quick icon
        ['plugin', 'downloadkey', 'quickicon', 0],
        ['plugin', 'extensionupdate', 'quickicon', 0],
        ['plugin', 'joomlaupdate', 'quickicon', 0],
        ['plugin', 'overridecheck', 'quickicon', 0],
        ['plugin', 'phpversioncheck', 'quickicon', 0],
        ['plugin', 'privacycheck', 'quickicon', 0],
        ['plugin', 'eos', 'quickicon', 0],

        // Core plugin extensions - sample data
        ['plugin', 'blog', 'sampledata', 0],
        ['plugin', 'multilang', 'sampledata', 0],

        // Core plugin extensions - schemaorg
        ['plugin', 'article', 'schemaorg', 0],
        ['plugin', 'blogposting', 'schemaorg', 0],
        ['plugin', 'book', 'schemaorg', 0],
        ['plugin', 'custom', 'schemaorg', 0],
        ['plugin', 'event', 'schemaorg', 0],
        ['plugin', 'jobposting', 'schemaorg', 0],
        ['plugin', 'organization', 'schemaorg', 0],
        ['plugin', 'person', 'schemaorg', 0],
        ['plugin', 'recipe', 'schemaorg', 0],

        // Core plugin extensions - system
        ['plugin', 'accessibility', 'system', 0],
        ['plugin', 'actionlogs', 'system', 0],
        ['plugin', 'cache', 'system', 0],
        ['plugin', 'debug', 'system', 0],
        ['plugin', 'fields', 'system', 0],
        ['plugin', 'guidedtours', 'system', 0],
        ['plugin', 'highlight', 'system', 0],
        ['plugin', 'httpheaders', 'system', 0],
        ['plugin', 'jooa11y', 'system', 0],
        ['plugin', 'languagecode', 'system', 0],
        ['plugin', 'languagefilter', 'system', 0],
        ['plugin', 'log', 'system', 0],
        ['plugin', 'logout', 'system', 0],
        ['plugin', 'privacyconsent', 'system', 0],
        ['plugin', 'redirect', 'system', 0],
        ['plugin', 'remember', 'system', 0],
        ['plugin', 'schedulerunner', 'system', 0],
        ['plugin', 'schemaorg', 'system', 0],
        ['plugin', 'sef', 'system', 0],
        ['plugin', 'shortcut', 'system', 0],
        ['plugin', 'skipto', 'system', 0],
        ['plugin', 'stats', 'system', 0],
        ['plugin', 'tasknotification', 'system', 0],
        ['plugin', 'webauthn', 'system', 0],

        // Core plugin extensions - task scheduler
        ['plugin', 'checkfiles', 'task', 0],
        ['plugin', 'deleteactionlogs', 'task', 0],
        ['plugin', 'globalcheckin', 'task', 0],
        ['plugin', 'privacyconsent', 'task', 0],
        ['plugin', 'requests', 'task', 0],
        ['plugin', 'rotatelogs', 'task', 0],
        ['plugin', 'sessiongc', 'task', 0],
        ['plugin', 'sitestatus', 'task', 0],
        ['plugin', 'updatenotification', 'task', 0],

        // Core plugin extensions - user
        ['plugin', 'contactcreator', 'user', 0],
        ['plugin', 'joomla', 'user', 0],
        ['plugin', 'profile', 'user', 0],
        ['plugin', 'terms', 'user', 0],
        ['plugin', 'token', 'user', 0],

        // Core plugin extensions - webservices
        ['plugin', 'banners', 'webservices', 0],
        ['plugin', 'config', 'webservices', 0],
        ['plugin', 'contact', 'webservices', 0],
        ['plugin', 'content', 'webservices', 0],
        ['plugin', 'installer', 'webservices', 0],
        ['plugin', 'languages', 'webservices', 0],
        ['plugin', 'media', 'webservices', 0],
        ['plugin', 'menus', 'webservices', 0],
        ['plugin', 'messages', 'webservices', 0],
        ['plugin', 'modules', 'webservices', 0],
        ['plugin', 'newsfeeds', 'webservices', 0],
        ['plugin', 'plugins', 'webservices', 0],
        ['plugin', 'privacy', 'webservices', 0],
        ['plugin', 'redirect', 'webservices', 0],
        ['plugin', 'tags', 'webservices', 0],
        ['plugin', 'templates', 'webservices', 0],
        ['plugin', 'users', 'webservices', 0],

        // Core plugin extensions - workflow
        ['plugin', 'featuring', 'workflow', 0],
        ['plugin', 'notification', 'workflow', 0],
        ['plugin', 'publishing', 'workflow', 0],

        // Core template extensions - administrator
        ['template', 'atum', '', 1],

        // Core template extensions - site
        ['template', 'cassiopeia', '', 0],
    ];

    /**
     * Array of core extension IDs.
     *
     * @var    array
     * @since  4.0.0
     */
    protected static $coreExtensionIds;

    /**
     * Gets the core extensions.
     *
     * @return  array  Array with core extensions.
     *                 Each extension is an array with following format:
     *                 `type`, `element`, `folder`, `client_id`.
     *
     * @since   3.7.4
     */
    public static function getCoreExtensions()
    {
        return self::$coreExtensions;
    }

    /**
     * Returns an array of core extension IDs.
     *
     * @return  array
     *
     * @since   4.0.0
     * @throws  \RuntimeException
     */
    public static function getCoreExtensionIds()
    {
        if (self::$coreExtensionIds !== null) {
            return self::$coreExtensionIds;
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'));

        $values = [];

        foreach (self::$coreExtensions as $extension) {
            $values[] = $extension[0] . '|' . $extension[1] . '|' . $extension[2] . '|' . $extension[3];
        }

        $query->whereIn(
            $query->concatenate(
                [
                    $db->quoteName('type'),
                    $db->quoteName('element'),
                    $db->quoteName('folder'),
                    $db->quoteName('client_id'),
                ],
                '|'
            ),
            $values,
            ParameterType::STRING
        );

        $db->setQuery($query);
        self::$coreExtensionIds = $db->loadColumn();

        return self::$coreExtensionIds;
    }

    /**
     * Check if an extension is core or not
     *
     * @param   string   $type      The extension's type.
     * @param   string   $element   The extension's element name.
     * @param   integer  $clientId  The extension's client ID. Default 0.
     * @param   string   $folder    The extension's folder. Default ''.
     *
     * @return  boolean  True if core, false if not.
     *
     * @since   3.7.4
     */
    public static function checkIfCoreExtension($type, $element, $clientId = 0, $folder = '')
    {
        return \in_array([$type, $element, $folder, $clientId], self::$coreExtensions);
    }

    /**
     * Returns an extension record for the given name.
     *
     * @param   string        $element   The extension element
     * @param   string        $type      The extension type
     * @param   integer|null  $clientId  The client ID
     * @param   string|null   $folder    Plugin folder
     *
     * @return  \stdClass|null  The object or null if not found.
     *
     * @since   4.0.0
     * @throws  \InvalidArgumentException
     */
    public static function getExtensionRecord(string $element, string $type, ?int $clientId = null, ?string $folder = null): ?\stdClass
    {
        if ($type === 'plugin' && $folder === null) {
            throw new \InvalidArgumentException(\sprintf('`$folder` is required when `$type` is `plugin` in %s()', __METHOD__));
        }

        if (\in_array($type, ['module', 'language', 'template'], true) && $clientId === null) {
            throw new \InvalidArgumentException(
                \sprintf('`$clientId` is required when `$type` is `module`, `language` or `template` in %s()', __METHOD__)
            );
        }

        $key = $element . '.' . $type . '.' . $clientId . '.' . $folder;

        if (!\array_key_exists($key, self::$loadedExtensions)) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__extensions'))
                ->where(
                    [
                        $db->quoteName('element') . ' = :element',
                        $db->quoteName('type') . ' = :type',
                    ]
                )
                ->bind(':element', $element)
                ->bind(':type', $type);

            if ($clientId !== null) {
                $query->where($db->quoteName('client_id') . ' = :clientId')
                    ->bind(':clientId', $clientId, ParameterType::INTEGER);
            }

            if ($folder !== null) {
                $query->where($db->quoteName('folder') . ' = :folder')
                    ->bind(':folder', $folder);
            }

            $query->setLimit(1);
            $db->setQuery($query);

            self::$loadedExtensions[$key] = $db->loadObject();
        }

        return self::$loadedExtensions[$key];
    }
}

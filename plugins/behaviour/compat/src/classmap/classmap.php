<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
\defined('_JEXEC') or die;

require_once __DIR__ . '/extensions.classmap.php';

JLoader::registerAlias('JRegistry', '\\Joomla\\Registry\\Registry', '6.0');
JLoader::registerAlias('JRegistryFormatIni', '\\Joomla\\Registry\\Format\\Ini', '6.0');
JLoader::registerAlias('JRegistryFormatJson', '\\Joomla\\Registry\\Format\\Json', '6.0');
JLoader::registerAlias('JRegistryFormatPhp', '\\Joomla\\Registry\\Format\\Php', '6.0');
JLoader::registerAlias('JRegistryFormatXml', '\\Joomla\\Registry\\Format\\Xml', '6.0');
JLoader::registerAlias('JStringInflector', '\\Joomla\\String\\Inflector', '6.0');
JLoader::registerAlias('JStringNormalise', '\\Joomla\\String\\Normalise', '6.0');
JLoader::registerAlias('JData', '\\Joomla\\Data\\DataObject', '6.0');
JLoader::registerAlias('JDataSet', '\\Joomla\\Data\\DataSet', '6.0');
JLoader::registerAlias('JDataDumpable', '\\Joomla\\Data\\DumpableInterface', '6.0');

JLoader::registerAlias('JApplicationAdministrator', '\\Joomla\\CMS\\Application\\AdministratorApplication', '6.0');
JLoader::registerAlias('JApplicationHelper', '\\Joomla\\CMS\\Application\\ApplicationHelper', '6.0');
JLoader::registerAlias('JApplicationBase', '\\Joomla\\CMS\\Application\\BaseApplication', '6.0');
JLoader::registerAlias('JApplicationCli', '\\Joomla\\CMS\\Application\\CliApplication', '6.0');
JLoader::registerAlias('JApplicationCms', '\\Joomla\\CMS\\Application\\CMSApplication', '6.0');
JLoader::registerAlias('JApplicationDaemon', '\\Joomla\\CMS\\Application\\DaemonApplication', '6.0');
JLoader::registerAlias('JApplicationSite', '\\Joomla\\CMS\\Application\\SiteApplication', '6.0');
JLoader::registerAlias('JApplicationWeb', '\\Joomla\\CMS\\Application\\WebApplication', '6.0');
JLoader::registerAlias('JApplicationWebClient', '\\Joomla\\Application\\Web\\WebClient', '6.0');
JLoader::registerAlias('JDaemon', '\\Joomla\\CMS\\Application\\DaemonApplication', '6.0');
JLoader::registerAlias('JCli', '\\Joomla\\CMS\\Application\\CliApplication', '6.0');
JLoader::registerAlias('JWeb', '\\Joomla\\CMS\\Application\\WebApplication', '4.0');
JLoader::registerAlias('JWebClient', '\\Joomla\\Application\\Web\\WebClient', '4.0');

JLoader::registerAlias('JModelAdmin', '\\Joomla\\CMS\\MVC\\Model\\AdminModel', '6.0');
JLoader::registerAlias('JModelForm', '\\Joomla\\CMS\\MVC\\Model\\FormModel', '6.0');
JLoader::registerAlias('JModelItem', '\\Joomla\\CMS\\MVC\\Model\\ItemModel', '6.0');
JLoader::registerAlias('JModelList', '\\Joomla\\CMS\\MVC\\Model\\ListModel', '6.0');
JLoader::registerAlias('JModelLegacy', '\\Joomla\\CMS\\MVC\\Model\\BaseDatabaseModel', '6.0');
JLoader::registerAlias('JViewCategories', '\\Joomla\\CMS\\MVC\\View\\CategoriesView', '6.0');
JLoader::registerAlias('JViewCategory', '\\Joomla\\CMS\\MVC\\View\\CategoryView', '6.0');
JLoader::registerAlias('JViewCategoryfeed', '\\Joomla\\CMS\\MVC\\View\\CategoryFeedView', '6.0');
JLoader::registerAlias('JViewLegacy', '\\Joomla\\CMS\\MVC\\View\\HtmlView', '6.0');
JLoader::registerAlias('JControllerAdmin', '\\Joomla\\CMS\\MVC\\Controller\\AdminController', '6.0');
JLoader::registerAlias('JControllerLegacy', '\\Joomla\\CMS\\MVC\\Controller\\BaseController', '6.0');
JLoader::registerAlias('JControllerForm', '\\Joomla\\CMS\\MVC\\Controller\\FormController', '6.0');
JLoader::registerAlias('JTableInterface', '\\Joomla\\CMS\\Table\\TableInterface', '6.0');
JLoader::registerAlias('JTable', '\\Joomla\\CMS\\Table\\Table', '6.0');
JLoader::registerAlias('JTableNested', '\\Joomla\\CMS\\Table\\Nested', '6.0');
JLoader::registerAlias('JTableAsset', '\\Joomla\\CMS\\Table\\Asset', '6.0');
JLoader::registerAlias('JTableExtension', '\\Joomla\\CMS\\Table\\Extension', '6.0');
JLoader::registerAlias('JTableLanguage', '\\Joomla\\CMS\\Table\\Language', '6.0');
JLoader::registerAlias('JTableUpdate', '\\Joomla\\CMS\\Table\\Update', '6.0');
JLoader::registerAlias('JTableUpdatesite', '\\Joomla\\CMS\\Table\\UpdateSite', '6.0');
JLoader::registerAlias('JTableUser', '\\Joomla\\CMS\\Table\\User', '6.0');
JLoader::registerAlias('JTableUsergroup', '\\Joomla\\CMS\\Table\\Usergroup', '6.0');
JLoader::registerAlias('JTableViewlevel', '\\Joomla\\CMS\\Table\\ViewLevel', '6.0');
JLoader::registerAlias('JTableContenthistory', '\\Joomla\\CMS\\Table\\ContentHistory', '6.0');
JLoader::registerAlias('JTableContenttype', '\\Joomla\\CMS\\Table\\ContentType', '6.0');
JLoader::registerAlias('JTableCorecontent', '\\Joomla\\CMS\\Table\\CoreContent', '6.0');
JLoader::registerAlias('JTableUcm', '\\Joomla\\CMS\\Table\\Ucm', '6.0');
JLoader::registerAlias('JTableCategory', '\\Joomla\\CMS\\Table\\Category', '6.0');
JLoader::registerAlias('JTableContent', '\\Joomla\\CMS\\Table\\Content', '6.0');
JLoader::registerAlias('JTableMenu', '\\Joomla\\CMS\\Table\\Menu', '6.0');
JLoader::registerAlias('JTableMenuType', '\\Joomla\\CMS\\Table\\MenuType', '6.0');
JLoader::registerAlias('JTableModule', '\\Joomla\\CMS\\Table\\Module', '6.0');

JLoader::registerAlias('JAccess', '\\Joomla\\CMS\\Access\\Access', '6.0');
JLoader::registerAlias('JAccessRule', '\\Joomla\\CMS\\Access\\Rule', '6.0');
JLoader::registerAlias('JAccessRules', '\\Joomla\\CMS\\Access\\Rules', '6.0');
JLoader::registerAlias('JAccessExceptionNotallowed', '\\Joomla\\CMS\\Access\\Exception\\NotAllowed', '6.0');
JLoader::registerAlias('JRule', '\\Joomla\\CMS\\Access\\Rule', '6.0');
JLoader::registerAlias('JRules', '\\Joomla\\CMS\\Access\\Rules', '6.0');

JLoader::registerAlias('JHelp', '\\Joomla\\CMS\\Help\\Help', '6.0');
JLoader::registerAlias('JCaptcha', '\\Joomla\\CMS\\Captcha\\Captcha', '6.0');

JLoader::registerAlias('JLanguageAssociations', '\\Joomla\\CMS\\Language\\Associations', '6.0');
JLoader::registerAlias('JLanguage', '\\Joomla\\CMS\\Language\\Language', '6.0');
JLoader::registerAlias('JLanguageHelper', '\\Joomla\\CMS\\Language\\LanguageHelper', '6.0');
JLoader::registerAlias('JLanguageMultilang', '\\Joomla\\CMS\\Language\\Multilanguage', '6.0');
JLoader::registerAlias('JText', '\\Joomla\\CMS\\Language\\Text', '6.0');
JLoader::registerAlias('JLanguageTransliterate', '\\Joomla\\CMS\\Language\\Transliterate', '6.0');

JLoader::registerAlias('JComponentHelper', '\\Joomla\\CMS\\Component\\ComponentHelper', '6.0');
JLoader::registerAlias('JComponentRecord', '\\Joomla\\CMS\\Component\\ComponentRecord', '6.0');
JLoader::registerAlias('JComponentExceptionMissing', '\\Joomla\\CMS\\Component\\Exception\\MissingComponentException', '6.0');
JLoader::registerAlias('JComponentRouterBase', '\\Joomla\\CMS\\Component\\Router\\RouterBase', '6.0');
JLoader::registerAlias('JComponentRouterInterface', '\\Joomla\\CMS\\Component\\Router\\RouterInterface', '6.0');
JLoader::registerAlias('JComponentRouterLegacy', '\\Joomla\\CMS\\Component\\Router\\RouterLegacy', '6.0');
JLoader::registerAlias('JComponentRouterView', '\\Joomla\\CMS\\Component\\Router\\RouterView', '6.0');
JLoader::registerAlias('JComponentRouterViewconfiguration', '\\Joomla\\CMS\\Component\\Router\\RouterViewConfiguration', '6.0');
JLoader::registerAlias('JComponentRouterRulesMenu', '\\Joomla\\CMS\\Component\\Router\\Rules\\MenuRules', '6.0');
JLoader::registerAlias('JComponentRouterRulesNomenu', '\\Joomla\\CMS\\Component\\Router\\Rules\\NomenuRules', '6.0');
JLoader::registerAlias('JComponentRouterRulesInterface', '\\Joomla\\CMS\\Component\\Router\\Rules\\RulesInterface', '6.0');
JLoader::registerAlias('JComponentRouterRulesStandard', '\\Joomla\\CMS\\Component\\Router\\Rules\\StandardRules', '6.0');

JLoader::registerAlias('JEditor', '\\Joomla\\CMS\\Editor\\Editor', '6.0');

JLoader::registerAlias('JErrorPage', '\\Joomla\\CMS\\Exception\\ExceptionHandler', '6.0');

JLoader::registerAlias('JAuthenticationHelper', '\\Joomla\\CMS\\Helper\\AuthenticationHelper', '6.0');
JLoader::registerAlias('JHelper', '\\Joomla\\CMS\\Helper\\CMSHelper', '6.0');
JLoader::registerAlias('JHelperContent', '\\Joomla\\CMS\\Helper\\ContentHelper', '6.0');
JLoader::registerAlias('JLibraryHelper', '\\Joomla\\CMS\\Helper\\LibraryHelper', '6.0');
JLoader::registerAlias('JHelperMedia', '\\Joomla\\CMS\\Helper\\MediaHelper', '6.0');
JLoader::registerAlias('JModuleHelper', '\\Joomla\\CMS\\Helper\\ModuleHelper', '6.0');
JLoader::registerAlias('JHelperRoute', '\\Joomla\\CMS\\Helper\\RouteHelper', '6.0');
JLoader::registerAlias('JHelperTags', '\\Joomla\\CMS\\Helper\\TagsHelper', '6.0');
JLoader::registerAlias('JHelperUsergroups', '\\Joomla\\CMS\\Helper\\UserGroupsHelper', '6.0');

JLoader::registerAlias('JLayoutBase', '\\Joomla\\CMS\\Layout\\BaseLayout', '6.0');
JLoader::registerAlias('JLayoutFile', '\\Joomla\\CMS\\Layout\\FileLayout', '6.0');
JLoader::registerAlias('JLayoutHelper', '\\Joomla\\CMS\\Layout\\LayoutHelper', '6.0');
JLoader::registerAlias('JLayout', '\\Joomla\\CMS\\Layout\\LayoutInterface', '6.0');

JLoader::registerAlias('JResponseJson', '\\Joomla\\CMS\\Response\\JsonResponse', '6.0');

JLoader::registerAlias('JPlugin', '\\Joomla\\CMS\\Plugin\\CMSPlugin', '6.0');
JLoader::registerAlias('JPluginHelper', '\\Joomla\\CMS\\Plugin\\PluginHelper', '6.0');

JLoader::registerAlias('JMenu', '\\Joomla\\CMS\\Menu\\AbstractMenu', '6.0');
JLoader::registerAlias('JMenuAdministrator', '\\Joomla\\CMS\\Menu\\AdministratorMenu', '6.0');
JLoader::registerAlias('JMenuItem', '\\Joomla\\CMS\\Menu\\MenuItem', '6.0');
JLoader::registerAlias('JMenuSite', '\\Joomla\\CMS\\Menu\\SiteMenu', '6.0');

JLoader::registerAlias('JPagination', '\\Joomla\\CMS\\Pagination\\Pagination', '6.0');
JLoader::registerAlias('JPaginationObject', '\\Joomla\\CMS\\Pagination\\PaginationObject', '6.0');

JLoader::registerAlias('JPathway', '\\Joomla\\CMS\\Pathway\\Pathway', '6.0');
JLoader::registerAlias('JPathwaySite', '\\Joomla\\CMS\\Pathway\\SitePathway', '6.0');

JLoader::registerAlias('JSchemaChangeitem', '\\Joomla\\CMS\\Schema\\ChangeItem', '6.0');
JLoader::registerAlias('JSchemaChangeset', '\\Joomla\\CMS\\Schema\\ChangeSet', '6.0');
JLoader::registerAlias('JSchemaChangeitemMysql', '\\Joomla\\CMS\\Schema\\ChangeItem\\MysqlChangeItem', '6.0');
JLoader::registerAlias('JSchemaChangeitemPostgresql', '\\Joomla\\CMS\\Schema\\ChangeItem\\PostgresqlChangeItem', '6.0');

JLoader::registerAlias('JUcm', '\\Joomla\\CMS\\UCM\\UCM', '6.0');
JLoader::registerAlias('JUcmBase', '\\Joomla\\CMS\\UCM\\UCMBase', '6.0');
JLoader::registerAlias('JUcmContent', '\\Joomla\\CMS\\UCM\\UCMContent', '6.0');
JLoader::registerAlias('JUcmType', '\\Joomla\\CMS\\UCM\\UCMType', '6.0');

JLoader::registerAlias('JToolbar', '\\Joomla\\CMS\\Toolbar\\Toolbar', '6.0');
JLoader::registerAlias('JToolbarButton', '\\Joomla\\CMS\\Toolbar\\ToolbarButton', '6.0');
JLoader::registerAlias('JToolbarButtonConfirm', '\\Joomla\\CMS\\Toolbar\\Button\\ConfirmButton', '6.0');
JLoader::registerAlias('JToolbarButtonCustom', '\\Joomla\\CMS\\Toolbar\\Button\\CustomButton', '6.0');
JLoader::registerAlias('JToolbarButtonHelp', '\\Joomla\\CMS\\Toolbar\\Button\\HelpButton', '6.0');
JLoader::registerAlias('JToolbarButtonLink', '\\Joomla\\CMS\\Toolbar\\Button\\LinkButton', '6.0');
JLoader::registerAlias('JToolbarButtonPopup', '\\Joomla\\CMS\\Toolbar\\Button\\PopupButton', '6.0');
JLoader::registerAlias('JToolbarButtonSeparator', '\\Joomla\\CMS\\Toolbar\\Button\\SeparatorButton', '6.0');
JLoader::registerAlias('JToolbarButtonStandard', '\\Joomla\\CMS\\Toolbar\\Button\\StandardButton', '6.0');
JLoader::registerAlias('JToolbarHelper', '\\Joomla\\CMS\\Toolbar\\ToolbarHelper', '6.0');
JLoader::registerAlias('JButton', '\\Joomla\\CMS\\Toolbar\\ToolbarButton', '6.0');

JLoader::registerAlias('JVersion', '\\Joomla\\CMS\\Version', '6.0');

JLoader::registerAlias('JAuthentication', '\\Joomla\\CMS\\Authentication\\Authentication', '6.0');
JLoader::registerAlias('JAuthenticationResponse', '\\Joomla\\CMS\\Authentication\\AuthenticationResponse', '6.0');

JLoader::registerAlias('JBrowser', '\\Joomla\\CMS\\Environment\\Browser', '6.0');

JLoader::registerAlias('JAssociationExtensionInterface', '\\Joomla\\CMS\\Association\\AssociationExtensionInterface', '6.0');
JLoader::registerAlias('JAssociationExtensionHelper', '\\Joomla\\CMS\\Association\\AssociationExtensionHelper', '6.0');

JLoader::registerAlias('JDocument', '\\Joomla\\CMS\\Document\\Document', '6.0');
JLoader::registerAlias('JDocumentError', '\\Joomla\\CMS\\Document\\ErrorDocument', '6.0');
JLoader::registerAlias('JDocumentFeed', '\\Joomla\\CMS\\Document\\FeedDocument', '6.0');
JLoader::registerAlias('JDocumentHtml', '\\Joomla\\CMS\\Document\\HtmlDocument', '6.0');
JLoader::registerAlias('JDocumentImage', '\\Joomla\\CMS\\Document\\ImageDocument', '6.0');
JLoader::registerAlias('JDocumentJson', '\\Joomla\\CMS\\Document\\JsonDocument', '6.0');
JLoader::registerAlias('JDocumentOpensearch', '\\Joomla\\CMS\\Document\\OpensearchDocument', '6.0');
JLoader::registerAlias('JDocumentRaw', '\\Joomla\\CMS\\Document\\RawDocument', '6.0');
JLoader::registerAlias('JDocumentRenderer', '\\Joomla\\CMS\\Document\\DocumentRenderer', '6.0');
JLoader::registerAlias('JDocumentXml', '\\Joomla\\CMS\\Document\\XmlDocument', '6.0');
JLoader::registerAlias('JDocumentRendererFeedAtom', '\\Joomla\\CMS\\Document\\Renderer\\Feed\\AtomRenderer', '6.0');
JLoader::registerAlias('JDocumentRendererFeedRss', '\\Joomla\\CMS\\Document\\Renderer\\Feed\\RssRenderer', '6.0');
JLoader::registerAlias('JDocumentRendererHtmlComponent', '\\Joomla\\CMS\\Document\\Renderer\\Html\\ComponentRenderer', '6.0');
JLoader::registerAlias('JDocumentRendererHtmlHead', '\\Joomla\\CMS\\Document\\Renderer\\Html\\HeadRenderer', '6.0');
JLoader::registerAlias('JDocumentRendererHtmlMessage', '\\Joomla\\CMS\\Document\\Renderer\\Html\\MessageRenderer', '6.0');
JLoader::registerAlias('JDocumentRendererHtmlModule', '\\Joomla\\CMS\\Document\\Renderer\\Html\\ModuleRenderer', '6.0');
JLoader::registerAlias('JDocumentRendererHtmlModules', '\\Joomla\\CMS\\Document\\Renderer\\Html\\ModulesRenderer', '6.0');
JLoader::registerAlias('JDocumentRendererAtom', '\\Joomla\\CMS\\Document\\Renderer\\Feed\\AtomRenderer', '4.0');
JLoader::registerAlias('JDocumentRendererRSS', '\\Joomla\\CMS\\Document\\Renderer\\Feed\\RssRenderer', '4.0');
JLoader::registerAlias('JDocumentRendererComponent', '\\Joomla\\CMS\\Document\\Renderer\\Html\\ComponentRenderer', '4.0');
JLoader::registerAlias('JDocumentRendererHead', '\\Joomla\\CMS\\Document\\Renderer\\Html\\HeadRenderer', '4.0');
JLoader::registerAlias('JDocumentRendererMessage', '\\Joomla\\CMS\\Document\\Renderer\\Html\\MessageRenderer', '4.0');
JLoader::registerAlias('JDocumentRendererModule', '\\Joomla\\CMS\\Document\\Renderer\\Html\\ModuleRenderer', '4.0');
JLoader::registerAlias('JDocumentRendererModules', '\\Joomla\\CMS\\Document\\Renderer\\Html\\ModulesRenderer', '4.0');
JLoader::registerAlias('JFeedEnclosure', '\\Joomla\\CMS\\Document\\Feed\\FeedEnclosure', '6.0');
JLoader::registerAlias('JFeedImage', '\\Joomla\\CMS\\Document\\Feed\\FeedImage', '6.0');
JLoader::registerAlias('JFeedItem', '\\Joomla\\CMS\\Document\\Feed\\FeedItem', '6.0');
JLoader::registerAlias('JOpenSearchImage', '\\Joomla\\CMS\\Document\\Opensearch\\OpensearchImage', '6.0');
JLoader::registerAlias('JOpenSearchUrl', '\\Joomla\\CMS\\Document\\Opensearch\\OpensearchUrl', '6.0');

JLoader::registerAlias('JFilterInput', '\\Joomla\\CMS\\Filter\\InputFilter', '6.0');
JLoader::registerAlias('JFilterOutput', '\\Joomla\\CMS\\Filter\\OutputFilter', '6.0');

JLoader::registerAlias('JHttp', '\\Joomla\\CMS\\Http\\Http', '6.0');
JLoader::registerAlias('JHttpFactory', '\\Joomla\\CMS\\Http\\HttpFactory', '6.0');
JLoader::registerAlias('JHttpResponse', '\\Joomla\\CMS\\Http\\Response', '6.0');
JLoader::registerAlias('JHttpTransport', '\\Joomla\\CMS\\Http\\TransportInterface', '6.0');
JLoader::registerAlias('JHttpTransportCurl', '\\Joomla\\CMS\\Http\\Transport\\CurlTransport', '6.0');
JLoader::registerAlias('JHttpTransportSocket', '\\Joomla\\CMS\\Http\\Transport\\SocketTransport', '6.0');
JLoader::registerAlias('JHttpTransportStream', '\\Joomla\\CMS\\Http\\Transport\\StreamTransport', '6.0');

JLoader::registerAlias('JInstaller', '\\Joomla\\CMS\\Installer\\Installer', '6.0');
JLoader::registerAlias('JInstallerAdapter', '\\Joomla\\CMS\\Installer\\InstallerAdapter', '6.0');
JLoader::registerAlias('JInstallerExtension', '\\Joomla\\CMS\\Installer\\InstallerExtension', '6.0');
JLoader::registerAlias('JExtension', '\\Joomla\\CMS\\Installer\\InstallerExtension', '6.0');
JLoader::registerAlias('JInstallerHelper', '\\Joomla\\CMS\\Installer\\InstallerHelper', '6.0');
JLoader::registerAlias('JInstallerScript', '\\Joomla\\CMS\\Installer\\InstallerScript', '6.0');
JLoader::registerAlias('JInstallerManifest', '\\Joomla\\CMS\\Installer\\Manifest', '6.0');
JLoader::registerAlias('JInstallerAdapterComponent', '\\Joomla\\CMS\\Installer\\Adapter\\ComponentAdapter', '6.0');
JLoader::registerAlias('JInstallerComponent', '\\Joomla\\CMS\\Installer\\Adapter\\ComponentAdapter', '6.0');
JLoader::registerAlias('JInstallerAdapterFile', '\\Joomla\\CMS\\Installer\\Adapter\\FileAdapter', '6.0');
JLoader::registerAlias('JInstallerFile', '\\Joomla\\CMS\\Installer\\Adapter\\FileAdapter', '6.0');
JLoader::registerAlias('JInstallerAdapterLanguage', '\\Joomla\\CMS\\Installer\\Adapter\\LanguageAdapter', '6.0');
JLoader::registerAlias('JInstallerLanguage', '\\Joomla\\CMS\\Installer\\Adapter\\LanguageAdapter', '6.0');
JLoader::registerAlias('JInstallerAdapterLibrary', '\\Joomla\\CMS\\Installer\\Adapter\\LibraryAdapter', '6.0');
JLoader::registerAlias('JInstallerLibrary', '\\Joomla\\CMS\\Installer\\Adapter\\LibraryAdapter', '6.0');
JLoader::registerAlias('JInstallerAdapterModule', '\\Joomla\\CMS\\Installer\\Adapter\\ModuleAdapter', '6.0');
JLoader::registerAlias('JInstallerModule', '\\Joomla\\CMS\\Installer\\Adapter\\ModuleAdapter', '6.0');
JLoader::registerAlias('JInstallerAdapterPackage', '\\Joomla\\CMS\\Installer\\Adapter\\PackageAdapter', '6.0');
JLoader::registerAlias('JInstallerPackage', '\\Joomla\\CMS\\Installer\\Adapter\\PackageAdapter', '6.0');
JLoader::registerAlias('JInstallerAdapterPlugin', '\\Joomla\\CMS\\Installer\\Adapter\\PluginAdapter', '6.0');
JLoader::registerAlias('JInstallerPlugin', '\\Joomla\\CMS\\Installer\\Adapter\\PluginAdapter', '6.0');
JLoader::registerAlias('JInstallerAdapterTemplate', '\\Joomla\\CMS\\Installer\\Adapter\\TemplateAdapter', '6.0');
JLoader::registerAlias('JInstallerTemplate', '\\Joomla\\CMS\\Installer\\Adapter\\TemplateAdapter', '6.0');
JLoader::registerAlias('JInstallerManifestLibrary', '\\Joomla\\CMS\\Installer\\Manifest\\LibraryManifest', '6.0');
JLoader::registerAlias('JInstallerManifestPackage', '\\Joomla\\CMS\\Installer\\Manifest\\PackageManifest', '6.0');

JLoader::registerAlias('JRouterAdministrator', '\\Joomla\\CMS\\Router\\AdministratorRouter', '6.0');
JLoader::registerAlias('JRoute', '\\Joomla\\CMS\\Router\\Route', '6.0');
JLoader::registerAlias('JRouter', '\\Joomla\\CMS\\Router\\Router', '6.0');
JLoader::registerAlias('JRouterSite', '\\Joomla\\CMS\\Router\\SiteRouter', '6.0');

JLoader::registerAlias('JCategories', '\\Joomla\\CMS\\Categories\\Categories', '6.0');
JLoader::registerAlias('JCategoryNode', '\\Joomla\\CMS\\Categories\\CategoryNode', '6.0');

JLoader::registerAlias('JDate', '\\Joomla\\CMS\\Date\\Date', '6.0');

JLoader::registerAlias('JLog', '\\Joomla\\CMS\\Log\\Log', '6.0');
JLoader::registerAlias('JLogEntry', '\\Joomla\\CMS\\Log\\LogEntry', '6.0');
JLoader::registerAlias('JLogLogger', '\\Joomla\\CMS\\Log\\Logger', '6.0');
JLoader::registerAlias('JLogger', '\\Joomla\\CMS\\Log\\Logger', '6.0');
JLoader::registerAlias('JLogLoggerCallback', '\\Joomla\\CMS\\Log\\Logger\\CallbackLogger', '6.0');
JLoader::registerAlias('JLogLoggerDatabase', '\\Joomla\\CMS\\Log\\Logger\\DatabaseLogger', '6.0');
JLoader::registerAlias('JLogLoggerEcho', '\\Joomla\\CMS\\Log\\Logger\\EchoLogger', '6.0');
JLoader::registerAlias('JLogLoggerFormattedtext', '\\Joomla\\CMS\\Log\\Logger\\FormattedtextLogger', '6.0');
JLoader::registerAlias('JLogLoggerMessagequeue', '\\Joomla\\CMS\\Log\\Logger\\MessagequeueLogger', '6.0');
JLoader::registerAlias('JLogLoggerSyslog', '\\Joomla\\CMS\\Log\\Logger\\SyslogLogger', '6.0');
JLoader::registerAlias('JLogLoggerW3c', '\\Joomla\\CMS\\Log\\Logger\\W3cLogger', '6.0');

JLoader::registerAlias('JProfiler', '\\Joomla\\CMS\\Profiler\\Profiler', '6.0');

JLoader::registerAlias('JUri', '\\Joomla\\CMS\\Uri\\Uri', '6.0');

JLoader::registerAlias('JCache', '\\Joomla\\CMS\\Cache\\Cache', '6.0');
JLoader::registerAlias('JCacheController', '\\Joomla\\CMS\\Cache\\CacheController', '6.0');
JLoader::registerAlias('JCacheStorage', '\\Joomla\\CMS\\Cache\\CacheStorage', '6.0');
JLoader::registerAlias('JCacheControllerCallback', '\\Joomla\\CMS\\Cache\\Controller\\CallbackController', '6.0');
JLoader::registerAlias('JCacheControllerOutput', '\\Joomla\\CMS\\Cache\\Controller\\OutputController', '6.0');
JLoader::registerAlias('JCacheControllerPage', '\\Joomla\\CMS\\Cache\\Controller\\PageController', '6.0');
JLoader::registerAlias('JCacheControllerView', '\\Joomla\\CMS\\Cache\\Controller\\ViewController', '6.0');
JLoader::registerAlias('JCacheStorageApcu', '\\Joomla\\CMS\\Cache\\Storage\\ApcuStorage', '6.0');
JLoader::registerAlias('JCacheStorageHelper', '\\Joomla\\CMS\\Cache\\Storage\\CacheStorageHelper', '6.0');
JLoader::registerAlias('JCacheStorageFile', '\\Joomla\\CMS\\Cache\\Storage\\FileStorage', '6.0');
JLoader::registerAlias('JCacheStorageMemcached', '\\Joomla\\CMS\\Cache\\Storage\\MemcachedStorage', '6.0');
JLoader::registerAlias('JCacheStorageRedis', '\\Joomla\\CMS\\Cache\\Storage\\RedisStorage', '6.0');
JLoader::registerAlias('JCacheException', '\\Joomla\\CMS\\Cache\\Exception\\CacheExceptionInterface', '6.0');
JLoader::registerAlias('JCacheExceptionConnecting', '\\Joomla\\CMS\\Cache\\Exception\\CacheConnectingException', '6.0');
JLoader::registerAlias('JCacheExceptionUnsupported', '\\Joomla\\CMS\\Cache\\Exception\\UnsupportedCacheException', '6.0');

JLoader::registerAlias('JSession', '\\Joomla\\CMS\\Session\\Session', '6.0');

JLoader::registerAlias('JUser', '\\Joomla\\CMS\\User\\User', '6.0');
JLoader::registerAlias('JUserHelper', '\\Joomla\\CMS\\User\\UserHelper', '6.0');

JLoader::registerAlias('JForm', '\\Joomla\\CMS\\Form\\Form', '6.0');
JLoader::registerAlias('JFormField', '\\Joomla\\CMS\\Form\\FormField', '6.0');
JLoader::registerAlias('JFormHelper', '\\Joomla\\CMS\\Form\\FormHelper', '6.0');
JLoader::registerAlias('JFormRule', '\\Joomla\\CMS\\Form\\FormRule', '6.0');

JLoader::registerAlias('JFormFieldAccessLevel', '\\Joomla\\CMS\\Form\\Field\\AccesslevelField', '6.0');
JLoader::registerAlias('JFormFieldAliastag', '\\Joomla\\CMS\\Form\\Field\\AliastagField', '6.0');
JLoader::registerAlias('JFormFieldAuthor', '\\Joomla\\CMS\\Form\\Field\\AuthorField', '6.0');
JLoader::registerAlias('JFormFieldCacheHandler', '\\Joomla\\CMS\\Form\\Field\\CachehandlerField', '6.0');
JLoader::registerAlias('JFormFieldCalendar', '\\Joomla\\CMS\\Form\\Field\\CalendarField', '6.0');
JLoader::registerAlias('JFormFieldCaptcha', '\\Joomla\\CMS\\Form\\Field\\CaptchaField', '6.0');
JLoader::registerAlias('JFormFieldCategory', '\\Joomla\\CMS\\Form\\Field\\CategoryField', '6.0');
JLoader::registerAlias('JFormFieldCheckbox', '\\Joomla\\CMS\\Form\\Field\\CheckboxField', '6.0');
JLoader::registerAlias('JFormFieldCheckboxes', '\\Joomla\\CMS\\Form\\Field\\CheckboxesField', '6.0');
JLoader::registerAlias('JFormFieldChromeStyle', '\\Joomla\\CMS\\Form\\Field\\ChromestyleField', '6.0');
JLoader::registerAlias('JFormFieldColor', '\\Joomla\\CMS\\Form\\Field\\ColorField', '6.0');
JLoader::registerAlias('JFormFieldCombo', '\\Joomla\\CMS\\Form\\Field\\ComboField', '6.0');
JLoader::registerAlias('JFormFieldComponentlayout', '\\Joomla\\CMS\\Form\\Field\\ComponentlayoutField', '6.0');
JLoader::registerAlias('JFormFieldComponents', '\\Joomla\\CMS\\Form\\Field\\ComponentsField', '6.0');
JLoader::registerAlias('JFormFieldContenthistory', '\\Joomla\\CMS\\Form\\Field\\ContenthistoryField', '6.0');
JLoader::registerAlias('JFormFieldContentlanguage', '\\Joomla\\CMS\\Form\\Field\\ContentlanguageField', '6.0');
JLoader::registerAlias('JFormFieldContenttype', '\\Joomla\\CMS\\Form\\Field\\ContenttypeField', '6.0');
JLoader::registerAlias('JFormFieldDatabaseConnection', '\\Joomla\\CMS\\Form\\Field\\DatabaseconnectionField', '6.0');
JLoader::registerAlias('JFormFieldEditor', '\\Joomla\\CMS\\Form\\Field\\EditorField', '6.0');
JLoader::registerAlias('JFormFieldEMail', '\\Joomla\\CMS\\Form\\Field\\EmailField', '6.0');
JLoader::registerAlias('JFormFieldFile', '\\Joomla\\CMS\\Form\\Field\\FileField', '6.0');
JLoader::registerAlias('JFormFieldFileList', '\\Joomla\\CMS\\Form\\Field\\FilelistField', '6.0');
JLoader::registerAlias('JFormFieldFolderList', '\\Joomla\\CMS\\Form\\Field\\FolderlistField', '6.0');
JLoader::registerAlias('JFormFieldFrontend_Language', '\\Joomla\\CMS\\Form\\Field\\FrontendlanguageField', '6.0');
JLoader::registerAlias('JFormFieldGroupedList', '\\Joomla\\CMS\\Form\\Field\\GroupedlistField', '6.0');
JLoader::registerAlias('JFormFieldHeadertag', '\\Joomla\\CMS\\Form\\Field\\HeadertagField', '6.0');
JLoader::registerAlias('JFormFieldHidden', '\\Joomla\\CMS\\Form\\Field\\HiddenField', '6.0');
JLoader::registerAlias('JFormFieldImageList', '\\Joomla\\CMS\\Form\\Field\\ImagelistField', '6.0');
JLoader::registerAlias('JFormFieldInteger', '\\Joomla\\CMS\\Form\\Field\\IntegerField', '6.0');
JLoader::registerAlias('JFormFieldLanguage', '\\Joomla\\CMS\\Form\\Field\\LanguageField', '6.0');
JLoader::registerAlias('JFormFieldLastvisitDateRange', '\\Joomla\\CMS\\Form\\Field\\LastvisitdaterangeField', '6.0');
JLoader::registerAlias('JFormFieldLimitbox', '\\Joomla\\CMS\\Form\\Field\\LimitboxField', '6.0');
JLoader::registerAlias('JFormFieldList', '\\Joomla\\CMS\\Form\\Field\\ListField', '6.0');
JLoader::registerAlias('JFormFieldMedia', '\\Joomla\\CMS\\Form\\Field\\MediaField', '6.0');
JLoader::registerAlias('JFormFieldMenu', '\\Joomla\\CMS\\Form\\Field\\MenuField', '6.0');
JLoader::registerAlias('JFormFieldMenuitem', '\\Joomla\\CMS\\Form\\Field\\MenuitemField', '6.0');
JLoader::registerAlias('JFormFieldMeter', '\\Joomla\\CMS\\Form\\Field\\MeterField', '6.0');
JLoader::registerAlias('JFormFieldModulelayout', '\\Joomla\\CMS\\Form\\Field\\ModulelayoutField', '6.0');
JLoader::registerAlias('JFormFieldModuleOrder', '\\Joomla\\CMS\\Form\\Field\\ModuleorderField', '6.0');
JLoader::registerAlias('JFormFieldModulePosition', '\\Joomla\\CMS\\Form\\Field\\ModulepositionField', '6.0');
JLoader::registerAlias('JFormFieldModuletag', '\\Joomla\\CMS\\Form\\Field\\ModuletagField', '6.0');
JLoader::registerAlias('JFormFieldNote', '\\Joomla\\CMS\\Form\\Field\\NoteField', '6.0');
JLoader::registerAlias('JFormFieldNumber', '\\Joomla\\CMS\\Form\\Field\\NumberField', '6.0');
JLoader::registerAlias('JFormFieldOrdering', '\\Joomla\\CMS\\Form\\Field\\OrderingField', '6.0');
JLoader::registerAlias('JFormFieldPassword', '\\Joomla\\CMS\\Form\\Field\\PasswordField', '6.0');
JLoader::registerAlias('JFormFieldPlugins', '\\Joomla\\CMS\\Form\\Field\\PluginsField', '6.0');
JLoader::registerAlias('JFormFieldPlugin_Status', '\\Joomla\\CMS\\Form\\Field\\PluginstatusField', '6.0');
JLoader::registerAlias('JFormFieldPredefinedList', '\\Joomla\\CMS\\Form\\Field\\PredefinedListField', '6.0');
JLoader::registerAlias('JFormFieldRadio', '\\Joomla\\CMS\\Form\\Field\\RadioField', '6.0');
JLoader::registerAlias('JFormFieldRange', '\\Joomla\\CMS\\Form\\Field\\RangeField', '6.0');
JLoader::registerAlias('JFormFieldRedirect_Status', '\\Joomla\\CMS\\Form\\Field\\RedirectStatusField', '6.0');
JLoader::registerAlias('JFormFieldRegistrationDateRange', '\\Joomla\\CMS\\Form\\Field\\RegistrationdaterangeField', '6.0');
JLoader::registerAlias('JFormFieldRules', '\\Joomla\\CMS\\Form\\Field\\RulesField', '6.0');
JLoader::registerAlias('JFormFieldSessionHandler', '\\Joomla\\CMS\\Form\\Field\\SessionhandlerField', '6.0');
JLoader::registerAlias('JFormFieldSpacer', '\\Joomla\\CMS\\Form\\Field\\SpacerField', '6.0');
JLoader::registerAlias('JFormFieldSQL', '\\Joomla\\CMS\\Form\\Field\\SqlField', '6.0');
JLoader::registerAlias('JFormFieldStatus', '\\Joomla\\CMS\\Form\\Field\\StatusField', '6.0');
JLoader::registerAlias('JFormFieldSubform', '\\Joomla\\CMS\\Form\\Field\\SubformField', '6.0');
JLoader::registerAlias('JFormFieldTag', '\\Joomla\\CMS\\Form\\Field\\TagField', '6.0');
JLoader::registerAlias('JFormFieldTel', '\\Joomla\\CMS\\Form\\Field\\TelephoneField', '6.0');
JLoader::registerAlias('JFormFieldTemplatestyle', '\\Joomla\\CMS\\Form\\Field\\TemplatestyleField', '6.0');
JLoader::registerAlias('JFormFieldText', '\\Joomla\\CMS\\Form\\Field\\TextField', '6.0');
JLoader::registerAlias('JFormFieldTextarea', '\\Joomla\\CMS\\Form\\Field\\TextareaField', '6.0');
JLoader::registerAlias('JFormFieldTimezone', '\\Joomla\\CMS\\Form\\Field\\TimezoneField', '6.0');
JLoader::registerAlias('JFormFieldUrl', '\\Joomla\\CMS\\Form\\Field\\UrlField', '6.0');
JLoader::registerAlias('JFormFieldUserActive', '\\Joomla\\CMS\\Form\\Field\\UseractiveField', '6.0');
JLoader::registerAlias('JFormFieldUserGroupList', '\\Joomla\\CMS\\Form\\Field\\UsergrouplistField', '6.0');
JLoader::registerAlias('JFormFieldUserState', '\\Joomla\\CMS\\Form\\Field\\UserstateField', '6.0');
JLoader::registerAlias('JFormFieldUser', '\\Joomla\\CMS\\Form\\Field\\UserField', '6.0');
JLoader::registerAlias('JFormRuleBoolean', '\\Joomla\\CMS\\Form\\Rule\\BooleanRule', '6.0');
JLoader::registerAlias('JFormRuleCalendar', '\\Joomla\\CMS\\Form\\Rule\\CalendarRule', '6.0');
JLoader::registerAlias('JFormRuleCaptcha', '\\Joomla\\CMS\\Form\\Rule\\CaptchaRule', '6.0');
JLoader::registerAlias('JFormRuleColor', '\\Joomla\\CMS\\Form\\Rule\\ColorRule', '6.0');
JLoader::registerAlias('JFormRuleEmail', '\\Joomla\\CMS\\Form\\Rule\\EmailRule', '6.0');
JLoader::registerAlias('JFormRuleEquals', '\\Joomla\\CMS\\Form\\Rule\\EqualsRule', '6.0');
JLoader::registerAlias('JFormRuleNotequals', '\\Joomla\\CMS\\Form\\Rule\\NotequalsRule', '6.0');
JLoader::registerAlias('JFormRuleNumber', '\\Joomla\\CMS\\Form\\Rule\\NumberRule', '6.0');
JLoader::registerAlias('JFormRuleOptions', '\\Joomla\\CMS\\Form\\Rule\\OptionsRule', '6.0');
JLoader::registerAlias('JFormRulePassword', '\\Joomla\\CMS\\Form\\Rule\\PasswordRule', '6.0');
JLoader::registerAlias('JFormRuleRules', '\\Joomla\\CMS\\Form\\Rule\\RulesRule', '6.0');
JLoader::registerAlias('JFormRuleTel', '\\Joomla\\CMS\\Form\\Rule\\TelRule', '6.0');
JLoader::registerAlias('JFormRuleUrl', '\\Joomla\\CMS\\Form\\Rule\\UrlRule', '6.0');
JLoader::registerAlias('JFormRuleUsername', '\\Joomla\\CMS\\Form\\Rule\\UsernameRule', '6.0');

JLoader::registerAlias('JMicrodata', '\\Joomla\\CMS\\Microdata\\Microdata', '6.0');

JLoader::registerAlias('JDatabaseDriver', '\\Joomla\\Database\\DatabaseDriver', '6.0');
JLoader::registerAlias('JDatabaseExporter', '\\Joomla\\Database\\DatabaseExporter', '6.0');
JLoader::registerAlias('JDatabaseFactory', '\\Joomla\\Database\\DatabaseFactory', '6.0');
JLoader::registerAlias('JDatabaseImporter', '\\Joomla\\Database\\DatabaseImporter', '6.0');
JLoader::registerAlias('JDatabaseInterface', '\\Joomla\\Database\\DatabaseInterface', '6.0');
JLoader::registerAlias('JDatabaseIterator', '\\Joomla\\Database\\DatabaseIterator', '6.0');
JLoader::registerAlias('JDatabaseQuery', '\\Joomla\\Database\\DatabaseQuery', '6.0');
JLoader::registerAlias('JDatabaseDriverMysqli', '\\Joomla\\Database\\Mysqli\\MysqliDriver', '6.0');
JLoader::registerAlias('JDatabaseDriverPdo', '\\Joomla\\Database\\Pdo\\PdoDriver', '6.0');
JLoader::registerAlias('JDatabaseDriverPdomysql', '\\Joomla\\Database\\Mysql\\MysqlDriver', '6.0');
JLoader::registerAlias('JDatabaseDriverPgsql', '\\Joomla\\Database\\Pgsql\\PgsqlDriver', '6.0');
JLoader::registerAlias('JDatabaseDriverSqlazure', '\\Joomla\\Database\\Sqlazure\\SqlazureDriver', '6.0');
JLoader::registerAlias('JDatabaseDriverSqlite', '\\Joomla\\Database\\Sqlite\\SqliteDriver', '6.0');
JLoader::registerAlias('JDatabaseDriverSqlsrv', '\\Joomla\\Database\\Sqlsrv\\SqlsrvDriver', '6.0');
JLoader::registerAlias('JDatabaseExceptionConnecting', '\\Joomla\\Database\\Exception\\ConnectionFailureException', '6.0');
JLoader::registerAlias('JDatabaseExceptionExecuting', '\\Joomla\\Database\\Exception\\ExecutionFailureException', '6.0');
JLoader::registerAlias('JDatabaseExceptionUnsupported', '\\Joomla\\Database\\Exception\\UnsupportedAdapterException', '6.0');
JLoader::registerAlias('JDatabaseExporterMysqli', '\\Joomla\\Database\\Mysqli\\MysqliExporter', '6.0');
JLoader::registerAlias('JDatabaseExporterPdomysql', '\\Joomla\\Database\\Mysql\\MysqlExporter', '6.0');
JLoader::registerAlias('JDatabaseExporterPgsql', '\\Joomla\\Database\\Pgsql\\PgsqlExporter', '6.0');
JLoader::registerAlias('JDatabaseImporterMysqli', '\\Joomla\\Database\\Mysqli\\MysqliImporter', '6.0');
JLoader::registerAlias('JDatabaseImporterPdomysql', '\\Joomla\\Database\\Mysql\\MysqlImporter', '6.0');
JLoader::registerAlias('JDatabaseImporterPgsql', '\\Joomla\\Database\\Pgsql\\PgsqlImporter', '6.0');
JLoader::registerAlias('JDatabaseQueryElement', '\\Joomla\\Database\\Query\\QueryElement', '6.0');
JLoader::registerAlias('JDatabaseQueryLimitable', '\\Joomla\\Database\\Query\\LimitableInterface', '6.0');
JLoader::registerAlias('JDatabaseQueryPreparable', '\\Joomla\\Database\\Query\\PreparableInterface', '6.0');
JLoader::registerAlias('JDatabaseQueryMysqli', '\\Joomla\\Database\\Mysqli\\MysqliQuery', '6.0');
JLoader::registerAlias('JDatabaseQueryPdo', '\\Joomla\\Database\\Pdo\\PdoQuery', '6.0');
JLoader::registerAlias('JDatabaseQueryPdomysql', '\\Joomla\\Database\\Mysql\\MysqlQuery', '6.0');
JLoader::registerAlias('JDatabaseQueryPgsql', '\\Joomla\\Database\\Pgsql\\PgsqlQuery', '6.0');
JLoader::registerAlias('JDatabaseQuerySqlazure', '\\Joomla\\Database\\Sqlazure\\SqlazureQuery', '6.0');
JLoader::registerAlias('JDatabaseQuerySqlite', '\\Joomla\\Database\\Sqlite\\SqliteQuery', '6.0');
JLoader::registerAlias('JDatabaseQuerySqlsrv', '\\Joomla\\Database\\Sqlsrv\\SqlsrvQuery', '6.0');

JLoader::registerAlias('JFactory', '\\Joomla\\CMS\\Factory', '6.0');

JLoader::registerAlias('JMail', '\\Joomla\\CMS\\Mail\\Mail', '6.0');
JLoader::registerAlias('JMailHelper', '\\Joomla\\CMS\\Mail\\MailHelper', '6.0');

JLoader::registerAlias('JClientHelper', '\\Joomla\\CMS\\Client\\ClientHelper', '6.0');
JLoader::registerAlias('JClientFtp', '\\Joomla\\CMS\\Client\\FtpClient', '6.0');
JLoader::registerAlias('JFTP', '\\Joomla\\CMS\\Client\\FtpClient', '4.0');

JLoader::registerAlias('JUpdate', '\\Joomla\\CMS\\Updater\\Update', '6.0');
JLoader::registerAlias('JUpdateAdapter', '\\Joomla\\CMS\\Updater\\UpdateAdapter', '6.0');
JLoader::registerAlias('JUpdater', '\\Joomla\\CMS\\Updater\\Updater', '6.0');
JLoader::registerAlias('JUpdaterCollection', '\\Joomla\\CMS\\Updater\\Adapter\\CollectionAdapter', '6.0');
JLoader::registerAlias('JUpdaterExtension', '\\Joomla\\CMS\\Updater\\Adapter\\ExtensionAdapter', '6.0');

JLoader::registerAlias('JCrypt', '\\Joomla\\CMS\\Crypt\\Crypt', '6.0');
JLoader::registerAlias('JCryptCipher', '\\Joomla\\Crypt\\CipherInterface', '6.0');
JLoader::registerAlias('JCryptKey', '\\Joomla\\Crypt\\Key', '6.0');
JLoader::registerAlias('\\Joomla\\CMS\\Crypt\\CipherInterface', '\\Joomla\\Crypt\\CipherInterface', '6.0');
JLoader::registerAlias('\\Joomla\\CMS\\Crypt\\Key', '\\Joomla\\Crypt\\Key', '6.0');
JLoader::registerAlias('JCryptCipherCrypto', '\\Joomla\\CMS\\Crypt\\Cipher\\CryptoCipher', '6.0');

JLoader::registerAlias('JStringPunycode', '\\Joomla\\CMS\\String\\PunycodeHelper', '6.0');

JLoader::registerAlias('JBuffer', '\\Joomla\\CMS\\Utility\\BufferStreamHandler', '6.0');
JLoader::registerAlias('JUtility', '\\Joomla\\CMS\\Utility\\Utility', '6.0');

JLoader::registerAlias('JInputCli', '\\Joomla\\CMS\\Input\\Cli', '6.0');
JLoader::registerAlias('JInputCookie', '\\Joomla\\CMS\\Input\\Cookie', '6.0');
JLoader::registerAlias('JInputFiles', '\\Joomla\\CMS\\Input\\Files', '6.0');
JLoader::registerAlias('JInput', '\\Joomla\\CMS\\Input\\Input', '6.0');
JLoader::registerAlias('JInputJSON', '\\Joomla\\CMS\\Input\\Json', '6.0');

JLoader::registerAlias('JFeed', '\\Joomla\\CMS\\Feed\\Feed', '6.0');
JLoader::registerAlias('JFeedEntry', '\\Joomla\\CMS\\Feed\\FeedEntry', '6.0');
JLoader::registerAlias('JFeedFactory', '\\Joomla\\CMS\\Feed\\FeedFactory', '6.0');
JLoader::registerAlias('JFeedLink', '\\Joomla\\CMS\\Feed\\FeedLink', '6.0');
JLoader::registerAlias('JFeedParser', '\\Joomla\\CMS\\Feed\\FeedParser', '6.0');
JLoader::registerAlias('JFeedPerson', '\\Joomla\\CMS\\Feed\\FeedPerson', '6.0');
JLoader::registerAlias('JFeedParserAtom', '\\Joomla\\CMS\\Feed\\Parser\\AtomParser', '6.0');
JLoader::registerAlias('JFeedParserNamespace', '\\Joomla\\CMS\\Feed\\Parser\\NamespaceParserInterface', '6.0');
JLoader::registerAlias('JFeedParserRss', '\\Joomla\\CMS\\Feed\\Parser\\RssParser', '6.0');
JLoader::registerAlias('JFeedParserRssItunes', '\\Joomla\\CMS\\Feed\\Parser\\Rss\\ItunesRssParser', '6.0');
JLoader::registerAlias('JFeedParserRssMedia', '\\Joomla\\CMS\\Feed\\Parser\\Rss\\MediaRssParser', '6.0');

JLoader::registerAlias('JImage', '\\Joomla\\CMS\\Image\\Image', '6.0');
JLoader::registerAlias('JImageFilter', '\\Joomla\\CMS\\Image\\ImageFilter', '6.0');
JLoader::registerAlias('JImageFilterBackgroundfill', '\\Joomla\\CMS\\Image\\Filter\\Backgroundfill', '6.0');
JLoader::registerAlias('JImageFilterBrightness', '\\Joomla\\CMS\\Image\\Filter\\Brightness', '6.0');
JLoader::registerAlias('JImageFilterContrast', '\\Joomla\\CMS\\Image\\Filter\\Contrast', '6.0');
JLoader::registerAlias('JImageFilterEdgedetect', '\\Joomla\\CMS\\Image\\Filter\\Edgedetect', '6.0');
JLoader::registerAlias('JImageFilterEmboss', '\\Joomla\\CMS\\Image\\Filter\\Emboss', '6.0');
JLoader::registerAlias('JImageFilterNegate', '\\Joomla\\CMS\\Image\\Filter\\Negate', '6.0');
JLoader::registerAlias('JImageFilterSmooth', '\\Joomla\\CMS\\Image\\Filter\\Smooth', '6.0');

JLoader::registerAlias('JObject', '\\Joomla\\CMS\\Object\\CMSObject', '6.0');

JLoader::registerAlias('JExtensionHelper', '\\Joomla\\CMS\\Extension\\ExtensionHelper', '6.0');

JLoader::registerAlias('JHtml', '\\Joomla\\CMS\\HTML\\HTMLHelper', '6.0');

JLoader::registerAlias('JFile', '\\Joomla\\CMS\\Filesystem\\File', '6.0');
JLoader::registerAlias('JFolder', '\\Joomla\\CMS\\Filesystem\\Folder', '6.0');
JLoader::registerAlias('JFilesystemHelper', '\\Joomla\\CMS\\Filesystem\\FilesystemHelper', '6.0');
JLoader::registerAlias('JFilesystemPatcher', '\\Joomla\\CMS\\Filesystem\\Patcher', '6.0');
JLoader::registerAlias('JPath', '\\Joomla\\CMS\\Filesystem\\Path', '6.0');
JLoader::registerAlias('JStream', '\\Joomla\\CMS\\Filesystem\\Stream', '6.0');
JLoader::registerAlias('JStreamString', '\\Joomla\\CMS\\Filesystem\\Streams\\StreamString', '6.0');
JLoader::registerAlias('JStringController', '\\Joomla\\CMS\\Filesystem\\Support\\StringController', '6.0');

JLoader::registerAlias('JClassLoader', '\\Joomla\\CMS\\Autoload\\ClassLoader', '6.0');

JLoader::registerAlias('JFormFilterInt_Array', '\\Joomla\\CMS\\Form\\Filter\\IntarrayFilter', '6.0');

JLoader::registerAlias('JAdapter', '\\Joomla\\CMS\\Adapter\\Adapter', '6.0');
JLoader::registerAlias('JAdapterInstance', '\\Joomla\\CMS\\Adapter\\AdapterInstance', '6.0');

JLoader::registerAlias('JHtmlAccess', '\\Joomla\\CMS\\HTML\\Helpers\\Access', '6.0');
JLoader::registerAlias('JHtmlActionsDropdown', '\\Joomla\\CMS\\HTML\\Helpers\\ActionsDropdown', '6.0');
JLoader::registerAlias('JHtmlAdminLanguage', '\\Joomla\\CMS\\HTML\\Helpers\\AdminLanguage', '6.0');
JLoader::registerAlias('JHtmlBehavior', '\\Joomla\\CMS\\HTML\\Helpers\\Behavior', '6.0');
JLoader::registerAlias('JHtmlBootstrap', '\\Joomla\\CMS\\HTML\\Helpers\\Bootstrap', '6.0');
JLoader::registerAlias('JHtmlCategory', '\\Joomla\\CMS\\HTML\\Helpers\\Category', '6.0');
JLoader::registerAlias('JHtmlContent', '\\Joomla\\CMS\\HTML\\Helpers\\Content', '6.0');
JLoader::registerAlias('JHtmlContentlanguage', '\\Joomla\\CMS\\HTML\\Helpers\\ContentLanguage', '6.0');
JLoader::registerAlias('JHtmlDate', '\\Joomla\\CMS\\HTML\\Helpers\\Date', '6.0');
JLoader::registerAlias('JHtmlDebug', '\\Joomla\\CMS\\HTML\\Helpers\\Debug', '6.0');
JLoader::registerAlias('JHtmlDraggablelist', '\\Joomla\\CMS\\HTML\\Helpers\\DraggableList', '6.0');
JLoader::registerAlias('JHtmlDropdown', '\\Joomla\\CMS\\HTML\\Helpers\\Dropdown', '6.0');
JLoader::registerAlias('JHtmlEmail', '\\Joomla\\CMS\\HTML\\Helpers\\Email', '6.0');
JLoader::registerAlias('JHtmlForm', '\\Joomla\\CMS\\HTML\\Helpers\\Form', '6.0');
JLoader::registerAlias('JHtmlFormbehavior', '\\Joomla\\CMS\\HTML\\Helpers\\FormBehavior', '6.0');
JLoader::registerAlias('JHtmlGrid', '\\Joomla\\CMS\\HTML\\Helpers\\Grid', '6.0');
JLoader::registerAlias('JHtmlIcons', '\\Joomla\\CMS\\HTML\\Helpers\\Icons', '6.0');
JLoader::registerAlias('JHtmlJGrid', '\\Joomla\\CMS\\HTML\\Helpers\\JGrid', '6.0');
JLoader::registerAlias('JHtmlJquery', '\\Joomla\\CMS\\HTML\\Helpers\\Jquery', '6.0');
JLoader::registerAlias('JHtmlLinks', '\\Joomla\\CMS\\HTML\\Helpers\\Links', '6.0');
JLoader::registerAlias('JHtmlList', '\\Joomla\\CMS\\HTML\\Helpers\\ListHelper', '6.0');
JLoader::registerAlias('JHtmlMenu', '\\Joomla\\CMS\\HTML\\Helpers\\Menu', '6.0');
JLoader::registerAlias('JHtmlNumber', '\\Joomla\\CMS\\HTML\\Helpers\\Number', '6.0');
JLoader::registerAlias('JHtmlSearchtools', '\\Joomla\\CMS\\HTML\\Helpers\\SearchTools', '6.0');
JLoader::registerAlias('JHtmlSelect', '\\Joomla\\CMS\\HTML\\Helpers\\Select', '6.0');
JLoader::registerAlias('JHtmlSidebar', '\\Joomla\\CMS\\HTML\\Helpers\\Sidebar', '6.0');
JLoader::registerAlias('JHtmlSortableList', '\\Joomla\\CMS\\HTML\\Helpers\\SortableList', '6.0');
JLoader::registerAlias('JHtmlString', '\\Joomla\\CMS\\HTML\\Helpers\\StringHelper', '6.0');
JLoader::registerAlias('JHtmlTag', '\\Joomla\\CMS\\HTML\\Helpers\\Tag', '6.0');
JLoader::registerAlias('JHtmlTel', '\\Joomla\\CMS\\HTML\\Helpers\\Telephone', '6.0');
JLoader::registerAlias('JHtmlUser', '\\Joomla\\CMS\\HTML\\Helpers\\User', '6.0');

// As JLoader is not managing the \Joomla\Input namespace, we need to use the native class alias function
class_alias('\\Joomla\\Input\\Input', '\\Joomla\\CMS\\Input\\Input');

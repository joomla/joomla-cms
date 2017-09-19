<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

JLoader::registerAlias('JRegistry',                         '\\Joomla\\Registry\\Registry', '5.0');
JLoader::registerAlias('JRegistryFormat',                   '\\Joomla\\Registry\\AbstractRegistryFormat', '5.0');
JLoader::registerAlias('JRegistryFormatIni',                '\\Joomla\\Registry\\Format\\Ini', '5.0');
JLoader::registerAlias('JRegistryFormatJson',               '\\Joomla\\Registry\\Format\\Json', '5.0');
JLoader::registerAlias('JRegistryFormatPhp',                '\\Joomla\\Registry\\Format\\Php', '5.0');
JLoader::registerAlias('JRegistryFormatXml',                '\\Joomla\\Registry\\Format\\Xml', '5.0');
JLoader::registerAlias('JStringInflector',                  '\\Joomla\\String\\Inflector', '5.0');
JLoader::registerAlias('JStringNormalise',                  '\\Joomla\\String\\Normalise', '5.0');
JLoader::registerAlias('JData',                             '\\Joomla\\Data\\DataObject', '5.0');
JLoader::registerAlias('JDataSet',                          '\\Joomla\\Data\\DataSet', '5.0');
JLoader::registerAlias('JDataDumpable',                     '\\Joomla\\Data\\DumpableInterface', '5.0');

JLoader::registerAlias('JApplicationAdministrator',         '\\Joomla\\CMS\\Application\\AdministratorApplication', '5.0');
JLoader::registerAlias('JApplicationHelper',                '\\Joomla\\CMS\\Application\\ApplicationHelper', '5.0');
JLoader::registerAlias('JApplicationBase',                  '\\Joomla\\CMS\\Application\\BaseApplication', '5.0');
JLoader::registerAlias('JApplicationCli',                   '\\Joomla\\CMS\\Application\\CliApplication', '5.0');
JLoader::registerAlias('JApplicationCms',                   '\\Joomla\\CMS\\Application\\CMSApplication', '5.0');
JLoader::registerAlias('JApplicationDaemon',                '\\Joomla\\CMS\\Application\\DaemonApplication', '5.0');
JLoader::registerAlias('JApplicationSite',                  '\\Joomla\\CMS\\Application\\SiteApplication', '5.0');
JLoader::registerAlias('JApplicationWeb',                   '\\Joomla\\CMS\\Application\\WebApplication', '5.0');
JLoader::registerAlias('JApplicationWebClient',             '\\Joomla\\Application\\Web\\WebClient', '5.0');
JLoader::registerAlias('JDaemon',                           '\\Joomla\\CMS\\Application\\DaemonApplication', '5.0');
JLoader::registerAlias('JCli',                              '\\Joomla\\CMS\\Application\\CliApplication', '5.0');
JLoader::registerAlias('JWeb',                              '\\Joomla\\CMS\\Application\\WebApplication', '4.0');
JLoader::registerAlias('JWebClient',                        '\\Joomla\\Application\\Web\\WebClient', '4.0');

JLoader::registerAlias('JModelAdmin',                       '\\Joomla\\CMS\\MVC\\Model\\AdminModel', '5.0');
JLoader::registerAlias('JModelForm',                        '\\Joomla\\CMS\\MVC\\Model\\FormModel', '5.0');
JLoader::registerAlias('JModelItem',                        '\\Joomla\\CMS\\MVC\\Model\\ItemModel', '5.0');
JLoader::registerAlias('JModelList',                        '\\Joomla\\CMS\\MVC\\Model\\ListModel', '5.0');
JLoader::registerAlias('JModelLegacy',                      '\\Joomla\\CMS\\MVC\\Model\\BaseDatabaseModel', '5.0');
JLoader::registerAlias('JViewCategories',                   '\\Joomla\\CMS\\MVC\\View\\CategoriesView', '5.0');
JLoader::registerAlias('JViewCategory',                     '\\Joomla\\CMS\\MVC\\View\\CategoryView', '5.0');
JLoader::registerAlias('JViewCategoryfeed',                 '\\Joomla\\CMS\\MVC\\View\\CategoryFeedView', '5.0');
JLoader::registerAlias('JViewLegacy',                       '\\Joomla\\CMS\\MVC\\View\\HtmlView', '5.0');
JLoader::registerAlias('JControllerAdmin',                  '\\Joomla\\CMS\\MVC\\Controller\\AdminController', '5.0');
JLoader::registerAlias('JControllerLegacy',                 '\\Joomla\\CMS\\MVC\\Controller\\BaseController', '5.0');
JLoader::registerAlias('JControllerForm',                   '\\Joomla\\CMS\\MVC\\Controller\\FormController', '5.0');
JLoader::registerAlias('JTableInterface',                   '\\Joomla\\CMS\\Table\\TableInterface', '5.0');
JLoader::registerAlias('JTable',                            '\\Joomla\\CMS\\Table\\Table', '5.0');
JLoader::registerAlias('JTableNested',                      '\\Joomla\\CMS\\Table\\Nested', '5.0');
JLoader::registerAlias('JTableAsset',                       '\\Joomla\\CMS\\Table\\Asset', '5.0');
JLoader::registerAlias('JTableExtension',                   '\\Joomla\\CMS\\Table\\Extension', '5.0');
JLoader::registerAlias('JTableLanguage',                    '\\Joomla\\CMS\\Table\\Language', '5.0');
JLoader::registerAlias('JTableUpdate',                      '\\Joomla\\CMS\\Table\\Update', '5.0');
JLoader::registerAlias('JTableUpdatesite',                  '\\Joomla\\CMS\\Table\\UpdateSite', '5.0');
JLoader::registerAlias('JTableUser',                        '\\Joomla\\CMS\\Table\\User', '5.0');
JLoader::registerAlias('JTableUsergroup',                   '\\Joomla\\CMS\\Table\\Usergroup', '5.0');
JLoader::registerAlias('JTableViewlevel',                   '\\Joomla\\CMS\\Table\\ViewLevel', '5.0');
JLoader::registerAlias('JTableContenthistory',              '\\Joomla\\CMS\\Table\\ContentHistory', '5.0');
JLoader::registerAlias('JTableContenttype',                 '\\Joomla\\CMS\\Table\\ContentType', '5.0');
JLoader::registerAlias('JTableCorecontent',                 '\\Joomla\\CMS\\Table\\CoreContent', '5.0');
JLoader::registerAlias('JTableUcm',                         '\\Joomla\\CMS\\Table\\Ucm', '5.0');
JLoader::registerAlias('JTableCategory',                    '\\Joomla\\CMS\\Table\\Category', '5.0');
JLoader::registerAlias('JTableContent',                     '\\Joomla\\CMS\\Table\\Content', '5.0');
JLoader::registerAlias('JTableMenu',                        '\\Joomla\\CMS\\Table\\Menu', '5.0');
JLoader::registerAlias('JTableMenuType',                    '\\Joomla\\CMS\\Table\\MenuType', '5.0');
JLoader::registerAlias('JTableModule',                      '\\Joomla\\CMS\\Table\\Module', '5.0');
JLoader::registerAlias('JTableObserver',                    '\\Joomla\\CMS\\Table\\Observer\\AbstractObserver', '5.0');
JLoader::registerAlias('JTableObserverContenthistory',      '\\Joomla\\CMS\\Table\\Observer\\ContentHistory', '5.0');
JLoader::registerAlias('JTableObserverTags',                '\\Joomla\\CMS\\Table\\Observer\\Tags', '5.0');

JLoader::registerAlias('JAccess',                           '\\Joomla\\CMS\\Access\\Access', '5.0');
JLoader::registerAlias('JAccessRule',                       '\\Joomla\\CMS\\Access\\Rule', '5.0');
JLoader::registerAlias('JAccessRules',                      '\\Joomla\\CMS\\Access\\Rules', '5.0');
JLoader::registerAlias('JAccessWrapperAccess',              '\\Joomla\\CMS\\Access\\Wrapper\\Access', '4.0');
JLoader::registerAlias('JAccessExceptionNotallowed',        '\\Joomla\\CMS\\Access\\Exception\\NotAllowed', '5.0');
JLoader::registerAlias('JRule',                             '\\Joomla\\CMS\\Access\\Rule', '5.0');
JLoader::registerAlias('JRules',                            '\\Joomla\\CMS\\Access\\Rules', '5.0');

JLoader::registerAlias('JHelp',                             '\\Joomla\\CMS\\Help\\Help', '5.0');
JLoader::registerAlias('JCaptcha',                          '\\Joomla\\CMS\\Captcha\\Captcha', '5.0');

JLoader::registerAlias('JLanguageAssociations',             '\\Joomla\\CMS\\Language\\Associations', '5.0');
JLoader::registerAlias('JLanguage',                         '\\Joomla\\CMS\\Language\\Language', '5.0');
JLoader::registerAlias('JLanguageHelper',                   '\\Joomla\\CMS\\Language\\LanguageHelper', '5.0');
JLoader::registerAlias('JLanguageStemmer',                  '\\Joomla\\CMS\\Language\\LanguageStemmer', '5.0');
JLoader::registerAlias('JLanguageMultilang',                '\\Joomla\\CMS\\Language\\Multilanguage', '5.0');
JLoader::registerAlias('JText',                             '\\Joomla\\CMS\\Language\\Text', '5.0');
JLoader::registerAlias('JLanguageTransliterate',            '\\Joomla\\CMS\\Language\\Transliterate', '5.0');
JLoader::registerAlias('JLanguageStemmerPorteren',          '\\Joomla\\CMS\\Language\\Stemmer\\Porteren', '5.0');
JLoader::registerAlias('JLanguageWrapperText',              '\\Joomla\\CMS\\Language\\Wrapper\\JTextWrapper', '4.0');
JLoader::registerAlias('JLanguageWrapperHelper',            '\\Joomla\\CMS\\Language\\Wrapper\\LanguageHelperWrapper', '4.0');
JLoader::registerAlias('JLanguageWrapperTransliterate',     '\\Joomla\\CMS\\Language\\Wrapper\\TransliterateWrapper', '4.0');

JLoader::registerAlias('JComponentHelper',                  '\\Joomla\\CMS\\Component\\ComponentHelper', '5.0');
JLoader::registerAlias('JComponentRecord',                  '\\Joomla\\CMS\\Component\\ComponentRecord', '5.0');
JLoader::registerAlias('JComponentExceptionMissing',        '\\Joomla\\CMS\\Component\\Exception\\MissingComponentException', '5.0');
JLoader::registerAlias('JComponentRouterBase',              '\\Joomla\\CMS\\Component\\Router\\RouterBase', '5.0');
JLoader::registerAlias('JComponentRouterInterface',         '\\Joomla\\CMS\\Component\\Router\\RouterInterface', '5.0');
JLoader::registerAlias('JComponentRouterLegacy',            '\\Joomla\\CMS\\Component\\Router\\RouterLegacy', '5.0');
JLoader::registerAlias('JComponentRouterView',              '\\Joomla\\CMS\\Component\\Router\\RouterView', '5.0');
JLoader::registerAlias('JComponentRouterViewconfiguration', '\\Joomla\\CMS\\Component\\Router\\RouterViewConfiguration', '5.0');
JLoader::registerAlias('JComponentRouterRulesMenu',         '\\Joomla\\CMS\\Component\\Router\\Rules\\MenuRules', '5.0');
JLoader::registerAlias('JComponentRouterRulesNomenu',       '\\Joomla\\CMS\\Component\\Router\\Rules\\NomenuRules', '5.0');
JLoader::registerAlias('JComponentRouterRulesInterface',    '\\Joomla\\CMS\\Component\\Router\\Rules\\RulesInterface', '5.0');
JLoader::registerAlias('JComponentRouterRulesStandard',     '\\Joomla\\CMS\\Component\\Router\\Rules\\StandardRules', '5.0');

JLoader::registerAlias('JEditor',                           '\\Joomla\\CMS\\Editor\\Editor', '5.0');

JLoader::registerAlias('JErrorPage',                        '\\Joomla\\CMS\\Exception\\ExceptionHandler', '5.0');

JLoader::registerAlias('JAuthenticationHelper',             '\\Joomla\\CMS\\Helper\\AuthenticationHelper', '5.0');
JLoader::registerAlias('JHelper',                           '\\Joomla\\CMS\\Helper\\CMSHelper', '5.0');
JLoader::registerAlias('JHelperContent',                    '\\Joomla\\CMS\\Helper\\ContentHelper', '5.0');
JLoader::registerAlias('JHelperContenthistory',             '\\Joomla\\CMS\\Helper\\ContentHistoryHelper', '5.0');
JLoader::registerAlias('JLibraryHelper',                    '\\Joomla\\CMS\\Helper\\LibraryHelper', '5.0');
JLoader::registerAlias('JHelperMedia',                      '\\Joomla\\CMS\\Helper\\MediaHelper', '5.0');
JLoader::registerAlias('JModuleHelper',                     '\\Joomla\\CMS\\Helper\\ModuleHelper', '5.0');
JLoader::registerAlias('JHelperRoute',                      '\\Joomla\\CMS\\Helper\\RouteHelper', '5.0');
JLoader::registerAlias('JSearchHelper',                     '\\Joomla\\CMS\\Helper\\SearchHelper', '5.0');
JLoader::registerAlias('JHelperTags',                       '\\Joomla\\CMS\\Helper\\TagsHelper', '5.0');
JLoader::registerAlias('JHelperUsergroups',                 '\\Joomla\\CMS\\Helper\\UserGroupsHelper', '5.0');

JLoader::registerAlias('JLayoutBase',                       '\\Joomla\\CMS\\Layout\\BaseLayout', '5.0');
JLoader::registerAlias('JLayoutFile',                       '\\Joomla\\CMS\\Layout\\FileLayout', '5.0');
JLoader::registerAlias('JLayoutHelper',                     '\\Joomla\\CMS\\Layout\\LayoutHelper', '5.0');
JLoader::registerAlias('JLayout',                           '\\Joomla\\CMS\\Layout\\LayoutInterface', '5.0');

JLoader::registerAlias('JResponseJson',                     '\\Joomla\\CMS\\Response\\JsonResponse', '5.0');

JLoader::registerAlias('JPlugin',                           '\\Joomla\\CMS\\Plugin\\CMSPlugin', '5.0');
JLoader::registerAlias('JPluginHelper',                     '\\Joomla\\CMS\\Plugin\\PluginHelper', '5.0');

JLoader::registerAlias('JMenu',                             '\\Joomla\\CMS\\Menu\\AbstractMenu', '5.0');
JLoader::registerAlias('JMenuAdministrator',                '\\Joomla\\CMS\\Menu\\AdministratorMenu', '5.0');
JLoader::registerAlias('JMenuItem',                         '\\Joomla\\CMS\\Menu\\MenuItem', '5.0');
JLoader::registerAlias('JMenuSite',                         '\\Joomla\\CMS\\Menu\\SiteMenu', '5.0');

JLoader::registerAlias('JPagination',                       '\\Joomla\\CMS\\Pagination\\Pagination', '5.0');
JLoader::registerAlias('JPaginationObject',                 '\\Joomla\\CMS\\Pagination\\PaginationObject', '5.0');

JLoader::registerAlias('JPathway',                          '\\Joomla\\CMS\\Pathway\\Pathway', '5.0');
JLoader::registerAlias('JPathwaySite',                      '\\Joomla\\CMS\\Pathway\\SitePathway', '5.0');

JLoader::registerAlias('JSchemaChangeitem',                 '\\Joomla\\CMS\\Schema\\ChangeItem', '5.0');
JLoader::registerAlias('JSchemaChangeset',                  '\\Joomla\\CMS\\Schema\\ChangeSet', '5.0');
JLoader::registerAlias('JSchemaChangeitemMysql',            '\\Joomla\\CMS\\Schema\\ChangeItem\\MysqlChangeItem', '5.0');
JLoader::registerAlias('JSchemaChangeitemPostgresql',       '\\Joomla\\CMS\\Schema\\ChangeItem\\PostgresqlChangeItem', '5.0');
JLoader::registerAlias('JSchemaChangeitemSqlsrv',           '\\Joomla\\CMS\\Schema\\ChangeItem\\SqlsrvChangeItem', '5.0');

JLoader::registerAlias('JUcm',                              '\\Joomla\\CMS\\UCM\\UCM', '5.0');
JLoader::registerAlias('JUcmBase',                          '\\Joomla\\CMS\\UCM\\UCMBase', '5.0');
JLoader::registerAlias('JUcmContent',                       '\\Joomla\\CMS\\UCM\\UCMContent', '5.0');
JLoader::registerAlias('JUcmType',                          '\\Joomla\\CMS\\UCM\\UCMType', '5.0');

JLoader::registerAlias('JToolbar',                          '\\Joomla\\CMS\\Toolbar\\Toolbar', '5.0');
JLoader::registerAlias('JToolBar',                          '\\Joomla\\CMS\\Toolbar\\Toolbar', '5.0');
JLoader::registerAlias('JToolbarButton',                    '\\Joomla\\CMS\\Toolbar\\ToolbarButton', '5.0');
JLoader::registerAlias('JToolbarButtonConfirm',             '\\Joomla\\CMS\\Toolbar\\Button\\ConfirmButton', '5.0');
JLoader::registerAlias('JToolbarButtonCustom',              '\\Joomla\\CMS\\Toolbar\\Button\\CustomButton', '5.0');
JLoader::registerAlias('JToolbarButtonHelp',                '\\Joomla\\CMS\\Toolbar\\Button\\HelpButton', '5.0');
JLoader::registerAlias('JToolbarButtonLink',                '\\Joomla\\CMS\\Toolbar\\Button\\LinkButton', '5.0');
JLoader::registerAlias('JToolbarButtonPopup',               '\\Joomla\\CMS\\Toolbar\\Button\\PopupButton', '5.0');
JLoader::registerAlias('JToolbarButtonSeparator',           '\\Joomla\\CMS\\Toolbar\\Button\\SeparatorButton', '5.0');
JLoader::registerAlias('JToolbarButtonSlider',              '\\Joomla\\CMS\\Toolbar\\Button\\SliderButton', '5.0');
JLoader::registerAlias('JToolbarButtonStandard',            '\\Joomla\\CMS\\Toolbar\\Button\\StandardButton', '5.0');
JLoader::registerAlias('JToolbarButtonStandard',            '\\Joomla\\CMS\\Toolbar\\Button\\StandardButton', '5.0');
JLoader::registerAlias('JButton',                           '\\Joomla\\CMS\\Toolbar\\ToolbarButton', '5.0');

JLoader::registerAlias('JVersion',                          '\\Joomla\\CMS\\Version', '5.0');

JLoader::registerAlias('JAuthentication',                   '\\Joomla\\CMS\\Authentication\\Authentication', '5.0');
JLoader::registerAlias('JAuthenticationResponse',           '\\Joomla\\CMS\\Authentication\\AuthenticationResponse', '5.0');

JLoader::registerAlias('JBrowser',                          '\\Joomla\\CMS\\Environment\\Browser', '5.0');

JLoader::registerAlias('JAssociationExtensionInterface',    '\\Joomla\\CMS\\Association\\AssociationExtensionInterface', '5.0');
JLoader::registerAlias('JAssociationExtensionHelper',       '\\Joomla\\CMS\\Association\\AssociationExtensionHelper', '5.0');

JLoader::registerAlias('JDocument',                         '\\Joomla\\CMS\\Document\\Document', '5.0');
JLoader::registerAlias('JDocumentError',                    '\\Joomla\\CMS\\Document\\ErrorDocument', '5.0');
JLoader::registerAlias('JDocumentFeed',                     '\\Joomla\\CMS\\Document\\FeedDocument', '5.0');
JLoader::registerAlias('JDocumentHtml',                     '\\Joomla\\CMS\\Document\\HtmlDocument', '5.0');
JLoader::registerAlias('JDocumentImage',                    '\\Joomla\\CMS\\Document\\ImageDocument', '5.0');
JLoader::registerAlias('JDocumentJson',                     '\\Joomla\\CMS\\Document\\JsonDocument', '5.0');
JLoader::registerAlias('JDocumentOpensearch',               '\\Joomla\\CMS\\Document\\OpensearchDocument', '5.0');
JLoader::registerAlias('JDocumentRaw',                      '\\Joomla\\CMS\\Document\\RawDocument', '5.0');
JLoader::registerAlias('JDocumentRenderer',                 '\\Joomla\\CMS\\Document\\DocumentRenderer', '5.0');
JLoader::registerAlias('JDocumentXml',                      '\\Joomla\\CMS\\Document\\XmlDocument', '5.0');
JLoader::registerAlias('JDocumentRendererFeedAtom',         '\\Joomla\\CMS\\Document\\Renderer\\Feed\\AtomRenderer', '5.0');
JLoader::registerAlias('JDocumentRendererFeedRss',          '\\Joomla\\CMS\\Document\\Renderer\\Feed\\RssRenderer', '5.0');
JLoader::registerAlias('JDocumentRendererHtmlComponent',    '\\Joomla\\CMS\\Document\\Renderer\\Html\\ComponentRenderer', '5.0');
JLoader::registerAlias('JDocumentRendererHtmlHead',         '\\Joomla\\CMS\\Document\\Renderer\\Html\\HeadRenderer', '5.0');
JLoader::registerAlias('JDocumentRendererHtmlMessage',      '\\Joomla\\CMS\\Document\\Renderer\\Html\\MessageRenderer', '5.0');
JLoader::registerAlias('JDocumentRendererHtmlModule',       '\\Joomla\\CMS\\Document\\Renderer\\Html\\ModuleRenderer', '5.0');
JLoader::registerAlias('JDocumentRendererHtmlModules',      '\\Joomla\\CMS\\Document\\Renderer\\Html\\ModulesRenderer', '5.0');
JLoader::registerAlias('JDocumentRendererAtom',             '\\Joomla\\CMS\\Document\\Renderer\\Feed\\AtomRenderer', '4.0');
JLoader::registerAlias('JDocumentRendererRSS',              '\\Joomla\\CMS\\Document\\Renderer\\Feed\\RssRenderer', '4.0');
JLoader::registerAlias('JDocumentRendererComponent',        '\\Joomla\\CMS\\Document\\Renderer\\Html\\ComponentRenderer', '4.0');
JLoader::registerAlias('JDocumentRendererHead',             '\\Joomla\\CMS\\Document\\Renderer\\Html\\HeadRenderer', '4.0');
JLoader::registerAlias('JDocumentRendererMessage',          '\\Joomla\\CMS\\Document\\Renderer\\Html\\MessageRenderer', '4.0');
JLoader::registerAlias('JDocumentRendererModule',           '\\Joomla\\CMS\\Document\\Renderer\\Html\\ModuleRenderer', '4.0');
JLoader::registerAlias('JDocumentRendererModules',          '\\Joomla\\CMS\\Document\\Renderer\\Html\\ModulesRenderer', '4.0');
JLoader::registerAlias('JFeedEnclosure',                    '\\Joomla\\CMS\\Document\\Feed\\FeedEnclosure', '5.0');
JLoader::registerAlias('JFeedImage',                        '\\Joomla\\CMS\\Document\\Feed\\FeedImage', '5.0');
JLoader::registerAlias('JFeedItem',                         '\\Joomla\\CMS\\Document\\Feed\\FeedItem', '5.0');
JLoader::registerAlias('JOpenSearchImage',                  '\\Joomla\\CMS\\Document\\Opensearch\\OpensearchImage', '5.0');
JLoader::registerAlias('JOpenSearchUrl',                    '\\Joomla\\CMS\\Document\\Opensearch\\OpensearchUrl', '5.0');

JLoader::registerAlias('JFilterInput',                      '\\Joomla\\CMS\\Filter\\InputFilter', '5.0');
JLoader::registerAlias('JFilterOutput',                     '\\Joomla\\CMS\\Filter\\OutputFilter', '5.0');
JLoader::registerAlias('JFilterWrapperOutput',              '\\Joomla\\CMS\\Filter\\Wrapper\\OutputFilterWrapper', '4.0');

JLoader::registerAlias('JHttp',                             '\\Joomla\\CMS\\Http\\Http', '5.0');
JLoader::registerAlias('JHttpFactory',                      '\\Joomla\\CMS\\Http\\HttpFactory', '5.0');
JLoader::registerAlias('JHttpResponse',                     '\\Joomla\\CMS\\Http\\Response', '5.0');
JLoader::registerAlias('JHttpTransport',                    '\\Joomla\\CMS\\Http\\TransportInterface', '5.0');
JLoader::registerAlias('JHttpTransportCurl',                '\\Joomla\\CMS\\Http\\Transport\\CurlTransport', '5.0');
JLoader::registerAlias('JHttpTransportSocket',              '\\Joomla\\CMS\\Http\\Transport\\SocketTransport', '5.0');
JLoader::registerAlias('JHttpTransportStream',              '\\Joomla\\CMS\\Http\\Transport\\StreamTransport', '5.0');
JLoader::registerAlias('JHttpWrapperFactory',               '\\Joomla\\CMS\\Http\\Wrapper\\FactoryWrapper', '4.0');

JLoader::registerAlias('JInstaller',                        '\\Joomla\\CMS\\Installer\\Installer', '5.0');
JLoader::registerAlias('JInstallerAdapter',                 '\\Joomla\\CMS\\Installer\\InstallerAdapter', '5.0');
JLoader::registerAlias('JInstallerExtension',               '\\Joomla\\CMS\\Installer\\InstallerExtension', '5.0');
JLoader::registerAlias('JExtension',                        '\\Joomla\\CMS\\Installer\\InstallerExtension', '5.0');
JLoader::registerAlias('JInstallerHelper',                  '\\Joomla\\CMS\\Installer\\InstallerHelper', '5.0');
JLoader::registerAlias('JInstallerScript',                  '\\Joomla\\CMS\\Installer\\InstallerScript', '5.0');
JLoader::registerAlias('JInstallerManifest',                '\\Joomla\\CMS\\Installer\\Manifest', '5.0');
JLoader::registerAlias('JInstallerAdapterComponent',        '\\Joomla\\CMS\\Installer\\Adapter\\ComponentAdapter', '5.0');
JLoader::registerAlias('JInstallerComponent',               '\\Joomla\\CMS\\Installer\\Adapter\\ComponentAdapter', '5.0');
JLoader::registerAlias('JInstallerAdapterFile',             '\\Joomla\\CMS\\Installer\\Adapter\\FileAdapter', '5.0');
JLoader::registerAlias('JInstallerFile',                    '\\Joomla\\CMS\\Installer\\Adapter\\FileAdapter', '5.0');
JLoader::registerAlias('JInstallerAdapterLanguage',         '\\Joomla\\CMS\\Installer\\Adapter\\LanguageAdapter', '5.0');
JLoader::registerAlias('JInstallerLanguage',                '\\Joomla\\CMS\\Installer\\Adapter\\LanguageAdapter', '5.0');
JLoader::registerAlias('JInstallerAdapterLibrary',          '\\Joomla\\CMS\\Installer\\Adapter\\LibraryAdapter', '5.0');
JLoader::registerAlias('JInstallerLibrary',                 '\\Joomla\\CMS\\Installer\\Adapter\\LibraryAdapter', '5.0');
JLoader::registerAlias('JInstallerAdapterModule',           '\\Joomla\\CMS\\Installer\\Adapter\\ModuleAdapter', '5.0');
JLoader::registerAlias('JInstallerModule',                  '\\Joomla\\CMS\\Installer\\Adapter\\ModuleAdapter', '5.0');
JLoader::registerAlias('JInstallerAdapterPackage',          '\\Joomla\\CMS\\Installer\\Adapter\\PackageAdapter', '5.0');
JLoader::registerAlias('JInstallerPackage',                 '\\Joomla\\CMS\\Installer\\Adapter\\PackageAdapter', '5.0');
JLoader::registerAlias('JInstallerAdapterPlugin',           '\\Joomla\\CMS\\Installer\\Adapter\\PluginAdapter', '5.0');
JLoader::registerAlias('JInstallerPlugin',                  '\\Joomla\\CMS\\Installer\\Adapter\\PluginAdapter', '5.0');
JLoader::registerAlias('JInstallerAdapterTemplate',         '\\Joomla\\CMS\\Installer\\Adapter\\TemplateAdapter', '5.0');
JLoader::registerAlias('JInstallerTemplate',                '\\Joomla\\CMS\\Installer\\Adapter\\TemplateAdapter', '5.0');
JLoader::registerAlias('JInstallerManifestLibrary',         '\\Joomla\\CMS\\Installer\\Manifest\\LibraryManifest', '5.0');
JLoader::registerAlias('JInstallerManifestPackage',         '\\Joomla\\CMS\\Installer\\Manifest\\PackageManifest', '5.0');

JLoader::registerAlias('JRouterAdministrator',              '\\Joomla\\CMS\\Router\\AdministratorRouter', '5.0');
JLoader::registerAlias('JRoute',                            '\\Joomla\\CMS\\Router\\Route', '5.0');
JLoader::registerAlias('JRouter',                           '\\Joomla\\CMS\\Router\\Router', '5.0');
JLoader::registerAlias('JRouterSite',                       '\\Joomla\\CMS\\Router\\SiteRouter', '5.0');

JLoader::registerAlias('JCategories',                       '\\Joomla\\CMS\\Categories\\Categories', '5.0');
JLoader::registerAlias('JCategoryNode',                     '\\Joomla\\CMS\\Categories\\CategoryNode', '5.0');

JLoader::registerAlias('JDate',                             '\\Joomla\\CMS\\Date\\Date', '5.0');

JLoader::registerAlias('JLog',                              '\\Joomla\\CMS\\Log\\Log', '5.0');
JLoader::registerAlias('JLogEntry',                         '\\Joomla\\CMS\\Log\\LogEntry', '5.0');
JLoader::registerAlias('JLogLogger',                        '\\Joomla\\CMS\\Log\\Logger', '5.0');
JLoader::registerAlias('JLogger',                           '\\Joomla\\CMS\\Log\\Logger', '5.0');
JLoader::registerAlias('JLogLoggerCallback',                '\\Joomla\\CMS\\Log\\Logger\\CallbackLogger', '5.0');
JLoader::registerAlias('JLogLoggerDatabase',                '\\Joomla\\CMS\\Log\\Logger\\DatabaseLogger', '5.0');
JLoader::registerAlias('JLogLoggerEcho',                    '\\Joomla\\CMS\\Log\\Logger\\EchoLogger', '5.0');
JLoader::registerAlias('JLogLoggerFormattedtext',           '\\Joomla\\CMS\\Log\\Logger\\FormattedtextLogger', '5.0');
JLoader::registerAlias('JLogLoggerMessagequeue',            '\\Joomla\\CMS\\Log\\Logger\\MessagequeueLogger', '5.0');
JLoader::registerAlias('JLogLoggerSyslog',                  '\\Joomla\\CMS\\Log\\Logger\\SyslogLogger', '5.0');
JLoader::registerAlias('JLogLoggerW3c',                     '\\Joomla\\CMS\\Log\\Logger\\W3cLogger', '5.0');

JLoader::registerAlias('JProfiler',                         '\\Joomla\\CMS\\Profiler\\Profiler', '5.0');

JLoader::registerAlias('JUri',                              '\\Joomla\\CMS\\Uri\\Uri', '5.0');

JLoader::registerAlias('JCache',                            '\\Joomla\\CMS\\Cache\\Cache', '5.0');
JLoader::registerAlias('JCacheController',                  '\\Joomla\\CMS\\Cache\\CacheController', '5.0');
JLoader::registerAlias('JCacheStorage',                     '\\Joomla\\CMS\\Cache\\CacheStorage', '5.0');
JLoader::registerAlias('JCacheControllerCallback',          '\\Joomla\\CMS\\Cache\\Controller\\CallbackController', '5.0');
JLoader::registerAlias('JCacheControllerOutput',            '\\Joomla\\CMS\\Cache\\Controller\\OutputController', '5.0');
JLoader::registerAlias('JCacheControllerPage',              '\\Joomla\\CMS\\Cache\\Controller\\PageController', '5.0');
JLoader::registerAlias('JCacheControllerView',              '\\Joomla\\CMS\\Cache\\Controller\\ViewController', '5.0');
JLoader::registerAlias('JCacheStorageApc',                  '\\Joomla\\CMS\\Cache\\Storage\\ApcStorage', '5.0');
JLoader::registerAlias('JCacheStorageApcu',                 '\\Joomla\\CMS\\Cache\\Storage\\ApcuStorage', '5.0');
JLoader::registerAlias('JCacheStorageHelper',               '\\Joomla\\CMS\\Cache\\Storage\\CacheStorageHelper', '5.0');
JLoader::registerAlias('JCacheStorageCachelite',            '\\Joomla\\CMS\\Cache\\Storage\\CacheliteStorage', '5.0');
JLoader::registerAlias('JCacheStorageFile',                 '\\Joomla\\CMS\\Cache\\Storage\\FileStorage', '5.0');
JLoader::registerAlias('JCacheStorageMemcached',            '\\Joomla\\CMS\\Cache\\Storage\\MemcachedStorage', '5.0');
JLoader::registerAlias('JCacheStorageMemcache',             '\\Joomla\\CMS\\Cache\\Storage\\MemcacheStorage', '5.0');
JLoader::registerAlias('JCacheStorageRedis',                '\\Joomla\\CMS\\Cache\\Storage\\RedisStorage', '5.0');
JLoader::registerAlias('JCacheStorageWincache',             '\\Joomla\\CMS\\Cache\\Storage\\WincacheStorage', '5.0');
JLoader::registerAlias('JCacheStorageXcache',               '\\Joomla\\CMS\\Cache\\Storage\\XcacheStorage', '5.0');
JLoader::registerAlias('JCacheException',                   '\\Joomla\\CMS\\Cache\\Exception\\CacheExceptionInterface', '5.0');
JLoader::registerAlias('JCacheExceptionConnecting',         '\\Joomla\\CMS\\Cache\\Exception\\CacheConnectingException', '5.0');
JLoader::registerAlias('JCacheExceptionUnsupported',        '\\Joomla\\CMS\\Cache\\Exception\\UnsupportedCacheException', '5.0');

JLoader::registerAlias('JSession',                          '\\Joomla\\CMS\\Session\\Session', '5.0');
JLoader::registerAlias('JSessionExceptionUnsupported',      '\\Joomla\\CMS\\Session\\Exception\\UnsupportedStorageException', '5.0');

JLoader::registerAlias('JUser',                             '\\Joomla\\CMS\\User\\User', '5.0');
JLoader::registerAlias('JUserHelper',                       '\\Joomla\\CMS\\User\\UserHelper', '5.0');
JLoader::registerAlias('JUserWrapperHelper',                '\\Joomla\\CMS\\User\\UserWrapper', '4.0');

JLoader::registerAlias('JForm',                             '\\Joomla\\CMS\\Form\\Form', '5.0');
JLoader::registerAlias('JFormField',                        '\\Joomla\\CMS\\Form\\FormField', '5.0');
JLoader::registerAlias('JFormHelper',                       '\\Joomla\\CMS\\Form\\FormHelper', '5.0');
JLoader::registerAlias('JFormRule',                         '\\Joomla\\CMS\\Form\\FormRule', '5.0');
JLoader::registerAlias('JFormWrapper',                      '\\Joomla\\CMS\\Form\\FormWrapper', '4.0');
JLoader::registerAlias('JFormFieldAuthor',                  '\\Joomla\\CMS\\Form\\Field\\AuthorField', '5.0');
JLoader::registerAlias('JFormFieldCaptcha',                 '\\Joomla\\CMS\\Form\\Field\\CaptchaField', '5.0');
JLoader::registerAlias('JFormFieldChromeStyle',             '\\Joomla\\CMS\\Form\\Field\\ChromestyleField', '5.0');
JLoader::registerAlias('JFormFieldContenthistory',          '\\Joomla\\CMS\\Form\\Field\\ContenthistoryField', '5.0');
JLoader::registerAlias('JFormFieldContentlanguage',         '\\Joomla\\CMS\\Form\\Field\\ContentlanguageField', '5.0');
JLoader::registerAlias('JFormFieldContenttype',             '\\Joomla\\CMS\\Form\\Field\\ContenttypeField', '5.0');
JLoader::registerAlias('JFormFieldEditor',                  '\\Joomla\\CMS\\Form\\Field\\EditorField', '5.0');
JLoader::registerAlias('JFormFieldFrontend_Language',       '\\Joomla\\CMS\\Form\\Field\\FrontendlanguageField', '5.0');
JLoader::registerAlias('JFormFieldHeadertag',               '\\Joomla\\CMS\\Form\\Field\\HeadertagField', '5.0');
JLoader::registerAlias('JFormFieldHelpsite',                '\\Joomla\\CMS\\Form\\Field\\HelpsiteField', '5.0');
JLoader::registerAlias('JFormFieldLastvisitDateRange',      '\\Joomla\\CMS\\Form\\Field\\LastvisitdaterangeField', '5.0');
JLoader::registerAlias('JFormFieldLimitbox',                '\\Joomla\\CMS\\Form\\Field\\LimitboxField', '5.0');
JLoader::registerAlias('JFormFieldMedia',                   '\\Joomla\\CMS\\Form\\Field\\MediaField', '5.0');
JLoader::registerAlias('JFormFieldMenu',                    '\\Joomla\\CMS\\Form\\Field\\MenuField', '5.0');
JLoader::registerAlias('JFormFieldMenuitem',                '\\Joomla\\CMS\\Form\\Field\\MenuitemField', '5.0');
JLoader::registerAlias('JFormFieldModuleOrder',             '\\Joomla\\CMS\\Form\\Field\\ModuleorderField', '5.0');
JLoader::registerAlias('JFormFieldModulePosition',          '\\Joomla\\CMS\\Form\\Field\\ModulepositionField', '5.0');
JLoader::registerAlias('JFormFieldModuletag',               '\\Joomla\\CMS\\Form\\Field\\ModuletagField', '5.0');
JLoader::registerAlias('JFormFieldOrdering',                '\\Joomla\\CMS\\Form\\Field\\OrderingField', '5.0');
JLoader::registerAlias('JFormFieldPlugin_Status',           '\\Joomla\\CMS\\Form\\Field\\PluginstatusField', '5.0');
JLoader::registerAlias('JFormFieldRedirect_Status',         '\\Joomla\\CMS\\Form\\Field\\RedirectStatusField', '5.0');
JLoader::registerAlias('JFormFieldRegistrationDateRange',   '\\Joomla\\CMS\\Form\\Field\\RegistrationdaterangeField', '5.0');
JLoader::registerAlias('JFormFieldStatus',                  '\\Joomla\\CMS\\Form\\Field\\StatusField', '5.0');
JLoader::registerAlias('JFormFieldTag',                     '\\Joomla\\CMS\\Form\\Field\\TagField', '5.0');
JLoader::registerAlias('JFormFieldTemplatestyle',           '\\Joomla\\CMS\\Form\\Field\\TemplatestyleField', '5.0');
JLoader::registerAlias('JFormFieldUserActive',              '\\Joomla\\CMS\\Form\\Field\\UseractiveField', '5.0');
JLoader::registerAlias('JFormFieldUserGroupList',           '\\Joomla\\CMS\\Form\\Field\\UsergrouplistField', '5.0');
JLoader::registerAlias('JFormFieldUserState',               '\\Joomla\\CMS\\Form\\Field\\UserstateField', '5.0');
JLoader::registerAlias('JFormFieldUser',                    '\\Joomla\\CMS\\Form\\Field\\UserField', '5.0');
JLoader::registerAlias('JFormRuleBoolean',                  '\\Joomla\\CMS\\Form\\Rule\\BooleanRule', '5.0');
JLoader::registerAlias('JFormRuleCalendar',                 '\\Joomla\\CMS\\Form\\Rule\\CalendarRule', '5.0');
JLoader::registerAlias('JFormRuleCaptcha',                  '\\Joomla\\CMS\\Form\\Rule\\CaptchaRule', '5.0');
JLoader::registerAlias('JFormRuleColor',                    '\\Joomla\\CMS\\Form\\Rule\\ColorRule', '5.0');
JLoader::registerAlias('JFormRuleEmail',                    '\\Joomla\\CMS\\Form\\Rule\\EmailRule', '5.0');
JLoader::registerAlias('JFormRuleEquals',                   '\\Joomla\\CMS\\Form\\Rule\\EqualsRule', '5.0');
JLoader::registerAlias('JFormRuleNotequals',                '\\Joomla\\CMS\\Form\\Rule\\NotequalsRule', '5.0');
JLoader::registerAlias('JFormRuleNumber',                   '\\Joomla\\CMS\\Form\\Rule\\NumberRule', '5.0');
JLoader::registerAlias('JFormRuleOptions',                  '\\Joomla\\CMS\\Form\\Rule\\OptionsRule', '5.0');
JLoader::registerAlias('JFormRulePassword',                 '\\Joomla\\CMS\\Form\\Rule\\PasswordRule', '5.0');
JLoader::registerAlias('JFormRuleRules',                    '\\Joomla\\CMS\\Form\\Rule\\RulesRule', '5.0');
JLoader::registerAlias('JFormRuleTel',                      '\\Joomla\\CMS\\Form\\Rule\\TelRule', '5.0');
JLoader::registerAlias('JFormRuleUrl',                      '\\Joomla\\CMS\\Form\\Rule\\UrlRule', '5.0');
JLoader::registerAlias('JFormRuleUsername',                 '\\Joomla\\CMS\\Form\\Rule\\UsernameRule', '5.0');

JLoader::registerAlias('JMicrodata',                        '\\Joomla\\CMS\\Microdata\\Microdata', '5.0');

JLoader::registerAlias('JFactory',                          '\\Joomla\\CMS\\Factory', '5.0');

JLoader::registerAlias('JMail',                             '\\Joomla\\CMS\\Mail\\Mail', '5.0');
JLoader::registerAlias('JMailHelper',                       '\\Joomla\\CMS\\Mail\\MailHelper', '5.0');
JLoader::registerAlias('JMailWrapperHelper',                '\\Joomla\\CMS\\Mail\\MailWrapper', '5.0');

JLoader::registerAlias('JClientHelper',                     '\\Joomla\\CMS\\Client\\ClientHelper', '5.0');
JLoader::registerAlias('JClientWrapperHelper',              '\\Joomla\\CMS\\Client\\ClientWrapper', '5.0');
JLoader::registerAlias('JClientFtp',                        '\\Joomla\\CMS\\Client\\FtpClient', '5.0');
JLoader::registerAlias('JFTP',                              '\\Joomla\\CMS\\Client\\FtpClient', '4.0');
JLoader::registerAlias('JClientLdap',                       '\\Joomla\\Ldap\\LdapClient', '5.0');
JLoader::registerAlias('JLDAP',                             '\\Joomla\\Ldap\\LdapClient', '4.0');

JLoader::registerAlias('JUpdate',                           '\\Joomla\\CMS\\Updater\\Update', '5.0');
JLoader::registerAlias('JUpdateAdapter',                    '\\Joomla\\CMS\\Updater\\UpdateAdapter', '5.0');
JLoader::registerAlias('JUpdater',                          '\\Joomla\\CMS\\Updater\\Updater', '5.0');
JLoader::registerAlias('JUpdaterCollection',                '\\Joomla\\CMS\\Updater\\Adapter\\CollectionAdapter', '5.0');
JLoader::registerAlias('JUpdaterExtension',                 '\\Joomla\\CMS\\Updater\\Adapter\\ExtensionAdapter', '5.0');

JLoader::registerAlias('JCrypt',                            '\\Joomla\\CMS\\Crypt\\Crypt', '5.0');
JLoader::registerAlias('JCryptCipher',                      '\\Joomla\\CMS\\Crypt\\CipherInterface', '5.0');
JLoader::registerAlias('JCryptKey',                         '\\Joomla\\CMS\\Crypt\\Key', '5.0');
JLoader::registerAlias('JCryptPassword',                    '\\Joomla\\CMS\\Crypt\\CryptPassword', '4.0');
JLoader::registerAlias('JCryptCipherBlowfish',              '\\Joomla\\CMS\\Crypt\\Cipher\\BlowfishCipher', '4.0');
JLoader::registerAlias('JCryptCipherCrypto',                '\\Joomla\\CMS\\Crypt\\Cipher\\CryptoCipher', '5.0');
JLoader::registerAlias('JCryptCipherMcrypt',                '\\Joomla\\CMS\\Crypt\\Cipher\\McryptCipher', '4.0');
JLoader::registerAlias('JCryptCipherRijndael256',           '\\Joomla\\CMS\\Crypt\\Cipher\\Rijndael256Cipher', '4.0');
JLoader::registerAlias('JCryptCipherSimple',                '\\Joomla\\CMS\\Crypt\\Cipher\\SimpleCipher', '4.0');
JLoader::registerAlias('JCryptCipher3Des',                  '\\Joomla\\CMS\\Crypt\\Cipher\\TripleDesCipher', '4.0');
JLoader::registerAlias('JCryptPasswordSimple',              '\\Joomla\\CMS\\Crypt\\Password\\SimpleCryptPassword', '4.0');

JLoader::registerAlias('JStringPunycode',                   '\\Joomla\\CMS\\String\\PunycodeHelper', '5.0');

JLoader::registerAlias('JBuffer',                           '\\Joomla\\CMS\\Utility\\BufferStreamHandler', '5.0');
JLoader::registerAlias('JUtility',                          '\\Joomla\\CMS\\Utility\\Utility', '5.0');

JLoader::registerAlias('JInputCli',                         '\\Joomla\\CMS\\Input\\Cli', '5.0');
JLoader::registerAlias('JInputCookie',                      '\\Joomla\\CMS\\Input\\Cookie', '5.0');
JLoader::registerAlias('JInputFiles',                       '\\Joomla\\CMS\\Input\\Files', '5.0');
JLoader::registerAlias('JInput',                            '\\Joomla\\CMS\\Input\\Input', '5.0');
JLoader::registerAlias('JInputJSON',                        '\\Joomla\\CMS\\Input\\Json', '5.0');

JLoader::registerAlias('JFeed',                             '\\Joomla\\CMS\\Feed\\Feed', '5.0');
JLoader::registerAlias('JFeedEntry',                        '\\Joomla\\CMS\\Feed\\FeedEntry', '5.0');
JLoader::registerAlias('JFeedFactory',                      '\\Joomla\\CMS\\Feed\\FeedFactory', '5.0');
JLoader::registerAlias('JFeedLink',                         '\\Joomla\\CMS\\Feed\\FeedLink', '5.0');
JLoader::registerAlias('JFeedParser',                       '\\Joomla\\CMS\\Feed\\FeedParser', '5.0');
JLoader::registerAlias('JFeedPerson',                       '\\Joomla\\CMS\\Feed\\FeedPerson', '5.0');
JLoader::registerAlias('JFeedParserAtom',                   '\\Joomla\\CMS\\Feed\\Parser\\AtomParser', '5.0');
JLoader::registerAlias('JFeedParserNamespace',              '\\Joomla\\CMS\\Feed\\Parser\\NamespaceParserInterface', '5.0');
JLoader::registerAlias('JFeedParserRss',                    '\\Joomla\\CMS\\Feed\\Parser\\RssParser', '5.0');
JLoader::registerAlias('JFeedParserRssItunes',              '\\Joomla\\CMS\\Feed\\Parser\\Rss\\ItunesRssParser', '5.0');
JLoader::registerAlias('JFeedParserRssMedia',               '\\Joomla\\CMS\\Feed\\Parser\\Rss\\MediaRssParser', '5.0');

JLoader::registerAlias('JImage',                            '\\Joomla\\CMS\\Image\\Image', '5.0');
JLoader::registerAlias('JImageFilter',                      '\\Joomla\\CMS\\Image\\ImageFilter', '5.0');
JLoader::registerAlias('JImageFilterBackgroundfill',        '\\Joomla\\Image\\Filter\\Backgroundfill', '5.0');
JLoader::registerAlias('JImageFilterBrightness',            '\\Joomla\\Image\\Filter\\Brightness', '5.0');
JLoader::registerAlias('JImageFilterContrast',              '\\Joomla\\Image\\Filter\\Contrast', '5.0');
JLoader::registerAlias('JImageFilterEdgedetect',            '\\Joomla\\Image\\Filter\\Edgedetect', '5.0');
JLoader::registerAlias('JImageFilterEmboss',                '\\Joomla\\Image\\Filter\\Emboss', '5.0');
JLoader::registerAlias('JImageFilterNegate',                '\\Joomla\\Image\\Filter\\Negate', '5.0');
JLoader::registerAlias('JImageFilterSketchy',               '\\Joomla\\Image\\Filter\\Sketchy', '5.0');
JLoader::registerAlias('JImageFilterSmooth',                '\\Joomla\\Image\\Filter\\Smooth', '5.0');

JLoader::registerAlias('JObject',                           '\\Joomla\\CMS\\Object\\CMSObject', '5.0');

JLoader::registerAlias('JExtensionHelper',                  '\\Joomla\\CMS\\Extension\\ExtensionHelper', '5.0');

JLoader::registerAlias('JHtml',                             '\\Joomla\\CMS\\HTML\\HTMLHelper', '5.0');

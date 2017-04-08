<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

JLoader::registerAlias('JRegistry',                         '\\Joomla\\Registry\\Registry', '4.0');
JLoader::registerAlias('JRegistryFormat',                   '\\Joomla\\Registry\\AbstractRegistryFormat', '4.0');
JLoader::registerAlias('JRegistryFormatIni',                '\\Joomla\\Registry\\Format\\Ini', '4.0');
JLoader::registerAlias('JRegistryFormatJson',               '\\Joomla\\Registry\\Format\\Json', '4.0');
JLoader::registerAlias('JRegistryFormatPhp',                '\\Joomla\\Registry\\Format\\Php', '4.0');
JLoader::registerAlias('JRegistryFormatXml',                '\\Joomla\\Registry\\Format\\Xml', '4.0');
JLoader::registerAlias('JStringInflector',                  '\\Joomla\\String\\Inflector', '4.0');
JLoader::registerAlias('JStringNormalise',                  '\\Joomla\\String\\Normalise', '4.0');
JLoader::registerAlias('JApplicationWebClient',             '\\Joomla\\Application\\Web\\WebClient', '4.0');
JLoader::registerAlias('JData',                             '\\Joomla\\Data\\DataObject', '4.0');
JLoader::registerAlias('JDataSet',                          '\\Joomla\\Data\\DataSet', '4.0');
JLoader::registerAlias('JDataDumpable',                     '\\Joomla\\Data\\DumpableInterface', '4.0');

JLoader::registerAlias('JApplicationAdministrator',         '\\Joomla\\CMS\\Application\\AdministratorApplication', '4.0');
JLoader::registerAlias('JApplicationHelper',                '\\Joomla\\CMS\\Application\\ApplicationHelper', '4.0');
JLoader::registerAlias('JApplicationBase',                  '\\Joomla\\CMS\\Application\\BaseApplication', '4.0');
JLoader::registerAlias('JApplicationCli',                   '\\Joomla\\CMS\\Application\\CliApplication', '4.0');
JLoader::registerAlias('JApplicationCms',                   '\\Joomla\\CMS\\Application\\CmsApplication', '4.0');
JLoader::registerAlias('JApplicationDaemon',                '\\Joomla\\CMS\\Application\\DaemonApplication', '4.0');
JLoader::registerAlias('JApplicationSite',                  '\\Joomla\\CMS\\Application\\SiteApplication', '4.0');
JLoader::registerAlias('JApplicationWeb',                   '\\Joomla\\CMS\\Application\\WebApplication', '4.0');
JLoader::registerAlias('JDaemon',                           '\\Joomla\\CMS\\Application\\DaemonApplication', '4.0');
JLoader::registerAlias('JCli',                              '\\Joomla\\CMS\\Application\\CliApplication', '4.0');

JLoader::registerAlias('JModelAdmin',                       '\\Joomla\\CMS\\Model\\Admin', '4.0');
JLoader::registerAlias('JModelForm',                        '\\Joomla\\CMS\\Model\\Form', '4.0');
JLoader::registerAlias('JModelItem',                        '\\Joomla\\CMS\\Model\\Item', '4.0');
JLoader::registerAlias('JModelList',                        '\\Joomla\\CMS\\Model\\ListModel', '4.0');
JLoader::registerAlias('JModelLegacy',                      '\\Joomla\\CMS\\Model\\Model', '4.0');
JLoader::registerAlias('JViewCategories',                   '\\Joomla\\CMS\\View\\Categories', '4.0');
JLoader::registerAlias('JViewCategory',                     '\\Joomla\\CMS\\View\\Category', '4.0');
JLoader::registerAlias('JViewCategoryfeed',                 '\\Joomla\\CMS\\View\\CategoryFeed', '4.0');
JLoader::registerAlias('JViewLegacy',                       '\\Joomla\\CMS\\View\\HtmlView', '4.0');
JLoader::registerAlias('JControllerAdmin',                  '\\Joomla\\CMS\\Controller\\Admin', '4.0');
JLoader::registerAlias('JControllerLegacy',                 '\\Joomla\\CMS\\Controller\\Controller', '4.0');
JLoader::registerAlias('JControllerForm',                   '\\Joomla\\CMS\\Controller\\Form', '4.0');
JLoader::registerAlias('JTableInterface',                   '\\Joomla\\CMS\\Table\\TableInterface', '4.0');
JLoader::registerAlias('JTable',                            '\\Joomla\\CMS\\Table\\Table', '4.0');
JLoader::registerAlias('JTableNested',                      '\\Joomla\\CMS\\Table\\Nested', '4.0');
JLoader::registerAlias('JTableAsset',                       '\\Joomla\\CMS\\Table\\Asset', '4.0');
JLoader::registerAlias('JTableExtension',                   '\\Joomla\\CMS\\Table\\Extension', '4.0');
JLoader::registerAlias('JTableLanguage',                    '\\Joomla\\CMS\\Table\\Language', '4.0');
JLoader::registerAlias('JTableUpdate',                      '\\Joomla\\CMS\\Table\\Update', '4.0');
JLoader::registerAlias('JTableUpdatesite',                  '\\Joomla\\CMS\\Table\\UpdateSite', '4.0');
JLoader::registerAlias('JTableUser',                        '\\Joomla\\CMS\\Table\\User', '4.0');
JLoader::registerAlias('JTableUsergroup',                   '\\Joomla\\CMS\\Table\\Usergroup', '4.0');
JLoader::registerAlias('JTableViewlevel',                   '\\Joomla\\CMS\\Table\\ViewLevel', '4.0');
JLoader::registerAlias('JTableObserver',                    '\\Joomla\\CMS\\Table\\Observer\\AbstractObserver', '4.0');
JLoader::registerAlias('JTableObserverContenthistory',      '\\Joomla\\CMS\\Table\\Observer\\ContentHistory', '4.0');
JLoader::registerAlias('JTableObserverTags',                '\\Joomla\\CMS\\Table\\Observer\\Tags', '4.0');

JLoader::registerAlias('JAccess',                           '\\Joomla\\CMS\\Access\\Access', '4.0');
JLoader::registerAlias('JAccessRule',                       '\\Joomla\\CMS\\Access\\Rule', '4.0');
JLoader::registerAlias('JAccessRules',                      '\\Joomla\\CMS\\Access\\Rules', '4.0');
JLoader::registerAlias('JAccessWrapperAccess',              '\\Joomla\\CMS\\Access\\Wrapper\\Access', '4.0');
JLoader::registerAlias('JAccessExceptionNotallowed',        '\\Joomla\\CMS\\Access\\Exception\\Notallowed', '4.0');
JLoader::registerAlias('JRule',                             '\\Joomla\\CMS\\Access\\Rule', '4.0');
JLoader::registerAlias('JRules',                            '\\Joomla\\CMS\\Access\\Rules', '4.0');

JLoader::registerAlias('JAuthenticationHelper',             '\\Joomla\\CMS\\Authentication\\AuthenticationHelper', '4.0');

JLoader::registerAlias('JHelp',                             '\\Joomla\\CMS\\Help\\Help', '4.0');
JLoader::registerAlias('JCaptcha',                          '\\Joomla\\CMS\\Captcha\\Captcha', '4.0');

JLoader::registerAlias('JLanguageAssociations',             '\\Joomla\\CMS\\Language\\Associations', '4.0');
JLoader::registerAlias('JLanguageMultilang',                '\\Joomla\\CMS\\Language\\Multilanguage', '4.0');

JLoader::registerAlias('JComponentHelper',                  '\\Joomla\\CMS\\Component\\ComponentHelper', '4.0');
JLoader::registerAlias('JComponentRecord',                  '\\Joomla\\CMS\\Component\\ComponentRecord', '4.0');
JLoader::registerAlias('JComponentExceptionMissing',        '\\Joomla\\CMS\\Component\\Exception\\MissingException', '4.0');
JLoader::registerAlias('JComponentRouterBase',              '\\Joomla\\CMS\\Component\\Router\\RouterBase', '4.0');
JLoader::registerAlias('JComponentRouterInterface',         '\\Joomla\\CMS\\Component\\Router\\RouterInterface', '4.0');
JLoader::registerAlias('JComponentRouterLegacy',            '\\Joomla\\CMS\\Component\\Router\\RouterLegacy', '4.0');
JLoader::registerAlias('JComponentRouterView',              '\\Joomla\\CMS\\Component\\Router\\RouterView', '4.0');
JLoader::registerAlias('JComponentRouterViewconfiguration', '\\Joomla\\CMS\\Component\\Router\\RouterViewConfiguration', '4.0');
JLoader::registerAlias('JComponentRouterRulesMenu',         '\\Joomla\\CMS\\Component\\Router\\Rules\\MenuRules', '4.0');
JLoader::registerAlias('JComponentRouterRulesNomenu',       '\\Joomla\\CMS\\Component\\Router\\Rules\\NomenuRules', '4.0');
JLoader::registerAlias('JComponentRouterRulesInterface',    '\\Joomla\\CMS\\Component\\Router\\Rules\\RulesInterface', '4.0');
JLoader::registerAlias('JComponentRouterRulesStandard',     '\\Joomla\\CMS\\Component\\Router\\Rules\\StandardRules', '4.0');

JLoader::registerAlias('JEditor',                           '\\Joomla\\CMS\\Editor\\Editor', '4.0');

JLoader::registerAlias('JErrorPage',                        '\\Joomla\\CMS\\Exception\\ExceptionHandler', '4.0');

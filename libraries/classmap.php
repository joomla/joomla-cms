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

JLoader::registerAlias('JClassLoader',                      '\\Joomla\\Cms\\ClassLoader\\ComposerDecorator', '4.0');

JLoader::registerAlias('JModelAdmin',                       '\\Joomla\\Cms\\Model\\Admin', '4.0');
JLoader::registerAlias('JModelForm',                        '\\Joomla\\Cms\\Model\\Form', '4.0');
JLoader::registerAlias('JModelItem',                        '\\Joomla\\Cms\\Model\\Item', '4.0');
JLoader::registerAlias('JModelList',                        '\\Joomla\\Cms\\Model\\ListModel', '4.0');
JLoader::registerAlias('JModelLegacy',                      '\\Joomla\\Cms\\Model\\Model', '4.0');
JLoader::registerAlias('JViewCategories',                   '\\Joomla\\Cms\\View\\Categories', '4.0');
JLoader::registerAlias('JViewCategory',                     '\\Joomla\\Cms\\View\\Category', '4.0');
JLoader::registerAlias('JViewCategoryfeed',                 '\\Joomla\\Cms\\View\\CategoryFeed', '4.0');
JLoader::registerAlias('JViewLegacy',                       '\\Joomla\\Cms\\View\\View', '4.0');
JLoader::registerAlias('JControllerAdmin',                  '\\Joomla\\Cms\\Controller\\Admin', '4.0');
JLoader::registerAlias('JControllerLegacy',                 '\\Joomla\\Cms\\Controller\\Controller', '4.0');
JLoader::registerAlias('JControllerForm',                   '\\Joomla\\Cms\\Controller\\Form', '4.0');
JLoader::registerAlias('JTableInterface',                   '\\Joomla\\Cms\\Table\\TableInterface', '4.0');
JLoader::registerAlias('JTable',                            '\\Joomla\\Cms\\Table\\Table', '4.0');
JLoader::registerAlias('JTableNested',                      '\\Joomla\\Cms\\Table\\Nested', '4.0');
JLoader::registerAlias('JTableAsset',                       '\\Joomla\\Cms\\Table\\Asset', '4.0');
JLoader::registerAlias('JTableExtension',                   '\\Joomla\\Cms\\Table\\Extension', '4.0');
JLoader::registerAlias('JTableLanguage',                    '\\Joomla\\Cms\\Table\\Language', '4.0');
JLoader::registerAlias('JTableUpdate',                      '\\Joomla\\Cms\\Table\\Update', '4.0');
JLoader::registerAlias('JTableUpdatesite',                  '\\Joomla\\Cms\\Table\\UpdateSite', '4.0');
JLoader::registerAlias('JTableUser',                        '\\Joomla\\Cms\\Table\\User', '4.0');
JLoader::registerAlias('JTableUsergroup',                   '\\Joomla\\Cms\\Table\\Usergroup', '4.0');
JLoader::registerAlias('JTableViewlevel',                   '\\Joomla\\Cms\\Table\\ViewLevel', '4.0');
JLoader::registerAlias('JTableObserver',                    '\\Joomla\\Cms\\Table\\Observer\\AbstractObserver', '4.0');
JLoader::registerAlias('JTableObserverContenthistory',      '\\Joomla\\Cms\\Table\\Observer\\ContentHistory', '4.0');
JLoader::registerAlias('JTableObserverTags',                '\\Joomla\\Cms\\Table\\Observer\\Tags', '4.0');

JLoader::registerAlias('JAccess',                           '\\Joomla\\Cms\\Access\\Access', '4.0');
JLoader::registerAlias('JAccessRule',                       '\\Joomla\\Cms\\Access\\Rule', '4.0');
JLoader::registerAlias('JAccessRules',                      '\\Joomla\\Cms\\Access\\Rules', '4.0');
JLoader::registerAlias('JAccessWrapperAccess',              '\\Joomla\\Cms\\Access\\Wrapper\\Access', '4.0');
JLoader::registerAlias('JAccessExceptionNotallowed',        '\\Joomla\\Cms\\Access\\Exception\\Notallowed', '4.0');
JLoader::registerAlias('JRule',                             '\\Joomla\\Cms\\Access\\Rule', '4.0');
JLoader::registerAlias('JRules',                            '\\Joomla\\Cms\\Access\\Rules', '4.0');

JLoader::registerAlias('JAuthenticationHelper',             '\\Joomla\\Cms\\Authentication\\AuthenticationHelper', '4.0');

JLoader::registerAlias('JHelp',                             '\\Joomla\\Cms\\Help\\Help', '4.0');
JLoader::registerAlias('JCaptcha',                          '\\Joomla\\Cms\\Captcha\\Captcha', '4.0');

JLoader::registerAlias('JLanguageAssociations',             '\\Joomla\\Cms\\Language\\Associations', '4.0');
JLoader::registerAlias('JLanguageMultilang',                '\\Joomla\\Cms\\Language\\Multilanguage', '4.0');

JLoader::registerAlias('JComponentHelper',                  '\\Joomla\\Cms\\Component\\ComponentHelper', '4.0');
JLoader::registerAlias('JComponentRecord',                  '\\Joomla\\Cms\\Component\\ComponentRecord', '4.0');
JLoader::registerAlias('JComponentExceptionMissing',        '\\Joomla\\Cms\\Component\\Exception\\MissingException', '4.0');
JLoader::registerAlias('JComponentRouterBase',              '\\Joomla\\Cms\\Component\\Router\\RouterBase', '4.0');
JLoader::registerAlias('JComponentRouterInterface',         '\\Joomla\\Cms\\Component\\Router\\RouterInterface', '4.0');
JLoader::registerAlias('JComponentRouterLegacy',            '\\Joomla\\Cms\\Component\\Router\\RouterLegacy', '4.0');
JLoader::registerAlias('JComponentRouterView',              '\\Joomla\\Cms\\Component\\Router\\RouterView', '4.0');
JLoader::registerAlias('JComponentRouterViewconfiguration', '\\Joomla\\Cms\\Component\\Router\\RouterViewConfiguration', '4.0');
JLoader::registerAlias('JComponentRouterRulesMenu',         '\\Joomla\\Cms\\Component\\Router\\Rules\\MenuRules', '4.0');
JLoader::registerAlias('JComponentRouterRulesNomenu',       '\\Joomla\\Cms\\Component\\Router\\Rules\\NomenuRules', '4.0');
JLoader::registerAlias('JComponentRouterRulesInterface',    '\\Joomla\\Cms\\Component\\Router\\Rules\\RulesInterface', '4.0');
JLoader::registerAlias('JComponentRouterRulesStandard',     '\\Joomla\\Cms\\Component\\Router\\Rules\\StandardRules', '4.0');

JLoader::registerAlias('JEditor',                           '\\Joomla\\Cms\\Editor\\Editor', '4.0');

JLoader::registerAlias('JErrorPage',                        '\\Joomla\\Cms\\Exception\\ExceptionHandler', '4.0');

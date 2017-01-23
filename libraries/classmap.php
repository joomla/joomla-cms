<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

JLoader::registerAlias('JRegistry',             '\\Joomla\\Registry\\Registry', '4.0');
JLoader::registerAlias('JRegistryFormat',       '\\Joomla\\Registry\\AbstractRegistryFormat', '4.0');
JLoader::registerAlias('JRegistryFormatIni',    '\\Joomla\\Registry\\Format\\Ini', '4.0');
JLoader::registerAlias('JRegistryFormatJson',   '\\Joomla\\Registry\\Format\\Json', '4.0');
JLoader::registerAlias('JRegistryFormatPhp',    '\\Joomla\\Registry\\Format\\Php', '4.0');
JLoader::registerAlias('JRegistryFormatXml',    '\\Joomla\\Registry\\Format\\Xml', '4.0');
JLoader::registerAlias('JStringInflector',      '\\Joomla\\String\\Inflector', '4.0');
JLoader::registerAlias('JStringNormalise',      '\\Joomla\\String\\Normalise', '4.0');
JLoader::registerAlias('JApplicationWebClient', '\\Joomla\\Application\\Web\\WebClient', '4.0');
JLoader::registerAlias('JData',                 '\\Joomla\\Data\\DataObject', '4.0');
JLoader::registerAlias('JDataSet',              '\\Joomla\\Data\\DataSet', '4.0');
JLoader::registerAlias('JDataDumpable',         '\\Joomla\\Data\\DumpableInterface', '4.0');

JLoader::registerAlias('JViewLegacy',           '\\Joomla\\Cms\\View\\View', '4.0');
JLoader::registerAlias('JControllerAdmin',      '\\Joomla\\Cms\\Controller\\Admin', '4.0');
JLoader::registerAlias('JControllerLegacy',     '\\Joomla\\Cms\\Controller\\Controller', '4.0');
JLoader::registerAlias('JControllerForm',       '\\Joomla\\Cms\\Controller\\Form', '4.0');

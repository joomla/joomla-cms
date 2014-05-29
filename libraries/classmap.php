<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

JLoader::registerAlias('JRegistry',           '\\Joomla\\Registry\\Registry');
JLoader::registerAlias('JRegistryFormat',     '\\Joomla\\Registry\\AbstractRegistryFormat');
JLoader::registerAlias('JRegistryFormatINI',  '\\Joomla\\Registry\\Format\\Ini');
JLoader::registerAlias('JRegistryFormatJSON', '\\Joomla\\Registry\\Format\\Json');
JLoader::registerAlias('JRegistryFormatPHP',  '\\Joomla\\Registry\\Format\\Php');
JLoader::registerAlias('JRegistryFormatXML',  '\\Joomla\\Registry\\Format\\Xml');

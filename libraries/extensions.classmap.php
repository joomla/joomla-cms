<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// No direct access.
defined('_JEXEC') or die;

// Class map of the core extensions

// com_fields
JLoader::registerAlias('FieldsPlugin',     '\\Joomla\\Component\\Fields\\Administrator\\Plugin\\FieldsPlugin', '5.0');
JLoader::registerAlias('FieldsListPlugin', '\\Joomla\\Component\\Fields\\Administrator\\Plugin\\FieldsListPlugin', '5.0');

// com_tags
JLoader::registerAlias('TagsTableTag',     '\\Joomla\\Component\\Tags\\Administrator\\Table\\TagTable', '5.0');

// com_content
JLoader::registerAlias('ContentHelper',    '\\Joomla\\Component\\Content\\Administrator\\Helper\\ContentHelper', '5.0');

// com_banners
JLoader::registerAlias('BannersHelper',    '\\Joomla\\Component\\Banners\\Administrator\\Helper\\BannersHelper', '5.0');

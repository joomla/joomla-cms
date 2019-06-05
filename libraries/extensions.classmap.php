<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// No direct access.
defined('_JEXEC') or die;

// Class map of the core extensions
JLoader::registerAlias('FieldsPlugin',     '\\Joomla\\Component\\Fields\\Administrator\\Plugin\\FieldsPlugin', '4.0');
JLoader::registerAlias('FieldsListPlugin', '\\Joomla\\Component\\Fields\\Administrator\\Plugin\\FieldsListPlugin', '4.0');

JLoader::registerAlias('PrivacyExportDomain',     '\\Joomla\\Component\\Privacy\\Administrator\\Export\\Domain', '5.0');
JLoader::registerAlias('PrivacyExportField', '\\Joomla\\Component\\Privacy\\Administrator\\Export\\Field', '5.0');
JLoader::registerAlias('PrivacyExportItem', '\\Joomla\\Component\\Privacy\\Administrator\\Export\\Item', '5.0');
JLoader::registerAlias('PrivacyPlugin', '\\Joomla\\Component\\Privacy\\Administrator\\Plugin\\PrivacyPlugin', '5.0');
JLoader::registerAlias('PrivacyRemovalStatus', '\\Joomla\\Component\\Privacy\\Administrator\\Removal\\Status', '5.0');
JLoader::registerAlias('PrivacyTableRequest', '\\Joomla\\Component\\Privacy\\Administrator\\Table\\RequestTable', '5.0');

JLoader::registerAlias('TagsTableTag',     '\\Joomla\\Component\\Tags\\Administrator\\Table\\TagTable', '4.0');

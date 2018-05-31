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

// Component com_fields
JLoader::registerAlias('FieldsPlugin',     '\\Joomla\\Component\\Fields\\Administrator\\Plugin\\FieldsPlugin', '5.0');
JLoader::registerAlias('FieldsListPlugin', '\\Joomla\\Component\\Fields\\Administrator\\Plugin\\FieldsListPlugin', '5.0');

// Component com_tags
JLoader::registerAlias('TagsTableTag',     '\\Joomla\\Component\\Tags\\Administrator\\Table\\TagTable', '5.0');

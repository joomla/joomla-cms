<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the com_contenthistory language files, default to the admin file and fall back to site if one isn't found
$lang = JFactory::getLanguage();

/**
 * Note: Do NOT combine these lines with a Boolean Or (||) operator. That causes the default
 *       language (en-GB) files to only be loaded from the first directory that has a (partial)
 *       translation, leading to untranslated strings. See gh-17372 for context of this issue.
 */
$lang->load('com_contenthistory', JPATH_SITE, null, false, true);
$lang->load('com_contenthistory', JPATH_ADMINISTRATOR, null, false, true);

// Hand processing over to the admin base file
require_once JPATH_COMPONENT_ADMINISTRATOR . '/contenthistory.php';

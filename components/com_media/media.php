<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the com_media language files, default to the admin file and fall back to site if one isn't found
$lang = JFactory::getLanguage();
$lang->load('com_media', JPATH_ADMINISTRATOR, null, false, true)
||	$lang->load('com_media', JPATH_SITE, null, false, true);

// Hand processing over to the admin base file
require_once JPATH_COMPONENT_ADMINISTRATOR . '/media.php';

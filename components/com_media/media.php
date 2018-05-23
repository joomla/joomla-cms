<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

// Load the com_media language files, default to the admin file and fall back to site if one isn't found
$lang = Factory::getLanguage();

$lang->load('com_media', JPATH_ADMINISTRATOR);

// Hand processing over to the admin base file
require_once JPATH_COMPONENT_ADMINISTRATOR . '/media.php';

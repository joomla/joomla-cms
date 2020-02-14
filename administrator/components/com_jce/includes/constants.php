<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('_JEXEC') or die('RESTRICTED');

// Some shortcuts to make life easier
define('WF_VERSION', '2.7.14');

// JCE Administration Component
define('WF_ADMINISTRATOR',     JPATH_ADMINISTRATOR.'/components/com_jce');
// JCE Site Component
define('WF_SITE',              JPATH_SITE.'/components/com_jce');
// JCE Plugin
if (defined('JPATH_PLATFORM')) {
    define('WF_PLUGIN',        JPATH_SITE.'/plugins/editors/jce');
} else {
    define('WF_PLUGIN',        JPATH_SITE.'/plugins/editors');
}
// JCE Editor
define('WF_EDITOR',            WF_SITE.'/editor');
// JCE Editor Plugins
define('WF_EDITOR_PLUGINS',    WF_EDITOR.'/tiny_mce/plugins');
// JCE Editor Themes
define('WF_EDITOR_THEMES',     WF_EDITOR.'/tiny_mce/themes');
// JCE Editor Libraries
define('WF_EDITOR_LIBRARIES',  WF_EDITOR.'/libraries');
// JCE Editor Classes
define('WF_EDITOR_CLASSES',    WF_EDITOR_LIBRARIES.'/classes');
// JCE Editor Extensions
define('WF_EDITOR_EXTENSIONS', WF_EDITOR.'/extensions');

define('WF_EDITOR_URI', JURI::root(true) . '/components/com_jce/editor');

define('WF_EDITOR_PRO', '0');

// required for some plugins
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

<?php

/**
 * @package    Joomla.Site
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

// Define JPATH constants if not defined yet
\defined('JPATH_BASE') || \define('JPATH_BASE', \dirname(__DIR__));
\defined('JPATH_ROOT') || \define('JPATH_ROOT', JPATH_BASE);
\defined('JPATH_SITE') || \define('JPATH_SITE', JPATH_ROOT);
\defined('JPATH_PUBLIC') || \define('JPATH_PUBLIC', JPATH_ROOT);
\defined('JPATH_CONFIGURATION') || \define('JPATH_CONFIGURATION', JPATH_ROOT);
\defined('JPATH_ADMINISTRATOR') || \define('JPATH_ADMINISTRATOR', JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator');
\defined('JPATH_LIBRARIES') || \define('JPATH_LIBRARIES', JPATH_ROOT . DIRECTORY_SEPARATOR . 'libraries');
\defined('JPATH_PLUGINS') || \define('JPATH_PLUGINS', JPATH_ROOT . DIRECTORY_SEPARATOR . 'plugins');
\defined('JPATH_INSTALLATION') || \define('JPATH_INSTALLATION', JPATH_ROOT . DIRECTORY_SEPARATOR . 'installation');
\defined('JPATH_THEMES') || \define('JPATH_THEMES', JPATH_BASE . DIRECTORY_SEPARATOR . 'templates');
\defined('JPATH_CACHE') || \define('JPATH_CACHE', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'cache');
\defined('JPATH_MANIFESTS') || \define('JPATH_MANIFESTS', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'manifests');
\defined('JPATH_API') || \define('JPATH_API', JPATH_ROOT . DIRECTORY_SEPARATOR . 'api');
\defined('JPATH_CLI') || \define('JPATH_CLI', JPATH_ROOT . DIRECTORY_SEPARATOR . 'cli');

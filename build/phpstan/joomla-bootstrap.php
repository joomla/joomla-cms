<?php

/**
 * @package    Joomla.Build
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

\define('_JEXEC', 1);
\define('JPATH_PLATFORM', 1);
\define('JPATH_BASE', \dirname(__DIR__, 2));

// Load the Joomla environment
require_once JPATH_BASE . '/includes/defines.php';

// Load the Joomla class loader
require_once JPATH_LIBRARIES . '/loader.php';
JLoader::setup();
require_once JPATH_LIBRARIES . '/vendor/autoload.php';

// Load the extension namespaces
JLoader::register('JNamespacePsr4Map', JPATH_LIBRARIES . '/namespacemap.php');
$map = new JNamespacePsr4Map();
$map->load();

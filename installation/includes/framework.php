<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Application
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

// Ensure sensible default for JDEBUG is set.
const JDEBUG = false;

// Import the Joomla Platform.
require_once JPATH_LIBRARIES . '/bootstrap.php';

// If debug mode enabled, set new Exception handler with debug enabled.
if (JDEBUG) {
    $errorHandler->setExceptionHandler(
        [
            new \Symfony\Component\ErrorHandler\ErrorHandler(null, true),
            'renderException',
        ]
    );
}

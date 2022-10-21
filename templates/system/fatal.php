<?php

/**
 * @package     Joomla.Site
 * @subpackage  Template.system
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;

/**
 * Note: This file is only used when unrecoverable errors happen,
 * normally at boot up stages, and therefore it cannot be assumed
 * that any part of Joomla is available (Eg: a Factory or application)
 *
 * For "normal" error handling, use error.php not this file.
 *
 * @var  HtmlErrorRenderer  $this       object containing charset
 * @var  string             $statusText exception error message
 * @var  string             $statusCode exception error code
 */

// Fallback template
$template = '{{statusCode_statusText}}';

// Joomla supplied fatal error page
if (file_exists(__DIR__ . '/fatal-error.html')) {
    $template = file_get_contents(__DIR__ . '/fatal-error.html');
}

/**
 * User supplied fatal error page.
 *
 * Allow overriding Joomla supplied page to prevent changes being wiped on Joomla upgrade.
 * We allow it to be a PHP file so that any post-processing, alerting etc can happen.
 */
if (file_exists(__DIR__ . '/fatal-error.custom.php')) {
    require __DIR__ . '/fatal-error.custom.php';

    return;
}

echo str_replace(
    ['{{statusCode_statusText}}', '{{statusCode}}', '{{statusText}}'],
    [$statusCode . ' - ' . $statusText, $statusCode, $statusText],
    $template
);

<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 * @version     14-Nov-19
 * @copyright   Copyright(c) 2016-2019 Troy T. Hall
 * @license     GPL2
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
/** @var string $theme */
/** @var string $customCSS */

// load J! icon set
HTMLHelper::_('stylesheet', 'media/jui/css/icomoon.css');

// Normalize is dumb so load it separately
HTMLHelper::_('stylesheet', 'libraries/normalize/normalize.css', $HTMLHelperDebug);

// BS helper for navigation

// Bootstrap & default theme are included in template now
HTMLHelper::_('stylesheet', 'template.min.css', $HTMLHelperDebug);

// Jasny
//HTMLHelper::_( 'stylesheet', 'libraries/jasny/jasny-bootstrap.min.css', $HTMLHelperDebug);

// BS custom theme
HTMLHelper::_('stylesheet', $theme . '.css', $HTMLHelperDebug );


if ( $customCSS )
{
	HTMLHelper::_( 'stylesheet', 'custom.css', $HTMLHelperDebug );
}
// END OF CSS

// Add JavaScript Frameworks
HTMLHelper::_( 'bootstrap.framework' );
HTMLHelper::_( 'script', 'isotope.pkgd.min.js', $HTMLHelperDebug );
HTMLHelper::_( 'script', 'template.js', $HTMLHelperDebug );

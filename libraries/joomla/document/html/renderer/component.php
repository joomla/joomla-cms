<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDocumentRendererComponent is deprecated, use JDocumentRendererHtmlComponent instead.', JLog::WARNING, 'deprecated');

/**
 * Component renderer
 *
 * @since       11.1
 * @deprecated  4.0  Use JDocumentRendererHtmlComponent instead
 */
class JDocumentRendererComponent extends JDocumentRendererHtmlComponent
{
}

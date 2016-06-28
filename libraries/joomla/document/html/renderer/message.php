<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDocumentRendererMessage is deprecated, use JDocumentRendererHtmlMessage instead.', JLog::WARNING, 'deprecated');

/**
 * JDocument system message renderer
 *
 * @since       11.1
 * @deprecated  4.0  Use JDocumentRendererHtmlMessage instead
 */
class JDocumentRendererMessage extends JDocumentRendererHtmlMessage
{
}

<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDocumentRendererHead is deprecated, use JDocumentRendererHtmlHead instead.', JLog::WARNING, 'deprecated');

/**
 * JDocument head renderer
 *
 * @since       11.1
 * @deprecated  4.0  Use JDocumentRendererHtmlHead instead
 */
class JDocumentRendererHead extends JDocumentRendererHtmlHead
{
}

<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDocumentRendererModules is deprecated, use JDocumentRendererHtmlModules instead.', JLog::WARNING, 'deprecated');

/**
 * JDocument Modules renderer
 *
 * @since       11.1
 * @deprecated  4.0  Use JDocumentRendererHtmlModules instead
 */
class JDocumentRendererModules extends JDocumentRendererHtmlModules
{
}

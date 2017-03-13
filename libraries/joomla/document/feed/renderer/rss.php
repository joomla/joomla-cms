<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDocumentRendererRSS is deprecated, use JDocumentRendererFeedRss instead.', JLog::WARNING, 'deprecated');

/**
 * JDocumentRendererRSS is a feed that implements RSS 2.0 Specification
 *
 * @see         http://www.rssboard.org/rss-specification
 * @since       11.1
 * @deprecated  4.0  Use JDocumentRendererFeedRss instead
 */
class JDocumentRendererRSS extends JDocumentRendererFeedRss
{
}

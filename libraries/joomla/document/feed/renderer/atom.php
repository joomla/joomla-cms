<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDocumentRendererAtom is deprecated, use JDocumentRendererFeedAtom instead.', JLog::WARNING, 'deprecated');

/**
 * JDocumentRendererAtom is a feed that implements the atom specification
 *
 * Please note that just by using this class you won't automatically
 * produce valid atom files. For example, you have to specify either an editor
 * for the feed or an author for every single feed item.
 *
 * @link        http://www.atomenabled.org/developers/syndication/atom-format-spec.php
 * @since       11.1
 * @deprecated  4.0  Use JDocumentRendererFeedAtom instead
 */
class JDocumentRendererAtom extends JDocumentRendererFeedAtom
{
}

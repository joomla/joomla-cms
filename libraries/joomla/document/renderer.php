<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\RendererInterface;

/**
 * Abstract class for a renderer
 *
 * @since  11.1
 */
abstract class JDocumentRenderer implements RendererInterface
{
	/**
	 * Reference to the JDocument object that instantiated the renderer
	 *
	 * @var    JDocument
	 * @since  11.1
	 */
	protected $_doc = null;

	/**
	 * Renderer mime type
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_mime = 'text/html';

	/**
	 * Class constructor
	 *
	 * @param   JDocument  $doc  A reference to the JDocument object that instantiated the renderer
	 *
	 * @since   11.1
	 */
	public function __construct(JDocument $doc)
	{
		$this->_doc = $doc;
	}

	/**
	 * Return the content type of the renderer
	 *
	 * @return  string  The contentType
	 *
	 * @since   11.1
	 */
	public function getContentType()
	{
		return $this->_mime;
	}

	/**
	 * Convert links in a text from relative to absolute
	 *
	 * @param   string  $text  The text processed
	 *
	 * @return  string   Text with converted links
	 *
	 * @since   11.1
	 */
	protected function _relToAbs($text)
	{
		$base = JUri::base();
		$text = preg_replace("/(href|src)=\"(?!http|ftp|https|mailto|data|\/\/)([^\"]*)\"/", "$1=\"$base\$2\"", $text);

		return $text;
	}
}

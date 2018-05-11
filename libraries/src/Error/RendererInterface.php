<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Error;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\Document;

/**
 * Interface defining the rendering engine for the error handling layer
 *
 * @since  4.0
 */
interface RendererInterface
{
	/**
	 * Retrieve the Document instance attached to this renderer
	 *
	 * @return  Document
	 *
	 * @since   4.0
	 */
	public function getDocument(): Document;

	/**
	 * Render the error page for the given object
	 *
	 * @param   \Throwable  $error  The error object to be rendered
	 *
	 * @return  string
	 *
	 * @since   4.0
	 */
	public function render(\Throwable $error): string;
}

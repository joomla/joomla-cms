<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Error;

/**
 * Interface defining the rendering engine for the error handling layer
 *
 * @since  4.0
 */
interface RendererInterface
{
	/**
	 * Retrieve the JDocument instance attached to this renderer
	 *
	 * @return  \JDocument
	 *
	 * @since   4.0
	 */
	public function getDocument();

	/**
	 * Render the error page for the given object
	 *
	 * @param   \Throwable|\Exception  $error  The error object to be rendered
	 *
	 * @return  string
	 *
	 * @since   4.0
	 * @throws  \InvalidArgumentException if a non-Throwable object was provided
	 */
	public function render($error);
}

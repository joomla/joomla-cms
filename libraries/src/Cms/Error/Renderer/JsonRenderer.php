<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Error\Renderer;

use Joomla\Cms\Error\AbstractRenderer;

/**
 * JSON error page renderer
 *
 * @since  4.0
 */
class JsonRenderer extends AbstractRenderer
{
	/**
	 * The format (type) of the error page
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $type = 'json';

	/**
	 * Render the error page for the given object
	 *
	 * @param   \Throwable|\Exception  $error  The error object to be rendered
	 *
	 * @return  string
	 *
	 * @since   4.0
	 */
	protected function doRender($error)
	{
		// Create our data object to be rendered
		$data = [
			'error'   => true,
			'code'    => $error->getCode(),
			'message' => $error->getMessage(),
		];

		// Include the stack trace if in debug mode
		if (JDEBUG)
		{
			$data['trace'] = $error->getTraceAsString();
		}

		// Push the data object into the document
		$this->getDocument()->setBuffer(json_encode($data));

		if (ob_get_contents())
		{
			ob_end_clean();
		}

		return $this->getDocument()->render();
	}
}

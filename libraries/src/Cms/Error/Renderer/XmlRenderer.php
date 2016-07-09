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
 * XML error page renderer
 *
 * @since  4.0
 */
class XmlRenderer extends AbstractRenderer
{
	/**
	 * The format (type) of the error page
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $type = 'xml';

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
		$xw = new \XMLWriter;
		$xw->openMemory();
		$xw->setIndent(true);
		$xw->setIndentString("\t");
		$xw->startDocument('1.0', 'UTF-8');

		$xw->startElement('error');

		$xw->writeElement('code', $error->getCode());
		$xw->writeElement('message', $error->getMessage());

		// Include the stack trace if in debug mode
		if (JDEBUG)
		{
			$xw->writeElement('trace', $error->getTraceAsString());
		}

		// End error element
		$xw->endElement();

		// Push the data object into the document
		$this->getDocument()->setBuffer($xw->outputMemory(true));

		if (ob_get_contents())
		{
			ob_end_clean();
		}

		return $this->getDocument()->render();
	}
}
